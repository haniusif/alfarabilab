@extends('layouts.app')
@section('title', __('Patient'))

@section('content')
<a href="{{ route('doctor.index') }}" class="inline-flex items-center gap-1 text-sm text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 mb-4">
    <span class="rtl:hidden">←</span><span class="ltr:hidden">→</span> {{ __('Back to dashboard') }}
</a>

<div class="grid gap-6 lg:grid-cols-3">
    {{-- Main file details --}}
    <div class="lg:col-span-2 space-y-6">
        @if ($duplicates->isNotEmpty())
            <div class="bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-200 rounded-2xl p-4 flex items-center justify-between gap-3">
                <span>⚠️ {{ __('This report has :count other copy with the same accession number.', ['count' => $duplicates->count()]) }}</span>
                <a href="{{ route('doctor.duplicates') }}" class="text-sm font-semibold underline whitespace-nowrap">{{ __('View duplicates') }}</a>
            </div>
        @endif

        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-6">
            <div class="flex items-start justify-between mb-5">
                <div>
                    <h1 class="text-2xl font-extrabold text-slate-800 dark:text-slate-100">{{ $file->patient->name ?? __('Processing') }}</h1>
                    <p class="text-slate-400 dark:text-slate-500 text-sm" dir="ltr">{{ $file->patient->mobile ?? '' }}</p>
                </div>
                <x-status-badge :status="$file->status" class="text-sm" />
            </div>

            <div class="grid sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                @php
                    $rows = [
                        'Membership number' => $file->patient->membership_no ?? null,
                        'National ID'       => $file->patient->national_id ?? null,
                        'Patient ID'        => $file->patient_external_id,
                        'Referring doctor'  => $file->referral_doctor,
                        'Age/Gender'        => $file->age_gender,
                        'Accession No.'     => $file->accession_no,
                        'Patient Ref.'      => $file->patient_ref_no,
                        'Report status'     => $file->report_status,
                    ];
                @endphp
                @foreach ($rows as $label => $value)
                    <div class="flex justify-between border-b border-slate-50 dark:border-slate-700 py-1.5">
                        <span class="text-slate-400 dark:text-slate-500">{{ __($label) }}</span>
                        <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $value ?: '—' }}</span>
                    </div>
                @endforeach
            </div>

            <div class="mt-5 rounded-xl bg-slate-50 dark:bg-slate-700/50 p-4">
                <div class="text-xs text-slate-400 dark:text-slate-500 mb-1">{{ __('Test') }}</div>
                <div class="font-bold text-slate-800 dark:text-slate-100">{{ $file->test_name ?? '—' }}</div>
                <div class="grid grid-cols-3 gap-3 mt-3 text-sm">
                    <div><div class="text-slate-400 dark:text-slate-500 text-xs">{{ __('Result') }}</div><div class="font-semibold">{{ $file->result ?: '—' }}</div></div>
                    <div><div class="text-slate-400 dark:text-slate-500 text-xs">{{ __('Unit') }}</div><div class="font-semibold">{{ $file->unit ?: '—' }}</div></div>
                    <div><div class="text-slate-400 dark:text-slate-500 text-xs">{{ __('Reference range') }}</div><div class="font-semibold">{{ $file->reference_range ?: '—' }}</div></div>
                </div>
            </div>
        </div>

        {{-- Status actions --}}
        @unless ($file->status->isClosed())
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-6" x-data="{ tab: 'explained' }">
            <h2 class="font-bold mb-4 text-slate-800 dark:text-slate-100">{{ __('Update status after the call') }}</h2>
            <div class="flex gap-2 mb-4">
                <button @click="tab='explained'" :class="tab==='explained' ? 'bg-emerald-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300'" class="px-4 py-2 rounded-xl text-sm font-semibold transition">{{ __('Explained') }}</button>
                <button @click="tab='no_reply'" :class="tab==='no_reply' ? 'bg-amber-500 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300'" class="px-4 py-2 rounded-xl text-sm font-semibold transition">{{ __('No reply') }}</button>
                <button @click="tab='deferred'" :class="tab==='deferred' ? 'bg-violet-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300'" class="px-4 py-2 rounded-xl text-sm font-semibold transition">{{ __('Defer') }}</button>
            </div>

            {{-- explained --}}
            <form x-show="tab==='explained'" method="POST" action="{{ route('doctor.status', $file) }}" class="space-y-3">
                @csrf @method('PATCH')
                <input type="hidden" name="action" value="explained">
                <textarea name="note" rows="2" placeholder="{{ __('Note (optional)') }}" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 outline-none"></textarea>
                <button class="bg-emerald-600 hover:bg-emerald-700 transition text-white font-bold px-5 py-2 rounded-xl">{{ __('Confirm: explained') }}</button>
            </form>

            {{-- no_reply --}}
            <form x-show="tab==='no_reply'" x-cloak method="POST" action="{{ route('doctor.status', $file) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="action" value="no_reply">
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-3">{{ __('No reply will be recorded and the attempts counter will increase (currently :count).', ['count' => $file->call_attempts]) }}</p>
                <button class="bg-amber-500 hover:bg-amber-600 transition text-white font-bold px-5 py-2 rounded-xl">{{ __('Record: no reply') }}</button>
            </form>

            {{-- deferred --}}
            <form x-show="tab==='deferred'" x-cloak method="POST" action="{{ route('doctor.status', $file) }}" class="space-y-3">
                @csrf @method('PATCH')
                <input type="hidden" name="action" value="deferred">
                <div>
                    <label class="block text-sm font-semibold text-slate-600 dark:text-slate-300 mb-1">{{ __('Call-back time') }}</label>
                    <input type="datetime-local" name="deferred_to" required class="rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 outline-none">
                </div>
                <textarea name="note" rows="2" placeholder="{{ __('Reason for deferral (optional)') }}" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 outline-none"></textarea>
                <button class="bg-violet-600 hover:bg-violet-700 transition text-white font-bold px-5 py-2 rounded-xl">{{ __('Defer file') }}</button>
            </form>
        </div>
        @else
            <div class="bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-200 rounded-2xl p-5 text-center font-semibold">
                ✅ {{ __('This file is closed — explained') }} @if($file->explained_at) {{ __('on :date', ['date' => $file->explained_at->format('Y-m-d H:i')]) }} @endif
            </div>
        @endunless

        @if ($file->source_path)
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-6">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-bold flex items-center gap-2 text-slate-800 dark:text-slate-100">🖼️ {{ __('Original report') }}</h2>
                <a href="{{ route('doctor.source', $file) }}" target="_blank"
                   class="text-sm font-semibold text-brand-700 dark:text-brand-500 hover:underline">{{ __('Open / download') }}</a>
            </div>
            @if ($file->source_type === 'pdf')
                <iframe src="{{ route('doctor.source', $file) }}" class="w-full h-[600px] rounded-lg border border-slate-200 dark:border-slate-700"></iframe>
            @else
                <a href="{{ route('doctor.source', $file) }}" target="_blank">
                    <img src="{{ route('doctor.source', $file) }}" alt="{{ __('Original report') }}"
                         class="max-h-[520px] w-auto mx-auto rounded-lg border border-slate-200 dark:border-slate-700">
                </a>
            @endif
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-3">{{ __('Compare the extracted data above against the original.') }}</p>
        </div>
        @endif
    </div>

    {{-- Sidebar: family + timeline --}}
    <div class="space-y-6">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-6">
            <h2 class="font-bold mb-3 flex items-center gap-2 text-slate-800 dark:text-slate-100">👨‍👩‍👧 {{ __('Family members') }} <span class="text-xs text-slate-400 dark:text-slate-500">{{ __('(same mobile)') }}</span></h2>
            @if ($family->isEmpty())
                <p class="text-sm text-slate-400 dark:text-slate-500">{{ __('No dependents for this number.') }}</p>
            @else
                <p class="text-xs text-slate-400 dark:text-slate-500 mb-3">{{ __('Explain their files in the same call.') }}</p>
                <ul class="space-y-3">
                    @foreach ($family as $member)
                        <li class="rounded-xl bg-slate-50 dark:bg-slate-700/50 p-3">
                            <div class="font-semibold text-slate-700 dark:text-slate-200 text-sm">{{ $member->name }}</div>
                            <div class="text-xs text-slate-400 dark:text-slate-500 mb-1">{{ $member->is_head ? __('Guardian') : __('Dependent') }}</div>
                            @foreach ($member->files as $mf)
                                <div class="flex items-center justify-between text-xs mt-1">
                                    <span class="text-slate-500 dark:text-slate-400">{{ $mf->test_name ?? __('file') }}</span>
                                    <x-status-badge :status="$mf->status" />
                                </div>
                            @endforeach
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-6">
            <h2 class="font-bold mb-3 text-slate-800 dark:text-slate-100">📜 {{ __('Status log') }}</h2>
            @if ($file->statusLogs->isEmpty())
                <p class="text-sm text-slate-400 dark:text-slate-500">{{ __('No log yet.') }}</p>
            @else
                <ol class="space-y-3 text-sm">
                    @foreach ($file->statusLogs->sortByDesc('created_at') as $log)
                        @php($logStatus = \App\Enums\FileStatus::tryFrom($log->to_status))
                        @php($statusLabels = ['new'=>'New','assigned'=>'Assigned','explained'=>'Explained','no_reply'=>'No reply','deferred'=>'Deferred'])
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
</div>
@endsection
