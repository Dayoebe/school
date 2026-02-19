@php
    $breadcrumbs = [['href' => route('dashboard'), 'text' => 'Dashboard', 'active' => true]];
@endphp

@extends('layouts.app')

@section('title', __('Dashboard'))
@section('page_heading', 'Dashboard')

@section('content')
    @if (session('status'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('status') }}
        </div>
    @endif

    @livewire('dashboard.password-warning')

    <div class="mb-6">
        @livewire('dashboard.dashboard-stats')
    </div>

    @php
        $user = auth()->user();
        $isStaff = $user->hasAnyRole(['super-admin', 'super_admin', 'principal', 'admin', 'teacher']);
    @endphp

    @if ($isStaff || $user->can('read notice'))
        <div class="mb-6">
            @livewire('notices.list-notices-table')
        </div>
    @endif
@endsection
