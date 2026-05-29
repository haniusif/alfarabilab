@php
    $statusLabels = ['new'=>'New','assigned'=>'Assigned','explained'=>'Explained','no_reply'=>'No reply','deferred'=>'Deferred'];
    $showStatus = $showStatus ?? true;
@endphp
<form method="GET" action="{{ $action }}" class="flex flex-wrap items-end gap-2 mb-4">
    @if (request('when'))
        <input type="hidden" name="when" value="{{ request('when') }}">
    @endif
    <div class="flex-1 min-w-[200px]">
        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ __('Search by name or mobile') }}</label>
        <input type="text" name="q" value="{{ request('q') }}"
               class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 outline-none">
    </div>
    @if ($showStatus)
        <div>
            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ __('Status') }}</label>
            <select name="status"
                    class="rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                <option value="">{{ __('All statuses') }}</option>
                @foreach (\App\Enums\FileStatus::cases() as $st)
                    <option value="{{ $st->value }}" @selected(request('status') === $st->value)>{{ __($statusLabels[$st->value]) }}</option>
                @endforeach
            </select>
        </div>
    @endif
    <button class="bg-brand-700 hover:bg-brand-800 transition text-white text-sm font-semibold px-4 py-2 rounded-xl">{{ __('Search') }}</button>
    @if (request('q') || request('status'))
        <a href="{{ $action }}" class="text-sm text-slate-500 dark:text-slate-400 px-3 py-2 hover:underline">{{ __('Reset') }}</a>
    @endif
</form>
