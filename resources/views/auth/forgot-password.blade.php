@extends('layouts.app', ['mode' => 'public'])

@section('title', 'Forgot Password')

@php
    $settings = $publicSiteSettings ?? [];
    $schoolName = (string) data_get($settings, 'school_name', config('app.name', 'School Portal'));
    $schoolLocation = (string) data_get($settings, 'school_location', 'Quality Learning Community');
    $contactPhonePrimary = (string) data_get($settings, 'contact.phone_primary', '');
    $contactEmail = (string) data_get($settings, 'contact.email', '');
    $themeLogoUrl = trim((string) data_get($settings, 'theme.logo_url', ''));
    $logoUrl = $themeLogoUrl !== '' ? $themeLogoUrl : asset(config('app.logo', 'logo.png'));
@endphp

@section('content')
    <div class="relative overflow-hidden bg-slate-50 text-slate-900">
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute -left-20 top-8 h-64 w-64 rounded-full bg-emerald-500/15 blur-3xl"></div>
            <div class="absolute right-0 top-0 h-72 w-72 rounded-full bg-sky-500/15 blur-3xl"></div>
        </div>

        <section class="relative mx-auto max-w-6xl px-4 pb-14 pt-10 sm:px-6 lg:px-8">
            <div class="mb-8 max-w-3xl">
                <div class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                    <i class="fas fa-key"></i>
                    <span>Password Recovery</span>
                </div>

                <h1 class="mt-4 text-3xl font-black leading-tight text-slate-900 sm:text-4xl">
                    Reset access to your <span class="site-primary-text">{{ $schoolName }}</span> account
                </h1>

                <p class="mt-3 text-sm leading-relaxed text-slate-600 sm:text-base">
                    Enter your email address and we will send a secure link to reset your password.
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
                                <p class="text-xs font-semibold text-teal-700">{{ $schoolLocation }}</p>
                            </div>
                        </div>

                        <div class="mt-4 space-y-2 text-sm text-slate-700">
                            <p class="flex items-start gap-2">
                                <i class="fas fa-circle-check mt-0.5 text-emerald-600"></i>
                                <span>Use the email linked to your account.</span>
                            </p>
                            <p class="flex items-start gap-2">
                                <i class="fas fa-circle-check mt-0.5 text-emerald-600"></i>
                                <span>Check your inbox and spam folder for the reset link.</span>
                            </p>
                            <p class="flex items-start gap-2">
                                <i class="fas fa-circle-check mt-0.5 text-emerald-600"></i>
                                <span>Use the link promptly for secure password update.</span>
                            </p>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-900 p-5 text-slate-100">
                        <p class="text-xs font-bold uppercase tracking-wide text-cyan-300">Support</p>
                        <p class="mt-2 text-sm leading-relaxed text-slate-200">
                            Still unable to access your account? Contact school support.
                        </p>

                        <div class="mt-4 space-y-2 text-sm">
                            @if ($contactPhonePrimary !== '')
                                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $contactPhonePrimary) }}"
                                    class="inline-flex items-center gap-2 font-semibold text-cyan-200 transition hover:text-white">
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
                            <div class="pointer-events-none absolute inset-0 bg-gradient-to-r from-emerald-500/25 via-sky-500/10 to-transparent"></div>
                            <div class="relative">
                                <p class="text-xs font-bold uppercase tracking-wider text-emerald-300">Forgot Password</p>
                                <h2 class="mt-2 text-2xl font-black text-white">Request Reset Link</h2>
                                <p class="mt-1 text-sm text-slate-200">We will email a link to reset your password.</p>
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
                                    <p class="font-bold">Password reset request failed.</p>
                                    <ul class="mt-2 list-inside list-disc space-y-1">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form action="{{ route('password.email') }}" method="POST" class="space-y-5">
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
                                        @class([
                                            'w-full rounded-xl border px-4 py-3 text-sm text-slate-900 shadow-sm transition focus:outline-none focus:ring-4',
                                            'border-red-300 focus:border-red-500 focus:ring-red-100' => $errors->has('email'),
                                            'border-slate-300 focus:border-emerald-400 focus:ring-emerald-100' => !$errors->has('email'),
                                        ])
                                        placeholder="you@example.com"
                                    >
                                </div>

                                <button
                                    type="submit"
                                    class="site-primary-bg w-full rounded-xl px-5 py-3 text-sm font-bold text-white transition hover:opacity-90"
                                >
                                    Email Password Reset Link
                                </button>
                            </form>

                            <div class="mt-5 border-t border-slate-200 pt-4 text-center text-sm text-slate-600">
                                <a href="{{ route('login') }}" class="font-bold text-red-700 transition hover:text-red-800 hover:underline">
                                    Back to login
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
