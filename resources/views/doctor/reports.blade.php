@extends('layouts.app')
@section('title', __('Reports'))

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-extrabold text-slate-800 dark:text-slate-100">{{ __('Reports') }}</h1>
    <span class="text-sm text-slate-500 dark:text-slate-400" dir="ltr">
        {{ $from->format('Y-m-d') }} → {{ $to->format('Y-m-d') }}
    </span>
</div>

@include('partials.reports-filter', ['exportRoute' => 'doctor.reports.export', 'printRoute' => 'doctor.reports.print'])

@include('partials.reports-summary')

<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-6">
    <h2 class="font-bold text-slate-800 dark:text-slate-100 mb-4">{{ __('Files in this period') }}</h2>
    @if ($files->isEmpty())
        <p class="text-sm text-slate-400 dark:text-slate-500">{{ __('No results.') }}</p>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-slate-400 dark:text-slate-500 border-b border-slate-100 dark:border-slate-700">
                        <th class="text-start py-2 font-semibold">{{ __('Patient') }}</th>
                        <th class="text-start py-2 font-semibold">{{ __('Patient Ref.') }}</th>
                        <th class="text-start py-2 font-semibold">{{ __('Test') }}</th>
                        <th class="text-start py-2 font-semibold">{{ __('Status') }}</th>
                        <th class="text-start py-2 font-semibold">{{ __('Date') }}</th>
                        <th class="text-start py-2 font-semibold">{{ __('Explained on') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($files as $f)
                        <tr class="border-b border-slate-50 dark:border-slate-700/50 last:border-0">
                            <td class="py-2 text-slate-700 dark:text-slate-200">{{ $f->patient->name ?? '—' }}</td>
                            <td class="py-2 text-slate-500 dark:text-slate-400 font-mono text-xs" dir="ltr">{{ $f->patient_ref_no ?? '—' }}</td>
                            <td class="py-2 text-slate-600 dark:text-slate-300">{{ $f->test_name ?? '—' }}</td>
                            <td class="py-2"><x-status-badge :status="$f->status" /></td>
                            <td class="py-2 text-slate-500 dark:text-slate-400" dir="ltr">{{ $f->created_at->format('Y-m-d H:i') }}</td>
                            <td class="py-2 text-slate-500 dark:text-slate-400" dir="ltr">{{ $f->explained_at?->format('Y-m-d H:i') ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
