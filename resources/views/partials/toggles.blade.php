@php($loc = app()->getLocale())
<div class="flex items-center gap-2">
    {{-- Language switch --}}
    <a href="{{ route('locale.switch', $loc === 'ar' ? 'en' : 'ar') }}"
       class="inline-flex items-center justify-center min-w-9 h-9 px-2 rounded-lg bg-white/15 hover:bg-white/25 text-white text-sm font-bold transition"
       title="{{ $loc === 'ar' ? 'English' : 'العربية' }}">
        {{ $loc === 'ar' ? 'EN' : 'ع' }}
    </a>

    {{-- Theme switch --}}
    <button type="button"
            onclick="(function(){var r=document.documentElement;r.classList.toggle('dark');localStorage.setItem('theme', r.classList.contains('dark')?'dark':'light');})()"
            class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-white/15 hover:bg-white/25 text-white transition"
            title="Toggle theme">
        <span class="dark:hidden">🌙</span>
        <span class="hidden dark:inline">☀️</span>
    </button>
</div>
