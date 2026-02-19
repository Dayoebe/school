@extends('layouts.app', ['mode' => 'guest'])

@section('title', 'Confirm Password')

@section('body')
    <x-partials.authentication-card>
        <x-slot:header>
            <h1 class="text-2xl font-bold text-gray-900">Confirm Password</h1>
            <p class="text-gray-600 mt-2">Please confirm your password before continuing.</p>
        </x-slot:header>

        <x-display-validation-errors />

        <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
            @csrf
            <x-input id="password" label="Password" type="password" name="password" required autocomplete="current-password" autofocus />

            <button type="submit" class="w-full rounded-lg bg-blue-600 py-3 text-white font-semibold hover:bg-blue-700 transition-colors">
                Confirm
            </button>
        </form>
    </x-partials.authentication-card>
@endsection
