@extends('layouts.app', ['mode' => 'guest'])

@section('title', 'Two Factor Challenge')

@section('body')
    <x-partials.authentication-card>
        <x-slot:header>
            <h1 class="text-2xl font-bold text-gray-900">Two-Factor Verification</h1>
            <p class="text-gray-600 mt-2">Confirm access using your authentication code.</p>
        </x-slot:header>

        <x-display-validation-errors />

        <div x-data="{ recovery: false }" class="space-y-4">
            <p class="text-sm text-gray-600" x-show="!recovery">
                {{ __('Enter the authentication code provided by your authenticator app.') }}
            </p>

            <p class="text-sm text-gray-600" x-show="recovery">
                {{ __('Enter one of your emergency recovery codes.') }}
            </p>

            <form method="POST" action="{{ route('two-factor.login') }}" class="space-y-4">
                @csrf

                <div x-show="!recovery">
                    <x-input id="code" label="Authentication Code" type="text" inputmode="numeric" name="code" autofocus x-ref="code" autocomplete="one-time-code" />
                </div>

                <div x-show="recovery">
                    <x-input id="recovery-code" label="Recovery Code" type="text" name="recovery_code" x-ref="recovery_code" autocomplete="one-time-code" />
                </div>

                <div class="flex flex-col sm:flex-row gap-2">
                    <button
                        type="button"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100"
                        x-show="!recovery"
                        x-on:click="recovery = true; $nextTick(() => { $refs.recovery_code.focus() })"
                    >
                        {{ __('Use a recovery code') }}
                    </button>

                    <button
                        type="button"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100"
                        x-show="recovery"
                        x-on:click="recovery = false; $nextTick(() => { $refs.code.focus() })"
                    >
                        {{ __('Use an authentication code') }}
                    </button>

                    <button type="submit" class="sm:ml-auto rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition-colors">
                        {{ __('Log in') }}
                    </button>
                </div>
            </form>
        </div>
    </x-partials.authentication-card>
@endsection
