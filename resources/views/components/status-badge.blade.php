@props(['status'])

@php
    $key = $status instanceof \App\Enums\FileStatus ? $status->value : $status;
    $labels = [
        'new' => 'New', 'assigned' => 'Assigned', 'explained' => 'Explained',
        'no_reply' => 'No reply', 'deferred' => 'Deferred',
    ];
    $styles = [
        'new'       => 'bg-slate-100 text-slate-700 ring-slate-200 dark:bg-slate-700 dark:text-slate-200 dark:ring-slate-600',
        'assigned'  => 'bg-blue-100 text-blue-700 ring-blue-200 dark:bg-blue-900/40 dark:text-blue-200 dark:ring-blue-800',
        'explained' => 'bg-emerald-100 text-emerald-700 ring-emerald-200 dark:bg-emerald-900/40 dark:text-emerald-200 dark:ring-emerald-800',
        'no_reply'  => 'bg-amber-100 text-amber-700 ring-amber-200 dark:bg-amber-900/40 dark:text-amber-200 dark:ring-amber-800',
        'deferred'  => 'bg-violet-100 text-violet-700 ring-violet-200 dark:bg-violet-900/40 dark:text-violet-200 dark:ring-violet-800',
    ];
    $cls = $styles[$key] ?? $styles['new'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset $cls"]) }}>
    {{ __($labels[$key] ?? $key) }}
</span>
