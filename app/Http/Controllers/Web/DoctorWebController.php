<?php

namespace App\Http\Controllers\Web;

use App\Enums\FileStatus;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessUploadedReport;
use App\Models\Patient;
use App\Models\PatientFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DoctorWebController extends Controller
{
    /** يعرض/ينزّل الملف الأصلي المرفوع (صورة/PDF) لطبيبه فقط */
    public function source(Request $request, PatientFile $file)
    {
        abort_unless($file->doctor_id === $request->user()->id, 403, __('This file is not assigned to you'));
        abort_unless($file->source_path && Storage::exists($file->source_path), 404);

        return Storage::response($file->source_path);
    }

    public function index(Request $request)
    {
        $doctorId = $request->user()->id;

        // ملخّص الأعداد لكل حالة (للقمة) — نستثني «جديد» لأنه يخصّ ما لم يُسند بعد (لا يعني الطبيب)
        $counts = PatientFile::forDoctor($doctorId)
            ->where('status', '!=', FileStatus::New->value)
            ->selectRaw('status, count(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        // قائمة العمل النشطة فقط — الملفات المغلقة في صفحة «كل الملفات»
        $groups = [
            'due_today' => PatientFile::forDoctor($doctorId)->dueToday()->with('patient')->get(),
            'pending'   => PatientFile::forDoctor($doctorId)->pending()->with('patient')->latest()->get(),
            'deferred'  => PatientFile::forDoctor($doctorId)
                ->where('status', FileStatus::Deferred->value)
                ->whereDate('deferred_to', '>', now())
                ->with('patient')->get(),
            'no_reply'  => PatientFile::forDoctor($doctorId)->where('status', FileStatus::NoReply->value)->with('patient')->get(),
        ];

        // أرقام الجوال التي تنتمي إلى عائلة (أكثر من مريض) — لإظهار شارة «رئيسي» فقط حين يهم
        $mobiles = collect($groups)->flatten(1)->pluck('patient.mobile')->filter()->unique();
        $familyMobiles = $mobiles->isEmpty()
            ? collect()
            : Patient::whereIn('mobile', $mobiles)
                ->selectRaw('mobile, count(*) as c')
                ->groupBy('mobile')->having('c', '>', 1)
                ->pluck('mobile')->flip();

        return view('doctor.index', compact('groups', 'counts', 'familyMobiles'));
    }

    /** كل ملفات الطبيب — مع بحث وتصفية وتقسيم صفحات */
    public function files(Request $request)
    {
        $files = PatientFile::forDoctor($request->user()->id)
            ->with('patient')
            ->tap(fn ($q) => $this->applyFilters($q, $request))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('doctor.files', ['files' => $files]);
    }

    /** الملفات المكررة: تشترك في نفس رقم Accession */
    public function duplicates(Request $request)
    {
        $doctorId = $request->user()->id;

        $dupAccessions = PatientFile::forDoctor($doctorId)
            ->whereNotNull('accession_no')->where('accession_no', '!=', '')
            ->groupBy('accession_no')
            ->havingRaw('count(*) > 1')
            ->pluck('accession_no');

        $groups = PatientFile::forDoctor($doctorId)
            ->whereIn('accession_no', $dupAccessions)
            ->with('patient')
            ->orderBy('accession_no')->latest()
            ->get()
            ->groupBy('accession_no');

        return view('doctor.duplicates', ['groups' => $groups]);
    }

    /** نقل ملف إلى سلة المحذوفات (حذف مؤقّت — يمكن استعادته) */
    public function destroy(Request $request, PatientFile $file)
    {
        abort_unless($file->doctor_id === $request->user()->id, 403, __('This file is not assigned to you'));

        $file->delete();

        return redirect()->route('doctor.trash')->with('status', __('File moved to trash'));
    }

    /** قائمة الملفات المحذوفة حذفاً مؤقتاً للطبيب */
    public function trash(Request $request)
    {
        $files = PatientFile::onlyTrashed()
            ->forDoctor($request->user()->id)
            ->with('patient')
            ->orderByDesc('deleted_at')
            ->paginate(15)
            ->withQueryString();

        return view('doctor.trash', ['files' => $files]);
    }

    /** استعادة ملف محذوف مؤقتاً */
    public function restore(Request $request, int $file)
    {
        $patientFile = PatientFile::onlyTrashed()->findOrFail($file);

        abort_unless($patientFile->doctor_id === $request->user()->id, 403, __('This file is not assigned to you'));

        $patientFile->restore();

        return back()->with('status', __('File restored from trash'));
    }

    /** حذف نهائي — يُزيل السجل والملف الأصلي على القرص */
    public function forceDestroy(Request $request, int $file)
    {
        $patientFile = PatientFile::onlyTrashed()->findOrFail($file);

        abort_unless($patientFile->doctor_id === $request->user()->id, 403, __('This file is not assigned to you'));

        if ($patientFile->source_path && Storage::exists($patientFile->source_path)) {
            Storage::delete($patientFile->source_path);
        }

        $patientFile->forceDelete();

        return back()->with('status', __('File permanently deleted'));
    }

    public function show(Request $request, PatientFile $file)
    {
        abort_unless($file->doctor_id === $request->user()->id, 403, __('This file is not assigned to you'));

        $file->load('patient', 'statusLogs.user', 'insuranceCompany');

        $family = $file->patient
            ? $file->patient->familyMembers()->load('files')
            : collect();

        // ملفات العائلة (نفس الجوال) المُسندة لهذا الطبيب وما زالت مفتوحة — قابلة لتطبيق نفس الإجراء عليها
        $familyOpenFiles = $file->patient
            ? PatientFile::forDoctor($file->doctor_id)
                ->where('id', '!=', $file->id)
                ->whereHas('patient', fn ($p) => $p->where('mobile', $file->patient->mobile))
                ->open()
                ->with('patient')->get()
            : collect();

        $duplicates = $file->accession_no
            ? PatientFile::forDoctor($file->doctor_id)
                ->where('accession_no', $file->accession_no)
                ->where('id', '!=', $file->id)
                ->with('patient')->get()
            : collect();

        return view('doctor.show', compact('file', 'family', 'duplicates', 'familyOpenFiles'));
    }

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

        // ملف واحد: اقرأه مباشرة (تزامنياً) وأظهر النتيجة فوراً
        if ($records->count() === 1) {
            $file = $records->first();

            try {
                ProcessUploadedReport::dispatchSync($file->id, $doctorId);
            } catch (\Throwable $e) {
                report($e);
                $file->update(['report_status' => 'فشل الاستخراج — يتطلب إدخالاً يدوياً']);

                return redirect()->route('doctor.show', $file)
                    ->with('status', __('Could not read the file automatically — please enter the data manually'));
            }

            return redirect()->route('doctor.show', $file->fresh())
                ->with('status', __('File read and added'));
        }

        // عدة ملفات: عالِجها في الخلفية عبر الطابور
        $records->each(fn ($file) => ProcessUploadedReport::dispatch($file->id, $doctorId));

        return redirect()->route('doctor.index')
            ->with('status', __(':count files uploaded — reading them in the background', ['count' => $records->count()]));
    }

    public function manual(Request $request)
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

        return redirect()->route('doctor.show', $file)
            ->with('status', __('File added manually'));
    }

    /** تعيين أحد أفراد العائلة (نفس الجوال) رأساً للعائلة — الملف الرئيسي */
    public function markMain(Request $request, Patient $patient)
    {
        $hasFile = PatientFile::forDoctor($request->user()->id)
            ->where('patient_id', $patient->id)
            ->exists();

        abort_unless($hasFile, 403, __('This patient is not in your files'));

        $patient->promoteToHead();

        return back()->with('status', __(':name marked as the main family member', ['name' => $patient->name]));
    }

    public function status(Request $request, PatientFile $file)
    {
        abort_unless($file->doctor_id === $request->user()->id, 403, __('This file is not assigned to you'));

        $data = $request->validate([
            'action'          => ['required', 'in:explained,no_reply,deferred'],
            'note'            => ['nullable', 'string'],
            'deferred_to'     => ['required_if:action,deferred', 'nullable', 'date'],
            'apply_to_family' => ['nullable', 'boolean'],
        ]);

        $actor = $request->user();

        // الملف الأصلي + (اختيارياً) ملفات العائلة المفتوحة المُسندة لهذا الطبيب
        $targets = collect([$file]);

        if (! empty($data['apply_to_family']) && $file->patient) {
            $targets = $targets->concat(
                PatientFile::forDoctor($actor->id)
                    ->where('id', '!=', $file->id)
                    ->whereHas('patient', fn ($p) => $p->where('mobile', $file->patient->mobile))
                    ->open()->get()
            );
        }

        $deferredAt = ! empty($data['deferred_to']) ? new \DateTime($data['deferred_to']) : null;

        foreach ($targets as $target) {
            match ($data['action']) {
                'explained' => $target->markExplained($actor, $data['note'] ?? null),
                'no_reply'  => $target->markNoReply($actor),
                'deferred'  => $target->defer($deferredAt, $actor, $data['note'] ?? null),
            };
        }

        $msg = $targets->count() > 1
            ? __(':n files updated', ['n' => $targets->count()])
            : __('File status updated');

        return back()->with('status', $msg);
    }

    private function applyFilters($query, Request $request): void
    {
        if ($q = trim((string) $request->input('q'))) {
            $query->whereHas('patient', function ($p) use ($q) {
                $p->where('name', 'like', "%{$q}%")->orWhere('mobile', 'like', "%{$q}%");
            });
        }

        if (($status = $request->input('status')) && FileStatus::tryFrom($status)) {
            $query->where('status', $status);
        }

        // تصفية حسب موعد المتابعة (deferred_to): اليوم / غدًا / القادمة / السابقة
        match ($request->input('when')) {
            'today'    => $query->whereDate('deferred_to', today()),
            'tomorrow' => $query->whereDate('deferred_to', today()->addDay()),
            'upcoming' => $query->whereDate('deferred_to', '>', today()->addDay()),
            'past'     => $query->whereNotNull('deferred_to')->whereDate('deferred_to', '<', today()),
            default    => null,
        };
    }

    private function placeholderPatientId(): int
    {
        return Patient::firstOrCreate(
            ['national_id' => 'PENDING'],
            ['name' => 'قيد المعالجة', 'mobile' => '0', 'membership_no' => '0', 'is_head' => true]
        )->id;
    }
}
