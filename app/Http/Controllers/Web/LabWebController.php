<?php

namespace App\Http\Controllers\Web;

use App\Enums\FileStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Concerns\ExportsCsv;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessUploadedReport;
use App\Models\Patient;
use App\Models\PatientFile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LabWebController extends Controller
{
    use ExportsCsv;

    public function index()
    {
        $unassigned = PatientFile::where('status', FileStatus::New->value)
            ->whereNull('doctor_id')
            ->with('patient', 'insuranceCompany')
            ->latest()
            ->get();

        $doctors = $this->activeDoctorsWithLoad();

        $assignedCount = PatientFile::whereNotNull('doctor_id')->count();

        $overdueCount = PatientFile::breachingSla()->count();

        $insurers = User::where('role', UserRole::InsuranceCompany->value)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('lab.index', compact('unassigned', 'doctors', 'assignedCount', 'overdueCount', 'insurers'));
    }

    /** كل الملفات في المعمل — بحث وتصفية وتقسيم صفحات */
    public function files(Request $request)
    {
        $files = $this->filteredFiles($request)->latest()->paginate(15)->withQueryString();

        $doctors = User::where('role', UserRole::Doctor->value)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'specialty']);

        return view('lab.files', ['files' => $files, 'doctors' => $doctors]);
    }

    /** تصدير الملفات (مع نفس عوامل التصفية) إلى CSV */
    public function export(Request $request)
    {
        $files = $this->filteredFiles($request)->latest()->get();

        $headers = [__('Patient'), __('Mobile'), __('Test'), __('Insurance company'), __('Doctor'), __('Status'), __('Date')];

        $rows = $files->map(fn (PatientFile $f) => [
            $f->patient->name ?? '',
            $f->patient->mobile ?? '',
            $f->test_name ?? '',
            $f->insuranceCompany->name ?? '',
            $f->doctor->name ?? '',
            $f->status->label(),
            $f->created_at->format('Y-m-d H:i'),
        ]);

        return $this->streamCsv('lab-files', $headers, $rows);
    }

    /** بناء استعلام الملفات وفق عوامل البحث والتصفية */
    private function filteredFiles(Request $request)
    {
        $query = PatientFile::with('patient', 'doctor', 'insuranceCompany');

        if ($q = trim((string) $request->input('q'))) {
            $query->whereHas('patient', function ($p) use ($q) {
                $p->where('name', 'like', "%{$q}%")->orWhere('mobile', 'like', "%{$q}%");
            });
        }

        if (($status = $request->input('status')) && FileStatus::tryFrom($status)) {
            $query->where('status', $status);
        }

        return $query;
    }

    /** رفع تقرير/تقارير، اختيار شركة التأمين، وإسنادها لطبيب — مع قراءة OCR */
    public function upload(Request $request)
    {
        $data = $request->validate([
            'files'                => ['required', 'array', 'min:1'],
            'files.*'              => ['file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
            'insurance_company_id' => ['required', 'exists:users,id'],
            'doctor_id'            => ['nullable', 'exists:users,id'],
        ]);

        $insurer = User::findOrFail($data['insurance_company_id']);
        abort_unless($insurer->isInsurance(), 422, __('The selected user is not an insurance company'));

        // الطبيب اختياري — بدونه يدخل الملف قائمة الانتظار
        $doctor = ! empty($data['doctor_id']) ? $this->resolveDoctor($data['doctor_id']) : null;

        $read = 0;
        $manual = 0;

        foreach ($request->file('files') as $uploaded) {
            $file = PatientFile::create([
                'patient_id'           => $this->placeholderPatientId(),
                'insurance_company_id' => $insurer->id,
                'doctor_id'            => $doctor?->id,
                'source_path'          => $uploaded->store('reports'),
                'source_type'          => strtolower($uploaded->getClientOriginalExtension()) === 'pdf' ? 'pdf' : 'image',
                'status'               => FileStatus::New->value,
            ]);

            try {
                // قراءة متزامنة لتعمل دون عامل طابور؛ تحافظ على شركة التأمين وتُسند للطبيب إن وُجد
                ProcessUploadedReport::dispatchSync($file->id, $doctor?->id, $request->user()->id);
                $read++;
            } catch (\Throwable $e) {
                report($e);
                // تعذّرت القراءة: أبقِ الملف (مُسنداً إن اختير طبيب، وإلا في قائمة الانتظار) للإدخال اليدوي
                $file->update(['report_status' => __('Auto-read failed — manual entry required')]);
                if ($doctor) {
                    $file->assignTo($doctor, $request->user());
                }
                $manual++;
            }
        }

        $total = $read + $manual;
        $msg = $doctor
            ? __(':count files uploaded and assigned to :name', ['count' => $total, 'name' => $doctor->name])
            : __(':count files uploaded to the unassigned queue', ['count' => $total]);
        if ($manual > 0) {
            $msg .= ' — '.__(':n could not be auto-read (manual entry needed)', ['n' => $manual]);
        }

        return back()->with('status', $msg);
    }

    public function assign(Request $request, PatientFile $file)
    {
        $data = $request->validate([
            'doctor_id' => ['required', 'exists:users,id'],
        ]);

        $doctor = $this->resolveDoctor($data['doctor_id']);

        $file->assignTo($doctor, $request->user());

        return back()->with('status', __('File assigned to :name', ['name' => $doctor->name]));
    }

    /** سحب إسناد ملف وإعادته لقائمة الانتظار */
    public function unassign(Request $request, PatientFile $file)
    {
        $file->unassign($request->user());

        return back()->with('status', __('File returned to the unassigned queue'));
    }

    /** إسناد مجموعة ملفات لطبيب واحد دفعة واحدة */
    public function bulkAssign(Request $request)
    {
        $data = $request->validate([
            'doctor_id'  => ['required', 'exists:users,id'],
            'file_ids'   => ['required', 'array', 'min:1'],
            'file_ids.*' => ['integer', 'exists:patient_files,id'],
        ]);

        $doctor = $this->resolveDoctor($data['doctor_id']);

        $files = PatientFile::whereIn('id', $data['file_ids'])->get();
        foreach ($files as $file) {
            $file->assignTo($doctor, $request->user());
        }

        return back()->with('status', __(':count files assigned to :name', [
            'count' => $files->count(),
            'name'  => $doctor->name,
        ]));
    }

    /** توزيع تلقائي للملفات غير المُسندة على أقل الأطباء حِملاً */
    public function autoAssign(Request $request)
    {
        $doctors = $this->activeDoctorsWithLoad();

        if ($doctors->isEmpty()) {
            return back()->withErrors(['doctor_id' => __('No active doctors available')]);
        }

        // الحِمل الحالي لكل طبيب (id => عدد الملفات المفتوحة)
        $load = $doctors->mapWithKeys(fn ($d) => [$d->id => $d->open_files_count])->all();

        $unassigned = PatientFile::where('status', FileStatus::New->value)
            ->whereNull('doctor_id')
            ->get();

        foreach ($unassigned as $file) {
            $targetId = array_keys($load, min($load))[0]; // الطبيب الأقل حِملاً
            $file->assignTo($doctors->firstWhere('id', $targetId), $request->user());
            $load[$targetId]++;
        }

        return back()->with('status', __(':count files auto-assigned to least-loaded doctors', [
            'count' => $unassigned->count(),
        ]));
    }

    /** نقل ملف إلى سلة المحذوفات (حذف مؤقّت — يمكن استعادته) */
    public function destroy(Request $request, PatientFile $file)
    {
        $file->delete();

        return redirect()->route('lab.trash')->with('status', __('File moved to trash'));
    }

    /** قائمة الملفات المحذوفة حذفاً مؤقتاً (نظرة عامة لإدارة المعمل) */
    public function trash(Request $request)
    {
        $files = PatientFile::onlyTrashed()
            ->with('patient', 'doctor', 'insuranceCompany')
            ->orderByDesc('deleted_at')
            ->paginate(15)
            ->withQueryString();

        return view('lab.trash', ['files' => $files]);
    }

    /** استعادة ملف محذوف مؤقتاً */
    public function restore(int $file)
    {
        $patientFile = PatientFile::onlyTrashed()->findOrFail($file);
        $patientFile->restore();

        return back()->with('status', __('File restored from trash'));
    }

    /** حذف نهائي — يُزيل السجل والملف الأصلي على القرص */
    public function forceDestroy(int $file)
    {
        $patientFile = PatientFile::onlyTrashed()->findOrFail($file);

        if ($patientFile->source_path && Storage::exists($patientFile->source_path)) {
            Storage::delete($patientFile->source_path);
        }

        $patientFile->forceDelete();

        return back()->with('status', __('File permanently deleted'));
    }

    /** الأطباء النشطون مع عدد ملفاتهم المفتوحة */
    private function activeDoctorsWithLoad()
    {
        return User::where('role', UserRole::Doctor->value)
            ->where('is_active', true)
            ->withCount(['assignedFiles as open_files_count' => fn ($q) => $q->open()])
            ->orderBy('name')
            ->get();
    }

    private function resolveDoctor(int|string $id): User
    {
        $doctor = User::findOrFail($id);
        abort_unless($doctor->isDoctor(), 422, __('The selected user is not a doctor'));

        return $doctor;
    }

    /** مريض مؤقت يُربط بالملف حتى تُستخرج بياناته من التقرير */
    private function placeholderPatientId(): int
    {
        return Patient::firstOrCreate(
            ['national_id' => 'PENDING'],
            ['name' => 'قيد المعالجة', 'mobile' => '0', 'membership_no' => '0', 'is_head' => true]
        )->id;
    }
}
