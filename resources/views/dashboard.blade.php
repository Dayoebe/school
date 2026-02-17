@php
    $breadcrumbs = [['href' => route('dashboard'), 'text' => 'Dashboard', 'active' => true]];
@endphp

@extends('layouts.app')

@section('title', __('Dashboard'))
@section('page_heading', 'Dashboard')

@section('content')
    @php
        $user = auth()->user();
        $isSuperAdmin = $user->hasAnyRole(['super-admin', 'super_admin']);
        $isPrincipal = $user->hasRole('principal');
        $isAdmin = $user->hasRole('admin');
        $isTeacher = $user->hasRole('teacher');
        $isStudent = $user->hasRole('student');
        $isParent = $user->hasRole('parent');
        $isStaff = $isSuperAdmin || $isPrincipal || $isAdmin || $isTeacher;
    @endphp

    {{-- Status Messages --}}
    @if (session('status'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('status') }}
        </div>
    @endif

    {{-- Password Warning --}}
    @livewire('dashboard.password-warning')

    {{-- Welcome --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-2">Welcome, {{ $user->name }}</h2>
        <p class="text-gray-600 dark:text-gray-400">
            This is your unified dashboard. Menu items and sections are automatically filtered by role and permission.
        </p>
    </div>

    {{-- Staff Statistics --}}
    @if ($isStaff)
        <div class="mb-6">
            @livewire('dashboard.dashboard-stats')
        </div>
    @endif

    {{-- Notices --}}
    @if ($isStaff || $user->can('read notice'))
        <div class="mb-6">
            @livewire('notices.list-notices-table')
        </div>
    @endif

    {{-- Role Highlights --}}
    @if ($isStudent || $isParent || !$isStaff)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            @if ($isStudent)
                <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-2">Student Overview</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-4">
                    Track your results, review your academic history, and continue your CBT assessments.
                </p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('result') }}" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Results</a>
                    <a href="{{ route('cbt.exams') }}" class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">CBT Exams</a>
                </div>
            @elseif ($isParent)
                <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-2">Parent Overview</h3>
                <p class="text-gray-600 dark:text-gray-400">
                    View your children and follow their academic progress from one place.
                </p>
            @else
                <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-2">Account Overview</h3>
                <p class="text-gray-600 dark:text-gray-400">
                    Use the menu to access the sections available to your current role.
                </p>
            @endif
        </div>
    @endif
@endsection
