@extends('layouts.app', ['breadcrumbs' => [
    ['href' => route('dashboard'), 'text' => 'Dashboard'],
    ['href' => route('exams.index'), 'text' => 'Exams'],
    ['href' => route('exams.create'), 'text' => 'Upload Exam', 'active'],
]])

@section('title', __('Upload Exam'))
@section('page_heading', __('Upload Exam'))

@section('content')
    @php
        $user = auth()->user();
        $canUploadAnySubject = $user?->hasAnyRole(['super-admin', 'super_admin', 'principal']);
        $scopeNote = $canUploadAnySubject
            ? 'You can upload an exam for any class and subject in your school for the active term.'
            : 'You can upload only for the class subjects currently assigned to you in the active term.';
    @endphp

    <div class="space-y-6">
        <section class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-emerald-500 via-cyan-600 to-slate-900 px-6 py-8 text-white shadow-2xl md:px-8">
            <div class="pointer-events-none absolute inset-0">
                <div class="absolute -left-10 top-0 h-40 w-40 rounded-full bg-white/10 blur-3xl"></div>
                <div class="absolute bottom-0 right-0 h-56 w-56 rounded-full bg-cyan-200/10 blur-3xl"></div>
                <div class="absolute right-1/4 top-8 h-20 w-20 rounded-full border border-white/15"></div>
            </div>

            <div class="relative flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
                <div class="max-w-3xl">
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.28em] text-cyan-50 backdrop-blur-sm">
                        <i class="fas fa-file-arrow-up"></i>
                        Current Term Upload
                    </span>
                    <h2 class="mt-4 text-3xl font-black tracking-tight md:text-4xl">
                        Upload the class exam for {{ $semester->name }}
                    </h2>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-cyan-50/90 md:text-base">
                        This page is tied to the active academic session and term. Upload typed questions with MathJax, attach a photo or PDF, or use both so the paper remains printable and visible in the portal when published.
                    </p>

                    <div class="mt-6 flex flex-wrap gap-3 text-xs font-semibold uppercase tracking-[0.22em] text-cyan-50/85">
                        <span class="rounded-full border border-white/15 bg-white/10 px-4 py-2">Session: {{ $semester->academicYear?->name ?? 'N/A' }}</span>
                        <span class="rounded-full border border-white/15 bg-white/10 px-4 py-2">Term: {{ $semester->name }}</span>
                        <span class="rounded-full border border-white/15 bg-white/10 px-4 py-2">MathJax Enabled</span>
                        <span class="rounded-full border border-white/15 bg-white/10 px-4 py-2">Photo / PDF / Typed</span>
                        <span class="rounded-full border border-white/15 bg-white/10 px-4 py-2">Printable Record</span>
                    </div>
                </div>

                <div class="flex flex-col gap-3 xl:w-[22rem]">
                    <div class="rounded-3xl border border-white/15 bg-white/10 p-5 backdrop-blur-sm">
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-cyan-100/80">Upload Scope</p>
                        <p class="mt-3 text-sm leading-6 text-white">{{ $scopeNote }}</p>
                    </div>

                    @can('create exam')
                        <a href="{{ route('exams.setup.create') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-white/20 bg-white/10 px-5 py-3 text-sm font-semibold text-white backdrop-blur-sm transition hover:-translate-y-0.5 hover:bg-white/20">
                            <i class="fas fa-sliders text-cyan-200"></i>
                            Need exam setup instead?
                        </a>
                    @endcan
                </div>
            </div>
        </section>

        @include('livewire.exams.pages.exam-paper._form', [
            'formAction' => route('exams.store'),
            'backUrl' => route('exams.index'),
            'backLabel' => 'Back to Manage Exams',
            'formTitle' => 'Upload Exam',
            'formSubtitle' => 'Current term and session are locked automatically from school settings. Choose the class and subject, then upload typed content, a file attachment, or both.',
            'contextBadge' => 'Active Term',
            'submitLabel' => 'Upload Exam',
            'scopeNote' => $scopeNote,
        ])
    </div>
@endsection
