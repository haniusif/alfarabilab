<?php

namespace App\Http\Controllers;

use App\Enums\FileStatus;
use App\Jobs\ProcessUploadedReport;
use App\Models\Patient;
use App\Models\PatientFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DoctorFileController extends Controller
{
    /** لوحة الطبيب: ملفاته مُجمّعة حسب الحالة */
    public function index(Request $request)
    {
        $doctor = $request->user();

        return response()->json([
            'pending'   => PatientFile::forDoctor($doctor->id)->pending()->with('patient')->get(),
            'due_today' => PatientFile::forDoctor($doctor->id)->dueToday()->with('patient')->get(),
            'no_reply'  => PatientFile::forDoctor($doctor->id)->where('status', FileStatus::NoReply->value)->with('patient')->get(),
            'explained' => PatientFile::forDoctor($doctor->id)->where('status', FileStatus::Explained->value)->with('patient')->get(),
        ]);
    }

    /**
     * رفع صورة/PDF. ملف واحد يُقرأ مباشرةً (تزامنياً) وتُعاد نتيجته،
     * وأكثر من ملف يُعالَج في الخلفية عبر الطابور.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'files'   => ['required', 'array', 'min:1'],
            'files.*' => ['file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
        ]);

        $doctorId = $request->user()->id;

        $records = collect($request->file('files'))->map(function ($uploaded) use ($doctorId) {
            return PatientFile::create([
                'patient_id'  => $this->placeholderPatientId(),
                'doctor_id'   => $doctorId,
                'source_path' => $uploaded->store('reports'),
                'source_type' => strtolower($uploaded->getClientOriginalExtension()) === 'pdf' ? 'pdf' : 'image',
                'status'      => FileStatus::New->value,
            ]);
        });

        // ملف واحد: اقرأه الآن وأعِد النتيجة
        if ($records->count() === 1) {
            $file = $records->first();

            try {
                ProcessUploadedReport::dispatchSync($file->id, $doctorId);
            } catch (\Throwable $e) {
                report($e);
                $file->update(['report_status' => 'فشل الاستخراج — يتطلب إدخالاً يدوياً']);

                return response()->json([
                    'message' => 'تعذّرت القراءة — يتطلب إدخالاً يدوياً',
                    'file'    => $file->fresh()->load('patient'),
                ], 422);
            }

            return response()->json($file->fresh()->load('patient'), 201);
        }

        // عدة ملفات: في الخلفية
        $records->each(fn ($file) => ProcessUploadedReport::dispatch($file->id, $doctorId));

        return response()->json([
            'message'  => 'تم الرفع — جاري قراءة الملفات في الخلفية',
            'file_ids' => $records->pluck('id'),
        ], 202);
    }

    /** إدخال يدوي احتياطي (إن فشلت القراءة أو الصورة غير واضحة) */
    public function storeManual(Request $request)
    {
        $data = $request->validate([
            'name'            => ['required', 'string'],
            'mobile'          => ['required', 'string'],
            'membership_no'   => ['required', 'string'],
            'national_id'     => ['required', 'string'],
            'test_name'       => ['nullable', 'string'],
            'result'          => ['nullable', 'string'],
            'unit'            => ['nullable', 'string'],
            'reference_range' => ['nullable', 'string'],
        ]);

        $patient = Patient::findOrCreateByMobile($data);

        $file = PatientFile::create([
            'patient_id'      => $patient->id,
            'doctor_id'       => $request->user()->id,
            'test_name'       => $data['test_name'] ?? null,
            'result'          => $data['result'] ?? null,
            'unit'            => $data['unit'] ?? null,
            'reference_range' => $data['reference_range'] ?? null,
            'status'          => FileStatus::Assigned->value,
        ]);

        return response()->json($file->load('patient'), 201);
    }

    /** عرض ملف مع أفراد العائلة (نفس رقم الجوال) */
    public function show(PatientFile $file)
    {
        $file->load('patient', 'statusLogs.user');

        return response()->json([
            'file'   => $file,
            'family' => $file->patient->familyMembers()
                ->load('files'),
        ]);
    }

    /** تحديث الحالة بعد المكالمة */
    public function updateStatus(Request $request, PatientFile $file)
    {
        $data = $request->validate([
            'action'      => ['required', 'in:explained,no_reply,deferred'],
            'note'        => ['nullable', 'string'],
            'deferred_to' => ['required_if:action,deferred', 'nullable', 'date'],
        ]);

        $actor = $request->user();

        match ($data['action']) {
            'explained' => $file->markExplained($actor, $data['note'] ?? null),
            'no_reply'  => $file->markNoReply($actor),
            'deferred'  => $file->defer(new \DateTime($data['deferred_to']), $actor, $data['note'] ?? null),
        };

        return response()->json($file->fresh());
    }

    private function placeholderPatientId(): int
    {
        return Patient::firstOrCreate(
            ['national_id' => 'PENDING'],
            ['name' => 'قيد المعالجة', 'mobile' => '0', 'membership_no' => '0', 'is_head' => true]
        )->id;
    }
}
