<?php

namespace App\Http\Controllers\Concerns;

use App\Enums\FileStatus;
use App\Models\PatientFile;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait BuildsReports
{
    /** يقرأ مدى التاريخ من الطلب — افتراضياً آخر 30 يوماً */
    protected function reportRange(Request $request): array
    {
        $to   = $this->parseDate($request->input('to'))   ?? CarbonImmutable::now()->endOfDay();
        $from = $this->parseDate($request->input('from')) ?? $to->copy()->subDays(29)->startOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to->startOfDay(), $from->endOfDay()];
        }

        return [
            'from'      => $from->startOfDay(),
            'to'        => $to->endOfDay(),
            'fromInput' => $from->format('Y-m-d'),
            'toInput'   => $to->format('Y-m-d'),
        ];
    }

    private function parseDate(?string $value): ?CarbonImmutable
    {
        if (! $value) {
            return null;
        }

        try {
            return CarbonImmutable::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * يحسب أرقام الملخّص لمجموعة ملفات ضمن المدى.
     * يُمرَّر استعلام أساسي (مع نطاقات الدور)، فيُعاد منه:
     *  - عدّ الملفات حسب الحالة (incoming)
     *  - عدّ المنجَزة (explained) ضمن المدى — بحسب explained_at
     *  - متوسط زمن الإنجاز بالساعات
     *
     * @param  list<FileStatus>|null  $statuses  حالات تُعرض في التوزيع (null = كلها)
     */
    protected function summarize(callable $scopedQuery, CarbonImmutable $from, CarbonImmutable $to, ?array $statuses = null): array
    {
        $byStatus = $scopedQuery()
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('status, count(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        $cases = $statuses ?? FileStatus::cases();
        $statusCounts = collect($cases)->map(fn ($s) => [
            'status' => $s,
            'count'  => (int) ($byStatus[$s->value] ?? 0),
        ]);

        $totalIncoming = (int) $statusCounts->sum('count');

        // المنجَزة فعلياً ضمن المدى بحسب وقت الشرح (قد لا تتطابق مع تاريخ إنشائها)
        $explainedInPeriod = $scopedQuery()
            ->whereNotNull('explained_at')
            ->whereBetween('explained_at', [$from, $to])
            ->get(['created_at', 'explained_at']);

        $avgTurnaroundHours = $explainedInPeriod->isEmpty() ? null
            : round($explainedInPeriod->avg(
                fn (PatientFile $f) => $f->created_at->diffInHours($f->explained_at)
            ), 1);

        // متسلسلة يومية للوارد/المنجَز
        $days = collect();
        for ($d = $from; $d->lte($to); $d = $d->addDay()) {
            $start = $d->startOfDay();
            $end   = $d->endOfDay();
            $days->push([
                'label'    => $start->format('m-d'),
                'incoming' => $scopedQuery()->whereBetween('created_at', [$start, $end])->count(),
                'done'     => $scopedQuery()->whereBetween('explained_at', [$start, $end])->count(),
            ]);
        }

        $maxDay = max(1, $days->max(fn ($r) => max($r['incoming'], $r['done'])));

        return [
            'statusCounts'       => $statusCounts,
            'totalIncoming'      => $totalIncoming,
            'totalExplained'     => $explainedInPeriod->count(),
            'avgTurnaroundHours' => $avgTurnaroundHours,
            'days'               => $days,
            'maxDay'             => $maxDay,
        ];
    }

    /** صفوف CSV قياسية تلخّص الفترة وتوزيع الحالات */
    protected function summaryCsvRows(array $summary, CarbonImmutable $from, CarbonImmutable $to): array
    {
        $rows = [
            [__('Period'), $from->format('Y-m-d').' → '.$to->format('Y-m-d')],
            [__('Incoming files'), $summary['totalIncoming']],
            [__('Explained in period'), $summary['totalExplained']],
            [__('Avg. turnaround'), $summary['avgTurnaroundHours'] !== null ? $summary['avgTurnaroundHours'].' '.__('h') : '—'],
            [],
            [__('Status'), __('Files')],
        ];

        foreach ($summary['statusCounts'] as $row) {
            $rows[] = [$row['status']->label(), $row['count']];
        }

        return $rows;
    }
}
