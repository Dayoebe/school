@php
    $isEditing = isset($examPaper);
    $formAction = $formAction ?? ($isEditing ? route('exam-papers.update', [$exam, $examPaper]) : route('exam-papers.store', $exam));
    $backUrl = $backUrl ?? route('exam-papers.index', $exam);
    $backLabel = $backLabel ?? 'Back to Papers';
    $formTitle = $formTitle ?? ($isEditing ? 'Edit Exam' : 'Upload Exam');
    $formSubtitle = $formSubtitle ?? 'Upload typed content, a photo, a PDF, or combine them in one printable record.';
    $contextBadge = $contextBadge ?? ($isEditing ? 'Edit Mode' : 'Exam Upload');
    $submitLabel = $submitLabel ?? ($isEditing ? 'Update Exam' : 'Upload Exam');
    $scopeNote = $scopeNote ?? null;
    $termLabel = $exam->semester?->name ?? 'N/A';
    $sessionLabel = $exam->semester?->academicYear?->name ?? 'N/A';
@endphp

<div class="space-y-6">
    <section class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-xl">
        <div class="bg-gradient-to-r from-slate-900 via-sky-800 to-cyan-600 px-6 py-6 text-white md:px-8">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-3xl">
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.28em] text-cyan-100">
                        <i class="fas fa-scroll"></i>
                        {{ $contextBadge }}
                    </span>
                    <h3 class="mt-4 text-3xl font-black tracking-tight">{{ $formTitle }}</h3>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-100">{{ $formSubtitle }}</p>
                </div>

                <div class="flex flex-wrap gap-3 lg:justify-end">
                    <span class="rounded-full border border-white/15 bg-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-white">Term: {{ $termLabel }}</span>
                    <span class="rounded-full border border-white/15 bg-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-white">Session: {{ $sessionLabel }}</span>
                </div>
            </div>
        </div>

        <div class="grid gap-4 border-t border-slate-100 bg-slate-50/80 px-6 py-5 md:grid-cols-3 md:px-8">
            <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-100">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-sky-600">Format</p>
                <p class="mt-2 text-sm font-semibold text-slate-900">Typed, photo, PDF, or both</p>
                <p class="mt-1 text-sm text-slate-500">Keep a printable exam record even when the paper was set offline.</p>
            </div>
            <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-100">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-emerald-600">Math Support</p>
                <p class="mt-2 text-sm font-semibold text-slate-900">MathJax live preview</p>
                <p class="mt-1 text-sm text-slate-500">Use <code>$...$</code> inline or <code>$$...$$</code> for display equations.</p>
            </div>
            <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-100">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-amber-600">Visibility</p>
                <p class="mt-2 text-sm font-semibold text-slate-900">Portal access stays controlled</p>
                <p class="mt-1 text-sm text-slate-500">Parents and students only see the paper after it is published.</p>
            </div>
        </div>
    </section>

    @if ($classes->isEmpty() || $subjects->isEmpty())
        <div class="rounded-3xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-900 shadow-sm">
            <p class="font-semibold">Upload is not available yet.</p>
            <p class="mt-1">
                {{ $classes->isEmpty() ? 'No class is available for your upload scope.' : 'No subject is available for your upload scope.' }}
                Assign the teacher to the correct class subject or use a principal / super admin account.
            </p>
        </div>
    @endif

    @if ($scopeNote)
        <div class="rounded-3xl border border-cyan-200 bg-cyan-50 px-5 py-4 text-sm text-cyan-900 shadow-sm">
            <p class="font-semibold">Upload scope</p>
            <p class="mt-1">{{ $scopeNote }}</p>
        </div>
    @endif

    <x-display-validation-errors />

    <form method="POST" action="{{ $formAction }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @if ($isEditing)
            @method('PUT')
        @endif

        <div class="grid gap-6 xl:grid-cols-[1.35fr,0.95fr]">
            <div class="space-y-6">
                <section class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-xl">
                    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.26em] text-sky-600">Exam Details</p>
                            <h4 class="mt-2 text-2xl font-black text-slate-900">Class and subject</h4>
                        </div>
                        <a href="{{ $backUrl }}" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:-translate-y-0.5 hover:bg-slate-100">
                            <i class="fas fa-arrow-left text-sky-600"></i>
                            {{ $backLabel }}
                        </a>
                    </div>

                    <div class="mt-6 grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="my_class_id" class="mb-2 block text-sm font-semibold text-slate-700">Class *</label>
                            <select id="my_class_id" name="my_class_id" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-sky-100">
                                <option value="">Select class</option>
                                @foreach ($classes as $class)
                                    <option value="{{ $class->id }}" @selected(old('my_class_id', $examPaper->my_class_id ?? '') == $class->id)>
                                        {{ $class->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="subject_id" class="mb-2 block text-sm font-semibold text-slate-700">Subject *</label>
                            <select id="subject_id" name="subject_id" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-sky-100">
                                <option value="">Select subject</option>
                                @foreach ($subjects as $subject)
                                    <option value="{{ $subject->id }}" @selected(old('subject_id', $examPaper->subject_id ?? '') == $subject->id)>
                                        {{ $subject->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mt-5">
                        <label for="title" class="mb-2 block text-sm font-semibold text-slate-700">Paper title</label>
                        <input
                            id="title"
                            name="title"
                            type="text"
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-sky-100"
                            value="{{ old('title', $examPaper->title ?? '') }}"
                            placeholder="Leave blank to auto-generate from subject, term, and session"
                        >
                        <p class="mt-2 text-xs text-slate-500">Title is optional. If you leave it empty, the system will generate one automatically.</p>
                    </div>
                </section>

                <section class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-xl">
                    <p class="text-xs font-semibold uppercase tracking-[0.26em] text-amber-600">Instructions</p>
                    <h4 class="mt-2 text-2xl font-black text-slate-900">Student guidance</h4>
                    <p class="mt-2 text-sm text-slate-500">Add time limits, calculator rules, answer instructions, or any invigilator note.</p>

                    <div class="mt-5">
                        <label for="instructions" class="mb-2 block text-sm font-semibold text-slate-700">Instructions</label>
                        <textarea id="instructions" name="instructions" rows="5" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-amber-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-amber-100" placeholder="Example: Answer four questions. Show all workings for Section B.">{{ old('instructions', $examPaper->instructions ?? '') }}</textarea>
                    </div>
                </section>

                <section class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-xl">
                    <p class="text-xs font-semibold uppercase tracking-[0.26em] text-violet-600">Typed Paper</p>
                    <h4 class="mt-2 text-2xl font-black text-slate-900">Compose the exam content</h4>
                    <p class="mt-2 text-sm text-slate-500">You can mix plain text with LaTeX. Example: <code>$x^2 + y^2 = 25$</code>.</p>

                    <div class="mt-5">
                        <label for="typed_content" class="mb-2 block text-sm font-semibold text-slate-700">Typed content</label>
                        <textarea id="typed_content" name="typed_content" rows="20" class="w-full rounded-[1.5rem] border border-slate-200 bg-slate-950 px-4 py-4 font-mono text-sm text-slate-50 shadow-sm focus:border-violet-400 focus:outline-none focus:ring-4 focus:ring-violet-100" placeholder="Type the exam here. Use $...$ for inline math and $$...$$ for display math.">{{ old('typed_content', $examPaper->typed_content ?? '') }}</textarea>
                        <p class="mt-3 text-xs text-slate-500">You can submit a typed paper only, a file only, or both together.</p>
                    </div>
                </section>
            </div>

            <div class="space-y-6">
                <section class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-xl">
                    <p class="text-xs font-semibold uppercase tracking-[0.26em] text-emerald-600">Attachment</p>
                    <h4 class="mt-2 text-2xl font-black text-slate-900">Photo or PDF upload</h4>
                    <p class="mt-2 text-sm text-slate-500">Upload a scanned paper, a phone photo, or a prepared PDF. Max size: 15MB.</p>

                    <div class="mt-5">
                        <label for="attachment" class="mb-2 block text-sm font-semibold text-slate-700">Exam file</label>
                        <input id="attachment" name="attachment" type="file" accept=".jpg,.jpeg,.png,.webp,.pdf" class="w-full rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-700 shadow-sm file:mr-4 file:rounded-xl file:border-0 file:bg-emerald-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-emerald-700">
                        <p class="mt-2 text-xs text-slate-500">Allowed files: JPG, PNG, WEBP, PDF.</p>
                    </div>

                    @if ($isEditing && $examPaper->attachment_path)
                        <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Current file</p>
                            <a href="{{ $examPaper->attachment_url }}" target="_blank" rel="noopener noreferrer" class="mt-2 inline-flex items-center gap-2 text-sm font-semibold text-sky-700 hover:text-sky-800">
                                <i class="fas fa-paperclip"></i>
                                {{ $examPaper->attachment_name ?: 'Open attachment' }}
                            </a>
                            <div class="mt-4">
                                <label class="inline-flex items-center gap-2 text-sm text-red-700">
                                    <input type="checkbox" name="remove_attachment" value="1" class="rounded border-slate-300" @checked(old('remove_attachment'))>
                                    <span>Remove existing attachment</span>
                                </label>
                            </div>
                        </div>
                    @endif
                </section>

                <section class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-xl">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.26em] text-fuchsia-600">Live Preview</p>
                            <h4 class="mt-2 text-2xl font-black text-slate-900">MathJax rendering</h4>
                            <p class="mt-2 text-sm text-slate-500">Preview updates while you type so you can catch formatting issues before saving.</p>
                        </div>
                        <div class="rounded-2xl bg-fuchsia-50 px-3 py-2 text-xs font-semibold uppercase tracking-[0.22em] text-fuchsia-700">
                            Preview
                        </div>
                    </div>

                    <div class="mt-6 space-y-5">
                        <div>
                            <p class="mb-2 text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Instructions Preview</p>
                            <div id="exam-paper-instructions-preview" class="math-preview-block rounded-[1.5rem] border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 shadow-sm">
                                Instructions preview will appear here.
                            </div>
                        </div>

                        <div>
                            <p class="mb-2 text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Paper Preview</p>
                            <div id="exam-paper-typed-preview" class="math-preview-block min-h-[16rem] rounded-[1.5rem] border border-slate-200 bg-white p-5 text-sm text-slate-800 shadow-sm">
                                Typed paper preview will appear here.
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3 rounded-[1.75rem] border border-slate-200 bg-white px-6 py-5 shadow-xl">
            <button type="submit" class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-sky-600 via-blue-700 to-cyan-600 px-6 py-3 text-sm font-semibold text-white shadow-lg transition hover:-translate-y-0.5">
                <i class="fas fa-cloud-upload-alt"></i>
                {{ $submitLabel }}
            </button>
            <a href="{{ $backUrl }}" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-6 py-3 text-sm font-semibold text-slate-700 transition hover:-translate-y-0.5 hover:bg-slate-100">
                <i class="fas fa-xmark text-slate-500"></i>
                Cancel
            </a>
        </div>
    </form>
</div>

@once
    @push('scripts')
        <script>
            (function () {
                let renderTimer = null;

                function ensureMathJax() {
                    if (window.MathJax && typeof window.MathJax.typesetPromise === 'function') {
                        document.dispatchEvent(new Event('mathjax-loaded'));
                        return;
                    }

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

                    if (document.getElementById('exam-paper-form-mathjax-script')) {
                        return;
                    }

                    const script = document.createElement('script');
                    script.id = 'exam-paper-form-mathjax-script';
                    script.src = 'https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-chtml.js';
                    script.async = true;
                    document.head.appendChild(script);
                }

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

                    element.textContent = content;
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

                ensureMathJax();

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
