@extends('layouts.app')
@section('title', __('Trash'))

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-extrabold text-slate-800 dark:text-slate-100">🗑️ {{ __('Trash') }}</h1>
    <span class="text-sm text-slate-500 dark:text-slate-400">{{ $files->total() }} {{ __('files') }}</span>
</div>

<p class="text-sm text-slate-500 dark:text-slate-400 mb-4">{{ __('Restore a file to bring it back, or delete permanently to remove the original report from storage.') }}</p>

<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">
    @if ($files->isEmpty())
        <div class="p-10 text-center text-slate-400 dark:text-slate-500">{{ __('Trash is empty.') }}</div>
    @else
        <table class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-700/50 text-slate-500 dark:text-slate-300">
                <tr>
                    <th class="px-4 py-3 font-semibold text-start">{{ __('Patient') }}</th>
                    <th class="px-4 py-3 font-semibold text-start">{{ __('Test') }}</th>
                    <th class="px-4 py-3 font-semibold text-start">{{ __('Insurance company') }}</th>
                    <th class="px-4 py-3 font-semibold text-start">{{ __('Doctor') }}</th>
                    <th class="px-4 py-3 font-semibold text-start">{{ __('Status') }}</th>
                    <th class="px-4 py-3 font-semibold text-start">{{ __('Deleted') }}</th>
                    <th class="px-4 py-3 font-semibold text-end">{{ __('Actions') }}</th>
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
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $file->doctor->name ?? '—' }}</td>
                        <td class="px-4 py-3"><x-status-badge :status="$file->status" /></td>
                        <td class="px-4 py-3 whitespace-nowrap text-slate-400 dark:text-slate-500" dir="ltr">{{ $file->deleted_at?->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-3 text-end whitespace-nowrap">
                            <form method="POST" action="{{ route('lab.trash.restore', $file->id) }}" class="inline">
                                @csrf
                                <button class="text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 text-sm font-semibold px-3 py-1.5 rounded-lg">
                                    {{ __('Restore') }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('lab.trash.force-destroy', $file->id) }}" class="inline"
                                  onsubmit="return confirm('{{ __('Delete permanently? This cannot be undone.') }}')">
                                @csrf @method('DELETE')
                                <button class="text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/30 text-sm font-semibold px-3 py-1.5 rounded-lg">
                                    {{ __('Delete permanently') }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

<div class="mt-4">{{ $files->links() }}</div>
@endsection
