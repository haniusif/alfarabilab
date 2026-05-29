@extends('layouts.app')
@section('title', __('Doctor dashboard'))

@php
    $sections = [
        'due_today' => ['Urgent — follow-up due', 'border-rose-200 dark:border-rose-800', 'text-rose-600 dark:text-rose-400'],
        'pending'   => ['Awaiting call', 'border-blue-200 dark:border-blue-800', 'text-blue-600 dark:text-blue-400'],
        'deferred'  => ['Deferred files', 'border-violet-200 dark:border-violet-800', 'text-violet-600 dark:text-violet-400'],
        'no_reply'  => ['No reply', 'border-amber-200 dark:border-amber-800', 'text-amber-600 dark:text-amber-400'],
    ];
    $statCards = ['new'=>'New','assigned'=>'Assigned','deferred'=>'Deferred','no_reply'=>'No reply','explained'=>'Explained'];
@endphp

@section('content')
<div x-data="{ manual: false }">

    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <h1 class="text-2xl font-extrabold text-slate-800 dark:text-slate-100">{{ __('Doctor dashboard') }}</h1>
        <div class="flex flex-wrap items-center gap-2">
            <form method="POST" action="{{ route('doctor.upload') }}" enctype="multipart/form-data"
                  class="flex items-center gap-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2">
                @csrf
                <label class="text-sm font-semibold text-slate-600 dark:text-slate-300">📤 {{ __('Upload report(s):') }}</label>
                <input type="file" name="files[]" required multiple accept=".jpg,.jpeg,.png,.webp,.pdf" class="text-sm max-w-[200px] text-slate-600 dark:text-slate-300">
                <button class="bg-brand-700 hover:bg-brand-800 transition text-white text-sm font-semibold px-3 py-1.5 rounded-lg">{{ __('Upload & read') }}</button>
            </form>
            <button @click="manual = true" class="bg-slate-800 dark:bg-slate-600 hover:bg-slate-900 dark:hover:bg-slate-500 transition text-white text-sm font-semibold px-4 py-2.5 rounded-xl">
                ➕ {{ __('Manual entry') }}
            </button>
        </div>
    </div>

    {{-- Status summary --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-8">
        <a href="{{ route('doctor.files') }}"
           class="bg-brand-700 text-white rounded-2xl p-4 hover:bg-brand-800 transition">
            <div class="text-xs text-brand-100">{{ __('Total') }}</div>
            <div class="text-2xl font-extrabold">{{ $counts->sum() }}</div>
        </a>
        @foreach ($statCards as $sv => $lbl)
            <a href="{{ route('doctor.files', ['status' => $sv]) }}"
               class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 p-4 hover:shadow-md transition">
                <div class="text-xs text-slate-400 dark:text-slate-500">{{ __($lbl) }}</div>
                <div class="text-2xl font-extrabold text-slate-700 dark:text-slate-100">{{ $counts[$sv] ?? 0 }}</div>
            </a>
        @endforeach
    </div>

    @foreach ($sections as $key => [$title, $border, $text])
        @php($items = $groups[$key])
        @if ($items->isNotEmpty())
            <section class="mb-8">
                <h2 class="flex items-center gap-2 font-bold mb-3 {{ $text }}">
                    {{ __($title) }} <span class="text-xs bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-300 rounded-full px-2 py-0.5">{{ $items->count() }}</span>
                </h2>
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($items as $file)
                        <a href="{{ route('doctor.show', $file) }}"
                           class="block bg-white dark:bg-slate-800 rounded-2xl shadow-sm border {{ $border }} p-4 hover:shadow-md transition">
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <div class="font-bold text-slate-700 dark:text-slate-100">{{ $file->patient->name ?? __('Processing') }}</div>
                                <x-status-badge :status="$file->status" />
                            </div>
                            <div class="text-sm text-slate-500 dark:text-slate-400">{{ $file->test_name ?? __('Awaiting reading') }}</div>
                            @if ($file->result)
                                <div class="text-sm mt-1"><span class="text-slate-400 dark:text-slate-500">{{ __('Result:') }}</span> <span class="font-semibold">{{ $file->result }} {{ $file->unit }}</span></div>
                            @endif
                            <div class="text-xs text-slate-400 dark:text-slate-500 mt-2" dir="ltr">{{ $file->patient->mobile ?? '' }}</div>
                            @if ($file->deferred_to)
                                <div class="text-xs text-violet-500 dark:text-violet-400 mt-1">⏰ {{ $file->deferred_to->format('Y-m-d H:i') }}</div>
                            @endif
                            @if ($file->call_attempts)
                                <div class="text-xs text-amber-500 dark:text-amber-400 mt-1">{{ __('call attempts:') }} {{ $file->call_attempts }}</div>
                            @endif
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    @endforeach

    @if (collect($groups)->every(fn ($g) => $g->isEmpty()))
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 p-12 text-center text-slate-400 dark:text-slate-500">
            {{ __('No files assigned to you yet.') }}
        </div>
    @endif

    {{-- Manual entry modal --}}
    <div x-show="manual" x-cloak style="display:none"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
         @keydown.escape.window="manual = false">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl w-full max-w-lg p-6" @click.outside="manual = false">
            <div class="flex items-center justify-between mb-5">
                <h3 class="font-bold text-lg text-slate-800 dark:text-slate-100">{{ __('Add a file manually') }}</h3>
                <button @click="manual = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">✕</button>
            </div>
            <form method="POST" action="{{ route('doctor.manual') }}" class="grid grid-cols-2 gap-3">
                @csrf
                @foreach ([
                    ['name','Patient name', true],
                    ['mobile','Mobile number', true],
                    ['membership_no','Membership number', true],
                    ['national_id','National ID', true],
                    ['test_name','Test name', false],
                    ['result','Result', false],
                    ['unit','Unit', false],
                    ['reference_range','Reference range', false],
                ] as [$field,$label,$required])
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">{{ __($label) }}</label>
                        <input name="{{ $field }}" @if($required) required @endif value="{{ old($field) }}"
                               class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                    </div>
                @endforeach
                <div class="col-span-2 flex justify-end gap-2 mt-2">
                    <button type="button" @click="manual = false" class="px-4 py-2 rounded-xl text-slate-500 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">{{ __('Cancel') }}</button>
                    <button class="bg-brand-700 hover:bg-brand-800 transition text-white font-bold px-5 py-2 rounded-xl">{{ __('Save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
