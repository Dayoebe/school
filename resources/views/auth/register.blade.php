@extends('layouts.app', ['mode' => 'guest'])

@section('title', 'Register')

@section('body')
    <x-partials.authentication-card>
        <x-slot:header>
            <h1 class="text-2xl font-bold text-gray-900">Create Account</h1>
            <p class="text-gray-600 mt-2">Register as student, parent, or teacher.</p>
        </x-slot:header>

        <x-display-validation-errors />

        <livewire:auth.registration-form />

        <x-slot:footer>
            <span>Already have an account?</span>
            <a href="{{ route('login') }}" class="text-blue-700 hover:underline ml-1">Log in</a>
        </x-slot:footer>
    </x-partials.authentication-card>
@endsection
