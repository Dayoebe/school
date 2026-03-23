@extends('layouts.app', ['breadcrumbs' => [
    ['href' => route('dashboard'), 'text' => 'Dashboard'],
    ['href' => route('exams.index'), 'text' => 'Exams'],
    ['href' => route('exams.setup.create'), 'text' => 'Exam Setup', 'active'],
]])

@section('title', __('Exam Setup'))
@section('page_heading', __('Exam Setup'))

@section('content')
    <div class="space-y-6">
        <div class="rounded-[1.75rem] bg-gradient-to-br from-slate-900 via-sky-800 to-cyan-600 px-6 py-8 text-white shadow-2xl md:px-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl">
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.28em] text-cyan-100">
                        <i class="fas fa-sliders"></i>
                        Result Processing Setup
                    </span>
                    <h2 class="mt-4 text-3xl font-black tracking-tight md:text-4xl">Create an exam setup record</h2>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-100">
                        Use this only when you need a managed exam window for slots, marks, or result publishing. For the printable paper itself, use <a href="{{ route('exams.create') }}" class="font-semibold text-white underline underline-offset-4">Upload Exam</a>.
                    </p>
                </div>
                <a href="{{ route('exams.create') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-white px-5 py-3 text-sm font-semibold text-slate-900 shadow-lg transition hover:-translate-y-0.5 hover:bg-slate-100">
                    <i class="fas fa-upload text-sky-600"></i>
                    Upload exam instead
                </a>
            </div>
        </div>

        <div class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-xl md:p-8">
            <x-display-validation-errors/>

            <div class="mb-6 rounded-2xl border border-blue-200 bg-blue-50 px-5 py-4 text-sm text-blue-900">
                Set the exam name, date range, and term here. After saving, you can manage slots and attach uploaded papers from the exam management screen.
            </div>

            <form action="{{ route('exams.setup.store') }}" method="POST" class="grid gap-5 lg:max-w-3xl">
                @csrf

                <div>
                    <label for="name" class="mb-2 block text-sm font-semibold text-slate-700">Exam Name *</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" placeholder="Enter exam name" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-sky-100">
                </div>

                <div>
                    <label for="description" class="mb-2 block text-sm font-semibold text-slate-700">Description</label>
                    <textarea id="description" name="description" rows="4" placeholder="Enter description" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-sky-100">{{ old('description') }}</textarea>
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label for="start_date" class="mb-2 block text-sm font-semibold text-slate-700">Start date *</label>
                        <input id="start_date" type="date" name="start_date" required value="{{ old('start_date') }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-sky-100">
                    </div>
                    <div>
                        <label for="stop_date" class="mb-2 block text-sm font-semibold text-slate-700">Stop date *</label>
                        <input id="stop_date" type="date" name="stop_date" required value="{{ old('stop_date') }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-sky-100">
                    </div>
                </div>

                <div>
                    <label for="semester_id" class="mb-2 block text-sm font-semibold text-slate-700">Select term *</label>
                    <select id="semester_id" name="semester_id" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-sky-100">
                        @forelse ($semesters as $item)
                            <option value="{{ $item->id }}" @selected(old('semester_id', $activeSemesterId) == $item->id)>{{ $item->name }}</option>
                        @empty
                            <option value="">No term available</option>
                        @endforelse
                    </select>
                </div>

                <div class="flex flex-wrap items-center gap-3 pt-2">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-sky-600 via-blue-700 to-cyan-600 px-6 py-3 text-sm font-semibold text-white shadow-lg transition hover:-translate-y-0.5">
                        <i class="fas fa-key"></i>
                        Save setup
                    </button>
                    <a href="{{ route('exams.index') }}" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-6 py-3 text-sm font-semibold text-slate-700 transition hover:-translate-y-0.5 hover:bg-slate-100">
                        <i class="fas fa-arrow-left text-slate-500"></i>
                        Back to Manage Exams
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
