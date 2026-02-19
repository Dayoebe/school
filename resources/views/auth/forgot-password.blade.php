@extends('layouts.app', ['mode' => 'guest'])

@section('title', 'Forgot Password')

@section('body')
    <x-partials.authentication-card>
        <x-slot:header>
            <h1 class="text-2xl font-bold text-gray-900">Forgot Password</h1>
            <p class="text-gray-600 mt-2">Enter your email address to receive a reset link.</p>
        </x-slot:header>

        <x-display-validation-errors />

        @if (session('status'))
            <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                {{ session('status') }}
            </div>
        @endif

        <form action="{{ route('password.email') }}" method="POST" class="space-y-4">
            @csrf
            <x-input type="email" name="email" id="email" label="Email" required />

            <button type="submit" class="w-full rounded-lg bg-blue-600 py-3 text-white font-semibold hover:bg-blue-700 transition-colors">
                Email Password Reset Link
            </button>
        </form>

        <x-slot:footer>
            <a class="text-blue-700 hover:underline" href="{{ route('login') }}">Back to login</a>
        </x-slot:footer>
    </x-partials.authentication-card>
@endsection
