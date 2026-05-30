<?php

namespace App\Http\Controllers\Web;

use App\Enums\FileStatus;
use App\Http\Controllers\Concerns\BuildsReports;
use App\Http\Controllers\Concerns\ExportsCsv;
use App\Http\Controllers\Controller;
use App\Models\PatientFile;
use Illuminate\Http\Request;

class DoctorReportsController extends Controller
{
    use BuildsReports;
    use ExportsCsv;

    /** التقرير يستثني الملفات غير المُسندة — يعرض فقط ما أُسند للطبيب */
    private const ASSIGNED_STATUSES = [
        FileStatus::Assigned,
        FileStatus::Explained,
        FileStatus::NoReply,
        FileStatus::Deferred,
    ];

    public function index(Request $request)
    {
        [$data, $summary, $files] = $this->build($request);

        return view('doctor.reports', array_merge($data, $summary, ['files' => $files]));
    }

    public function print(Request $request)
    {
        [$data, $summary, $files] = $this->build($request);

        return view('doctor.reports-print', array_merge($data, $summary, ['files' => $files]));
    }

    public function export(Request $request)
    {
        [$data, $summary, $files] = $this->build($request);

        $rows = $this->summaryCsvRows($summary, $data['from'], $data['to']);

        $rows[] = [];
        $rows[] = [__('Patient'), __('Patient Ref.'), __('Test'), __('Status'), __('Date'), __('Explained on')];
        foreach ($files as $f) {
            $rows[] = [
                $f->patient->name ?? '',
                $f->patient_ref_no ?? '',
                $f->test_name ?? '',
                $f->status->label(),
                $f->created_at->format('Y-m-d H:i'),
                $f->explained_at?->format('Y-m-d H:i') ?? '',
            ];
        }

        return $this->streamCsv('my-report', [__('Field'), __('Value'), '', '', '', ''], $rows);
    }

    private function build(Request $request): array
    {
        $doctorId = $request->user()->id;

        $data    = $this->reportRange($request);
        $from    = $data['from'];
        $to      = $data['to'];

        $assignedOnly = fn () => PatientFile::query()
            ->forDoctor($doctorId)
            ->whereIn('status', array_map(fn ($s) => $s->value, self::ASSIGNED_STATUSES));

        $summary = $this->summarize($assignedOnly, $from, $to, self::ASSIGNED_STATUSES);

        $files = $assignedOnly()
            ->whereBetween('created_at', [$from, $to])
            ->with('patient')
            ->latest()
            ->get();

        return [$data, $summary, $files];
    }
}
