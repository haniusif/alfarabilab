<?php

namespace App\Http\Controllers\Web;

use App\Enums\FileStatus;
use App\Http\Controllers\Concerns\ExportsCsv;
use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\PatientFile;
use Illuminate\Http\Request;

class InsuranceWebController extends Controller
{
    use ExportsCsv;

    public function index(Request $request)
    {
        $files = $this->filteredFiles($request)->latest()->paginate(15)->withQueryString();

        // إحصائيات ملفات هذه الشركة حسب الحالة
        $byStatus = PatientFile::where('insurance_company_id', $request->user()->id)
            ->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status');

        $stats = [
            'total'      => (int) $byStatus->sum(),
            'new'        => (int) ($byStatus[FileStatus::New->value] ?? 0),
            'inProgress' => (int) collect([FileStatus::Assigned, FileStatus::NoReply, FileStatus::Deferred])
                ->sum(fn ($s) => $byStatus[$s->value] ?? 0),
            'explained'  => (int) ($byStatus[FileStatus::Explained->value] ?? 0),
        ];

        return view('insurance.index', compact('files', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => ['required', 'string'],
            'mobile'        => ['required', 'string'],
            'membership_no' => ['required', 'string'],
            'national_id'   => ['required', 'string'],
            'test_name'     => ['required', 'string'],
        ]);

        $patient = Patient::findOrCreateByMobile($data);

        PatientFile::create([
            'patient_id'           => $patient->id,
            'insurance_company_id' => $request->user()->id,
            'test_name'            => $data['test_name'],
            'status'               => FileStatus::New->value,
        ]);

        return redirect()->route('insurance.index')
            ->with('status', __("Patient file ':name' sent to the lab", ['name' => $data['name']]));
    }

    /** تفاصيل ملف واحد — مقصورة على ملفات هذه الشركة */
    public function show(Request $request, PatientFile $file)
    {
        abort_unless($file->insurance_company_id === $request->user()->id, 403);

        $file->load('patient', 'doctor', 'statusLogs.user');

        return view('insurance.show', compact('file'));
    }

    /** تصدير ملفات هذه الشركة (مع عوامل التصفية) إلى CSV */
    public function export(Request $request)
    {
        $files = $this->filteredFiles($request)->latest()->get();

        $headers = [__('Patient'), __('Mobile'), __('Test'), __('Doctor'), __('Status'), __('Date')];

        $rows = $files->map(fn (PatientFile $f) => [
            $f->patient->name ?? '',
            $f->patient->mobile ?? '',
            $f->test_name ?? '',
            $f->doctor->name ?? '',
            $f->status->label(),
            $f->created_at->format('Y-m-d H:i'),
        ]);

        return $this->streamCsv('my-files', $headers, $rows);
    }

    /** ملفات هذه الشركة وفق البحث والتصفية */
    private function filteredFiles(Request $request)
    {
        $query = PatientFile::where('insurance_company_id', $request->user()->id)
            ->with('patient', 'doctor');

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
}
