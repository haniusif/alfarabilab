@php
    $barColors = [
        'new'       => 'bg-slate-400',
        'assigned'  => 'bg-blue-500',
        'explained' => 'bg-emerald-500',
        'no_reply'  => 'bg-amber-500',
        'deferred'  => 'bg-violet-500',
    ];
@endphp

{{-- المؤشرات الرئيسية --}}
<div class="grid gap-4 sm:grid-cols-3 mb-6">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-5">
        <div class="text-slate-400 dark:text-slate-500 text-sm">{{ __('Incoming files') }}</div>
        <div class="text-3xl font-extrabold text-slate-800 dark:text-slate-100">{{ $totalIncoming }}</div>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-5">
        <div class="text-slate-400 dark:text-slate-500 text-sm">{{ __('Explained in period') }}</div>
        <div class="text-3xl font-extrabold text-emerald-600 dark:text-emerald-400">{{ $totalExplained }}</div>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-5">
        <div class="text-slate-400 dark:text-slate-500 text-sm">{{ __('Avg. turnaround') }}</div>
        <div class="text-3xl font-extrabold text-brand-700 dark:text-brand-500">
            {{ $avgTurnaroundHours !== null ? $avgTurnaroundHours.' '.__('h') : '—' }}
        </div>
    </div>
</div>

{{-- توزيع الحالات + الإنتاجية اليومية --}}
<div class="grid gap-6 lg:grid-cols-2 mb-6">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-6">
        <h2 class="font-bold text-slate-800 dark:text-slate-100 mb-4">{{ __('Files by status') }}</h2>
        <div class="space-y-3">
            @foreach ($statusCounts as $row)
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-slate-600 dark:text-slate-300">{{ $row['status']->label() }}</span>
                        <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $row['count'] }}</span>
                    </div>
                    <div class="h-2.5 rounded-full bg-slate-100 dark:bg-slate-700 overflow-hidden">
                        <div class="h-full rounded-full {{ $barColors[$row['status']->value] ?? 'bg-slate-400' }}"
                             style="width: {{ $totalIncoming ? round($row['count'] / $totalIncoming * 100) : 0 }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-bold text-slate-800 dark:text-slate-100">{{ __('Daily throughput') }}</h2>
            <div class="flex items-center gap-3 text-xs text-slate-500 dark:text-slate-400">
                <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded bg-slate-400 inline-block"></span>{{ __('Incoming') }}</span>
                <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded bg-emerald-500 inline-block"></span>{{ __('Explained') }}</span>
            </div>
        </div>
        <div class="flex items-end gap-1 h-40">
            @foreach ($days as $day)
                <div class="flex-1 flex flex-col items-center justify-end gap-0.5 h-full" title="{{ $day['label'] }} — {{ __('Incoming') }}: {{ $day['incoming'] }} · {{ __('Explained') }}: {{ $day['done'] }}">
                    <div class="w-full flex items-end justify-center gap-0.5 h-full">
                        <div class="w-1/2 rounded-t bg-slate-300 dark:bg-slate-600" style="height: {{ round($day['incoming'] / $maxDay * 100) }}%"></div>
                        <div class="w-1/2 rounded-t bg-emerald-500" style="height: {{ round($day['done'] / $maxDay * 100) }}%"></div>
                    </div>
                    @if ($loop->iteration === 1 || $loop->last || $loop->iteration % max(1, intdiv($days->count(), 7)) === 0)
                        <span class="text-[10px] text-slate-400 dark:text-slate-500" dir="ltr">{{ $day['label'] }}</span>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
