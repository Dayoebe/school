@extends('layouts.app', ['breadcrumbs' => [
    ['href' => route('dashboard'), 'text' => 'Dashboard'],
    ['href' => route('notices.index'), 'text' => 'Notices'],
    ['href' => route('notices.create'), 'text' => 'Create', 'active'],
]])

@section('title', __('Create Notice'))
@section('page_heading', __('Create Notice'))

@section('content')
    <div class="space-y-5">
        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-xl font-bold text-slate-900">New Notice</h2>
            <p class="mt-1 text-sm text-slate-600">Fill in the details below to publish a new notice for your school community.</p>
        </div>

        @livewire('notices.create-notice-form')
    </div>
@endsection

