@php($loc = app()->getLocale())
<!DOCTYPE html>
<html dir="{{ $loc === 'ar' ? 'rtl' : 'ltr' }}" lang="{{ $loc }}">
<head>
    @include('partials.head', ['title' => trim($__env->yieldContent('title'))])
</head>
<body class="bg-slate-100 dark:bg-slate-900 text-slate-800 dark:text-slate-100 min-h-screen flex flex-col transition-colors">

@auth
    @php($role = auth()->user()->role)
    @php($roleLabels = ['insurance' => 'Insurance company', 'lab_admin' => 'Lab administration', 'doctor' => 'Doctor'])
    <nav class="bg-brand-800 text-white shadow-lg">
        <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between gap-3">
            <div class="flex items-center gap-4 min-w-0">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2 font-extrabold text-lg shrink-0">
                    <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white/15">🧪</span>
                    <span class="hidden sm:inline">{{ config('app.name') }}</span>
                </a>
                @include('partials.nav-links')
            </div>
            <div class="flex items-center gap-3 text-sm">
                <span class="hidden sm:inline-flex items-center gap-2 bg-white/10 px-3 py-1.5 rounded-full">
                    <span class="font-semibold">{{ auth()->user()->name }}</span>
                    <span class="text-brand-100">· {{ __($roleLabels[$role->value] ?? '') }}</span>
                </span>
                @include('partials.toggles')
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="bg-white/15 hover:bg-white/25 transition px-3 py-1.5 rounded-lg font-semibold">{{ __('Logout') }}</button>
                </form>
            </div>
        </div>
    </nav>
@endauth

<main class="flex-1 w-full max-w-6xl mx-auto px-4 py-8">
    @if (session('status'))
        <div x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,5000)"
             class="mb-6 flex items-center justify-between gap-3 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 px-4 py-3">
            <span>✅ {{ session('status') }}</span>
            <button @click="show=false" class="text-emerald-500 hover:text-emerald-700">✕</button>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-xl bg-rose-50 dark:bg-rose-900/30 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 px-4 py-3">
            <ul class="list-disc px-5 space-y-1 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</main>

<footer class="text-center text-xs text-slate-400 dark:text-slate-500 py-6">
    {{ config('app.name') }} — {{ __('Lab–insurance integration system') }}
</footer>
</body>
</html>
