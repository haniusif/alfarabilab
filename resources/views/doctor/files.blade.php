@extends('layouts.app')
@section('title', __('All files'))

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-extrabold text-slate-800 dark:text-slate-100">{{ __('All files') }}</h1>
    <span class="text-sm text-slate-500 dark:text-slate-400">{{ $files->total() }} {{ __('files') }}</span>
</div>

@php
    $whenTabs = ['' => 'All', 'today' => 'Today', 'tomorrow' => 'Tomorrow', 'upcoming' => 'Upcoming', 'past' => 'Previous'];
@endphp
<div class="flex flex-wrap gap-2 mb-4">
    @foreach ($whenTabs as $key => $label)
        @php($active = request('when', '') === $key)
        <a href="{{ route('doctor.files', array_merge(request()->only('q', 'status'), $key !== '' ? ['when' => $key] : [])) }}"
           class="px-3 py-1.5 rounded-lg text-sm font-semibold transition border {{ $active ? 'bg-brand-700 text-white border-brand-700' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700' }}">
            {{ __($label) }}
        </a>
    @endforeach
</div>

@include('partials.file-filters', ['action' => route('doctor.files')])

<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">
    @if ($files->isEmpty())
        <div class="p-10 text-center text-slate-400 dark:text-slate-500">{{ __('No results.') }}</div>
    @else
        <table class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-700/50 text-slate-500 dark:text-slate-300">
                <tr>
                    <th class="px-4 py-3 font-semibold text-start">{{ __('Patient') }}</th>
                    <th class="px-4 py-3 font-semibold text-start">{{ __('Patient Ref.') }}</th>
                    <th class="px-4 py-3 font-semibold text-start">{{ __('Test') }}</th>
                    <th class="px-4 py-3 font-semibold text-start">{{ __('Result') }}</th>
                    <th class="px-4 py-3 font-semibold text-start">{{ __('Status') }}</th>
                    <th class="px-4 py-3 font-semibold text-start">{{ __('Date') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @foreach ($files as $file)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50">
                        <td class="px-4 py-3">
                            <a href="{{ route('doctor.show', $file) }}" class="font-semibold text-brand-700 dark:text-brand-500 hover:underline">
                                {{ $file->patient->name ?? __('Processing') }}
                            </a>
                            <div class="text-xs text-slate-400 dark:text-slate-500" dir="ltr">{{ $file->patient->mobile ?? '' }}</div>
                        </td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300 font-mono text-xs" dir="ltr">{{ $file->patient_ref_no ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $file->test_name ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $file->result ? $file->result.' '.$file->unit : '—' }}</td>
                        <td class="px-4 py-3"><x-status-badge :status="$file->status" /></td>
                        <td class="px-4 py-3 whitespace-nowrap" dir="ltr">
                            @if ($file->deferred_to)
                                <span class="text-violet-600 dark:text-violet-400">⏰ {{ $file->deferred_to->format('Y-m-d H:i') }}</span>
                            @else
                                <span class="text-slate-400 dark:text-slate-500">{{ $file->created_at->format('Y-m-d') }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

<div class="mt-4">{{ $files->links() }}</div>
@endsection
