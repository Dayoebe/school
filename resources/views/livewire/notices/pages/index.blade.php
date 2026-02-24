@extends('layouts.app', ['breadcrumbs' => [
    ['href' => route('dashboard'), 'text' => 'Dashboard'],
    ['href' => route('notices.index'), 'text' => 'Notices', 'active'],
]])

@section('title', __('Notices'))
@section('page_heading', __('Notices'))

@section('content')
    @php
        $schoolId = auth()->user()?->school_id;
        $today = now()->toDateString();

        $noticeBaseQuery = \App\Models\Notice::query()->where('school_id', $schoolId);

        $totalNotices = (clone $noticeBaseQuery)->count();
        $activeNotices = (clone $noticeBaseQuery)
            ->where('active', 1)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('stop_date', '>=', $today)
            ->count();
        $upcomingNotices = (clone $noticeBaseQuery)
            ->whereDate('start_date', '>', $today)
            ->count();
    @endphp

    <div class="space-y-5">
        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">School Notices</h2>
                    <p class="mt-1 text-sm text-slate-600">Create, track, and publish internal notices for your school.</p>
                </div>

                @can('create notice')
                    <a href="{{ route('notices.create') }}" class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                        <i class="fas fa-plus mr-2"></i>Create Notice
                    </a>
                @endcan
            </div>

            <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-3">
                <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Notices</p>
                    <p class="mt-1 text-2xl font-black text-slate-900">{{ number_format($totalNotices) }}</p>
                </div>
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Active</p>
                    <p class="mt-1 text-2xl font-black text-emerald-900">{{ number_format($activeNotices) }}</p>
                </div>
                <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Upcoming</p>
                    <p class="mt-1 text-2xl font-black text-blue-900">{{ number_format($upcomingNotices) }}</p>
                </div>
            </div>
        </div>

        @livewire('notices.list-notices-table')
    </div>
@endsection

