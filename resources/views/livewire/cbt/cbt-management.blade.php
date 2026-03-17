@push('head')
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
            },
            options: {
                skipHtmlTags: ['script', 'noscript', 'style', 'textarea', 'pre'],
                renderActions: {
                    addMenu: []
                }
            }
        };
    </script>
    <script id="cbt-mathjax-script" src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-chtml.js" async></script>
@endpush

<div class="cbt-solid-page min-h-screen bg-themed-primary px-3 py-4 transition-colors duration-300 sm:px-4 lg:px-6">
    @php
        $assessmentItems = collect($assessments->items());
        $pageAssessmentCount = $assessmentItems->count();
        $sealedCount = $assessmentItems->filter(fn ($assessment) => (bool) $assessment->is_locked)->count();
        $studentVisibleCount = $assessmentItems->filter(fn ($assessment) => $assessment->exam_published_at !== null)->count();
        $resultVisibleCount = $assessmentItems->filter(fn ($assessment) => $assessment->results_published_at !== null)->count();
    @endphp

    <section class="overflow-hidden rounded-[2rem] bg-slate-900 px-5 py-6 text-white shadow-2xl sm:px-6 sm:py-7 lg:px-8">
        <div class="grid gap-6 xl:grid-cols-[1.2fr,0.8fr] xl:items-end">
            <div>
                <div class="flex flex-wrap items-center gap-3">
                    <span class="rounded-full bg-amber-300 px-4 py-1.5 text-xs font-bold uppercase tracking-[0.24em] text-slate-950">
                        CBT Management
                    </span>
                    <span class="rounded-full bg-blue-100 px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.24em] text-blue-900">
                        Mobile First
                    </span>
                </div>

                <h1 class="mt-4 text-3xl font-black leading-tight sm:text-4xl">
                    Manage sealed papers, student access, and results from one screen.
                </h1>
                <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-200 sm:text-base">
                    Create assessments, vet question banks, control publication to students, and open participant eligibility without switching layouts across devices.
                </p>

                <div class="mt-5 flex flex-wrap gap-3">
                    <div class="rounded-2xl bg-red-500 px-4 py-3 text-white">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-red-100">Access Rule</p>
                        <p class="mt-1 text-sm font-semibold">{{ $canLockAssessments ? 'Super Admin Controls Sealing' : 'Teacher Limited Access' }}</p>
                    </div>
                    <div class="rounded-2xl bg-orange-500 px-4 py-3 text-white">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-orange-100">Current Page</p>
                        <p class="mt-1 text-sm font-semibold">{{ $pageAssessmentCount }} Assessment{{ $pageAssessmentCount === 1 ? '' : 's' }}</p>
                    </div>
                    <div class="rounded-2xl bg-amber-500 px-4 py-3 text-slate-950">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-amber-900/70">Question Flow</p>
                        <p class="mt-1 text-sm font-semibold">Draft, seal, publish</p>
                    </div>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div class="rounded-[1.5rem] bg-white p-4 text-slate-900 shadow-lg">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Quick Create</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Open the assessment form and configure class, subject, timing, and anti-cheating settings.</p>
                    <button wire:click="$set('showCreateModal', true)"
                        class="mt-4 inline-flex w-full items-center justify-center rounded-xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white transition-colors hover:bg-blue-700">
                        <i class="fas fa-plus mr-2"></i>Create CBT Assessment
                    </button>
                </div>
                <div class="rounded-[1.5rem] bg-lime-500 p-4 text-slate-950 shadow-lg">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-lime-900/70">Workflow Reminder</p>
                    <p class="mt-2 text-sm leading-6 text-slate-900">Seal the paper after vetting. Publish to students only on exam day. Publish results when ready.</p>
                </div>
            </div>
        </div>
    </section>

    @if (session()->has('message'))
        <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800 shadow-sm" role="alert">
            <div class="flex items-center gap-2">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('message') }}</span>
                <button type="button" class="ml-auto text-emerald-700 hover:text-emerald-900" onclick="this.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-red-800 shadow-sm" role="alert">
            <div class="flex items-center gap-2">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ session('error') }}</span>
                <button type="button" class="ml-auto text-red-700 hover:text-red-900" onclick="this.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    @endif

    @if($isRestrictedTeacherManager)
        <div class="mt-6 rounded-2xl border border-blue-200 bg-blue-50 px-4 py-4 text-sm text-blue-900 shadow-sm">
            CBT access is limited to the classes and subjects assigned to you. Teachers can create and maintain their own question banks only. Participant control, publishing, and result release stay with admin-level staff.
        </div>
    @endif

    <section class="mt-6 rounded-[1.75rem] border border-stone-200 bg-stone-50 p-4 shadow-sm sm:p-5 lg:p-6">
        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            <div class="rounded-2xl bg-blue-500 p-4 text-white">
                <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-blue-100">On This Page</p>
                <p class="mt-2 text-2xl font-black">{{ $pageAssessmentCount }}</p>
            </div>
            <div class="rounded-2xl bg-rose-500 p-4 text-white">
                <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-rose-100">Sealed Papers</p>
                <p class="mt-2 text-2xl font-black">{{ $sealedCount }}</p>
            </div>
            <div class="rounded-2xl bg-emerald-500 p-4 text-white">
                <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-emerald-100">Student Access Live</p>
                <p class="mt-2 text-2xl font-black">{{ $studentVisibleCount }}</p>
            </div>
            <div class="rounded-2xl bg-violet-500 p-4 text-white">
                <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-violet-100">Results Visible</p>
                <p class="mt-2 text-2xl font-black">{{ $resultVisibleCount }}</p>
            </div>
        </div>
    </section>

    <section class="mt-6 rounded-[1.75rem] border border-slate-200 bg-white p-4 shadow-lg sm:p-5 lg:p-6">
        @php
            $hasActiveLibraryFilters = filled($filterCourseId)
                || filled($filterLessonId)
                || filled($statusFilter)
                || trim((string) $search) !== ''
                || $sortBy !== 'created_at'
                || $sortDirection !== 'desc';
        @endphp

        <div class="flex flex-col gap-3 border-b border-slate-200 pb-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Assessment Library</p>
                <h2 class="mt-2 text-2xl font-bold text-slate-900">CBT assessments</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    Every assessment card is designed for touch first. The same layout scales up cleanly on larger screens.
                </p>
            </div>
            <div class="rounded-2xl bg-slate-100 px-4 py-3 text-sm font-medium text-slate-700">
                {{ number_format($assessments->total()) }} match{{ $assessments->total() === 1 ? '' : 'es' }} · Page {{ $assessments->currentPage() }} of {{ $assessments->lastPage() }}
            </div>
        </div>

        <div class="mt-5 rounded-[1.4rem] border border-slate-200 bg-slate-50 p-4 sm:p-5">
            <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Sort And Filter</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        Narrow the library by class or subject, then sort by title, questions, duration, pass mark, or recency.
                    </p>
                </div>
                @if($hasActiveLibraryFilters)
                    <button
                        type="button"
                        wire:click="clearAssessmentLibraryFilters"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-100"
                    >
                        <i class="fas fa-rotate-left mr-2"></i>Reset View
                    </button>
                @endif
            </div>

            <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-6">
                <div class="xl:col-span-2">
                    <label for="cbt-library-search" class="mb-2 block text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Search</label>
                    <input
                        id="cbt-library-search"
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search title, class, or subject"
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                    >
                </div>

                <div>
                    <label for="cbt-library-class-filter" class="mb-2 block text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Class</label>
                    <select
                        id="cbt-library-class-filter"
                        wire:model.live="filterCourseId"
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                    >
                        <option value="">All classes</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="cbt-library-subject-filter" class="mb-2 block text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Subject</label>
                    <select
                        id="cbt-library-subject-filter"
                        wire:model.live="filterLessonId"
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                    >
                        <option value="">{{ $filterCourseId ? 'All subjects' : 'Select class first' }}</option>
                        @foreach($filterSubjects as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="cbt-library-status-filter" class="mb-2 block text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Status</label>
                    <select
                        id="cbt-library-status-filter"
                        wire:model.live="statusFilter"
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                    >
                        <option value="">All statuses</option>
                        <option value="draft">Draft / editable</option>
                        <option value="locked">Locked / vetted</option>
                        <option value="student_visible">Student access live</option>
                        <option value="student_hidden">Student access hidden</option>
                        <option value="results_visible">Results visible</option>
                        <option value="results_hidden">Results hidden</option>
                    </select>
                </div>

                <div>
                    <label for="cbt-library-sort-by" class="mb-2 block text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Sort by</label>
                    <select
                        id="cbt-library-sort-by"
                        wire:model.live="sortBy"
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                    >
                        <option value="created_at">Date created</option>
                        <option value="updated_at">Last updated</option>
                        <option value="title">Assessment title</option>
                        <option value="class">Class</option>
                        <option value="subject">Subject</option>
                        <option value="questions_count">Question count</option>
                        <option value="duration">Duration</option>
                        <option value="pass_percentage">Pass mark</option>
                    </select>
                </div>
            </div>

            <div class="mt-3 grid gap-3 md:grid-cols-2 xl:grid-cols-6">
                <div class="xl:col-start-6">
                    <label for="cbt-library-sort-direction" class="mb-2 block text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Direction</label>
                    <select
                        id="cbt-library-sort-direction"
                        wire:model.live="sortDirection"
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                    >
                        <option value="desc">Descending</option>
                        <option value="asc">Ascending</option>
                    </select>
                </div>
            </div>
        </div>

        @if($assessments->count() > 0)
            <div class="mt-6 grid gap-5 xl:grid-cols-2">
                @foreach($assessments as $assessment)
                    <article wire:key="cbt-assessment-card-{{ $assessment->id }}" class="rounded-[1.6rem] border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="flex flex-wrap gap-2">
                                    <span class="inline-flex items-center rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-800">
                                        {{ $assessment->questions->count() }} Questions
                                    </span>
                                    @if($assessment->shuffle_questions)
                                        <span class="inline-flex items-center rounded-full bg-fuchsia-100 px-3 py-1 text-xs font-semibold text-fuchsia-800">
                                            <i class="fas fa-random mr-1"></i>Shuffled
                                        </span>
                                    @endif
                                </div>
                                <h3 class="mt-3 text-lg font-bold text-slate-900 sm:text-xl">{{ $assessment->title }}</h3>
                                <p class="mt-2 text-sm leading-6 text-slate-600">
                                    {{ Str::limit($assessment->description ?: 'No description added yet for this assessment.', 120) }}
                                </p>
                            </div>
                            <span class="inline-flex shrink-0 items-center rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-white">
                                {{ $assessment->formatted_duration }}
                            </span>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <span class="inline-flex items-center rounded-full bg-orange-100 px-3 py-1 text-xs font-medium text-orange-800">
                                <i class="fas fa-school mr-1"></i>{{ $assessment->course?->name ?? $assessment->course?->title ?? 'Not assigned' }}
                            </span>
                            <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-800">
                                <i class="fas fa-book mr-1"></i>{{ $assessment->lesson?->name ?? 'Not assigned' }}
                            </span>
                            <span class="inline-flex items-center rounded-full {{ $assessment->max_attempts === null ? 'bg-blue-100 text-blue-800' : 'bg-lime-100 text-lime-800' }} px-3 py-1 text-xs font-medium">
                                <i class="fas fa-repeat mr-1"></i>{{ $assessment->formatted_max_attempts }}
                            </span>
                        </div>

                        <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                            <div class="rounded-2xl bg-blue-50 px-3 py-4 text-center">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-blue-700">Pass %</p>
                                <p class="mt-2 text-lg font-bold text-blue-800">{{ $assessment->pass_percentage }}%</p>
                            </div>
                            <div class="rounded-2xl bg-violet-50 px-3 py-4 text-center">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-violet-700">Type</p>
                                <p class="mt-2 text-lg font-bold text-violet-800">CBT</p>
                            </div>
                            <div class="rounded-2xl bg-emerald-50 px-3 py-4 text-center">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-emerald-700">Questions</p>
                                <p class="mt-2 text-lg font-bold text-emerald-800">{{ $assessment->questions->count() }}</p>
                            </div>
                            <div class="rounded-2xl bg-amber-50 px-3 py-4 text-center">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-amber-700">Attempts</p>
                                <p class="mt-2 text-lg font-bold text-amber-800">{{ $assessment->formatted_max_attempts }}</p>
                            </div>
                        </div>

                        <div class="mt-5 grid gap-3 sm:grid-cols-3">
                            <div class="rounded-2xl border px-4 py-4 {{ $assessment->is_locked ? 'border-red-200 bg-red-50' : 'border-emerald-200 bg-emerald-50' }}">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.24em] {{ $assessment->is_locked ? 'text-red-700' : 'text-emerald-700' }}">Paper</p>
                                <p class="mt-2 text-sm font-semibold {{ $assessment->is_locked ? 'text-red-800' : 'text-emerald-800' }}">
                                    {{ $assessment->is_locked ? 'Sealed after vetting' : 'Draft and editable' }}
                                </p>
                            </div>
                            <div class="rounded-2xl border px-4 py-4 {{ $assessment->exam_published_at ? 'border-blue-200 bg-blue-50' : 'border-slate-200 bg-slate-50' }}">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.24em] {{ $assessment->exam_published_at ? 'text-blue-700' : 'text-slate-600' }}">Student Access</p>
                                <p class="mt-2 text-sm font-semibold {{ $assessment->exam_published_at ? 'text-blue-800' : 'text-slate-700' }}">
                                    {{ $assessment->exam_published_at ? 'Published to students' : 'Hidden from students' }}
                                </p>
                            </div>
                            <div class="rounded-2xl border px-4 py-4 {{ $assessment->results_published_at ? 'border-violet-200 bg-violet-50' : 'border-amber-200 bg-amber-50' }}">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.24em] {{ $assessment->results_published_at ? 'text-violet-700' : 'text-amber-700' }}">Results</p>
                                <p class="mt-2 text-sm font-semibold {{ $assessment->results_published_at ? 'text-violet-800' : 'text-amber-800' }}">
                                    {{ $assessment->results_published_at ? 'Visible to students' : 'Still hidden' }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                            <div class="flex items-start gap-3">
                                <i class="fas fa-circle-info mt-0.5 text-slate-400"></i>
                                <p>
                                    Use <span class="font-semibold text-slate-800">Participants</span> to control eligibility, <span class="font-semibold text-slate-800">Questions</span> to manage the paper, and publication controls to decide when students can write and when they can see results.
                                </p>
                            </div>
                        </div>

                        <div class="mt-5 grid grid-cols-1 gap-2 sm:grid-cols-2">
                            @if($canAdministerParticipants)
                                <button type="button" wire:click.prevent="viewParticipants({{ $assessment->id }})"
                                    class="inline-flex items-center justify-center rounded-xl border border-purple-200 bg-purple-50 px-4 py-3 text-sm font-medium text-purple-700 transition-colors hover:bg-purple-100">
                                    <i class="fas fa-users mr-2"></i>Participants
                                </button>
                            @endif

                            @if(!$assessment->is_locked || $canLockAssessments)
                                <button type="button" wire:click.prevent="manageQuestions({{ $assessment->id }})"
                                    class="inline-flex items-center justify-center rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-medium text-blue-700 transition-colors hover:bg-blue-100">
                                    <i class="fas fa-question-circle mr-2"></i>{{ $assessment->is_locked ? 'View Questions' : 'Add Questions' }}
                                </button>
                            @endif

                            @if($canLockAssessments)
                                <button wire:click="toggleAssessmentLock({{ $assessment->id }})"
                                    class="inline-flex items-center justify-center rounded-xl border px-4 py-3 text-sm font-medium transition-colors {{ $assessment->is_locked ? 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100' : 'border-red-200 bg-red-50 text-red-700 hover:bg-red-100' }}">
                                    <i class="fas {{ $assessment->is_locked ? 'fa-lock-open' : 'fa-lock' }} mr-2"></i>{{ $assessment->is_locked ? 'Unlock Paper' : 'Lock Paper' }}
                                </button>

                                @if($assessment->exam_published_at)
                                    <button wire:click="unpublishExam({{ $assessment->id }})"
                                        class="inline-flex items-center justify-center rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-700 transition-colors hover:bg-amber-100">
                                        <i class="fas fa-eye-slash mr-2"></i>Withdraw Paper
                                    </button>
                                @else
                                    <button wire:click="publishExam({{ $assessment->id }})"
                                        @if(!$assessment->is_locked || $assessment->questions->count() === 0) disabled @endif
                                        class="inline-flex items-center justify-center rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm font-medium text-indigo-700 transition-colors {{ !$assessment->is_locked || $assessment->questions->count() === 0 ? 'cursor-not-allowed opacity-50' : 'hover:bg-indigo-100' }}">
                                        <i class="fas fa-paper-plane mr-2"></i>Publish Paper
                                    </button>
                                @endif
                            @endif

                            @if($canPublishCbtResults)
                                @if($assessment->results_published_at)
                                    <button wire:click="unpublishResults({{ $assessment->id }})"
                                        class="inline-flex items-center justify-center rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-700 transition-colors hover:bg-amber-100">
                                        <i class="fas fa-eye-slash mr-2"></i>Hide Results
                                    </button>
                                @else
                                    <button wire:click="publishResults({{ $assessment->id }})"
                                        class="inline-flex items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 transition-colors hover:bg-emerald-100">
                                        <i class="fas fa-bullhorn mr-2"></i>Publish Results
                                    </button>
                                @endif
                            @endif

                            <button wire:click="editAssessment({{ $assessment->id }})"
                                class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-100">
                                <i class="fas fa-edit mr-2"></i>Edit
                            </button>

                            @if($canDeleteAssessments)
                                <button wire:click="deleteAssessment({{ $assessment->id }})"
                                    wire:confirm="Are you sure you want to delete this assessment?"
                                    class="inline-flex items-center justify-center rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 transition-colors hover:bg-red-100 sm:col-span-2">
                                    <i class="fas fa-trash mr-2"></i>Delete Assessment
                                </button>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $assessments->links() }}
            </div>
        @else
            <div class="py-12 text-center">
                <div class="mx-auto flex h-24 w-24 items-center justify-center rounded-full bg-slate-100">
                    <i class="fas fa-clipboard-list text-4xl text-slate-400"></i>
                </div>
                @if($hasActiveLibraryFilters)
                    <h5 class="mt-5 text-xl font-semibold text-slate-800">No Assessments Match This View</h5>
                    <p class="mx-auto mt-2 max-w-lg text-sm leading-6 text-slate-500 sm:text-base">
                        Adjust the class, subject, status, search term, or sort settings to broaden the list again.
                    </p>
                    <button wire:click="clearAssessmentLibraryFilters"
                        class="mt-6 inline-flex items-center rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-slate-700">
                        <i class="fas fa-rotate-left mr-2"></i>Clear Filters
                    </button>
                @else
                    <h5 class="mt-5 text-xl font-semibold text-slate-800">No CBT Assessments Yet</h5>
                    <p class="mx-auto mt-2 max-w-lg text-sm leading-6 text-slate-500 sm:text-base">
                        Create your first CBT assessment to start building question banks, locking vetted papers, and controlling student access.
                    </p>
                    <button wire:click="$set('showCreateModal', true)"
                        class="mt-6 inline-flex items-center rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-blue-700">
                        <i class="fas fa-plus mr-2"></i>Create CBT Assessment
                    </button>
                @endif
            </div>
        @endif
    </section>


    <!-- Create Assessment Modal -->
    <!-- Create Assessment Modal -->
    <div class="@if($showCreateModal) fixed inset-0 bg-gray-600 bg-opacity-50 dark:bg-gray-900 dark:bg-opacity-75 overflow-y-auto h-full w-full z-50 @else hidden @endif">
        <div class="relative top-4 sm:top-20 mx-2 sm:mx-auto p-4 sm:p-5 border w-auto sm:w-11/12 max-w-4xl shadow-lg rounded-lg bg-themed-secondary border-themed-primary @if($showCreateModal) animate-fade-in-down @endif">
            <div class="border-b border-themed-secondary pb-3 sm:pb-4 mb-3 sm:mb-4">
                <h5 class="text-lg sm:text-xl font-semibold text-themed-primary flex items-center pr-8">
                    <i class="fas fa-plus mr-2"></i>Create CBT Assessment
                </h5>
                <button type="button" class="absolute top-3 sm:top-4 right-3 sm:right-4 text-themed-tertiary hover:text-themed-secondary" wire:click="closeModals">
                    <i class="fas fa-times text-lg sm:text-xl"></i>
                </button>
            </div>
            <div>
                <form wire:submit="createAssessment">
                    <div class="mb-3 sm:mb-4">
                        <label for="course_id" class="block text-xs sm:text-sm font-medium text-themed-primary mb-2">Class</label>
                        <select wire:model.live="course_id" class="w-full px-2 sm:px-3 py-2 text-sm sm:text-base border border-themed-secondary rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-themed-primary focus:border-accent-themed-primary bg-themed-primary text-themed-primary placeholder-themed-tertiary">
                            <option value="">Select class</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-themed-tertiary mt-1">Only students currently assigned to this class will see and take this CBT.</p>
                        @error('course_id') <div class="text-red-500 dark:text-red-400 text-xs sm:text-sm mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3 sm:mb-4">
                        <label for="lesson_id" class="block text-xs sm:text-sm font-medium text-themed-primary mb-2">Subject</label>
                        <select wire:model="lesson_id" class="w-full px-2 sm:px-3 py-2 text-sm sm:text-base border border-themed-secondary rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-themed-primary focus:border-accent-themed-primary bg-themed-primary text-themed-primary placeholder-themed-tertiary">
                            <option value="">{{ $course_id ? 'Select subject' : 'Select class first' }}</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-themed-tertiary mt-1">Restricted teachers only see subjects they teach in the selected class.</p>
                        @error('lesson_id') <div class="text-red-500 dark:text-red-400 text-xs sm:text-sm mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3 sm:mb-4">
                        <label for="title" class="block text-xs sm:text-sm font-medium text-themed-primary mb-2">Assessment Title</label>
                        <input type="text" wire:model="title" class="w-full px-2 sm:px-3 py-2 text-sm sm:text-base border border-themed-secondary rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-themed-primary focus:border-accent-themed-primary bg-themed-primary text-themed-primary placeholder-themed-tertiary" required>
                        @error('title') <div class="text-red-500 dark:text-red-400 text-xs sm:text-sm mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3 sm:mb-4">
                        <label for="description" class="block text-xs sm:text-sm font-medium text-themed-primary mb-2">Description</label>
                        <textarea wire:model="description" class="w-full px-2 sm:px-3 py-2 text-sm sm:text-base border border-themed-secondary rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-themed-primary focus:border-accent-themed-primary bg-themed-primary text-themed-primary placeholder-themed-tertiary" rows="3"></textarea>
                        @error('description') <div class="text-red-500 dark:text-red-400 text-xs sm:text-sm mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 sm:gap-4 mb-4 sm:mb-6">
                        <div>
                            <label for="pass_percentage" class="block text-xs sm:text-sm font-medium text-themed-primary mb-2">Pass %</label>
                            <input type="number" wire:model="pass_percentage" class="w-full px-2 sm:px-3 py-2 text-sm sm:text-base border border-themed-secondary rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-themed-primary focus:border-accent-themed-primary bg-themed-primary text-themed-primary placeholder-themed-tertiary" min="1" max="100" required>
                            @error('pass_percentage') <div class="text-red-500 dark:text-red-400 text-xs sm:text-sm mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label for="estimated_duration_minutes" class="block text-xs sm:text-sm font-medium text-themed-primary mb-2">Duration (Min)</label>
                            <input type="number" wire:model="estimated_duration_minutes" class="w-full px-2 sm:px-3 py-2 text-sm sm:text-base border border-themed-secondary rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-themed-primary focus:border-accent-themed-primary bg-themed-primary text-themed-primary placeholder-themed-tertiary" min="1" required>
                            @error('estimated_duration_minutes') <div class="text-red-500 dark:text-red-400 text-xs sm:text-sm mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label for="max_score" class="block text-xs sm:text-sm font-medium text-themed-primary mb-2">Max Score</label>
                            <input type="number" wire:model="max_score" class="w-full px-2 sm:px-3 py-2 text-sm sm:text-base border border-themed-secondary rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-themed-primary focus:border-accent-themed-primary bg-themed-primary text-themed-primary placeholder-themed-tertiary" min="1" required>
                            @error('max_score') <div class="text-red-500 dark:text-red-400 text-xs sm:text-sm mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label for="max_attempts" class="block text-xs sm:text-sm font-medium text-themed-primary mb-2">Max Attempts</label>
                            <input type="number" wire:model="max_attempts" class="w-full px-2 sm:px-3 py-2 text-sm sm:text-base border border-themed-secondary rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-themed-primary focus:border-accent-themed-primary bg-themed-primary text-themed-primary placeholder-themed-tertiary" min="1" max="100" placeholder="Unlimited">
                            <p class="text-xs text-themed-tertiary mt-1">Leave empty for unlimited</p>
                            @error('max_attempts') <div class="text-red-500 dark:text-red-400 text-xs sm:text-sm mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- NEW: Shuffle Settings -->
                    <div class="bg-themed-tertiary rounded-lg p-4 mb-4 sm:mb-6 border border-themed-secondary">
                        <h6 class="text-sm font-semibold text-themed-primary mb-3 flex items-center">
                            <i class="fas fa-random mr-2 text-accent-themed-primary"></i>
                            Anti-Cheating Settings (UTME Style)
                        </h6>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <label for="shuffle_questions" class="text-sm font-medium text-themed-primary">Shuffle Questions</label>
                                    <p class="text-xs text-themed-tertiary mt-1">Randomize question order for each student</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer ml-3">
                                    <input type="checkbox" wire:model="shuffle_questions" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                </label>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <label for="shuffle_options" class="text-sm font-medium text-themed-primary">Shuffle Options</label>
                                    <p class="text-xs text-themed-tertiary mt-1">Randomize A, B, C, D order</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer ml-3">
                                    <input type="checkbox" wire:model="shuffle_options" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3 pt-3 sm:pt-4 border-t border-themed-secondary">
                        <button type="button" class="w-full sm:w-auto px-3 sm:px-4 py-2 text-sm sm:text-base bg-themed-tertiary text-themed-primary rounded-lg hover:bg-themed-secondary transition-colors border border-themed-secondary" wire:click="closeModals">Cancel</button>
                        <button type="submit" class="w-full sm:w-auto px-3 sm:px-4 py-2 text-sm sm:text-base bg-accent-themed-primary text-white rounded-lg hover:bg-accent-themed-secondary transition-colors flex items-center justify-center">
                            <i class="fas fa-save mr-2"></i>Create Assessment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Assessment Modal -->
    <div
        class="@if($showEditModal) fixed inset-0 bg-gray-600 bg-opacity-50 dark:bg-gray-900 dark:bg-opacity-75 overflow-y-auto h-full w-full z-50 @else hidden @endif">
        <div
            class="relative top-4 sm:top-20 mx-2 sm:mx-auto p-4 sm:p-5 border w-auto sm:w-11/12 max-w-4xl shadow-lg rounded-lg bg-themed-secondary border-themed-primary @if($showEditModal) animate-fade-in-down @endif">
            <div class="border-b border-themed-secondary pb-3 sm:pb-4 mb-3 sm:mb-4">
                <h5 class="text-lg sm:text-xl font-semibold text-themed-primary flex items-center pr-8">
                    <i class="fas fa-edit mr-2"></i>Edit CBT Assessment
                </h5>
                <button type="button"
                    class="absolute top-3 sm:top-4 right-3 sm:right-4 text-themed-tertiary hover:text-themed-secondary"
                    wire:click="closeModals">
                    <i class="fas fa-times text-lg sm:text-xl"></i>
                </button>
            </div>
            <div>
                <form wire:submit="updateAssessment">
                    <div class="mb-3 sm:mb-4">
                        <label for="course_id"
                            class="block text-xs sm:text-sm font-medium text-themed-primary mb-2">Class</label>
                        <select wire:model.live="course_id"
                            class="w-full px-2 sm:px-3 py-2 text-sm sm:text-base border border-themed-secondary rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-themed-primary focus:border-accent-themed-primary bg-themed-primary text-themed-primary placeholder-themed-tertiary">
                            <option value="">Select class</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-themed-tertiary mt-1">Only students currently assigned to this class will see and take this CBT.</p>
                        @error('course_id') <div class="text-red-500 dark:text-red-400 text-xs sm:text-sm mt-1">
                        {{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3 sm:mb-4">
                        <label for="lesson_id"
                            class="block text-xs sm:text-sm font-medium text-themed-primary mb-2">Subject</label>
                        <select wire:model="lesson_id"
                            class="w-full px-2 sm:px-3 py-2 text-sm sm:text-base border border-themed-secondary rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-themed-primary focus:border-accent-themed-primary bg-themed-primary text-themed-primary placeholder-themed-tertiary">
                            <option value="">{{ $course_id ? 'Select subject' : 'Select class first' }}</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-themed-tertiary mt-1">Restricted teachers only see subjects they teach in the selected class.</p>
                        @error('lesson_id') <div class="text-red-500 dark:text-red-400 text-xs sm:text-sm mt-1">
                        {{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3 sm:mb-4">
                        <label for="title"
                            class="block text-xs sm:text-sm font-medium text-themed-primary mb-2">Assessment
                            Title</label>
                        <input type="text" wire:model="title"
                            class="w-full px-2 sm:px-3 py-2 text-sm sm:text-base border border-themed-secondary rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-themed-primary focus:border-accent-themed-primary bg-themed-primary text-themed-primary placeholder-themed-tertiary"
                            required>
                        @error('title') <div class="text-red-500 dark:text-red-400 text-xs sm:text-sm mt-1">
                        {{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3 sm:mb-4">
                        <label for="description"
                            class="block text-xs sm:text-sm font-medium text-themed-primary mb-2">Description</label>
                        <textarea wire:model="description"
                            class="w-full px-2 sm:px-3 py-2 text-sm sm:text-base border border-themed-secondary rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-themed-primary focus:border-accent-themed-primary bg-themed-primary text-themed-primary placeholder-themed-tertiary"
                            rows="3"></textarea>
                        @error('description') <div class="text-red-500 dark:text-red-400 text-xs sm:text-sm mt-1">
                        {{ $message }}</div> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 sm:gap-4 mb-4 sm:mb-6">
                        <div>
                            <label for="pass_percentage"
                                class="block text-xs sm:text-sm font-medium text-themed-primary mb-2">Pass %</label>
                            <input type="number" wire:model="pass_percentage"
                                class="w-full px-2 sm:px-3 py-2 text-sm sm:text-base border border-themed-secondary rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-themed-primary focus:border-accent-themed-primary bg-themed-primary text-themed-primary placeholder-themed-tertiary"
                                min="1" max="100" required>
                            @error('pass_percentage') <div
                                class="text-red-500 dark:text-red-400 text-xs sm:text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label for="estimated_duration_minutes"
                                class="block text-xs sm:text-sm font-medium text-themed-primary mb-2">Duration
                                (Min)</label>
                            <input type="number" wire:model="estimated_duration_minutes"
                                class="w-full px-2 sm:px-3 py-2 text-sm sm:text-base border border-themed-secondary rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-themed-primary focus:border-accent-themed-primary bg-themed-primary text-themed-primary placeholder-themed-tertiary"
                                min="1" required>
                            @error('estimated_duration_minutes') <div
                                class="text-red-500 dark:text-red-400 text-xs sm:text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label for="max_score"
                                class="block text-xs sm:text-sm font-medium text-themed-primary mb-2">Max Score</label>
                            <input type="number" wire:model="max_score"
                                class="w-full px-2 sm:px-3 py-2 text-sm sm:text-base border border-themed-secondary rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-themed-primary focus:border-accent-themed-primary bg-themed-primary text-themed-primary placeholder-themed-tertiary"
                                min="1" required>
                            @error('max_score') <div class="text-red-500 dark:text-red-400 text-xs sm:text-sm mt-1">
                            {{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label for="max_attempts"
                                class="block text-xs sm:text-sm font-medium text-themed-primary mb-2">Max
                                Attempts</label>
                            <input type="number" wire:model="max_attempts"
                                class="w-full px-2 sm:px-3 py-2 text-sm sm:text-base border border-themed-secondary rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-themed-primary focus:border-accent-themed-primary bg-themed-primary text-themed-primary placeholder-themed-tertiary"
                                min="1" max="100" placeholder="Unlimited">
                            <p class="text-xs text-themed-tertiary mt-1">Leave empty for unlimited</p>
                            @error('max_attempts') <div class="text-red-500 dark:text-red-400 text-xs sm:text-sm mt-1">
                            {{ $message }}</div> @enderror
                        </div>
                    </div>
                     <!-- NEW: Shuffle Settings -->
                     <div class="bg-themed-tertiary rounded-lg p-4 mb-4 sm:mb-6 border border-themed-secondary">
                        <h6 class="text-sm font-semibold text-themed-primary mb-3 flex items-center">
                            <i class="fas fa-random mr-2 text-accent-themed-primary"></i>
                            Anti-Cheating Settings (UTME Style)
                        </h6>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <label for="shuffle_questions" class="text-sm font-medium text-themed-primary">Shuffle Questions</label>
                                    <p class="text-xs text-themed-tertiary mt-1">Randomize question order for each student</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer ml-3">
                                    <input type="checkbox" wire:model="shuffle_questions" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                </label>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <label for="shuffle_options" class="text-sm font-medium text-themed-primary">Shuffle Options</label>
                                    <p class="text-xs text-themed-tertiary mt-1">Randomize A, B, C, D order</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer ml-3">
                                    <input type="checkbox" wire:model="shuffle_options" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div
                        class="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3 pt-3 sm:pt-4 border-t border-themed-secondary">
                        <button type="button"
                            class="w-full sm:w-auto px-3 sm:px-4 py-2 text-sm sm:text-base bg-themed-tertiary text-themed-primary rounded-lg hover:bg-themed-secondary transition-colors border border-themed-secondary"
                            wire:click="closeModals">Cancel</button>
                        <button type="submit"
                            class="w-full sm:w-auto px-3 sm:px-4 py-2 text-sm sm:text-base bg-accent-themed-primary text-white rounded-lg hover:bg-accent-themed-secondary transition-colors flex items-center justify-center">
                            <i class="fas fa-save mr-2"></i>Update Assessment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Questions Management Modal -->
    @if($selectedAssessment && $showQuestionModal)
        @php($questionsLocked = $selectedAssessment->is_locked)
        <div
            class="fixed inset-0 bg-gray-600 bg-opacity-50 dark:bg-gray-900 dark:bg-opacity-75 overflow-y-auto h-full w-full z-50">
            <div
                class="relative top-10 mx-auto p-5 border w-11/12 max-w-7xl shadow-lg rounded-lg bg-themed-secondary border-themed-primary animate-fade-in-up">
                <div class="border-b border-themed-secondary pb-4 mb-4">
                    <h5 class="text-xl font-semibold text-themed-primary flex items-center">
                        <i class="fas fa-question-circle mr-2"></i>
                        Manage Questions - {{ $selectedAssessment->title }}
                    </h5>
                    <button type="button" class="absolute top-4 right-4 text-themed-tertiary hover:text-themed-secondary"
                        wire:click="closeModals">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                @if($questionsLocked)
                    <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-200">
                        This paper is locked after vetting. Questions are read-only until the paper is unlocked. Students will only see it after you publish the paper.
                    </div>
                @endif

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Add Question Form -->
                    <div>
                        @if(!$questionsLocked)
                            <h6 class="text-lg font-semibold text-themed-primary mb-4">Add New Question</h6>
                            <details class="mb-4 rounded-lg border border-blue-200 bg-blue-50 text-sm text-blue-900 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-200">
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-3 p-4 font-semibold">
                                    <span>Math Upload Guide</span>
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </summary>
                                <div class="border-t border-blue-200 px-4 pb-4 pt-3 dark:border-blue-800">
                                    <p>Inline math: <code>$x^2 + y^2 = 25$</code>. Block math: <code>$$\frac{-b \pm \sqrt{b^2-4ac}}{2a}$$</code>.</p>
                                    <p class="mt-1">Use normal text outside the math markers. The preview updates as you type.</p>
                                </div>
                            </details>
                            <form wire:submit="addQuestion">
                            <div class="mb-4">

                                <div class="mb-4">
                                <label for="explanation" class="block text-sm font-medium text-themed-primary mb-2">
                                    Instruction
                                    <span class="text-xs text-themed-tertiary">(Use $...$ for inline math and $$...$$ for
                                        display math)</span>
                                </label>
                                <textarea wire:model="explanation"
                                    class="w-full px-3 py-2 border border-themed-secondary rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-themed-primary focus:border-accent-themed-primary bg-themed-primary text-themed-primary placeholder-themed-tertiary"
                                    rows="2" placeholder="E.g., Using Pythagorean theorem: $a^2 + b^2 = c^2$"></textarea>

                                <!-- Live Preview -->
                                <div class="mt-2 p-3 bg-themed-tertiary rounded-lg">
                                    <label class="text-xs text-themed-secondary mb-1 block">Preview:</label>
                                    <div id="explanation-preview" class="text-themed-primary min-h-6 math-content">
                                        @if($explanation)
                                            {!! $explanation !!}
                                        @else
                                            <span class="text-themed-tertiary">Preview will appear here</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                                <label for="question_text" class="block text-sm font-medium text-themed-primary mb-2">
                                    Question Text
                                    <span class="text-xs text-themed-tertiary">(Optional if you upload a question file. Use $...$ for inline math and $$...$$ for
                                        display math)</span>
                                </label>
                                <textarea wire:model="question_text"
                                    class="w-full px-3 py-2 border border-themed-secondary rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-themed-primary focus:border-accent-themed-primary bg-themed-primary text-themed-primary placeholder-themed-tertiary"
                                    rows="3" placeholder="E.g., Solve the equation: $x^2 + y^2 = 25$"></textarea>
                                @error('question_text') <div class="text-red-500 dark:text-red-400 text-sm mt-1">
                                {{ $message }}</div> @enderror

                                <!-- Live Preview -->
                                <div class="mt-2 p-3 bg-themed-tertiary rounded-lg">
                                    <label class="text-xs text-themed-secondary mb-1 block">Preview:</label>
                                    <div id="question-preview" class="text-themed-primary min-h-6 math-content">
                                        @if($question_text)
                                            {!! $question_text !!}
                                        @else
                                            <span class="text-themed-tertiary">Preview will appear here</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="question_media" class="block text-sm font-medium text-themed-primary mb-2">
                                    Question File
                                    <span class="text-xs text-themed-tertiary">(Optional. Images display inside the CBT. PDF files will open as links.)</span>
                                </label>
                                <input type="file"
                                    id="question_media"
                                    wire:model="question_media"
                                    accept=".jpg,.jpeg,.png,.gif,.webp,.svg,.pdf,.doc,.docx"
                                    class="w-full px-3 py-2 border border-themed-secondary rounded-lg bg-themed-primary text-themed-primary file:mr-4 file:rounded-md file:border-0 file:bg-accent-themed-primary file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white">
                                @error('question_media') <div class="text-red-500 dark:text-red-400 text-sm mt-1">{{ $message }}</div> @enderror

                                @if($question_media)
                                    <div class="mt-3 rounded-lg border border-themed-secondary bg-themed-tertiary p-3">
                                        <div class="flex items-center justify-between gap-3">
                                            <div>
                                                <div class="text-sm font-semibold text-themed-primary">{{ $question_media->getClientOriginalName() }}</div>
                                                <div class="text-xs text-themed-secondary">Selected for upload</div>
                                            </div>
                                        </div>
                                        @if(str_starts_with((string) $question_media->getMimeType(), 'image/'))
                                            <img src="{{ $question_media->temporaryUrl() }}"
                                                alt="Question file preview"
                                                class="mt-3 max-h-64 rounded-lg border border-themed-secondary object-contain bg-white">
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                <div class="md:col-span-2">
                                    <label for="question_type"
                                        class="block text-sm font-medium text-themed-primary mb-2">Question Type</label>
                                    <select wire:model.live="question_type"
                                        class="w-full px-3 py-2 border border-themed-secondary rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-themed-primary focus:border-accent-themed-primary bg-themed-primary text-themed-primary placeholder-themed-tertiary"
                                        required>
                                        <option value="multiple_choice">Multiple Choice</option>
                                        <option value="true_false">True/False</option>
                                        <option value="short_answer">Short Answer</option>
                                        <option value="essay">Essay</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="points"
                                        class="block text-sm font-medium text-themed-primary mb-2">Points</label>
                                    <input type="number" wire:model="points"
                                        class="w-full px-3 py-2 border border-themed-secondary rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-themed-primary focus:border-accent-themed-primary bg-themed-primary text-themed-primary placeholder-themed-tertiary"
                                        step="0.1" min="0.1" required>
                                    @error('points') <div class="text-red-500 dark:text-red-400 text-sm mt-1">{{ $message }}
                                    </div> @enderror
                                </div>
                            </div>

                            @if($question_type === 'multiple_choice')
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-themed-primary mb-2">
                                        Options
                                        <span class="text-xs text-themed-tertiary">(Use $...$ for inline math and $$...$$ for
                                            display math)</span>
                                    </label>
                                    @foreach($options as $index => $option)
                                        <div class="mb-3">
                                            <div class="flex items-center mb-1">
                                                <span
                                                    class="bg-themed-tertiary text-themed-primary px-3 py-2 rounded-l-lg border border-r-0 border-themed-secondary text-sm font-medium">
                                                    {{ chr(65 + $index) }}
                                                </span>
                                                <input type="text" wire:model="options.{{ $index }}"
                                                    class="flex-1 px-3 py-2 border border-themed-secondary focus:outline-none focus:ring-2 focus:ring-accent-themed-primary focus:border-accent-themed-primary bg-themed-primary text-themed-primary placeholder-themed-tertiary"
                                                    placeholder="Option {{ chr(65 + $index) }} (e.g., $x^2 + 5x + 6$)">
                                                <div
                                                    class="bg-themed-tertiary border border-l-0 border-r-0 border-themed-secondary px-3 py-2">
                                                    <input type="checkbox" wire:model="correct_answers" value="{{ $index }}"
                                                        class="form-checkbox h-4 w-4 text-accent-themed-primary"
                                                        title="Correct Answer">
                                                </div>
                                                <button type="button" onclick="toggleOptionPreview({{ $index }})"
                                                    class="bg-themed-tertiary border border-l-0 border-themed-secondary rounded-r-lg px-3 py-2 hover:bg-themed-secondary transition-colors"
                                                    title="Toggle Preview">
                                                    <i class="fas fa-eye text-themed-secondary"></i>
                                                </button>
                                            </div>

                                            <!-- Preview Container -->
                                            <div id="option-preview-container-{{ $index }}"
                                                class="hidden mt-1 ml-12 option-preview-container">
                                                <div class="p-2 bg-themed-tertiary rounded-lg border border-themed-secondary">
                                                    <label class="text-xs text-themed-secondary mb-1 block">Preview:</label>
                                                    <div id="option-preview-{{ $index }}"
                                                        class="text-themed-primary min-h-6 math-content option-preview-content">
                                                        <span class="text-themed-tertiary">Preview will appear here</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    @error('options') <div class="text-red-500 dark:text-red-400 text-sm mt-1">{{ $message }}
                                    </div> @enderror
                                    @error('correct_answers') <div class="text-red-500 dark:text-red-400 text-sm mt-1">
                                    {{ $message }}</div> @enderror
                                </div>
                            @elseif($question_type === 'true_false')
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-themed-primary mb-2">Correct Answer</label>
                                    <div class="space-y-2">
                                        <div class="flex items-center">
                                            <input type="radio" wire:model="correct_answers" value="0"
                                                class="form-radio h-4 w-4 text-accent-themed-primary" id="true_option">
                                            <label class="ml-2 text-sm text-themed-primary" for="true_option">True</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input type="radio" wire:model="correct_answers" value="1"
                                                class="form-radio h-4 w-4 text-accent-themed-primary" id="false_option">
                                            <label class="ml-2 text-sm text-themed-primary" for="false_option">False</label>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            

                                <button type="submit"
                                    class="w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors flex items-center justify-center">
                                    <i class="fas fa-plus mr-2"></i>Add Question
                                </button>
                            </form>
                        @else
                            <h6 class="text-lg font-semibold text-themed-primary mb-4">Question Bank Sealed</h6>
                            <div class="rounded-lg border border-themed-secondary bg-themed-tertiary p-5 text-sm text-themed-secondary">
                                <p class="font-semibold text-themed-primary">This paper is in a sealed stage.</p>
                                <p class="mt-2">Unlock the paper first if you need to add, edit, delete, or reorder questions.</p>
                                <p class="mt-2">When the exam should start, publish the paper from the CBT management list so only students can see it.</p>
                            </div>
                        @endif
                    </div>

                    <!-- Questions List with Edit and Reorder -->
                    <div>
                        <h6 class="text-lg font-semibold text-themed-primary mb-4">Questions
                            ({{ $selectedAssessment->questions->count() }})</h6>
                        @if($selectedAssessment->questions->count() > 0)
                            <div @if(!$questionsLocked) id="questions-sortable" @endif class="space-y-3 max-h-[600px] overflow-y-auto pr-2">
                                @foreach($selectedAssessment->questions->sortBy('order') as $question)
                                    <div class="question-item bg-themed-tertiary border border-themed-secondary rounded-lg p-4 hover:shadow-md transition-shadow"
                                        data-id="{{ $question->id }}">
                                        <div class="flex items-start">
                                            <!-- Drag Handle -->
                                            <div
                                                class="{{ $questionsLocked ? 'mr-3 pt-1 text-themed-tertiary opacity-40' : 'drag-handle cursor-move mr-3 text-themed-tertiary hover:text-themed-primary pt-1' }}">
                                                <i class="fas {{ $questionsLocked ? 'fa-lock' : 'fa-grip-vertical' }} text-lg"></i>
                                            </div>

                                            <!-- Question Content -->
                                            <div class="flex-1">
                                                @if($question->explanation)
                                                    <div class="mb-2 text-sm text-themed-secondary">
                                                        <strong>Instruction:</strong>
                                                        <span class="math-content">{!! Str::limit($question->explanation, 80) !!}</span>
                                                    </div>
                                                @endif
                                                <h6 class="font-semibold text-themed-primary mb-1">
                                                    Q{{ $loop->iteration }}.
                                                    <span
                                                        class="math-content">{!! Str::limit($question->question_text, 100) !!}</span>
                                                </h6>
                                                @if($question->options && count($question->options) > 0)
                                                    <div class="mt-2 ml-4 space-y-1">
                                                        @foreach($question->options as $index => $option)
                                                            <div class="text-sm text-themed-secondary flex items-start">
                                                                <span class="font-medium mr-2">{{ chr(65 + $index) }}.</span>
                                                                <span class="math-content flex-1">{!! Str::limit($option, 50) !!}</span>
                                                                @if(in_array($index, $question->correct_answers ?? []))
                                                                    <i class="fas fa-check text-green-600 ml-2"></i>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                                @if($question->has_question_media)
                                                    <div class="mt-2 text-sm text-themed-secondary">
                                                        <strong>Question File:</strong>
                                                        <a href="{{ $question->question_media_url }}"
                                                            target="_blank"
                                                            rel="noopener noreferrer"
                                                            class="font-medium text-blue-700 hover:text-blue-800 dark:text-blue-300 dark:hover:text-blue-200">
                                                            {{ $question->question_media_original_name ?: 'Open file' }}
                                                        </a>
                                                    </div>
                                                @endif
                                                <div class="flex space-x-2 mt-2">
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-themed-secondary text-accent-themed-primary">
                                                        {{ ucfirst(str_replace('_', ' ', $question->question_type)) }}
                                                    </span>
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-themed-tertiary text-accent-themed-primary">
                                                        {{ $question->points }} pts
                                                    </span>
                                                </div>
                                            </div>

                                            @if(!$questionsLocked)
                                                <!-- Action Buttons -->
                                                <div class="ml-3 flex flex-col gap-2">
                                                    <button wire:click="editQuestion({{ $question->id }})"
                                                        class="inline-flex items-center gap-2 rounded-lg border border-blue-200 px-3 py-2 text-sm font-medium text-blue-700 transition-colors hover:bg-blue-50 dark:border-blue-800 dark:text-blue-300 dark:hover:bg-blue-900/30"
                                                        title="Edit Question">
                                                        <i class="fas fa-edit"></i>
                                                        <span>Edit</span>
                                                    </button>
                                                    <button wire:click="deleteQuestion({{ $question->id }})"
                                                        wire:confirm="Are you sure you want to delete this question?"
                                                        class="inline-flex items-center gap-2 rounded-lg border border-red-200 px-3 py-2 text-sm font-medium text-red-700 transition-colors hover:bg-red-50 dark:border-red-800 dark:text-red-300 dark:hover:bg-red-900/30"
                                                        title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                        <span>Delete</span>
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8 border-2 border-dashed border-themed-secondary rounded-lg">
                                <i class="fas fa-question text-4xl text-themed-tertiary mb-3"></i>
                                <p class="text-themed-secondary">No questions added yet</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Edit Question Modal -->
    @if($showEditQuestionModal && $editingQuestion)
        <div class="fixed inset-0 bg-black bg-opacity-50 z-[60] flex items-center justify-center p-4">
            <div class="bg-themed-secondary rounded-lg max-w-4xl w-full max-h-screen overflow-y-auto">
                <div class="p-6 border-b border-themed-secondary sticky top-0 bg-themed-secondary z-10">
                    <h3 class="text-xl font-bold text-themed-primary">Edit Question</h3>
                    <button type="button" class="absolute top-4 right-4 text-themed-tertiary hover:text-themed-secondary"
                        wire:click="closeModals">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <div class="p-6">
                    <form wire:submit="updateQuestion">
                        <!-- Instruction -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-themed-primary mb-2">
                                Instruction (Optional)
                                <span class="text-xs text-themed-tertiary">(Use $...$ for inline math and $$...$$ for display math)</span>
                            </label>
                            <textarea wire:model="explanation" rows="2"
                                class="w-full px-3 py-2 border border-themed-secondary rounded-lg bg-themed-primary focus:ring-2 focus:ring-accent-themed-primary"></textarea>

                            <!-- Live Preview -->
                            <div class="mt-2 p-3 bg-themed-tertiary rounded-lg">
                                <label class="text-xs text-themed-secondary mb-1 block">Preview:</label>
                                <div id="edit-explanation-preview" class="math-content text-themed-primary min-h-6">
                                    @if($explanation)
                                        {!! $explanation !!}
                                    @else
                                        <span class="text-themed-tertiary">Preview will appear here</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Question Text -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-themed-primary mb-2">
                                Question Text
                                <span class="text-xs text-themed-tertiary">(Optional if a question file is attached. Use $...$ for inline math, $$...$$ for
                                    display)</span>
                            </label>
                            <textarea wire:model="question_text" rows="3"
                                class="w-full px-3 py-2 border border-themed-secondary rounded-lg bg-themed-primary text-themed-primary focus:ring-2 focus:ring-accent-themed-primary"
                                ></textarea>

                            <!-- Live Preview -->
                            <div class="mt-2 p-3 bg-themed-tertiary rounded-lg">
                                <label class="text-xs text-themed-secondary mb-1 block">Preview:</label>
                                <div id="edit-question-preview" class="math-content text-themed-primary min-h-6">
                                    @if($question_text)
                                        {!! $question_text !!}
                                    @else
                                        <span class="text-themed-tertiary">Preview will appear here</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Question Type and Points -->
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-themed-primary mb-2">Question Type</label>
                                <select wire:model.live="question_type"
                                    class="w-full px-3 py-2 border border-themed-secondary rounded-lg bg-themed-primary focus:ring-2 focus:ring-accent-themed-primary">
                                    <option value="multiple_choice">Multiple Choice</option>
                                    <option value="true_false">True/False</option>
                                    <option value="short_answer">Short Answer</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-themed-primary mb-2">Points</label>
                                <input type="number" wire:model="points" step="0.1" min="0.1"
                                    class="w-full px-3 py-2 border border-themed-secondary rounded-lg bg-themed-primary focus:ring-2 focus:ring-accent-themed-primary"
                                    required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-themed-primary mb-2">
                                Question File
                                <span class="text-xs text-themed-tertiary">(Optional. Upload a new file to replace the current one.)</span>
                            </label>
                            <input type="file"
                                wire:model="question_media"
                                accept=".jpg,.jpeg,.png,.gif,.webp,.svg,.pdf,.doc,.docx"
                                class="w-full px-3 py-2 border border-themed-secondary rounded-lg bg-themed-primary text-themed-primary file:mr-4 file:rounded-md file:border-0 file:bg-accent-themed-primary file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white">
                            @error('question_media') <div class="text-red-500 dark:text-red-400 text-sm mt-1">{{ $message }}</div> @enderror

                            @if($existing_question_media_path && !$question_media && !$remove_question_media)
                                <div class="mt-3 rounded-lg border border-themed-secondary bg-themed-tertiary p-3">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <div class="text-sm font-semibold text-themed-primary">{{ $existing_question_media_name ?: 'Current question file' }}</div>
                                            <a href="{{ asset('storage/' . $existing_question_media_path) }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="mt-1 inline-flex text-xs font-medium text-blue-700 hover:text-blue-800 dark:text-blue-300 dark:hover:text-blue-200">
                                                Open current file
                                            </a>
                                        </div>
                                        <label class="inline-flex items-center gap-2 text-sm text-themed-primary">
                                            <input type="checkbox" wire:model="remove_question_media" class="form-checkbox h-4 w-4 text-red-600">
                                            <span>Remove file</span>
                                        </label>
                                    </div>
                                    @if($existing_question_media_mime_type && str_starts_with($existing_question_media_mime_type, 'image/'))
                                        <img src="{{ asset('storage/' . $existing_question_media_path) }}"
                                            alt="Current question file"
                                            class="mt-3 max-h-64 rounded-lg border border-themed-secondary object-contain bg-white">
                                    @endif
                                </div>
                            @endif

                            @if($question_media)
                                <div class="mt-3 rounded-lg border border-themed-secondary bg-themed-tertiary p-3">
                                    <div class="text-sm font-semibold text-themed-primary">{{ $question_media->getClientOriginalName() }}</div>
                                    <div class="text-xs text-themed-secondary">New file selected</div>
                                    @if(str_starts_with((string) $question_media->getMimeType(), 'image/'))
                                        <img src="{{ $question_media->temporaryUrl() }}"
                                            alt="New question file preview"
                                            class="mt-3 max-h-64 rounded-lg border border-themed-secondary object-contain bg-white">
                                    @endif
                                </div>
                            @endif
                        </div>

                        <!-- Options (Multiple Choice) -->
                        @if($question_type === 'multiple_choice')
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-themed-primary mb-2">Options</label>
                                @foreach($options as $index => $option)
                                    <div class="flex items-center mb-2">
                                        <span
                                            class="bg-themed-tertiary px-3 py-2 rounded-l-lg border border-r-0 border-themed-secondary text-sm font-medium text-themed-primary">
                                            {{ chr(65 + $index) }}
                                        </span>
                                        <input type="text" wire:model="options.{{ $index }}"
                                            class="flex-1 px-3 py-2 border border-themed-secondary bg-themed-primary focus:ring-2 focus:ring-accent-themed-primary">
                                        <div
                                            class="bg-themed-tertiary border border-l-0 border-themed-secondary px-3 py-2 rounded-r-lg">
                                            <input type="checkbox" wire:model="correct_answers" value="{{ $index }}"
                                                class="form-checkbox h-4 w-4 text-accent-themed-primary" title="Correct Answer">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @elseif($question_type === 'true_false')
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-themed-primary mb-2">Correct Answer</label>
                                <div class="space-y-2">
                                    <div class="flex items-center">
                                        <input type="radio" wire:model="correct_answers" value="0"
                                            class="form-radio h-4 w-4 text-accent-themed-primary" id="edit_true">
                                        <label class="ml-2 text-sm text-themed-primary" for="edit_true">True</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="radio" wire:model="correct_answers" value="1"
                                            class="form-radio h-4 w-4 text-accent-themed-primary" id="edit_false">
                                        <label class="ml-2 text-sm text-themed-primary" for="edit_false">False</label>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Buttons -->
                        <div class="flex justify-end space-x-3 pt-4 border-t border-themed-secondary">
                            <button type="button" wire:click="closeModals"
                                class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-themed-primary dark:text-white rounded-lg hover:bg-gray-400 dark:hover:bg-gray-700">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                <i class="fas fa-save mr-2"></i>Update Question
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Participants Modal -->
    @if($showParticipantsModal && $selectedAssessment)
        <?php
            $participants = $this->getParticipantsData();
            $eligibleCount = $participants->where('is_eligible', true)->count();
            $ineligibleCount = $participants->where('is_eligible', false)->count();
        ?>

        <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-themed-secondary rounded-lg max-w-6xl w-full max-h-screen overflow-hidden"
                x-data="{ expandedUser: null }">
                <div class="p-6 border-b border-themed-secondary flex justify-between items-center">
                    <div>
                        <h3 class="text-xl font-bold text-themed-primary">
                            <i class="fas fa-users mr-2"></i>
                            Participants - {{ $selectedAssessment->title }}
                        </h3>
                        <p class="mt-1 text-sm text-themed-secondary">
                            {{ $selectedAssessment->course?->name ?? 'No class' }} • {{ $selectedAssessment->lesson?->name ?? 'No subject' }}
                        </p>
                    </div>
                    <button type="button" wire:click="closeModals" class="text-themed-tertiary hover:text-themed-secondary">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <div class="p-6 overflow-y-auto max-h-[70vh]">
                    @if($participants->count() > 0)
                        <div class="mb-5 rounded-xl border border-themed-secondary bg-themed-tertiary p-4">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-themed-primary">Participant eligibility</p>
                                    <p class="text-sm text-themed-secondary">
                                        @if($canLockAssessments)
                                            Everyone in this class is eligible by default. Uncheck any student you want to block from writing this CBT paper.
                                        @else
                                            Everyone in this class is eligible by default. Only super admin can mark a student ineligible for this CBT paper.
                                        @endif
                                    </p>
                                </div>
                                <div class="flex flex-wrap gap-2 text-xs font-medium">
                                    <span class="rounded-full bg-themed-secondary px-3 py-1 text-themed-primary">Total: {{ $participants->count() }}</span>
                                    <span class="rounded-full bg-green-100 px-3 py-1 text-green-800 dark:bg-green-900/30 dark:text-green-300">Eligible: {{ $eligibleCount }}</span>
                                    <span class="rounded-full bg-red-100 px-3 py-1 text-red-800 dark:bg-red-900/30 dark:text-red-300">Ineligible: {{ $ineligibleCount }}</span>
                                </div>
                            </div>
                        </div>

                        <table class="w-full border-collapse">
                            <thead class="bg-themed-tertiary sticky top-0">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-themed-secondary">Rank
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-themed-secondary">Student
                                        Name</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-themed-secondary">Email
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-medium uppercase text-themed-secondary">
                                        Attempts</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium uppercase text-themed-secondary">Best
                                        Score</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium uppercase text-themed-secondary">Status
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-medium uppercase text-themed-secondary">Eligibility
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-medium uppercase text-themed-secondary">
                                        Details</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-themed-primary">
                                @foreach($participants as $index => $participant)
                                    <tr class="hover:bg-themed-tertiary transition-colors">
                                        <td class="px-4 py-3 text-center">
                                            <span
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-full 
                                                {{ $index < 3 ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300 font-bold' : 'bg-themed-tertiary text-themed-primary' }}">
                                                {{ $index + 1 }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-semibold text-themed-primary">{{ $participant['user']->name }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-themed-secondary text-sm">
                                            {{ $participant['user']->email }}
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="px-2 py-1 bg-themed-tertiary rounded-full text-sm">
                                                {{ $participant['total_attempts'] }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            @if($participant['best_attempt'])
                                                <span
                                                    class="font-bold text-lg {{ $participant['best_attempt']['passed'] ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                                    {{ $participant['best_attempt']['percentage'] }}%
                                                </span>
                                                <div class="text-xs text-themed-secondary">
                                                    {{ $participant['best_attempt']['total_points'] }}/{{ $participant['best_attempt']['max_points'] }}
                                                    pts
                                                </div>
                                            @else
                                                <span class="text-sm text-themed-secondary">No attempt yet</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            @if($participant['best_attempt'])
                                                <span
                                                    class="px-3 py-1 rounded-full text-sm font-medium
                                                    {{ $participant['best_attempt']['passed'] ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300' : 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300' }}">
                                                    {{ $participant['best_attempt']['passed'] ? 'PASSED' : 'FAILED' }}
                                                </span>
                                            @else
                                                <span
                                                    class="px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300">
                                                    NOT STARTED
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <div class="mb-2">
                                                <span class="px-3 py-1 rounded-full text-sm font-medium {{ $participant['is_eligible'] ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300' : 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300' }}">
                                                    {{ $participant['is_eligible'] ? 'ELIGIBLE' : 'INELIGIBLE' }}
                                                </span>
                                            </div>
                                            @if($canLockAssessments)
                                                @if($participant['eligible_for_exam'] || $participant['is_locked'])
                                                    <label class="inline-flex items-center gap-2 text-sm text-themed-primary">
                                                        <input
                                                            type="checkbox"
                                                            class="h-4 w-4 rounded border-gray-300 text-green-600 focus:ring-green-500"
                                                            wire:click="toggleStudentEligibility({{ $participant['user_id'] }})"
                                                            @checked($participant['is_eligible'])
                                                            title="Checked students can write this CBT paper">
                                                        <span>{{ $participant['is_eligible'] ? 'Can write exam' : 'Blocked from exam' }}</span>
                                                    </label>
                                                @else
                                                    <span class="text-xs text-themed-secondary">Not in class</span>
                                                @endif
                                            @else
                                                <span class="text-xs text-themed-secondary">Super admin only</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            @if($participant['total_attempts'] > 0)
                                                <button
                                                    @click="expandedUser = expandedUser === {{ $participant['user_id'] }} ? null : {{ $participant['user_id'] }}"
                                                    class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 px-3 py-1 rounded hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-colors">
                                                    <i class="fas"
                                                        :class="expandedUser === {{ $participant['user_id'] }} ? 'fa-eye-slash' : 'fa-eye'"></i>
                                                    <span
                                                        x-text="expandedUser === {{ $participant['user_id'] }} ? 'Hide' : 'View All'"></span>
                                                </button>
                                            @else
                                                <span class="text-xs text-themed-secondary">No attempts</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <!-- Expandable Attempts Details -->
                                    <tr x-show="expandedUser === {{ $participant['user_id'] }}"
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 transform scale-95"
                                        x-transition:enter-end="opacity-100 transform scale-100"
                                        x-transition:leave="transition ease-in duration-150"
                                        x-transition:leave-start="opacity-100 transform scale-100"
                                        x-transition:leave-end="opacity-0 transform scale-95" class="bg-themed-tertiary">
                                        <td colspan="8" class="px-4 py-3">
                                            <div class="pl-12">
                                                <div class="flex justify-between items-center mb-3">
                                                    <h4 class="font-semibold text-themed-primary">All Attempts:</h4>
                                                    <button wire:click="clearAllUserAttempts({{ $participant['user_id'] }})"
                                                        wire:confirm="Are you sure you want to clear ALL attempts for {{ $participant['user']->name }}? This action cannot be undone."
                                                        class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm transition-colors flex items-center">
                                                        <i class="fas fa-trash-alt mr-1"></i>Clear All Attempts
                                                    </button>
                                                </div>
                                                <div class="space-y-2">
                                                    @foreach($participant['attempts'] as $attempt)
                                                        <div
                                                            class="flex items-center justify-between p-3 bg-themed-secondary rounded-lg">
                                                            <div class="flex items-center space-x-4">
                                                                <span
                                                                    class="px-2 py-1 bg-themed-tertiary rounded font-mono text-sm text-themed-primary">
                                                                    #{{ $attempt['attempt_number'] }}
                                                                </span>
                                                                <span
                                                                    class="font-semibold {{ $attempt['passed'] ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                                                    {{ $attempt['percentage'] }}%
                                                                </span>
                                                                <span class="text-themed-secondary text-sm">
                                                                    {{ $attempt['total_points'] }}/{{ $attempt['max_points'] }} points
                                                                </span>
                                                                <span class="text-themed-secondary text-sm">
                                                                    {{ $attempt['submitted_at']->format('M d, Y - H:i') }}
                                                                </span>
                                                            </div>
                                                            <div class="flex items-center gap-2">
                                                                <a href="{{ route('cbt.results.print', ['assessment' => $selectedAssessment->id, 'student' => $participant['user_id'], 'attemptNumber' => $attempt['attempt_number']]) }}"
                                                                    target="_blank"
                                                                    rel="noopener"
                                                                    class="inline-flex items-center gap-2 rounded-lg border border-blue-200 px-3 py-2 text-sm font-medium text-blue-700 transition-colors hover:bg-blue-50 dark:border-blue-800 dark:text-blue-300 dark:hover:bg-blue-900/30"
                                                                    title="Print result">
                                                                    <i class="fas fa-print"></i>
                                                                    <span>Print</span>
                                                                </a>
                                                                <button
                                                                    wire:click="clearAttempt({{ $participant['user_id'] }}, {{ $attempt['attempt_number'] }})"
                                                                    wire:confirm="Are you sure you want to clear attempt #{{ $attempt['attempt_number'] }} for {{ $participant['user']->name }}?"
                                                                    class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 p-2 rounded hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors"
                                                                    title="Clear this attempt">
                                                                    <i class="fas fa-times-circle"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="text-center py-12">
                            <i class="fas fa-users text-6xl text-themed-tertiary mb-4"></i>
                            <h5 class="text-xl text-themed-secondary mb-2">No Students Found</h5>
                            <p class="text-themed-tertiary">No students are currently assigned to this class for the active academic year.</p>
                        </div>
                    @endif
                </div>

                <div class="p-6 border-t border-themed-secondary flex justify-between">
                    <div class="text-themed-secondary text-sm">
                        Total Students: <span
                            class="font-semibold text-themed-primary">{{ ($participants ?? collect())->count() }}</span>
                    </div>
                    <button wire:click="closeModals" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                        Close
                    </button>
                </div>
            </div>
        </div>


        <script>
            function toggleAttempts(userId) {
                const el = document.getElementById('attempts-' + userId);
                el.classList.toggle('hidden');
            }
        </script>
    @endif

    <!-- Sortable.js and MathJax Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

    <script>
        // Wait for MathJax to be ready
        function waitForMathJax() {
            return new Promise((resolve) => {
                if (typeof MathJax !== 'undefined' && typeof MathJax.typesetPromise !== 'undefined') {
                    resolve();
                } else {
                    document.addEventListener('mathjax-loaded', resolve);
                }
            });
        }

        function renderMath(elements) {
            if (typeof MathJax === 'undefined' || typeof MathJax.typesetPromise === 'undefined') {
                return;
            }

            const nodes = Array.isArray(elements)
                ? elements.filter(Boolean)
                : Array.from(elements || []).filter(Boolean);

            if (!nodes.length) {
                return;
            }

            if (typeof MathJax.typesetClear === 'function') {
                MathJax.typesetClear(nodes);
            }

            MathJax.typesetPromise(nodes).catch(err => {
                if (err) console.error('MathJax error:', err);
            });
        }

        // Initialize MathJax integration
        async function initMathJax() {
            setupLivePreviews();

            await waitForMathJax();
            renderMath(document.querySelectorAll('.math-content'));
        }

        // Set up live preview for question and explanation inputs
        function setupLivePreviews() {
            let questionTimeout, explanationTimeout;

            // Update preview function
            function updatePreview(inputValue, previewId) {
                const preview = document.getElementById(previewId);
                if (!preview) return;

                const value = String(inputValue || '');

                if (value.trim()) {
                    preview.innerHTML = value;
                    preview.querySelectorAll('mjx-container').forEach(el => el.remove());
                    renderMath([preview]);
                } else {
                    preview.innerHTML = '<span class="text-themed-tertiary">Preview will appear here</span>';
                }
            }

            // Toggle option preview visibility
            window.toggleOptionPreview = function (index) {
                const previewContainer = document.getElementById('option-preview-container-' + index);
                if (previewContainer) {
                    previewContainer.classList.toggle('hidden');
                    if (!previewContainer.classList.contains('hidden')) {
                        const input = document.querySelector(`input[wire\\:model="options.${index}"]`);
                        if (input) {
                            updatePreview(input.value, 'option-preview-' + index);
                        }
                    }
                }
            };

            // Listen for Livewire updates
            Livewire.on('question-text-updated', (value) => {
                clearTimeout(questionTimeout);
                questionTimeout = setTimeout(() => {
                    updatePreview(value, 'question-preview');
                    updatePreview(value, 'edit-question-preview');
                }, 300);
            });

            Livewire.on('explanation-updated', (value) => {
                clearTimeout(explanationTimeout);
                explanationTimeout = setTimeout(() => {
                    updatePreview(value, 'explanation-preview');
                    updatePreview(value, 'edit-explanation-preview');
                }, 300);
            });

            // Handle direct input events as fallback
            document.addEventListener('input', (e) => {
                const target = e.target;

                if (target.hasAttribute('wire:model') &&
                    (target.getAttribute('wire:model') === 'question_text' ||
                        target.getAttribute('wire:model').includes('question_text'))) {
                    clearTimeout(questionTimeout);
                    questionTimeout = setTimeout(() => {
                        updatePreview(target.value, 'question-preview');
                        updatePreview(target.value, 'edit-question-preview');
                    }, 300);
                }

                if (target.hasAttribute('wire:model') &&
                    (target.getAttribute('wire:model') === 'explanation' ||
                        target.getAttribute('wire:model').includes('explanation'))) {
                    clearTimeout(explanationTimeout);
                    explanationTimeout = setTimeout(() => {
                        updatePreview(target.value, 'explanation-preview');
                        updatePreview(target.value, 'edit-explanation-preview');
                    }, 300);
                }

                if (target.hasAttribute('wire:model') &&
                    target.getAttribute('wire:model').includes('options.')) {
                    const match = target.getAttribute('wire:model').match(/options\.(\d+)/);
                    if (match) {
                        const optionIndex = match[1];
                        clearTimeout(window['optionTimeout' + optionIndex]);
                        window['optionTimeout' + optionIndex] = setTimeout(() => {
                            updatePreview(target.value, 'option-preview-' + optionIndex);
                        }, 300);
                    }
                }
            });
        }

        // Initialize Sortable for question reordering
        function initSortable() {
            const questionsList = document.getElementById('questions-sortable');
            if (questionsList && !questionsList.dataset.sortableInitialized) {
                new Sortable(questionsList, {
                    animation: 150,
                    handle: '.drag-handle',
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-chosen',
                    dragClass: 'sortable-drag',
                    onEnd: function (evt) {
                        const orderedIds = Array.from(questionsList.children)
                            .filter(item => item.classList.contains('question-item'))
                            .map(item => item.dataset.id);
                        @this.reorderQuestions(orderedIds);
                    }
                });
                questionsList.dataset.sortableInitialized = 'true';
            }
        }

        // Livewire hooks
        document.addEventListener('livewire:init', () => {
            Livewire.hook('morph.updated', ({ el, component }) => {
                // Reinitialize sortable after DOM updates
                initSortable();

                // Re-render MathJax
                if (typeof MathJax !== 'undefined' && MathJax.typesetPromise) {
                    const mathElements = el.querySelectorAll('.math-content');
                    if (mathElements.length > 0) {
                        renderMath(mathElements);
                    }
                }
            });
        });

        // Initialize on page load
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                initMathJax();
                initSortable();
            });
        } else {
            initMathJax();
            initSortable();
        }
    </script>

    <style>
        .cbt-solid-page {
            background-color: #f5f5f4;
            color: #0f172a;
        }

        .cbt-solid-page .bg-themed-primary {
            background-color: #f5f5f4 !important;
        }

        .cbt-solid-page .bg-themed-secondary {
            background-color: #ffffff !important;
        }

        .cbt-solid-page .bg-themed-tertiary {
            background-color: #fff7ed !important;
        }

        .cbt-solid-page .text-themed-primary {
            color: #0f172a !important;
        }

        .cbt-solid-page .text-themed-secondary {
            color: #475569 !important;
        }

        .cbt-solid-page .text-themed-tertiary {
            color: #78716c !important;
        }

        .cbt-solid-page .border-themed-primary,
        .cbt-solid-page .border-themed-secondary {
            border-color: #fed7aa !important;
        }

        .cbt-solid-page .bg-accent-themed-primary {
            background-color: #2563eb !important;
        }

        .cbt-solid-page .bg-accent-themed-secondary {
            background-color: #1d4ed8 !important;
        }

        .cbt-solid-page .text-accent-themed-primary {
            color: #2563eb !important;
        }

        .cbt-solid-page [class*="hover:bg-themed-secondary"]:hover {
            background-color: #fef3c7 !important;
        }

        .cbt-solid-page [class*="hover:bg-themed-tertiary"]:hover {
            background-color: #fff7ed !important;
        }

        .cbt-solid-page [class*="hover:bg-accent-themed-secondary"]:hover {
            background-color: #1d4ed8 !important;
        }

        .cbt-solid-page [class*="hover:text-themed-primary"]:hover {
            color: #0f172a !important;
        }

        .cbt-solid-page [class*="hover:text-white"]:hover {
            color: #ffffff !important;
        }

        /* Animation keyframes */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in-down {
            animation: fadeInDown 0.3s ease-out;
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.3s ease-out;
        }

        /* MathJax specific styling */
        .math-content mjx-container {
            display: inline-block !important;
            margin: 0.2em 0;
        }

        .math-content mjx-container[display="true"] {
            display: block !important;
            margin: 1em 0;
        }

        /* Option preview containers */
        .option-preview-container {
            transition: all 0.3s ease-in-out;
            max-height: 0;
            overflow: hidden;
            opacity: 0;
        }

        .option-preview-container:not(.hidden) {
            max-height: 200px;
            opacity: 1;
        }

        /* Sortable styles */
        .sortable-ghost {
            opacity: 0.4;
            background-color: rgb(var(--accent-primary) / 0.1);
        }

        .sortable-chosen {
            opacity: 0.8;
        }

        .sortable-drag {
            opacity: 0;
        }

        .drag-handle {
            cursor: move;
            transition: color 0.2s ease;
        }

        .drag-handle:hover {
            cursor: grab;
        }

        .drag-handle:active {
            cursor: grabbing;
        }

        /* Form elements */
        .form-checkbox:checked {
            background-color: rgb(var(--accent-primary));
            border-color: rgb(var(--accent-primary));
        }

        .form-radio:checked {
            background-color: rgb(var(--accent-primary));
            border-color: rgb(var(--accent-primary));
        }

        .dark .form-checkbox {
            background-color: #374151;
            border-color: #6b7280;
        }

        .dark .form-radio {
            background-color: #374151;
            border-color: #6b7280;
        }

        .dark .form-checkbox:checked {
            background-color: rgb(var(--accent-primary));
            border-color: rgb(var(--accent-primary));
        }

        .dark .form-radio:checked {
            background-color: rgb(var(--accent-primary));
            border-color: rgb(var(--accent-primary));
        }

        /* Prose styles for rich text content */
        .prose {
            max-width: none;
        }

        .prose img {
            max-width: 100%;
            height: auto;
            border-radius: 0.5rem;
            margin: 1rem 0;
        }

        .prose pre {
            background-color: #1f2937;
            color: #f3f4f6;
            padding: 1rem;
            border-radius: 0.5rem;
            overflow-x: auto;
        }

        .prose code {
            background-color: #e5e7eb;
            padding: 0.2rem 0.4rem;
            border-radius: 0.25rem;
            font-size: 0.875em;
        }

        .dark .prose code {
            background-color: #374151;
            color: #f3f4f6;
        }

        .prose blockquote {
            border-left: 4px solid rgb(var(--accent-primary));
            padding-left: 1rem;
            font-style: italic;
            color: #6b7280;
        }

        .dark .prose blockquote {
            color: #9ca3af;
        }

        .prose ul,
        .prose ol {
            padding-left: 1.5rem;
        }

        .prose li {
            margin: 0.5rem 0;
        }

        .prose a {
            color: rgb(var(--accent-primary));
            text-decoration: underline;
        }

        .prose table {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
        }

        .prose th,
        .prose td {
            border: 1px solid #e5e7eb;
            padding: 0.5rem;
        }

        .dark .prose th,
        .dark .prose td {
            border-color: #374151;
        }

        .prose th {
            background-color: #f3f4f6;
            font-weight: 600;
        }

        .dark .prose th {
            background-color: #1f2937;
        }

        /* Responsive scrollbar */
        .overflow-y-auto::-webkit-scrollbar {
            width: 6px;
        }

        .overflow-y-auto::-webkit-scrollbar-track {
            background: rgb(var(--bg-tertiary));
            border-radius: 3px;
        }

        .overflow-y-auto::-webkit-scrollbar-thumb {
            background: rgb(var(--accent-primary));
            border-radius: 3px;
        }

        .overflow-y-auto::-webkit-scrollbar-thumb:hover {
            background: rgb(var(--accent-secondary));
        }

        /* Mobile responsive text wrapping */
        @media (max-width: 640px) {
            .math-content {
                word-wrap: break-word;
                overflow-wrap: break-word;
            }
        }

        /* Smooth transitions */
        * {
            transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
        }

        /* Disable transitions for specific elements */
        .no-transition,
        .no-transition * {
            transition: none !important;
        }
    </style>
</div>
