@extends('layouts.app', ['mode' => 'print'])

@section('title', __('Print Exam Paper'))

@section('style')
    <script>
        window.MathJax = window.MathJax || {
            tex: {
                inlineMath: [['$', '$'], ['\\(', '\\)']],
                displayMath: [['$$', '$$'], ['\\[', '\\]']]
            },
            svg: {
                fontCache: 'global'
            }
        };
    </script>
    <script src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-chtml.js" async></script>
    <style>
        .exam-paper-print {
            font-size: 15px;
            line-height: 1.7;
        }
        .exam-paper-print mjx-container {
            display: inline-block;
            margin: 0.18rem 0;
        }
        .exam-paper-print mjx-container[display="true"] {
            display: block;
            margin: 0.9rem 0;
        }
        .no-print {
            margin-bottom: 1rem;
        }
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
@endsection

@section('content')
    <div class="no-print">
        <button onclick="window.print()" class="btn btn-primary">Print</button>
    </div>

    <div class="exam-paper-print">
        <h2>{{ $examPaper->title }}</h2>
        <p><strong>Exam:</strong> {{ $examPaper->exam?->name ?? 'N/A' }}</p>
        <p><strong>Term:</strong> {{ $examPaper->exam?->semester?->name ?? 'N/A' }}</p>
        <p><strong>Academic Session:</strong> {{ $examPaper->exam?->semester?->academicYear?->name ?? 'N/A' }}</p>
        <p><strong>Class:</strong> {{ $examPaper->myClass?->name ?? 'N/A' }}</p>
        <p><strong>Subject:</strong> {{ $examPaper->subject?->name ?? 'N/A' }}</p>

        @if($examPaper->instructions)
            <div class="math-content" style="margin-top: 1rem; padding: 1rem; border: 1px solid #e5e7eb; background: #f9fafb;">
                {!! \App\Support\SafeHtml::math($examPaper->instructions) !!}
            </div>
        @endif

        @if($examPaper->typed_content)
            <div class="math-content" style="margin-top: 1.5rem;">
                {!! \App\Support\SafeHtml::math($examPaper->typed_content) !!}
            </div>
        @endif

        @if($examPaper->attachment_url)
            <div style="margin-top: 1.5rem; padding: 1rem; border: 1px solid #dbeafe; background: #eff6ff;">
                <p><strong>Attached file:</strong></p>
                <p>{{ $examPaper->attachment_name ?: 'Uploaded exam file' }}</p>
                <p>Open online: {{ $examPaper->attachment_url }}</p>
            </div>
        @endif
    </div>

    <script>
        function renderPrintedExamPaperMath() {
            if (typeof MathJax === 'undefined' || typeof MathJax.typesetPromise === 'undefined') {
                return;
            }

            const nodes = Array.from(document.querySelectorAll('.math-content'));

            if (!nodes.length) {
                return;
            }

            if (typeof MathJax.typesetClear === 'function') {
                MathJax.typesetClear(nodes);
            }

            MathJax.typesetPromise(nodes).catch((error) => {
                console.error('Print MathJax error:', error);
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', renderPrintedExamPaperMath);
        } else {
            renderPrintedExamPaperMath();
        }

        document.addEventListener('mathjax-loaded', renderPrintedExamPaperMath);
    </script>
@endsection
