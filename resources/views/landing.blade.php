@php($loc = app()->getLocale())
<!DOCTYPE html>
<html dir="{{ $loc === 'ar' ? 'rtl' : 'ltr' }}" lang="{{ $loc }}">
<head>
    @include('partials.head', ['title' => __('Lab–insurance integration system')])
    @verbatim
    <style>
        html { scroll-behavior: smooth; }
        @media (prefers-reduced-motion: no-preference) {
            .reveal { opacity: 0; transform: translateY(14px); animation: reveal .6s cubic-bezier(.16,1,.3,1) forwards; }
            .reveal-1 { animation-delay: .05s } .reveal-2 { animation-delay: .15s } .reveal-3 { animation-delay: .25s } .reveal-4 { animation-delay: .35s }
            @keyframes reveal { to { opacity: 1; transform: none } }
        }
    </style>
    @endverbatim
</head>
<body class="bg-white dark:bg-slate-950 text-slate-800 dark:text-slate-100 antialiased">

{{-- ============ NAV ============ --}}
<nav class="sticky top-0 z-40 bg-teal-800/90 dark:bg-slate-900/90 backdrop-blur border-b border-white/10 text-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between gap-3">
        <a href="{{ route('landing') }}" class="inline-flex items-center bg-white rounded-xl px-3 py-1.5 shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-white/70">
            <img src="{{ asset('logo.png') }}" alt="{{ config('app.name') }}" class="h-8 w-auto">
        </a>
        <div class="hidden md:flex items-center gap-1 text-sm font-semibold">
            <a href="#how" class="px-3 py-2 rounded-lg text-teal-50 hover:bg-white/10 transition">{{ __('How it works') }}</a>
            <a href="#features" class="px-3 py-2 rounded-lg text-teal-50 hover:bg-white/10 transition">{{ __('Features') }}</a>
        </div>
        <div class="flex items-center gap-2 sm:gap-3">
            @include('partials.toggles')
            <a href="{{ route('login') }}" class="bg-white text-teal-800 font-bold text-sm px-4 sm:px-5 py-2.5 rounded-xl hover:bg-teal-50 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-white/70">
                {{ __('Sign in') }}
            </a>
        </div>
    </div>
</nav>

{{-- ============ HERO ============ --}}
<header class="relative overflow-hidden bg-gradient-to-b from-teal-800 via-teal-700 to-emerald-700 dark:from-slate-900 dark:via-teal-900 dark:to-slate-900 text-white">
    <div aria-hidden="true" class="pointer-events-none absolute -top-24 -end-24 w-96 h-96 rounded-full bg-emerald-400/20 blur-3xl"></div>
    <div aria-hidden="true" class="pointer-events-none absolute -bottom-32 -start-24 w-96 h-96 rounded-full bg-teal-300/10 blur-3xl"></div>

    <div class="relative max-w-6xl mx-auto px-4 sm:px-6 pt-16 pb-24 grid lg:grid-cols-2 gap-12 items-center">
        {{-- Copy --}}
        <div>
            <span class="reveal reveal-1 inline-flex items-center gap-2 rounded-full bg-white/10 ring-1 ring-white/20 px-3 py-1 text-xs sm:text-sm font-semibold text-teal-50">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-300"></span>
                {{ __('Smart lab–insurance platform') }}
            </span>
            <h1 class="reveal reveal-2 mt-5 text-4xl sm:text-5xl font-extrabold leading-[1.15]">
                {{ __('Connects the lab with insurance companies') }}
                <span class="block text-emerald-200">{{ __('and reads lab reports automatically') }}</span>
            </h1>
            <p class="reveal reveal-3 mt-5 text-teal-50/90 text-lg leading-relaxed max-w-xl">
                {{ __('Upload an image or PDF of the lab report; the system reads it automatically and turns it into a ready task for the doctor — linking family members by mobile number so they are explained in one call.') }}
            </p>
            <div class="reveal reveal-4 mt-8 flex flex-wrap items-center gap-3">
                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 bg-white text-teal-800 font-bold px-7 py-3.5 rounded-xl shadow-lg hover:bg-teal-50 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-white">
                    {{ __('Get started') }}
                    <svg class="w-4 h-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                </a>
                <a href="#how" class="inline-flex items-center gap-2 bg-white/10 ring-1 ring-white/25 text-white font-semibold px-6 py-3.5 rounded-xl hover:bg-white/20 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-white/70">
                    {{ __('How it works') }}
                </a>
            </div>

            {{-- Trust stats --}}
            <dl class="reveal reveal-4 mt-10 grid grid-cols-3 gap-4 max-w-md">
                @foreach ([['50+','Reports per day'], ['OCR','Automatic reading'], ['AR · EN','Bilingual & RTL']] as [$v,$l])
                    <div class="rounded-xl bg-white/5 ring-1 ring-white/10 px-3 py-3 text-center">
                        <dt class="text-2xl font-extrabold tabular-nums">{{ $v }}</dt>
                        <dd class="mt-0.5 text-xs text-teal-50/80">{{ __($l) }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>

        {{-- Live preview card --}}
        <div class="reveal reveal-3 lg:justify-self-end w-full max-w-md">
            <div class="rounded-3xl bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 shadow-2xl ring-1 ring-black/5 dark:ring-white/10 p-5">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700">
                    <div class="flex items-center gap-2 font-bold">
                        <x-icon d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" class="w-5 h-5 text-teal-700 dark:text-teal-400" />
                        {{ __('Lab report') }}
                    </div>
                    <span class="inline-flex items-center gap-1 rounded-full bg-teal-50 dark:bg-teal-900/40 text-teal-700 dark:text-teal-300 text-xs font-semibold px-2.5 py-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-teal-500 animate-pulse"></span>
                        {{ __('Automatic reading') }}
                    </span>
                </div>
                <dl class="py-1 text-sm divide-y divide-slate-100 dark:divide-slate-700">
                    @foreach ([['Patient','Mohammed Al-Qahtani'], ['Test','Complete blood count'], ['Result','Within normal range']] as [$k,$v])
                        <div class="flex items-center justify-between py-2.5">
                            <dt class="text-slate-400 dark:text-slate-500">{{ __($k) }}</dt>
                            <dd class="font-semibold">{{ __($v) }}</dd>
                        </div>
                    @endforeach
                </dl>
                <div class="mt-2 flex items-center justify-between rounded-xl bg-emerald-50 dark:bg-emerald-900/30 px-3 py-2.5">
                    <span class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">{{ __('Explained') }}</span>
                    <x-icon d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                </div>
            </div>
        </div>
    </div>

    <div aria-hidden="true" class="h-10 bg-white dark:bg-slate-950 rounded-t-[2.5rem] -mb-px"></div>
</header>

{{-- ============ ROLES (Enterprise Gateway path selection) ============ --}}
<section class="bg-white dark:bg-slate-950 py-16">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
        <div class="text-center max-w-2xl mx-auto mb-10">
            <h2 class="text-3xl font-extrabold text-slate-900 dark:text-white">{{ __('Built for every role') }}</h2>
            <p class="mt-3 text-slate-500 dark:text-slate-400">{{ __('One workflow, three perspectives.') }}</p>
        </div>
        <div class="grid gap-6 md:grid-cols-3">
            @foreach ($roles as [$path, $title, $desc])
                <div class="group rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 hover:border-teal-300 dark:hover:border-teal-700 hover:shadow-lg transition">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-teal-50 dark:bg-teal-900/40 text-teal-700 dark:text-teal-300 mb-4">
                        <x-icon :d="$path" />
                    </div>
                    <h3 class="font-bold text-lg text-slate-900 dark:text-white">{{ __($title) }}</h3>
                    <p class="mt-1.5 text-sm leading-relaxed text-slate-500 dark:text-slate-400">{{ __($desc) }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============ HOW IT WORKS ============ --}}
<section id="how" class="bg-slate-50 dark:bg-slate-900 py-16 scroll-mt-20">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
        <h2 class="text-3xl font-extrabold text-center text-slate-900 dark:text-white mb-12">{{ __('How it works') }}</h2>
        <ol class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($steps as $i => [$title, $desc])
                <li class="relative">
                    <div class="flex items-center justify-center w-11 h-11 rounded-full bg-teal-700 text-white font-extrabold text-lg shadow-md">{{ $i + 1 }}</div>
                    <h3 class="mt-4 font-bold text-slate-900 dark:text-white">{{ __($title) }}</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400 leading-relaxed">{{ __($desc) }}</p>
                </li>
            @endforeach
        </ol>
    </div>
</section>

{{-- ============ FEATURES ============ --}}
<section id="features" class="bg-white dark:bg-slate-950 py-16 scroll-mt-20">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
        <h2 class="text-3xl font-extrabold text-center text-slate-900 dark:text-white mb-12">{{ __('Why Alfarabi lab') }}</h2>
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($features as [$path, $title, $desc])
                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6">
                    <div class="inline-flex items-center justify-center w-11 h-11 rounded-xl bg-emerald-50 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 mb-4">
                        <x-icon :d="$path" />
                    </div>
                    <h3 class="font-bold text-slate-900 dark:text-white">{{ __($title) }}</h3>
                    <p class="mt-1.5 text-sm leading-relaxed text-slate-500 dark:text-slate-400">{{ __($desc) }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============ CTA BAND ============ --}}
<section class="bg-white dark:bg-slate-950 pb-16">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
        <div class="rounded-3xl bg-gradient-to-l from-teal-800 to-emerald-700 dark:from-teal-900 dark:to-emerald-900 text-white px-6 py-12 sm:px-12 text-center shadow-xl">
            <h2 class="text-2xl sm:text-3xl font-extrabold">{{ __('Ready to streamline your lab?') }}</h2>
            <p class="mt-3 text-teal-50/90">{{ __('Sign in and start turning reports into tasks.') }}</p>
            <a href="{{ route('login') }}" class="mt-7 inline-flex items-center gap-2 bg-white text-teal-800 font-bold px-7 py-3.5 rounded-xl shadow-lg hover:bg-teal-50 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-white">
                {{ __('Sign in') }}
                <svg class="w-4 h-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
            </a>
        </div>
    </div>
</section>

{{-- ============ FOOTER ============ --}}
<footer class="border-t border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-950">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-8 flex flex-col sm:flex-row items-center justify-between gap-4">
        <span class="inline-flex items-center bg-white rounded-lg px-2.5 py-1.5 ring-1 ring-slate-100 shadow-sm">
            <img src="{{ asset('logo.png') }}" alt="{{ config('app.name') }}" class="h-7 w-auto">
        </span>
        <p class="text-xs text-slate-400 dark:text-slate-500 text-center">
            © {{ date('Y') }} {{ config('app.name') }} · {{ __('Lab–insurance integration system') }}
        </p>
    </div>
</footer>
</body>
</html>
