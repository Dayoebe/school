@php
    $isEditing = isset($examPaper);
@endphp

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">
            {{ $isEditing ? 'Edit Exam Paper' : 'Upload Exam Paper' }}
        </h3>
        <a href="{{ route('exam-papers.index', $exam) }}" class="btn btn-outline-secondary btn-sm">
            Back to Papers
        </a>
    </div>
    <div class="card-body">
        <x-display-validation-errors />

        <div class="mb-4 rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900">
            <p class="font-semibold">Exam: {{ $exam->name }}</p>
            <p class="mt-1">Term: {{ $exam->semester?->name ?? 'N/A' }} | Session: {{ $exam->semester?->academicYear?->name ?? 'N/A' }}</p>
            <p class="mt-2">Typed papers support MathJax. Use <code>$...$</code> for inline math and <code>$$...$$</code> for display math.</p>
        </div>

        <form method="POST"
              action="{{ $isEditing ? route('exam-papers.update', [$exam, $examPaper]) : route('exam-papers.store', $exam) }}"
              enctype="multipart/form-data"
              class="space-y-4">
            @csrf
            @if($isEditing)
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label for="my_class_id" class="mb-2 block text-sm font-medium text-gray-700">Class *</label>
                    <select id="my_class_id" name="my_class_id" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                        <option value="">Select class</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" @selected(old('my_class_id', $examPaper->my_class_id ?? '') == $class->id)>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="subject_id" class="mb-2 block text-sm font-medium text-gray-700">Subject *</label>
                    <select id="subject_id" name="subject_id" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                        <option value="">Select subject</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" @selected(old('subject_id', $examPaper->subject_id ?? '') == $subject->id)>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label for="title" class="mb-2 block text-sm font-medium text-gray-700">Paper Title *</label>
                <input id="title" name="title" type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2"
                       value="{{ old('title', $examPaper->title ?? '') }}" placeholder="e.g. Mathematics First Term Examination">
            </div>

            <div>
                <label for="instructions" class="mb-2 block text-sm font-medium text-gray-700">Instructions</label>
                <textarea id="instructions" name="instructions" rows="3" class="w-full rounded-lg border border-gray-300 px-3 py-2" placeholder="Special instructions for students">{{ old('instructions', $examPaper->instructions ?? '') }}</textarea>
            </div>

            <div>
                <label for="typed_content" class="mb-2 block text-sm font-medium text-gray-700">Typed Paper Content</label>
                <textarea id="typed_content" name="typed_content" rows="14" class="w-full rounded-lg border border-gray-300 px-3 py-2 font-mono text-sm" placeholder="Type the exam here. You can mix normal text and MathJax like $x^2 + y^2 = 25$.">{{ old('typed_content', $examPaper->typed_content ?? '') }}</textarea>
                <p class="mt-2 text-xs text-gray-500">You can save a typed paper, a file upload, or both.</p>
            </div>

            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                <div class="flex flex-col gap-1 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h4 class="text-sm font-semibold text-gray-900">Live Preview</h4>
                        <p class="text-xs text-gray-500">MathJax renders here while you type.</p>
                    </div>
                </div>

                <div class="mt-4 space-y-4">
                    <div>
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">Instructions Preview</p>
                        <div id="exam-paper-instructions-preview" class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-amber-900 math-preview-block">
                            Instructions preview will appear here.
                        </div>
                    </div>

                    <div>
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">Paper Preview</p>
                        <div id="exam-paper-typed-preview" class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm math-preview-block">
                            Typed paper preview will appear here.
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <label for="attachment" class="mb-2 block text-sm font-medium text-gray-700">Photo or PDF Upload</label>
                <input id="attachment" name="attachment" type="file" accept=".jpg,.jpeg,.png,.webp,.pdf" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                <p class="mt-2 text-xs text-gray-500">Allowed files: JPG, PNG, WEBP, PDF. Max size: 15MB.</p>

                @if($isEditing && $examPaper->attachment_path)
                    <div class="mt-3 rounded-lg border border-gray-200 bg-gray-50 p-3 text-sm">
                        <p class="font-semibold text-gray-800">Current file:</p>
                        <a href="{{ $examPaper->attachment_url }}" target="_blank" rel="noopener noreferrer" class="text-blue-700 hover:text-blue-800">
                            {{ $examPaper->attachment_name ?: 'Open attachment' }}
                        </a>
                        <div class="mt-3">
                            <label class="inline-flex items-center gap-2 text-sm text-red-700">
                                <input type="checkbox" name="remove_attachment" value="1" class="rounded border-gray-300" @checked(old('remove_attachment'))>
                                <span>Remove existing attachment</span>
                            </label>
                        </div>
                    </div>
                @endif
            </div>

            <div class="flex flex-wrap items-center gap-3 border-t border-gray-200 pt-4">
                <button type="submit" class="btn btn-primary">
                    {{ $isEditing ? 'Update Paper' : 'Save Paper' }}
                </button>
                <a href="{{ route('exam-papers.index', $exam) }}" class="btn btn-outline-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@once
    @push('scripts')
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
        <script id="exam-paper-form-mathjax-script" src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-chtml.js" async></script>
        <script>
            (function () {
                let renderTimer = null;

                function setPreviewContent(element, content, emptyMessage) {
                    if (!element) {
                        return;
                    }

                    const normalizedContent = (content || '').trim();

                    if (normalizedContent === '') {
                        element.textContent = emptyMessage;
                        element.classList.add('text-gray-500');
                        return;
                    }

                    element.innerHTML = content;
                    element.classList.remove('text-gray-500');
                }

                function renderExamPaperPreview() {
                    const instructionsInput = document.getElementById('instructions');
                    const typedContentInput = document.getElementById('typed_content');
                    const instructionsPreview = document.getElementById('exam-paper-instructions-preview');
                    const typedPreview = document.getElementById('exam-paper-typed-preview');

                    if (!instructionsInput || !typedContentInput || !instructionsPreview || !typedPreview) {
                        return;
                    }

                    setPreviewContent(
                        instructionsPreview,
                        instructionsInput.value,
                        'Instructions preview will appear here.'
                    );
                    setPreviewContent(
                        typedPreview,
                        typedContentInput.value,
                        'Typed paper preview will appear here.'
                    );

                    if (typeof MathJax === 'undefined' || typeof MathJax.typesetPromise === 'undefined') {
                        return;
                    }

                    const previewNodes = [instructionsPreview, typedPreview];

                    if (typeof MathJax.typesetClear === 'function') {
                        MathJax.typesetClear(previewNodes);
                    }

                    MathJax.typesetPromise(previewNodes).catch(function (error) {
                        console.error('Exam paper preview MathJax error:', error);
                    });
                }

                function scheduleExamPaperPreview() {
                    clearTimeout(renderTimer);
                    renderTimer = window.setTimeout(renderExamPaperPreview, 120);
                }

                function bindExamPaperPreview() {
                    const inputs = [
                        document.getElementById('instructions'),
                        document.getElementById('typed_content')
                    ].filter(Boolean);

                    if (!inputs.length) {
                        return;
                    }

                    inputs.forEach(function (input) {
                        if (input.dataset.examPaperPreviewBound === '1') {
                            return;
                        }

                        input.addEventListener('input', scheduleExamPaperPreview);
                        input.dataset.examPaperPreviewBound = '1';
                    });

                    renderExamPaperPreview();
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', bindExamPaperPreview);
                } else {
                    bindExamPaperPreview();
                }

                document.addEventListener('mathjax-loaded', renderExamPaperPreview);
                document.addEventListener('livewire:navigated', bindExamPaperPreview);
            })();
        </script>
    @endpush
@endonce
