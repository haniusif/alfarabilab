@extends('layouts.app')
@section('title', __('Duplicate files'))

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-extrabold text-slate-800 dark:text-slate-100">{{ __('Duplicate files') }}</h1>
    <span class="text-sm text-slate-500 dark:text-slate-400">{{ $groups->count() }}</span>
</div>

@if ($groups->isEmpty())
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 p-12 text-center text-slate-400 dark:text-slate-500">
        {{ __('No duplicate files 🎉') }}
    </div>
@else
    <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">{{ __('These files share the same accession number — keep one and delete the rest.') }}</p>

    <div class="space-y-5">
        @foreach ($groups as $accession => $files)
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-amber-200 dark:border-amber-800 overflow-hidden">
                <div class="bg-amber-50 dark:bg-amber-900/30 px-4 py-2 text-sm font-semibold text-amber-800 dark:text-amber-200 flex items-center justify-between">
                    <span>{{ __('Accession') }}: <span dir="ltr" class="font-mono">{{ $accession }}</span></span>
                    <span class="text-xs">{{ $files->count() }}</span>
                </div>
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @foreach ($files as $file)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50">
                                <td class="px-4 py-3">
                                    <a href="{{ route('doctor.show', $file) }}" class="font-semibold text-brand-700 dark:text-brand-500 hover:underline">
                                        {{ $file->patient->name ?? __('Processing') }}
                                    </a>
                                    <div class="text-xs text-slate-400 dark:text-slate-500">{{ $file->test_name ?? '—' }}</div>
                                </td>
                                <td class="px-4 py-3"><x-status-badge :status="$file->status" /></td>
                                <td class="px-4 py-3 text-slate-400 dark:text-slate-500 whitespace-nowrap" dir="ltr">{{ $file->created_at->format('Y-m-d H:i') }}</td>
                                <td class="px-4 py-3 text-end">
                                    <form method="POST" action="{{ route('doctor.destroy', $file) }}"
                                          onsubmit="return confirm('{{ __('Delete this duplicate?') }}')">
                                        @csrf @method('DELETE')
                                        <button class="text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/30 text-sm font-semibold px-3 py-1.5 rounded-lg">
                                            {{ __('Delete') }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    </div>
@endif
@endsection
