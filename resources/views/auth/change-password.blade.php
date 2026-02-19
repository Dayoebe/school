@extends('layouts.app')

@section('title', 'Change Password')
@section('page_heading', 'Change Password')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 p-8">
            <div class="text-center mb-6">
                <h2 class="text-2xl font-extrabold text-gray-900 dark:text-white">
                    Change Your Password
                </h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Update your account password securely.
                </p>
            </div>

            @include('profile.partials.change-password-form')
        </div>
    </div>
@endsection
