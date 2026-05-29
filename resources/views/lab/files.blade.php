@extends('layouts.app')
@section('title', __('All files'))

@section('content')
<div class="flex items-center justify-between gap-3 mb-6">
    <h1 class="text-2xl font-extrabold text-slate-800 dark:text-slate-100">{{ __('All files') }}</h1>
    <div class="flex items-center gap-3">
        <span class="text-sm text-slate-500 dark:text-slate-400">{{ $files->total() }} {{ __('files') }}</span>
        <a href="{{ route('lab.files.export', request()->only('q', 'status')) }}"
           class="inline-flex items-center gap-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition text-sm font-semibold px-3 py-1.5 rounded-lg">
            ⬇ {{ __('Export CSV') }}
        </a>
    </div>
</div>

@include('partials.file-filters', ['action' => route('lab.files')])

<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">
    @if ($files->isEmpty())
        <div class="p-10 text-center text-slate-400 dark:text-slate-500">{{ __('No results.') }}</div>
    @else
        <table class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-700/50 text-slate-500 dark:text-slate-300">
                <tr>
                    <th class="px-4 py-3 font-semibold text-start">{{ __('Patient') }}</th>
                    <th class="px-4 py-3 font-semibold text-start">{{ __('Test') }}</th>
                    <th class="px-4 py-3 font-semibold text-start">{{ __('Insurance company') }}</th>
                    <th class="px-4 py-3 font-semibold text-start">{{ __('Doctor') }}</th>
                    <th class="px-4 py-3 font-semibold text-start">{{ __('Status') }}</th>
                    <th class="px-4 py-3 font-semibold text-start">{{ __('Date') }}</th>
                    <th class="px-4 py-3 font-semibold text-start">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @foreach ($files as $file)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50">
                        <td class="px-4 py-3">
                            <div class="font-semibold text-slate-700 dark:text-slate-200">{{ $file->patient->name ?? __('Processing') }}</div>
                            <div class="text-xs text-slate-400 dark:text-slate-500" dir="ltr">{{ $file->patient->mobile ?? '' }}</div>
                        </td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $file->test_name ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $file->insuranceCompany->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $file->doctor->name ?? __('— not assigned') }}</td>
                        <td class="px-4 py-3"><x-status-badge :status="$file->status" /></td>
                        <td class="px-4 py-3 text-slate-400 dark:text-slate-500 whitespace-nowrap" dir="ltr">{{ $file->created_at->format('Y-m-d') }}</td>
                        <td class="px-4 py-3">
                            @unless ($file->status->isClosed())
                                <div x-data="{ open: false }" class="relative">
                                    <button type="button" @click="open = !open" @click.outside="open = false"
                                            class="text-slate-500 dark:text-slate-400 hover:text-brand-700 dark:hover:text-brand-400 text-sm font-semibold px-2 py-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700">
                                        {{ $file->doctor_id ? __('Reassign') : __('Assign') }} ▾
                                    </button>
                                    <div x-show="open" x-cloak x-transition.origin.top
                                         class="absolute z-10 mt-1 w-64 rounded-xl bg-white dark:bg-slate-800 shadow-lg border border-slate-100 dark:border-slate-700 p-3 space-y-2">
                                        <form method="POST" action="{{ route('lab.assign', $file) }}" class="flex items-center gap-2">
                                            @csrf
                                            <select name="doctor_id" required
                                                    class="flex-1 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 px-2 py-1.5 text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                                                <option value="">{{ __('— choose a doctor —') }}</option>
                                                @foreach ($doctors as $doctor)
                                                    <option value="{{ $doctor->id }}" @selected($file->doctor_id === $doctor->id)>{{ $doctor->name }}</option>
                                                @endforeach
                                            </select>
                                            <button class="bg-brand-700 hover:bg-brand-800 transition text-white text-sm font-semibold px-3 py-1.5 rounded-lg">
                                                {{ __('Save') }}
                                            </button>
                                        </form>
                                        @if ($file->doctor_id)
                                            <form method="POST" action="{{ route('lab.unassign', $file) }}"
                                                  onsubmit="return confirm('{{ __('Return this file to the unassigned queue?') }}')">
                                                @csrf
                                                <button class="w-full text-start text-sm text-rose-600 dark:text-rose-400 hover:underline px-1">
                                                    {{ __('Unassign') }}
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <span class="text-xs text-slate-300 dark:text-slate-600">—</span>
                            @endunless
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

<div class="mt-4">{{ $files->links() }}</div>
@endsection
