@extends('layouts.app', ['mode' => 'guest'])

@section('title', 'Verify Email')

@section('body')
    <x-partials.authentication-card>
        <x-slot:header>
            <h1 class="text-2xl font-bold text-gray-900">Verify Your Email</h1>
            <p class="text-gray-600 mt-2">Check your inbox and confirm your email address.</p>
        </x-slot:header>

        <x-display-validation-errors />

        @if (session('status') == 'verification-link-sent')
            <x-alert title="Verification Link Sent" icon="fa fa-check" colour="bg-green-500">
                <p class="text-sm">{{ __('A new verification link has been sent to the email address you provided during registration.') }}</p>
            </x-alert>
        @endif

        <p class="text-sm text-gray-600 mb-4">
            {{ __('Before getting started, please verify your email by clicking the link we sent. If you did not receive it, we can send another.') }}
        </p>

        <div class="flex flex-col sm:flex-row gap-3">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition-colors">
                    {{ __('Resend Verification Email') }}
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}" class="sm:ml-auto">
                @csrf
                <button type="submit" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100 transition-colors">
                    {{ __('Log Out') }}
                </button>
            </form>
        </div>
    </x-partials.authentication-card>
@endsection
