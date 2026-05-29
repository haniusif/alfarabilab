@extends('layouts.app')
@section('title', __('File details'))

@section('content')
@php
    $statusLabels = ['new'=>'New','assigned'=>'Assigned','explained'=>'Explained','no_reply'=>'No reply','deferred'=>'Deferred'];
@endphp

<a href="{{ route('insurance.index') }}" class="inline-flex items-center gap-1 text-sm text-slate-500 dark:text-slate-400 hover:underline mb-4">← {{ __('Back to dashboard') }}</a>

<div class="grid gap-6 lg:grid-cols-3">
    {{-- التفاصيل --}}
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-6">
            <div class="flex items-start justify-between gap-3 mb-4">
                <div>
                    <h1 class="text-xl font-extrabold text-slate-800 dark:text-slate-100">{{ $file->patient->name ?? '—' }}</h1>
                    <div class="text-sm text-slate-400 dark:text-slate-500" dir="ltr">{{ $file->patient->mobile ?? '' }}</div>
                </div>
                <x-status-badge :status="$file->status" />
            </div>

            <dl class="grid gap-x-6 gap-y-3 sm:grid-cols-2 text-sm">
                <div>
                    <dt class="text-slate-400 dark:text-slate-500">{{ __('Membership number') }}</dt>
                    <dd class="font-semibold text-slate-700 dark:text-slate-200" dir="ltr">{{ $file->patient->membership_no ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400 dark:text-slate-500">{{ __('National ID') }}</dt>
                    <dd class="font-semibold text-slate-700 dark:text-slate-200" dir="ltr">{{ $file->patient->national_id ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400 dark:text-slate-500">{{ __('Test') }}</dt>
                    <dd class="font-semibold text-slate-700 dark:text-slate-200">{{ $file->test_name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400 dark:text-slate-500">{{ __('Doctor') }}</dt>
                    <dd class="font-semibold text-slate-700 dark:text-slate-200">{{ $file->doctor->name ?? __('— not assigned') }}</dd>
                </div>
                @if ($file->result)
                    <div class="sm:col-span-2">
                        <dt class="text-slate-400 dark:text-slate-500">{{ __('Result') }}</dt>
                        <dd class="font-semibold text-slate-700 dark:text-slate-200">{{ $file->result }} {{ $file->unit }}</dd>
                    </div>
                @endif
                <div>
                    <dt class="text-slate-400 dark:text-slate-500">{{ __('Sent on') }}</dt>
                    <dd class="font-semibold text-slate-700 dark:text-slate-200" dir="ltr">{{ $file->created_at->format('Y-m-d H:i') }}</dd>
                </div>
                @if ($file->explained_at)
                    <div>
                        <dt class="text-slate-400 dark:text-slate-500">{{ __('Explained on') }}</dt>
                        <dd class="font-semibold text-emerald-600 dark:text-emerald-400" dir="ltr">{{ $file->explained_at->format('Y-m-d H:i') }}</dd>
                    </div>
                @endif
            </dl>
        </div>
    </div>

    {{-- سجل الحالة --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-6">
        <h2 class="font-bold mb-3 text-slate-800 dark:text-slate-100">📜 {{ __('Status log') }}</h2>
        @if ($file->statusLogs->isEmpty())
            <p class="text-sm text-slate-400 dark:text-slate-500">{{ __('No log yet.') }}</p>
        @else
            <ol class="space-y-3 text-sm">
                @foreach ($file->statusLogs->sortByDesc('created_at') as $log)
                    <li class="border-e-2 border-brand-200 dark:border-brand-700 pe-3">
                        <div class="font-semibold text-slate-700 dark:text-slate-200">{{ __($statusLabels[$log->to_status] ?? $log->to_status) }}</div>
                        @if ($log->note)<div class="text-slate-500 dark:text-slate-400">{{ $log->note }}</div>@endif
                        <div class="text-xs text-slate-400 dark:text-slate-500">{{ $log->user->name ?? __('System') }} · {{ $log->created_at->format('Y-m-d H:i') }}</div>
                    </li>
                @endforeach
            </ol>
        @endif
    </div>
</div>
@endsection
