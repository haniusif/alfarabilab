@php($loc = app()->getLocale())
<!DOCTYPE html>
<html dir="{{ $loc === 'ar' ? 'rtl' : 'ltr' }}" lang="{{ $loc }}">
<head>
    @include('partials.head', ['title' => __('Sign in')])
</head>
<body class="bg-gradient-to-br from-teal-800 to-emerald-600 dark:from-slate-900 dark:to-teal-900 min-h-screen flex items-center justify-center p-4">

<div class="absolute top-4 ltr:right-4 rtl:left-4">
    @include('partials.toggles')
</div>

<div class="w-full max-w-md">
    <a href="{{ route('landing') }}" class="flex items-center justify-center gap-2 text-white font-extrabold text-2xl mb-6">
        <span class="inline-flex items-center justify-center w-11 h-11 rounded-2xl bg-white/15">🧪</span>
        {{ config('app.name') }}
    </a>

    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl p-8">
        <h1 class="text-xl font-bold mb-6 text-slate-800 dark:text-slate-100">{{ __('Sign in') }}</h1>

        @if ($errors->any())
            <div class="mb-5 rounded-xl bg-rose-50 dark:bg-rose-900/30 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-200 px-4 py-3 text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-slate-600 dark:text-slate-300 mb-1">{{ __('Email') }}</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus dir="ltr"
                       class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 px-4 py-2.5 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none text-left">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-600 dark:text-slate-300 mb-1">{{ __('Password') }}</label>
                <input type="password" name="password" required dir="ltr"
                       class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 px-4 py-2.5 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none text-left">
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                <input type="checkbox" name="remember" class="rounded border-slate-300 text-teal-600 focus:ring-teal-500">
                {{ __('Remember me') }}
            </label>
            <button class="w-full bg-teal-700 hover:bg-teal-800 transition text-white font-bold py-2.5 rounded-xl">
                {{ __('Login') }}
            </button>
        </form>

        <div class="mt-6 pt-5 border-t border-slate-100 dark:border-slate-700 text-xs text-slate-400 space-y-1">
            <p class="font-semibold text-slate-500 dark:text-slate-400">{{ __('Demo accounts') }} (password):</p>
            <ul dir="ltr" class="font-mono space-y-0.5 list-disc list-inside">
                <li>insurance@alfarabilab.test</li>
                <li>lab@alfarabilab.test</li>
                <li>doctor@alfarabilab.test</li>
            </ul>
        </div>
    </div>
</div>
</body>
</html>
