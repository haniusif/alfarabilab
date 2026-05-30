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
                    <div class="flex items-center gap-2">
                        <h1 class="text-2xl font-extrabold text-slate-800 dark:text-slate-100">{{ $file->patient->name ?? __('Processing') }}</h1>
                        @if ($file->patient?->is_head && $family->isNotEmpty())
                            <span class="text-xs font-bold uppercase tracking-wide px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-200">★ {{ __('Main') }}</span>
                        @endif
                    </div>
                    @if ($file->patient?->mobile)
                        <div class="flex items-center gap-2 mt-0.5" dir="ltr">
                            <span class="text-slate-400 dark:text-slate-500 text-sm">{{ $file->patient->mobile }}</span>
                            <a href="tel:{{ preg_replace('/\s+/', '', $file->patient->mobile) }}"
                               class="inline-flex items-center gap-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold px-2.5 py-1 rounded-full transition"
                               title="{{ __('Call :n', ['n' => $file->patient->mobile]) }}">
                                📞 <span>{{ __('Call') }}</span>
                            </a>
                        </div>
                    @endif
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
        @php($familyCount = $familyOpenFiles->count())
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-6"
             x-data="{
                tab: 'explained',
                pending: null,
                pendingAction: '',
                actionLabels: {
                    explained: @js(__('Explained')),
                    no_reply: @js(__('No reply')),
                    deferred: @js(__('Defer')),
                },
                hasFamily: {{ $familyCount > 0 ? 'true' : 'false' }},
                intercept(event) {
                    if (! this.hasFamily) return;
                    event.preventDefault();
                    this.pending = event.target;
                    this.pendingAction = event.target.querySelector('input[name=action]')?.value || '';
                },
                submitWithFamily(all) {
                    if (! this.pending) return;
                    this.pending.querySelector('input[name=apply_to_family]').value = all ? '1' : '0';
                    const form = this.pending;
                    this.pending = null;
                    form.submit();
                },
             }">
            <h2 class="font-bold mb-4 text-slate-800 dark:text-slate-100">{{ __('Update status after the call') }}</h2>
            <div class="flex gap-2 mb-4">
                <button @click="tab='explained'" :class="tab==='explained' ? 'bg-emerald-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300'" class="px-4 py-2 rounded-xl text-sm font-semibold transition">{{ __('Explained') }}</button>
                <button @click="tab='no_reply'" :class="tab==='no_reply' ? 'bg-amber-500 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300'" class="px-4 py-2 rounded-xl text-sm font-semibold transition">{{ __('No reply') }}</button>
                <button @click="tab='deferred'" :class="tab==='deferred' ? 'bg-violet-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300'" class="px-4 py-2 rounded-xl text-sm font-semibold transition">{{ __('Defer') }}</button>
            </div>

            {{-- explained --}}
            <form x-show="tab==='explained'" @submit="intercept($event)" method="POST" action="{{ route('doctor.status', $file) }}" class="space-y-3">
                @csrf @method('PATCH')
                <input type="hidden" name="action" value="explained">
                <input type="hidden" name="apply_to_family" value="0">
                <textarea name="note" rows="2" placeholder="{{ __('Note (optional)') }}" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 outline-none"></textarea>
                <button class="bg-emerald-600 hover:bg-emerald-700 transition text-white font-bold px-5 py-2 rounded-xl">{{ __('Confirm: explained') }}</button>
            </form>

            {{-- no_reply --}}
            <form x-show="tab==='no_reply'" x-cloak @submit="intercept($event)" method="POST" action="{{ route('doctor.status', $file) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="action" value="no_reply">
                <input type="hidden" name="apply_to_family" value="0">
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-3">{{ __('No reply will be recorded and the attempts counter will increase (currently :count).', ['count' => $file->call_attempts]) }}</p>
                <button class="bg-amber-500 hover:bg-amber-600 transition text-white font-bold px-5 py-2 rounded-xl">{{ __('Record: no reply') }}</button>
            </form>

            {{-- deferred --}}
            <form x-show="tab==='deferred'" x-cloak @submit="intercept($event)" method="POST" action="{{ route('doctor.status', $file) }}" class="space-y-3">
                @csrf @method('PATCH')
                <input type="hidden" name="action" value="deferred">
                <input type="hidden" name="apply_to_family" value="0">
                <div>
                    <label class="block text-sm font-semibold text-slate-600 dark:text-slate-300 mb-1">{{ __('Call-back time') }}</label>
                    <input type="datetime-local" name="deferred_to" required class="rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 outline-none">
                </div>
                <textarea name="note" rows="2" placeholder="{{ __('Reason for deferral (optional)') }}" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 outline-none"></textarea>
                <button class="bg-violet-600 hover:bg-violet-700 transition text-white font-bold px-5 py-2 rounded-xl">{{ __('Defer file') }}</button>
            </form>

            {{-- Modal: apply to family? --}}
            @if ($familyCount > 0)
            <div x-show="pending" x-cloak style="display:none"
                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
                 @keydown.escape.window="pending = null">
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl w-full max-w-md p-6 space-y-4" @click.outside="pending = null">
                    <div>
                        <h3 class="font-bold text-lg text-slate-800 dark:text-slate-100">
                            👨‍👩‍👧 {{ __('Apply to family?') }}
                        </h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                            {{ __('Action:') }}
                            <span class="font-semibold text-slate-700 dark:text-slate-200" x-text="actionLabels[pendingAction] || ''"></span>
                            — {{ __('This patient shares a mobile with :count other open file(s) assigned to you. Apply the same action to all?', ['count' => $familyCount]) }}
                        </p>
                    </div>

                    <ul class="max-h-48 overflow-auto space-y-1 text-sm border border-slate-100 dark:border-slate-700 rounded-xl p-3 bg-slate-50 dark:bg-slate-700/30">
                        @foreach ($familyOpenFiles as $ff)
                            <li class="flex items-center justify-between gap-2">
                                <span class="text-slate-700 dark:text-slate-200 truncate">
                                    {{ $ff->patient->name ?? __('Processing') }}
                                    <span class="text-slate-400 dark:text-slate-500">— {{ $ff->test_name ?? __('file') }}</span>
                                </span>
                                <x-status-badge :status="$ff->status" />
                            </li>
                        @endforeach
                    </ul>

                    <div class="flex flex-col sm:flex-row gap-2 justify-end pt-2">
                        <button type="button" @click="pending = null"
                                class="px-4 py-2 rounded-xl text-slate-500 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 text-sm font-semibold">
                            {{ __('Cancel') }}
                        </button>
                        <button type="button" @click="submitWithFamily(false)"
                                class="px-4 py-2 rounded-xl bg-slate-200 dark:bg-slate-600 text-slate-700 dark:text-slate-100 hover:bg-slate-300 dark:hover:bg-slate-500 text-sm font-semibold">
                            {{ __('Only this file') }}
                        </button>
                        <button type="button" @click="submitWithFamily(true)"
                                class="px-4 py-2 rounded-xl bg-brand-700 hover:bg-brand-800 text-white text-sm font-bold">
                            ★ {{ __('Apply to all (:n)', ['n' => $familyCount + 1]) }}
                        </button>
                    </div>
                </div>
            </div>
            @endif
        </div>
        @else
            <div class="bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-200 rounded-2xl p-5 text-center font-semibold">
                ✅ {{ __('This file is closed — explained') }} @if($file->explained_at) {{ __('on :date', ['date' => $file->explained_at->format('Y-m-d H:i')]) }} @endif
            </div>
        @endunless

        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-6 flex items-center justify-between gap-4">
            <div>
                <h2 class="font-bold text-slate-800 dark:text-slate-100">🗑️ {{ __('Move to trash') }}</h2>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">{{ __('The file can be restored later from the trash.') }}</p>
            </div>
            <form method="POST" action="{{ route('doctor.destroy', $file) }}"
                  onsubmit="return confirm('{{ __('Move this file to trash?') }}')">
                @csrf @method('DELETE')
                <button class="bg-rose-600 hover:bg-rose-700 transition text-white font-bold px-4 py-2 rounded-xl text-sm">
                    {{ __('Move to trash') }}
                </button>
            </form>
        </div>

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
                    @if (! $file->patient?->is_head && $file->patient)
                        <li class="rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50/50 dark:bg-amber-900/10 p-3 flex items-center justify-between gap-2">
                            <span class="text-xs text-amber-800 dark:text-amber-200">{{ __('Mark this patient as the main of the family?') }}</span>
                            <form method="POST" action="{{ route('doctor.patient.mark-main', $file->patient) }}">
                                @csrf
                                <button class="text-xs font-bold px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-white transition whitespace-nowrap">
                                    ★ {{ __('Mark as main') }}
                                </button>
                            </form>
                        </li>
                    @endif
                    @foreach ($family as $member)
                        @php($memberFile = $member->files->firstWhere('doctor_id', $file->doctor_id))
                        <li class="rounded-xl bg-slate-50 dark:bg-slate-700/50 p-3">
                            <div class="flex items-start justify-between gap-2 mb-1">
                                <div class="min-w-0">
                                    @if ($memberFile)
                                        <a href="{{ route('doctor.show', $memberFile) }}" class="font-semibold text-sm text-brand-600 dark:text-brand-400 hover:underline">{{ $member->name }}</a>
                                    @else
                                        <div class="font-semibold text-sm text-slate-700 dark:text-slate-200">{{ $member->name }}</div>
                                    @endif
                                    <div class="text-xs text-slate-400 dark:text-slate-500 flex items-center gap-1.5">
                                        @if ($member->is_head)
                                            <span class="font-bold uppercase tracking-wide px-1.5 py-0.5 rounded bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-200">★ {{ __('Main') }}</span>
                                        @else
                                            <span>{{ __('Dependent') }}</span>
                                        @endif
                                    </div>
                                </div>
                                @if (! $member->is_head && $memberFile)
                                    <form method="POST" action="{{ route('doctor.patient.mark-main', $member) }}"
                                          onsubmit="return confirm('{{ __('Mark :name as the main family member?', ['name' => $member->name]) }}')">
                                        @csrf
                                        <button class="text-xs font-semibold text-amber-700 dark:text-amber-300 hover:bg-amber-100 dark:hover:bg-amber-900/30 px-2 py-1 rounded-md whitespace-nowrap">
                                            ★ {{ __('Mark as main') }}
                                        </button>
                                    </form>
                                @endif
                            </div>
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
