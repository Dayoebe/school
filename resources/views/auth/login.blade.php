@extends('layouts.app', ['mode' => 'guest'])

@section('title', 'Login')

@section('body')
    <x-partials.authentication-card>
        <x-slot:header>
            <h1 class="text-2xl font-bold text-gray-900">Welcome Back</h1>
            <p class="text-gray-600 mt-2">Sign in to continue.</p>
        </x-slot:header>

        <x-display-validation-errors />

        <form action="{{ route('login') }}" method="POST" class="space-y-4" x-data="{ showPassword: false }">
            @csrf

            <x-input name="email" id="email" type="email" label="Email" autocomplete="email" required />

            <div>
                <label for="password" class="font-semibold my-3 block">Password</label>
                <div class="relative">
                    <input
                        :type="showPassword ? 'text' : 'password'"
                        id="password"
                        name="password"
                        required
                        autocomplete="current-password"
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

            <div class="flex items-center justify-between text-sm">
                <label for="remember" class="inline-flex items-center gap-2 text-gray-700">
                    <input type="checkbox" id="remember" name="remember" class="rounded border-gray-300">
                    <span>Remember me</span>
                </label>

                <a href="{{ route('password.request') }}" class="text-blue-700 hover:underline">Forgot password?</a>
            </div>

            <button type="submit" class="w-full rounded-lg bg-blue-600 py-3 text-white font-semibold hover:bg-blue-700 transition-colors">
                Log In
            </button>
        </form>

        <x-slot:footer>
            <span>Don&apos;t have an account?</span>
            <a href="{{ route('register') }}" class="text-blue-700 hover:underline ml-1">Create account</a>
        </x-slot:footer>
    </x-partials.authentication-card>
@endsection
