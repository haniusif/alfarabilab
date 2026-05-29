@extends('layouts.app')
@section('title', __('Activity'))

@section('content')
@php
    $statusLabels = ['new'=>'New','assigned'=>'Assigned','explained'=>'Explained','no_reply'=>'No reply','deferred'=>'Deferred'];
@endphp

<h1 class="text-2xl font-extrabold text-slate-800 dark:text-slate-100 mb-6">{{ __('Activity') }}</h1>

{{-- تنبيهات تجاوز المهلة --}}
<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden mb-8">
    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-700">
        <h2 class="font-bold text-slate-800 dark:text-slate-100">🚨 {{ __('Files needing attention') }}</h2>
        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold
            {{ $overdue->isEmpty() ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200' : 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-200' }}">
            {{ $overdue->count() }}
        </span>
    </div>
    @if ($overdue->isEmpty())
        <div class="p-8 text-center text-slate-400 dark:text-slate-500">{{ __('Everything is on track 🎉') }}</div>
    @else
        <table class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-700/50 text-slate-500 dark:text-slate-300">
                <tr>
                    <th class="px-4 py-3 font-semibold text-start">{{ __('Patient') }}</th>
                    <th class="px-4 py-3 font-semibold text-start">{{ __('Doctor') }}</th>
                    <th class="px-4 py-3 font-semibold text-start">{{ __('Status') }}</th>
                    <th class="px-4 py-3 font-semibold text-start">{{ __('Reason') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @foreach ($overdue as $file)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50">
                        <td class="px-4 py-3">
                            <div class="font-semibold text-slate-700 dark:text-slate-200">{{ $file->patient->name ?? '—' }}</div>
                            <div class="text-xs text-slate-400 dark:text-slate-500" dir="ltr">{{ $file->patient->mobile ?? '' }}</div>
                        </td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $file->doctor->name ?? __('— not assigned') }}</td>
                        <td class="px-4 py-3"><x-status-badge :status="$file->status" /></td>
                        <td class="px-4 py-3 text-rose-600 dark:text-rose-400">{{ $file->slaReason() }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

{{-- سجل النشاط --}}
<h2 class="text-lg font-extrabold text-slate-800 dark:text-slate-100 mb-3">{{ __('Recent activity') }}</h2>
<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-6">
    @if ($logs->isEmpty())
        <p class="text-sm text-slate-400 dark:text-slate-500">{{ __('No activity yet.') }}</p>
    @else
        <ol class="space-y-4 text-sm">
            @foreach ($logs as $log)
                <li class="flex items-start gap-3 border-s-2 border-brand-200 dark:border-brand-700 ps-3">
                    <div class="flex-1">
                        <div class="text-slate-700 dark:text-slate-200">
                            <span class="font-semibold">{{ $log->file->patient->name ?? __('File #:id', ['id' => $log->patient_file_id]) }}</span>
                            @if ($log->from_status)
                                — {{ __($statusLabels[$log->from_status] ?? $log->from_status) }}
                                <span class="text-slate-400">→</span>
                            @endif
                            <span class="font-semibold">{{ __($statusLabels[$log->to_status] ?? $log->to_status) }}</span>
                        </div>
                        @if ($log->note)<div class="text-slate-500 dark:text-slate-400">{{ $log->note }}</div>@endif
                        <div class="text-xs text-slate-400 dark:text-slate-500">{{ $log->user->name ?? __('System') }} · {{ $log->created_at->format('Y-m-d H:i') }}</div>
                    </div>
                </li>
            @endforeach
        </ol>
        <div class="mt-4">{{ $logs->links() }}</div>
    @endif
</div>
@endsection
