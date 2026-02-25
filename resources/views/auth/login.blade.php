@extends('layouts.app', ['mode' => 'public'])

@section('title', 'Portal Login')

@php
    $settings = $publicSiteSettings ?? [];
    $schoolName = (string) data_get($settings, 'school_name', config('app.name', 'School Portal'));
    $schoolLocation = (string) data_get($settings, 'school_location', 'Quality Learning Community');
    $contactPhonePrimary = (string) data_get($settings, 'contact.phone_primary', '');
    $contactEmail = (string) data_get($settings, 'contact.email', '');
    $themeLogoUrl = trim((string) data_get($settings, 'theme.logo_url', ''));
    $logoUrl = $themeLogoUrl !== '' ? $themeLogoUrl : asset(config('app.logo', 'logo.png'));

    $portalHighlights = [
        'Secure login for parents, teachers, and students',
        'Quick access to results, classes, and notices',
        'Real-time updates from your school dashboard',
    ];
@endphp

@section('content')
    <div class="relative overflow-hidden bg-slate-50 text-slate-900">
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute -left-20 top-8 h-64 w-64 rounded-full bg-red-500/15 blur-3xl"></div>
            <div class="absolute right-0 top-0 h-72 w-72 rounded-full bg-orange-500/15 blur-3xl"></div>
        </div>

        <section class="relative mx-auto max-w-6xl px-4 pb-14 pt-10 sm:px-6 lg:px-8">
            <div class="mb-8 max-w-3xl">
                <div class="inline-flex items-center gap-2 rounded-full border border-red-200 bg-red-50 px-3 py-1 text-xs font-semibold text-red-700">
                    <i class="fas fa-shield-halved"></i>
                    <span>Secure Portal Access</span>
                </div>

                <h1 class="mt-4 text-3xl font-black leading-tight text-slate-900 sm:text-4xl">
                    Welcome back to <span class="site-primary-text">{{ $schoolName }}</span>
                </h1>

                <p class="mt-3 text-sm leading-relaxed text-slate-600 sm:text-base">
                    Sign in to continue to your school dashboard, manage daily activities, and stay connected with important updates.
                </p>
            </div>

            <div class="grid gap-6 lg:grid-cols-5 lg:items-start">
                <aside class="space-y-4 lg:col-span-2">
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex items-center gap-3">
                            <img src="{{ $logoUrl }}" alt="{{ $schoolName }} Logo"
                                class="h-12 w-12 rounded-full border border-slate-200 object-cover">
                            <div>
                                <p class="text-sm font-black text-slate-900">{{ $schoolName }}</p>
                                <p class="text-xs font-semibold text-orange-700">{{ $schoolLocation }}</p>
                            </div>
                        </div>

                        <div class="mt-4 space-y-2 text-sm text-slate-700">
                            @foreach ($portalHighlights as $highlight)
                                <p class="flex items-start gap-2">
                                    <i class="fas fa-circle-check mt-0.5 text-emerald-600"></i>
                                    <span>{{ $highlight }}</span>
                                </p>
                            @endforeach
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-900 p-5 text-slate-100">
                        <p class="text-xs font-bold uppercase tracking-wide text-red-300">Need Help?</p>
                        <p class="mt-2 text-sm leading-relaxed text-slate-200">
                            If you cannot access your account, contact school support for password recovery or account verification.
                        </p>

                        <div class="mt-4 space-y-2 text-sm">
                            @if ($contactPhonePrimary !== '')
                                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $contactPhonePrimary) }}"
                                    class="inline-flex items-center gap-2 font-semibold text-orange-200 transition hover:text-white">
                                    <i class="fas fa-phone"></i>
                                    <span>{{ $contactPhonePrimary }}</span>
                                </a>
                            @endif

                            @if ($contactEmail !== '')
                                <a href="mailto:{{ $contactEmail }}"
                                    class="flex items-center gap-2 font-semibold text-sky-200 transition hover:text-white">
                                    <i class="fas fa-envelope"></i>
                                    <span>{{ $contactEmail }}</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </aside>

                <div class="lg:col-span-3">
                    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl shadow-slate-900/5">
                        <div class="relative border-b border-slate-200 bg-slate-900 px-6 py-6 sm:px-8">
                            <div class="pointer-events-none absolute inset-0 bg-gradient-to-r from-red-500/25 via-orange-500/10 to-transparent"></div>
                            <div class="relative">
                                <p class="text-xs font-bold uppercase tracking-wider text-red-300">Account Login</p>
                                <h2 class="mt-2 text-2xl font-black text-white">Sign In</h2>
                                <p class="mt-1 text-sm text-slate-200">Use your email and password to continue.</p>
                            </div>
                        </div>

                        <div class="px-6 py-6 sm:px-8">
                            @if (session('status'))
                                <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
                                    {{ session('status') }}
                                </div>
                            @endif

                            @if ($errors->any())
                                <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                                    <p class="font-bold">Unable to sign in.</p>
                                    <ul class="mt-2 list-inside list-disc space-y-1">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form action="{{ route('login') }}" method="POST" class="space-y-5" x-data="{ showPassword: false }">
                                @csrf

                                <div>
                                    <label for="email" class="mb-2 block text-sm font-bold text-slate-700">Email Address</label>
                                    <input
                                        id="email"
                                        name="email"
                                        type="email"
                                        value="{{ old('email') }}"
                                        autocomplete="email"
                                        required
                                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 shadow-sm transition focus:border-red-400 focus:outline-none focus:ring-4 focus:ring-red-100"
                                        placeholder="you@example.com"
                                    >
                                </div>

                                <div>
                                    <label for="password" class="mb-2 block text-sm font-bold text-slate-700">Password</label>
                                    <div class="relative">
                                        <input
                                            :type="showPassword ? 'text' : 'password'"
                                            id="password"
                                            name="password"
                                            required
                                            autocomplete="current-password"
                                            class="w-full rounded-xl border border-slate-300 px-4 py-3 pr-12 text-sm text-slate-900 shadow-sm transition focus:border-red-400 focus:outline-none focus:ring-4 focus:ring-red-100"
                                            placeholder="Enter your password"
                                        >
                                        <button
                                            type="button"
                                            @click="showPassword = !showPassword"
                                            class="absolute inset-y-0 right-0 px-4 text-slate-500 transition hover:text-slate-700"
                                            aria-label="Toggle password visibility"
                                        >
                                            <i class="fas" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="flex flex-col gap-3 text-sm sm:flex-row sm:items-center sm:justify-between">
                                    <label for="remember" class="inline-flex items-center gap-2 text-slate-700">
                                        <input
                                            type="checkbox"
                                            id="remember"
                                            name="remember"
                                            @checked(old('remember'))
                                            class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-200"
                                        >
                                        <span class="font-medium">Remember me</span>
                                    </label>

                                    <a href="{{ route('password.request') }}" class="font-semibold text-red-700 transition hover:text-red-800 hover:underline">
                                        Forgot password?
                                    </a>
                                </div>

                                <button
                                    type="submit"
                                    class="site-primary-bg w-full rounded-xl px-5 py-3 text-sm font-bold text-white transition hover:opacity-90"
                                >
                                    Sign In to Portal
                                </button>
                            </form>

                            <div class="mt-5 border-t border-slate-200 pt-4 text-center text-sm text-slate-600">
                                <span>Don&apos;t have an account?</span>
                                <a href="{{ route('register') }}" class="ml-1 font-bold text-red-700 transition hover:text-red-800 hover:underline">
                                    Create account
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
