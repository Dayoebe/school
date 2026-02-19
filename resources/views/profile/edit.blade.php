@extends('layouts.app')

@section('title', 'Edit Profile')
@section('page_heading', 'My Profile')

@section('content')
<div x-data="profileEdit()" class="max-w-4xl mx-auto py-10 sm:px-6 lg:px-8">
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-8 rounded-2xl shadow-sm mb-10 text-center">
        <div class="flex flex-col items-center justify-center">
            <img src="{{ Auth::user()->profile_photo_url ?? asset('images/default-avatar.png') }}"
                 alt="Profile Avatar"
                 class="w-24 h-24 rounded-full border-2 border-gray-200 dark:border-gray-600 shadow-sm object-cover mb-4">
            <h1 class="text-3xl font-bold mb-2 text-gray-900 dark:text-white">
                <i class="fas fa-user-circle mr-2"></i>My Profile
            </h1>
            <p class="text-gray-600 dark:text-gray-300">Manage your personal and account settings</p>
        </div>
    </div>

    <!-- Success Message -->
    @if (session('success'))
    <div x-show="showSuccess" x-transition class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg shadow-sm relative mb-6 flex items-center">
        <div class="flex-shrink-0">
            <i class="fas fa-check-circle text-green-500 text-xl"></i>
        </div>
        <div class="ml-3 text-sm font-medium">
            <span>{{ session('success') }}</span>
        </div>
        <button @click="showSuccess = false" class="ml-auto text-green-700 hover:text-green-900 focus:outline-none">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @endif

    <!-- Tabs Navigation -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 mb-8 p-2 flex justify-center">
        <nav class="flex space-x-4 sm:space-x-8">
            <button type="button" @click="activeTab = 'profile'" 
                :class="activeTab === 'profile' ? 'bg-blue-500 text-white shadow-md' : 'text-gray-700 hover:bg-gray-100'"
                class="flex items-center px-4 py-2 rounded-lg font-medium text-sm transition-all duration-300">
                <i class="fas fa-user-edit mr-2"></i>Profile Information
            </button>
            <button type="button" @click="activeTab = 'password'" 
                :class="activeTab === 'password' ? 'bg-blue-500 text-white shadow-md' : 'text-gray-700 hover:bg-gray-100'"
                class="flex items-center px-4 py-2 rounded-lg font-medium text-sm transition-all duration-300">
                <i class="fas fa-lock mr-2"></i>Account Security
            </button>
        </nav>
    </div>

    <!-- Profile Tab Content -->
    <div x-show="activeTab === 'profile'" x-transition>
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 sm:p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-3">Personal Details</h2>
            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('patch')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-id-card mr-2 text-blue-500"></i>Full Name
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" autocomplete="name"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3 text-gray-800">
                        @error('name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-envelope mr-2 text-blue-500"></i>Email Address
                        </label>
                        <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" autocomplete="email"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3 text-gray-800">
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                        class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-300">
                        <i class="fas fa-save mr-2"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Password Tab Content -->
    <div x-show="activeTab === 'password'" x-transition>
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 sm:p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-3">Change Password</h2>
            @include('profile.partials.change-password-form')
        </div>
    </div>
</div>

@push('scripts')
<script>
    function profileEdit() {
        return {
            activeTab: 'profile',
            showSuccess: @json(session('success') ? true : false),
            init() {
                // Auto-hide success message after 5 seconds
                if (this.showSuccess) {
                    setTimeout(() => {
                        this.showSuccess = false;
                    }, 5000);
                }
            }
        }
    }
</script>
@endpush
@endsection
