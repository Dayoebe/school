@php
    $breadcrumbs = [['href' => route('dashboard'), 'text' => 'Dashboard', 'active' => true]];
@endphp

@extends('layouts.app')

@section('title', __('Dashboard'))
@section('page_heading', 'Dashboard')

@section('content')
    {{-- Status Messages --}}
    @if (session('status'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('status') }}
        </div>
    @endif

   
    {{-- Password Warning --}}
    @livewire('password-warning')

    {{-- Dashboard Statistics (Admin/Super Admin Only) --}}
        <div class="mb-6">
            @livewire('dashboard-stats')
        </div>
   
    {{-- Notices Section --}}
   
        <div class="mb-6">
            @livewire('list-notices-table')
        </div>
   
   

    {{-- Parent Dashboard --}}
    
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-4">
                Welcome, {{ auth()->user()->name }}
            </h2>
            <p class="text-gray-600 dark:text-gray-400">
                View your children's information and academic progress.
            </p>
        </div>
    
@endsection