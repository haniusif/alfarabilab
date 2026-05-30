{{-- شريط فلترة المدى — يُستخدم في تقارير كل الأدوار --}}
@php
    $exportRoute = $exportRoute ?? null;
    $printRoute  = $printRoute  ?? null;
@endphp
<form method="GET" action="" class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-4 mb-6 flex flex-wrap items-end gap-3">
    <div>
        <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1">{{ __('From') }}</label>
        <input type="date" name="from" value="{{ $fromInput }}"
               class="rounded-lg border-slate-200 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-sm">
    </div>
    <div>
        <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1">{{ __('To') }}</label>
        <input type="date" name="to" value="{{ $toInput }}"
               class="rounded-lg border-slate-200 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-sm">
    </div>
    <button class="bg-brand-700 hover:bg-brand-800 text-white px-4 py-2 rounded-lg font-semibold text-sm">
        {{ __('Apply') }}
    </button>

    <div class="ms-auto flex items-center gap-2">
        @if ($printRoute)
            <a href="{{ route($printRoute, ['from' => $fromInput, 'to' => $toInput]) }}" target="_blank"
               class="bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 px-4 py-2 rounded-lg font-semibold text-sm inline-flex items-center gap-1">
                🖨️ {{ __('Print / PDF') }}
            </a>
        @endif
        @if ($exportRoute)
            <a href="{{ route($exportRoute, ['from' => $fromInput, 'to' => $toInput]) }}"
               class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg font-semibold text-sm inline-flex items-center gap-1">
                ⬇️ {{ __('Export CSV') }}
            </a>
        @endif
    </div>
</form>
