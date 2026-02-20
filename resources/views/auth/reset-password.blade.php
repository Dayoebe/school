@extends('layouts.app', ['mode' => 'guest'])

@section('title', 'Reset Password')

@section('body')
    <x-partials.authentication-card>
        <x-slot:header>
            <h1 class="text-2xl font-bold text-gray-900">Reset Password</h1>
            <p class="text-gray-600 mt-2">Set a new password for your account.</p>
        </x-slot:header>

        <x-display-validation-errors />

        <form method="POST" action="{{ route('password.update') }}" class="space-y-4" x-data="{ showPassword: false, showPasswordConfirmation: false }">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">

            <x-input
                id="email"
                type="email"
                name="email"
                label="Email"
                :value="old('email', $email)"
                required
                autocomplete="username"
            />

            <div>
                <label for="password" class="font-semibold my-3 block">New Password</label>
                <div class="relative">
                    <input
                        :type="showPassword ? 'text' : 'password'"
                        id="password"
                        name="password"
                        required
                        autocomplete="new-password"
                        class="border border-gray-500 p-2 rounded bg-inherit dark:bg-transparent w-full pr-10"
                    >
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-0 px-3 text-gray-500 hover:text-blue-700"
                        aria-label="Toggle password visibility"
                    >
                        <i class="fas" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                    </button>
                </div>
            </div>

            <div>
                <label for="password_confirmation" class="font-semibold my-3 block">Confirm New Password</label>
                <div class="relative">
                    <input
                        :type="showPasswordConfirmation ? 'text' : 'password'"
                        id="password_confirmation"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                        class="border border-gray-500 p-2 rounded bg-inherit dark:bg-transparent w-full pr-10"
                    >
                    <button
                        type="button"
                        @click="showPasswordConfirmation = !showPasswordConfirmation"
                        class="absolute inset-y-0 right-0 px-3 text-gray-500 hover:text-blue-700"
                        aria-label="Toggle password confirmation visibility"
                    >
                        <i class="fas" :class="showPasswordConfirmation ? 'fa-eye-slash' : 'fa-eye'"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="w-full rounded-lg bg-blue-600 py-3 text-white font-semibold hover:bg-blue-700 transition-colors">
                Reset Password
            </button>
        </form>

        <x-slot:footer>
            <a class="text-blue-700 hover:underline" href="{{ route('login') }}">Back to login</a>
        </x-slot:footer>
    </x-partials.authentication-card>
@endsection
