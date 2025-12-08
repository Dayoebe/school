@php
    $breadcrumbs = [['href' => route('dashboard'), 'text' => 'Dashboard', 'active' => true]];
@endphp

{{-- Reverting to original layout. This file acts as a dispatcher for different roles. --}}
@extends('layouts.app')

@section('title', __('Dashboard'))
@section('page_heading', 'Dashboard')

@section('content')
    @if (session('status'))
        <div class="bg-green-100 text-green-800 p-4 rounded mb-4">
            {{ session('status') }}
        </div>
    @endif

    @can('set school')
        @livewire('set-school')
    @endcan

{{-- <livewire:manage-academic-years />
<livewire:manage-semesters />
     --}}

    @livewire('password-warning')
    <livewire:dashboard-data-cards />


    @can('read notice')
        @livewire('list-notices-table')
    @endcan

    @if (auth()->user()->hasRole('applicant'))
        @livewire('application-history', ['applicant' => auth()->user()])
    @endif

    @can('read applicant')
        @livewire('list-account-applications-table')
    @endcan


    {{-- Include the role-specific dashboard content.
         Each included partial or Livewire component should manage its own layout if needed,
         or simply provide the content to be inserted into layouts.app. --}}
    @if (auth()->user()->hasRole('admin'))
        @include('dashboard.admin')

    @elseif(auth()->user()->hasRole('teacher'))
    {{-- <livewire:teacher-dashboard /> --}}

    
    @include('dashboard.teacher', [
        'teacherClasses' => $teacherClasses ?? new \Illuminate\Support\Collection(),
        'teacherSubjects' => $teacherSubjects ?? new \Illuminate\Support\Collection(),
        'subjectPerformance' => $subjectPerformance ?? [],
        'upcomingEvents' => $upcomingEvents ?? [],
        'academicYear' => $academicYear ?? null,
        'semester' => $semester ?? null,
    ])

        
    @elseif(auth()->user()->hasRole('student'))
        
        <livewire:student-dashboard />
    @endif
@endsection
