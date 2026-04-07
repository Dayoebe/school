@extends('layouts.app', ['breadcrumbs' => [
    ['href' => route('dashboard'), 'text' => 'Dashboard'],
    ['href' => route('exam-papers.viewer'), 'text' => 'Exam Papers', 'active'],
]])

@section('title', __('Exam Papers'))
@section('page_heading', __('Exam Papers'))

@section('style')
    <script>
        window.MathJax = window.MathJax || {
            tex: {
                inlineMath: [['$', '$'], ['\\(', '\\)']],
                displayMath: [['$$', '$$'], ['\\[', '\\]']]
            },
            svg: {
                fontCache: 'global'
            },
            startup: {
                pageReady: function () {
                    return MathJax.startup.defaultPageReady().then(function () {
                        document.dispatchEvent(new Event('mathjax-loaded'));
                    });
                }
            }
        };
    </script>
    <script id="exam-paper-mathjax-script" src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-chtml.js" async></script>
    <style>
        .exam-paper-content mjx-container {
            display: inline-block;
            margin: 0.18rem 0;
        }
        .exam-paper-content mjx-container[display="true"] {
            display: block;
            margin: 0.85rem 0;
        }
    </style>
@endsection

@section('content')
    @if($isParentViewer)
        <div class="card mb-4">
            <div class="card-body">
                @if($availableStudents !== [])
                    <form method="GET" action="{{ route('exam-papers.viewer') }}" class="grid gap-4 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,1fr)] lg:items-end">
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-gray-700">Ward</label>
                            <select name="student" class="w-full rounded-lg border border-gray-300 px-4 py-3">
                                @foreach($availableStudents as $student)
                                    <option value="{{ $student['id'] }}" @selected((int) $selectedStudentId === (int) $student['id'])>
                                        {{ $student['name'] }} | {{ $student['class_name'] }} | {{ $student['section_name'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <button type="submit" class="btn btn-primary w-full">Load Papers</button>
                        </div>
                    </form>
                @else
                    <div class="rounded border border-dashed border-gray-300 bg-gray-50 px-4 py-5 text-sm text-gray-600">
                        No linked students were found for this parent account.
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if($selectedPaper)
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">{{ $selectedPaper->title }}</h3>
                <a href="{{ route('exam-papers.viewer', array_filter(['student' => $selectedStudentId ?: null])) }}" class="btn btn-outline-secondary btn-sm">Back to List</a>
            </div>
            <div class="card-body">
                <div class="mb-4 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded border border-gray-200 bg-gray-50 px-4 py-3"><strong>Exam:</strong> {{ $selectedPaper->exam?->name ?? 'N/A' }}</div>
                    <div class="rounded border border-gray-200 bg-gray-50 px-4 py-3"><strong>Term:</strong> {{ $selectedPaper->exam?->semester?->name ?? 'N/A' }}</div>
                    <div class="rounded border border-gray-200 bg-gray-50 px-4 py-3"><strong>Session:</strong> {{ $selectedPaper->exam?->semester?->academicYear?->name ?? 'N/A' }}</div>
                    <div class="rounded border border-gray-200 bg-gray-50 px-4 py-3"><strong>Subject:</strong> {{ $selectedPaper->subject?->name ?? 'N/A' }}</div>
                </div>

                @if($selectedPaper->instructions)
                    <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-4 text-amber-900 exam-paper-content math-content">
                        {!! $selectedPaper->instructions !!}
                    </div>
                @endif

                @if($selectedPaper->typed_content)
                    <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm exam-paper-content math-content">
                        {!! $selectedPaper->typed_content !!}
                    </div>
                @endif

                @if($selectedPaper->attachment_url)
                    <div class="mt-4 rounded-lg border border-blue-200 bg-blue-50 p-4 text-blue-900">
                        <p class="font-semibold">Attached file</p>
                        <a href="{{ $selectedPaper->attachment_url }}" target="_blank" rel="noopener noreferrer" class="mt-2 inline-flex text-sm font-medium text-blue-700 hover:text-blue-800">
                            {{ $selectedPaper->attachment_name ?: 'Open attachment' }}
                        </a>
                    </div>
                @endif

                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('exam-papers.print', $selectedPaper) }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary">Print Paper</a>
                    @if($selectedPaper->attachment_url)
                        <a href="{{ $selectedPaper->attachment_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-secondary">Open File</a>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Available Papers</h3>
        </div>
        <div class="card-body">
            @if($papers->count() === 0)
                <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 px-6 py-10 text-center text-gray-600">
                    No published exam papers are available for the active academic session and term yet.
                </div>
            @else
                <div class="grid gap-4 lg:grid-cols-2">
                    @foreach($papers as $paper)
                        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-900">{{ $paper->title }}</h4>
                                    <p class="mt-1 text-sm text-gray-600">{{ $paper->subject?->name ?? 'N/A' }} | {{ $paper->myClass?->name ?? 'N/A' }}</p>
                                </div>
                                <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">Published</span>
                            </div>
                            <div class="mt-4 space-y-2 text-sm text-gray-600">
                                <div><strong>Exam:</strong> {{ $paper->exam?->name ?? 'N/A' }}</div>
                                <div><strong>Term:</strong> {{ $paper->exam?->semester?->name ?? 'N/A' }}</div>
                                <div><strong>Session:</strong> {{ $paper->exam?->semester?->academicYear?->name ?? 'N/A' }}</div>
                                <div><strong>Format:</strong> {{ $paper->typed_content ? 'Typed' : '' }}{{ $paper->typed_content && $paper->attachment_path ? ' + ' : '' }}{{ $paper->attachment_path ? 'File' : '' }}</div>
                            </div>
                            <div class="mt-5 flex flex-wrap gap-3">
                                <a href="{{ route('exam-papers.viewer', array_filter(['student' => $selectedStudentId ?: null, 'paper' => $paper->id])) }}" class="btn btn-outline-primary btn-sm">View Paper</a>
                                <a href="{{ route('exam-papers.print', $paper) }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-secondary btn-sm">Print</a>
                                @if($paper->attachment_url)
                                    <a href="{{ $paper->attachment_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-info btn-sm">Open File</a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $papers->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function renderExamPaperMath() {
        if (typeof MathJax === 'undefined' || typeof MathJax.typesetPromise === 'undefined') {
            return;
        }

        const elements = Array.from(document.querySelectorAll('.math-content'));

        if (!elements.length) {
            return;
        }

        if (typeof MathJax.typesetClear === 'function') {
            MathJax.typesetClear(elements);
        }

        MathJax.typesetPromise(elements).catch((error) => {
            console.error('Exam paper MathJax error:', error);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', renderExamPaperMath);
    } else {
        renderExamPaperMath();
    }

    document.addEventListener('mathjax-loaded', renderExamPaperMath);
    document.addEventListener('livewire:navigated', renderExamPaperMath);
</script>
@endpush
