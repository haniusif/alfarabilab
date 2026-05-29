@extends('layouts.app')
@section('title', __('Doctors'))

@section('content')
<div class="flex items-center justify-between gap-3 mb-6" x-data="{ adding: false }">
    <h1 class="text-2xl font-extrabold text-slate-800 dark:text-slate-100">{{ __('Doctors') }}</h1>
    <div class="flex items-center gap-2">
        <a href="{{ route('lab.doctors.export') }}"
           class="inline-flex items-center gap-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition text-sm font-semibold px-3 py-2 rounded-xl">
            ⬇ {{ __('Export CSV') }}
        </a>
        <button @click="adding = true" class="bg-brand-700 hover:bg-brand-800 transition text-white text-sm font-semibold px-4 py-2 rounded-xl">
            + {{ __('Add doctor') }}
        </button>
    </div>

    {{-- نافذة إضافة طبيب --}}
    <div x-show="adding" x-cloak class="fixed inset-0 z-30 flex items-center justify-center p-4 bg-black/40"
         @click.self="adding = false" @keydown.escape.window="adding = false">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl w-full max-w-md p-6" x-transition>
            <h2 class="text-lg font-extrabold text-slate-800 dark:text-slate-100 mb-4">{{ __('Add doctor') }}</h2>
            <form method="POST" action="{{ route('lab.doctors.store') }}" class="space-y-3 text-start">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ __('Name') }}</label>
                    <input type="text" name="name" required value="{{ old('name') }}"
                           class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ __('Email') }}</label>
                    <input type="email" name="email" required dir="ltr" value="{{ old('email') }}"
                           class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ __('Specialty') }}</label>
                    <input type="text" name="specialty" value="{{ old('specialty') }}"
                           class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ __('Password') }}</label>
                    <input type="password" name="password" required dir="ltr" autocomplete="new-password"
                           class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                </div>
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="adding = false" class="text-sm text-slate-500 dark:text-slate-400 px-3 py-2 hover:underline">{{ __('Cancel') }}</button>
                    <button class="bg-brand-700 hover:bg-brand-800 transition text-white text-sm font-semibold px-4 py-2 rounded-xl">{{ __('Save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">
    @if ($doctors->isEmpty())
        <div class="p-10 text-center text-slate-400 dark:text-slate-500">{{ __('No doctors yet.') }}</div>
    @else
        <table class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-700/50 text-slate-500 dark:text-slate-300">
                <tr>
                    <th class="px-4 py-3 font-semibold text-start">{{ __('Name') }}</th>
                    <th class="px-4 py-3 font-semibold text-start">{{ __('Specialty') }}</th>
                    <th class="px-4 py-3 font-semibold text-start">{{ __('Open files') }}</th>
                    <th class="px-4 py-3 font-semibold text-start">{{ __('Total files') }}</th>
                    <th class="px-4 py-3 font-semibold text-start">{{ __('Status') }}</th>
                    <th class="px-4 py-3 font-semibold text-start">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @foreach ($doctors as $doctor)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50" x-data="{ editing: false }">
                        <td class="px-4 py-3">
                            <div class="font-semibold text-slate-700 dark:text-slate-200">{{ $doctor->name }}</div>
                            <div class="text-xs text-slate-400 dark:text-slate-500" dir="ltr">{{ $doctor->email }}</div>
                        </td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $doctor->specialty ?? '—' }}</td>
                        <td class="px-4 py-3 font-bold text-blue-600 dark:text-blue-400">{{ $doctor->open_files_count }}</td>
                        <td class="px-4 py-3 text-slate-500 dark:text-slate-400">{{ $doctor->total_files_count }}</td>
                        <td class="px-4 py-3">
                            @if ($doctor->is_active)
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200">{{ __('Active') }}</span>
                            @else
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400">{{ __('Inactive') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <button type="button" @click="editing = !editing" class="text-brand-700 dark:text-brand-400 hover:underline">{{ __('Edit') }}</button>
                                <form method="POST" action="{{ route('lab.doctors.toggle', $doctor) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="text-sm {{ $doctor->is_active ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }} hover:underline">
                                        {{ $doctor->is_active ? __('Deactivate') : __('Activate') }}
                                    </button>
                                </form>
                            </div>

                            {{-- نموذج التعديل --}}
                            <div x-show="editing" x-cloak class="mt-3">
                                <form method="POST" action="{{ route('lab.doctors.update', $doctor) }}" class="grid gap-2 sm:grid-cols-2 bg-slate-50 dark:bg-slate-700/40 rounded-xl p-3">
                                    @csrf
                                    @method('PATCH')
                                    <input type="text" name="name" value="{{ $doctor->name }}" placeholder="{{ __('Name') }}" required
                                           class="rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 px-2 py-1.5 text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                                    <input type="email" name="email" value="{{ $doctor->email }}" placeholder="{{ __('Email') }}" required dir="ltr"
                                           class="rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 px-2 py-1.5 text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                                    <input type="text" name="specialty" value="{{ $doctor->specialty }}" placeholder="{{ __('Specialty') }}"
                                           class="rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 px-2 py-1.5 text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                                    <input type="password" name="password" placeholder="{{ __('New password (optional)') }}" dir="ltr" autocomplete="new-password"
                                           class="rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 px-2 py-1.5 text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                                    <div class="sm:col-span-2 flex items-center justify-end gap-2">
                                        <button type="button" @click="editing = false" class="text-sm text-slate-500 dark:text-slate-400 px-3 py-1.5 hover:underline">{{ __('Cancel') }}</button>
                                        <button class="bg-brand-700 hover:bg-brand-800 transition text-white text-sm font-semibold px-3 py-1.5 rounded-lg">{{ __('Save') }}</button>
                                    </div>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
