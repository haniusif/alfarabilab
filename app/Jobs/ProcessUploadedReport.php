<?php

namespace App\Jobs;

use App\Enums\FileStatus;
use App\Models\Patient;
use App\Models\PatientFile;
use App\Models\User;
use App\Services\Extraction\ReportExtractor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * يعالج ملفاً مرفوعاً من الطبيب:
 * 1) يقرأ التقرير عبر مستخرِج البيانات (Tesseract أو Gemini حسب OCR_DRIVER)
 * 2) ينشئ/يربط المريض عبر رقم الجوال (التوابع)
 * 3) ينشئ الملف (task) ويُسنده للطبيب الذي رفعه
 */
class ProcessUploadedReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(
        public int $patientFileId,   // ملف مبدئي أُنشئ بحالة "قيد المعالجة"
        public ?int $doctorId,       // الطبيب المُسند — أو null لإبقائه في قائمة الانتظار
        public ?int $actorId = null, // من نفّذ الرفع (المعمل أو الطبيب) — للسجل
    ) {}

    public function handle(ReportExtractor $extractor): void
    {
        $file = PatientFile::findOrFail($this->patientFileId);

        $fields = $extractor->extract($file->source_path, $file->source_type);

        // ربط المريض بالعائلة عبر رقم الجوال
        $patient = Patient::findOrCreateByMobile([
            'name'          => $fields['name'] ?? 'غير معروف',
            'mobile'        => $fields['mobile'] ?? '',
            'membership_no' => $fields['patient_ref_no'] ?? ($fields['patient_external_id'] ?? ''),
            'national_id'   => $fields['patient_external_id'] ?? '',
        ]);

        // مُسند لطبيب، أو يبقى "جديد" في قائمة الانتظار إن لم يُحدَّد طبيب
        $assigned = $this->doctorId !== null;
        $status   = $assigned ? FileStatus::Assigned : FileStatus::New;

        $file->update([
            'patient_id'          => $patient->id,
            'doctor_id'           => $this->doctorId,
            'patient_external_id' => $fields['patient_external_id'] ?? null,
            'referral_doctor'     => $fields['referral_doctor'] ?? null,
            'age_gender'          => $fields['age_gender'] ?? null,
            'accession_no'        => $fields['accession_no'] ?? null,
            'report_status'       => $fields['report_status'] ?? null,
            'patient_ref_no'      => $fields['patient_ref_no'] ?? null,
            'test_name'           => $fields['test_name'] ?? null,
            'result'              => $fields['result'] ?? null,
            'unit'                => $fields['unit'] ?? null,
            'reference_range'     => $fields['reference_range'] ?? null,
            'raw_extraction'      => $fields,
            'status'              => $status->value,
        ]);

        $file->statusLogs()->create([
            'user_id'     => $this->actorId ?? $this->doctorId,
            'from_status' => FileStatus::New->value,
            'to_status'   => $status->value,
            'note'        => $assigned
                ? 'استُخرج تلقائياً من الملف المرفوع'
                : 'استُخرج تلقائياً — بانتظار الإسناد',
        ]);
    }

    public function failed(Throwable $e): void
    {
        // عند الفشل: يبقى الملف للمراجعة/الإدخال اليدوي بدل أن يضيع
        PatientFile::where('id', $this->patientFileId)
            ->update(['report_status' => 'فشل الاستخراج — يتطلب إدخالاً يدوياً']);
    }
}
