@extends('layouts.app')
@section('title', __('Profile'))

@section('content')
<h1 class="text-2xl font-extrabold text-slate-800 dark:text-slate-100 mb-6">{{ __('Profile') }}</h1>

<div class="grid gap-6 lg:grid-cols-3">
    {{-- بطاقة الصورة + الاسم --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-6 text-center">
        @php($avatar = $user->avatarUrl())
        @if ($avatar)
            <img src="{{ $avatar }}" alt="{{ $user->name }}"
                 class="w-32 h-32 rounded-full object-cover mx-auto ring-4 ring-brand-100 dark:ring-slate-700">
        @else
            <div class="w-32 h-32 rounded-full mx-auto bg-brand-100 dark:bg-slate-700 flex items-center justify-center text-4xl font-extrabold text-brand-700 dark:text-brand-300">
                {{ mb_substr($user->first_name ?? $user->name, 0, 1) }}
            </div>
        @endif
        <div class="mt-4 font-bold text-slate-800 dark:text-slate-100">{{ $user->name }}</div>
        <div class="text-sm text-slate-500 dark:text-slate-400">{{ $user->email }}</div>
        @if ($user->specialty)
            <div class="text-sm text-brand-700 dark:text-brand-400 mt-1">{{ $user->specialty }}</div>
        @endif
    </div>

    {{-- نموذج تعديل بيانات الملف الشخصي --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-6 lg:col-span-2">
        <h2 class="font-bold text-slate-800 dark:text-slate-100 mb-4">{{ __('Edit profile') }}</h2>

        <form method="POST" action="{{ route('doctor.profile.update') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PATCH')

            <div class="grid sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1">{{ __('Title') }}</label>
                    <input type="text" name="title" value="{{ old('title', $user->title) }}"
                           placeholder="{{ __('e.g. Dr.') }}"
                           class="w-full rounded-lg border-slate-200 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-sm">
                </div>
                <div>
                    <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1">{{ __('First name') }}</label>
                    <input type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}" required
                           class="w-full rounded-lg border-slate-200 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-sm">
                </div>
                <div>
                    <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1">{{ __('Last name') }}</label>
                    <input type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}" required
                           class="w-full rounded-lg border-slate-200 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-sm">
                </div>
            </div>

            <div>
                <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1">{{ __('Specialty') }}</label>
                <input type="text" name="specialty" value="{{ old('specialty', $user->specialty) }}"
                       class="w-full rounded-lg border-slate-200 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-sm">
            </div>

            <div>
                <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1">{{ __('Profile image') }}</label>
                <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp"
                       class="w-full text-sm text-slate-600 dark:text-slate-300 file:me-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:font-semibold file:bg-brand-100 file:text-brand-700 dark:file:bg-slate-700 dark:file:text-brand-300 hover:file:bg-brand-200">
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">{{ __('JPG, PNG or WEBP — up to 2 MB.') }}</p>
                @if ($user->avatar_path)
                    <label class="inline-flex items-center gap-2 mt-2 text-xs text-rose-600 dark:text-rose-400">
                        <input type="checkbox" name="remove_avatar" value="1" class="rounded">
                        {{ __('Remove current image') }}
                    </label>
                @endif
            </div>

            <div class="flex justify-end">
                <button class="bg-brand-700 hover:bg-brand-800 text-white px-5 py-2 rounded-lg font-semibold text-sm">
                    {{ __('Save') }}
                </button>
            </div>
        </form>
    </div>
</div>

{{-- تغيير كلمة المرور --}}
<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-6 mt-6">
    <h2 class="font-bold text-slate-800 dark:text-slate-100 mb-4">{{ __('Change password') }}</h2>

    <form method="POST" action="{{ route('doctor.profile.password') }}" class="space-y-4 max-w-xl">
        @csrf
        @method('PATCH')

        <div>
            <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1">{{ __('Current password') }}</label>
            <input type="password" name="current_password" required autocomplete="current-password"
                   class="w-full rounded-lg border-slate-200 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-sm">
        </div>
        <div class="grid sm:grid-cols-2 gap-3">
            <div>
                <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1">{{ __('New password') }}</label>
                <input type="password" name="password" required autocomplete="new-password"
                       class="w-full rounded-lg border-slate-200 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-sm">
            </div>
            <div>
                <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1">{{ __('Confirm new password') }}</label>
                <input type="password" name="password_confirmation" required autocomplete="new-password"
                       class="w-full rounded-lg border-slate-200 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-sm">
            </div>
        </div>

        <div class="flex justify-end">
            <button class="bg-slate-800 hover:bg-slate-900 dark:bg-slate-700 dark:hover:bg-slate-600 text-white px-5 py-2 rounded-lg font-semibold text-sm">
                {{ __('Change password') }}
            </button>
        </div>
    </form>
</div>
@endsection
