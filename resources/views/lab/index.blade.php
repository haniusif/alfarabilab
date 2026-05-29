@extends('layouts.app')
@section('title', __('Lab administration'))

@section('content')
{{-- شريط علوي: رفع تقرير --}}
<div class="flex items-center justify-between gap-3 mb-6" x-data="{ uploadOpen: false }">
    <h1 class="text-2xl font-extrabold text-slate-800 dark:text-slate-100">{{ __('Lab administration') }}</h1>
    <button @click="uploadOpen = true" class="bg-brand-700 hover:bg-brand-800 transition text-white text-sm font-semibold px-4 py-2 rounded-xl">
        📤 {{ __('Upload report') }}
    </button>

    {{-- نافذة الرفع --}}
    <div x-show="uploadOpen" x-cloak class="fixed inset-0 z-30 flex items-center justify-center p-4 bg-black/40"
         @click.self="uploadOpen = false" @keydown.escape.window="uploadOpen = false">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl w-full max-w-md p-6" x-transition>
            <h2 class="text-lg font-extrabold text-slate-800 dark:text-slate-100 mb-1">{{ __('Upload report') }}</h2>
            <p class="text-xs text-slate-400 dark:text-slate-500 mb-4">{{ __('Patient data is read from the file automatically.') }}</p>
            <form method="POST" action="{{ route('lab.upload') }}" enctype="multipart/form-data" class="space-y-3 text-start">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ __('Report file(s)') }}</label>
                    <input type="file" name="files[]" multiple required accept=".jpg,.jpeg,.png,.webp,.pdf"
                           class="w-full text-sm text-slate-600 dark:text-slate-300 file:me-3 file:rounded-lg file:border-0 file:bg-brand-50 dark:file:bg-brand-900/40 file:text-brand-700 dark:file:text-brand-300 file:px-3 file:py-1.5 file:font-semibold">
                    <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">{{ __('JPG, PNG, WEBP or PDF — up to 10 MB each.') }}</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ __('Insurance company') }}</label>
                    <select name="insurance_company_id" required
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                        <option value="">{{ __('— choose a company —') }}</option>
                        @foreach ($insurers as $insurer)
                            <option value="{{ $insurer->id }}">{{ $insurer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">
                        {{ __('Assign to doctor') }} <span class="font-normal text-slate-400">({{ __('optional') }})</span>
                    </label>
                    <select name="doctor_id"
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                        <option value="">{{ __('— leave unassigned —') }}</option>
                        @foreach ($doctors as $doctor)
                            <option value="{{ $doctor->id }}">{{ $doctor->name }} ({{ $doctor->open_files_count }})</option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">{{ __('Leave empty to send it to the unassigned queue.') }}</p>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="uploadOpen = false" class="text-sm text-slate-500 dark:text-slate-400 px-3 py-2 hover:underline">{{ __('Cancel') }}</button>
                    <button class="bg-brand-700 hover:bg-brand-800 transition text-white text-sm font-semibold px-4 py-2 rounded-xl">{{ __('Upload & assign') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if ($overdueCount > 0)
    <a href="{{ route('lab.activity') }}"
       class="flex items-center justify-between gap-3 mb-6 rounded-xl bg-rose-50 dark:bg-rose-900/30 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 px-4 py-3 hover:bg-rose-100 dark:hover:bg-rose-900/50 transition">
        <span>🚨 {{ trans_choice(':count file needs attention|:count files need attention', $overdueCount, ['count' => $overdueCount]) }}</span>
        <span class="text-sm font-semibold underline">{{ __('View') }}</span>
    </a>
@endif

<div class="grid gap-4 sm:grid-cols-3 mb-8">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-5">
        <div class="text-slate-400 dark:text-slate-500 text-sm">{{ __('Awaiting assignment') }}</div>
        <div class="text-3xl font-extrabold text-amber-600 dark:text-amber-400">{{ $unassigned->count() }}</div>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-5">
        <div class="text-slate-400 dark:text-slate-500 text-sm">{{ __('Assigned files') }}</div>
        <div class="text-3xl font-extrabold text-blue-600 dark:text-blue-400">{{ $assignedCount }}</div>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-5">
        <div class="text-slate-400 dark:text-slate-500 text-sm">{{ __('Available doctors') }}</div>
        <div class="text-3xl font-extrabold text-brand-700 dark:text-brand-500">{{ $doctors->count() }}</div>
    </div>
</div>

{{-- حِمل العمل لكل طبيب --}}
<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-5 mb-8">
    <h2 class="text-sm font-semibold text-slate-500 dark:text-slate-400 mb-3">{{ __('Doctor workload (open files)') }}</h2>
    @if ($doctors->isEmpty())
        <p class="text-sm text-slate-400 dark:text-slate-500">{{ __('No active doctors available') }}</p>
    @else
        <div class="flex flex-wrap gap-2">
            @foreach ($doctors as $doctor)
                <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 dark:bg-slate-700 px-3 py-1.5 text-sm">
                    <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $doctor->name }}</span>
                    <span class="inline-flex items-center justify-center min-w-[1.5rem] h-6 px-1.5 rounded-full text-xs font-bold
                        {{ $doctor->open_files_count == 0 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-200' }}">
                        {{ $doctor->open_files_count }}
                    </span>
                </span>
            @endforeach
        </div>
    @endif
</div>

<div class="flex items-center justify-between gap-3 mb-4">
    <h1 class="text-2xl font-extrabold text-slate-800 dark:text-slate-100">{{ __('Incoming files awaiting assignment') }}</h1>
    @if ($unassigned->isNotEmpty() && $doctors->isNotEmpty())
        <form method="POST" action="{{ route('lab.auto-assign') }}"
              onsubmit="return confirm('{{ __('Auto-assign all waiting files to the least-loaded doctors?') }}')">
            @csrf
            <button class="bg-brand-700 hover:bg-brand-800 transition text-white text-sm font-semibold px-4 py-2 rounded-xl">
                ⚡ {{ __('Auto-assign all') }}
            </button>
        </form>
    @endif
</div>

<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden"
     x-data="{ selected: [] }">
    @if ($unassigned->isEmpty())
        <div class="p-10 text-center text-slate-400 dark:text-slate-500">{{ __('No files awaiting assignment 🎉') }}</div>
    @else
        {{-- شريط الإسناد الجماعي --}}
        <form method="POST" action="{{ route('lab.bulk-assign') }}" x-show="selected.length > 0" x-cloak
              class="flex flex-wrap items-center gap-3 bg-brand-50 dark:bg-brand-900/30 border-b border-brand-100 dark:border-brand-800 px-4 py-3">
            @csrf
            <template x-for="id in selected" :key="id">
                <input type="hidden" name="file_ids[]" :value="id">
            </template>
            <span class="text-sm font-semibold text-brand-800 dark:text-brand-200">
                <span x-text="selected.length"></span> {{ __('selected') }}
            </span>
            <select name="doctor_id" required
                    class="rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 px-2 py-1.5 text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                <option value="">{{ __('— choose a doctor —') }}</option>
                @foreach ($doctors as $doctor)
                    <option value="{{ $doctor->id }}">{{ $doctor->name }} ({{ $doctor->open_files_count }})</option>
                @endforeach
            </select>
            <button class="bg-brand-700 hover:bg-brand-800 transition text-white text-sm font-semibold px-3 py-1.5 rounded-lg">
                {{ __('Assign selected') }}
            </button>
            <button type="button" @click="selected = []" class="text-sm text-slate-500 dark:text-slate-400 hover:underline">
                {{ __('Clear') }}
            </button>
        </form>

        <table class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-700/50 text-slate-500 dark:text-slate-300">
                <tr>
                    <th class="px-4 py-3 w-10">
                        <input type="checkbox" class="rounded border-slate-300 dark:border-slate-600"
                               @change="selected = $event.target.checked ? {{ $unassigned->pluck('id')->toJson() }} : []"
                               :checked="selected.length === {{ $unassigned->count() }}">
                    </th>
                    <th class="px-4 py-3 font-semibold text-start">{{ __('Patient') }}</th>
                    <th class="px-4 py-3 font-semibold text-start">{{ __('Test') }}</th>
                    <th class="px-4 py-3 font-semibold text-start">{{ __('Insurance company') }}</th>
                    <th class="px-4 py-3 font-semibold text-start">{{ __('Assign to doctor') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @foreach ($unassigned as $file)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50">
                        <td class="px-4 py-3">
                            <input type="checkbox" value="{{ $file->id }}" x-model.number="selected"
                                   class="rounded border-slate-300 dark:border-slate-600">
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-semibold text-slate-700 dark:text-slate-200">{{ $file->patient->name ?? '—' }}</div>
                            <div class="text-xs text-slate-400 dark:text-slate-500" dir="ltr">{{ $file->patient->mobile ?? '' }}</div>
                        </td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $file->test_name ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $file->insuranceCompany->name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <form method="POST" action="{{ route('lab.assign', $file) }}" class="flex items-center gap-2">
                                @csrf
                                <select name="doctor_id" required
                                        class="rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 px-2 py-1.5 text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                                    <option value="">{{ __('— choose a doctor —') }}</option>
                                    @foreach ($doctors as $doctor)
                                        <option value="{{ $doctor->id }}">{{ $doctor->name }} ({{ $doctor->specialty }})</option>
                                    @endforeach
                                </select>
                                <button class="bg-brand-700 hover:bg-brand-800 transition text-white text-sm font-semibold px-3 py-1.5 rounded-lg">
                                    {{ __('Assign') }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
