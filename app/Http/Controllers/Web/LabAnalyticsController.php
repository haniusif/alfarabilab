<?php

namespace App\Http\Controllers\Web;

use App\Enums\FileStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\PatientFile;
use App\Models\User;

class LabAnalyticsController extends Controller
{
    public function index()
    {
        // توزيع الملفات حسب الحالة
        $byStatus = PatientFile::selectRaw('status, count(*) as c')
            ->groupBy('status')->pluck('c', 'status');

        $statusCounts = collect(FileStatus::cases())->map(fn ($s) => [
            'status' => $s,
            'count'  => (int) ($byStatus[$s->value] ?? 0),
        ]);
        $totalFiles = $statusCounts->sum('count');

        // حِمل الأطباء (الملفات المفتوحة)
        $doctorLoad = User::where('role', UserRole::Doctor->value)
            ->where('is_active', true)
            ->withCount(['assignedFiles as open_files_count' => fn ($q) => $q->open()])
            ->orderByDesc('open_files_count')
            ->get();

        // الإنتاجية اليومية آخر 14 يوماً: وارد مقابل مُنجز (تم الشرح)
        $days = collect(range(13, 0))->map(function ($d) {
            $date = now()->subDays($d)->startOfDay();
            return [
                'label'    => $date->format('m-d'),
                'incoming' => PatientFile::whereDate('created_at', $date)->count(),
                'done'     => PatientFile::whereDate('explained_at', $date)->count(),
            ];
        });
        $maxDay = max(1, $days->max(fn ($r) => max($r['incoming'], $r['done'])));

        // متوسط زمن الإنجاز (من الإنشاء حتى الشرح) بالساعات
        $explained = PatientFile::whereNotNull('explained_at')->get(['created_at', 'explained_at']);
        $avgTurnaroundHours = $explained->isEmpty() ? null
            : round($explained->avg(fn ($f) => $f->created_at->diffInHours($f->explained_at)), 1);

        return view('lab.analytics', compact(
            'statusCounts', 'totalFiles', 'doctorLoad', 'days', 'maxDay', 'avgTurnaroundHours'
        ));
    }
}
