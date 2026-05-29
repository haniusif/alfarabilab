@extends('layouts.app')
@section('title', __('Submitted patient files'))

@section('content')
<h1 class="text-2xl font-extrabold text-slate-800 dark:text-slate-100 mb-6">{{ __('Submitted patient files') }}</h1>

{{-- بطاقات إحصائية --}}
<div class="grid gap-4 grid-cols-2 lg:grid-cols-4 mb-8">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-5">
        <div class="text-slate-400 dark:text-slate-500 text-sm">{{ __('Total files') }}</div>
        <div class="text-3xl font-extrabold text-slate-800 dark:text-slate-100">{{ $stats['total'] }}</div>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-5">
        <div class="text-slate-400 dark:text-slate-500 text-sm">{{ __('Awaiting assignment') }}</div>
        <div class="text-3xl font-extrabold text-amber-600 dark:text-amber-400">{{ $stats['new'] }}</div>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-5">
        <div class="text-slate-400 dark:text-slate-500 text-sm">{{ __('In progress') }}</div>
        <div class="text-3xl font-extrabold text-blue-600 dark:text-blue-400">{{ $stats['inProgress'] }}</div>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-5">
        <div class="text-slate-400 dark:text-slate-500 text-sm">{{ __('Explained (closed)') }}</div>
        <div class="text-3xl font-extrabold text-emerald-600 dark:text-emerald-400">{{ $stats['explained'] }}</div>
    </div>
</div>

<div class="grid gap-6 lg:grid-cols-3">
    {{-- Submit form --}}
    <div class="lg:col-span-1">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-6 sticky top-6">
            <h2 class="font-bold mb-4 flex items-center gap-2">📤 {{ __('Send a new file') }}</h2>
            <form method="POST" action="{{ route('insurance.store') }}" class="space-y-3">
                @csrf
                @foreach ([
                    ['name','Patient name'],
                    ['mobile','Mobile number'],
                    ['membership_no','Membership number'],
                    ['national_id','National ID'],
                    ['test_name','Test name'],
                ] as [$field,$label])
                    <div>
                        <label class="block text-sm font-semibold text-slate-600 dark:text-slate-300 mb-1">{{ __($label) }}</label>
                        <input type="text" name="{{ $field }}" value="{{ old($field) }}" required
                               class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 px-3 py-2 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
                    </div>
                @endforeach
                <button class="w-full bg-brand-700 hover:bg-brand-800 transition text-white font-bold py-2.5 rounded-xl mt-2">
                    {{ __('Send to lab') }}
                </button>
            </form>
        </div>
    </div>

    {{-- Files table --}}
    <div class="lg:col-span-2">
        <div class="flex items-center justify-between gap-3 mb-2">
            <span class="text-sm text-slate-500 dark:text-slate-400">{{ $files->total() }} {{ __('files') }}</span>
            @if ($files->total() > 0)
                <a href="{{ route('insurance.export', request()->only('q', 'status')) }}"
                   class="inline-flex items-center gap-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition text-sm font-semibold px-3 py-1.5 rounded-lg">
                    ⬇ {{ __('Export CSV') }}
                </a>
            @endif
        </div>
        @include('partials.file-filters', ['action' => route('insurance.index'), 'showStatus' => true])
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">
            @if ($files->isEmpty())
                <div class="p-10 text-center text-slate-400 dark:text-slate-500">{{ __('No files submitted yet.') }}</div>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-700/50 text-slate-500 dark:text-slate-300 text-start">
                        <tr>
                            <th class="px-4 py-3 font-semibold text-start">{{ __('Patient') }}</th>
                            <th class="px-4 py-3 font-semibold text-start">{{ __('Test') }}</th>
                            <th class="px-4 py-3 font-semibold text-start">{{ __('Doctor') }}</th>
                            <th class="px-4 py-3 font-semibold text-start">{{ __('Status') }}</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @foreach ($files as $file)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50">
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-slate-700 dark:text-slate-200">{{ $file->patient->name ?? '—' }}</div>
                                    <div class="text-xs text-slate-400 dark:text-slate-500" dir="ltr">{{ $file->patient->mobile ?? '' }}</div>
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $file->test_name ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $file->doctor->name ?? __('— not assigned') }}</td>
                                <td class="px-4 py-3"><x-status-badge :status="$file->status" /></td>
                                <td class="px-4 py-3 text-end">
                                    <a href="{{ route('insurance.show', $file) }}" class="text-brand-700 dark:text-brand-400 hover:underline text-sm font-semibold whitespace-nowrap">{{ __('View') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
        <div class="mt-4">{{ $files->links() }}</div>
    </div>
</div>
@endsection
