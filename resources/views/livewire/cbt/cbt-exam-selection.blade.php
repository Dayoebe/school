<div class="min-h-screen bg-stone-50 px-4 py-6">
    <section class="rounded-[2rem] bg-slate-900 px-6 py-8 text-white shadow-2xl">
        <div class="grid gap-6 lg:grid-cols-[1.25fr,0.75fr] lg:items-center">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-amber-200">CBT Portal</p>
                <h2 class="mt-3 text-3xl font-black md:text-4xl">CBT Examinations</h2>
                <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-200 md:text-base">
                    Only authorised students in the approved class and subject can begin a computer-based test.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="rounded-2xl bg-red-500 px-4 py-4 text-white shadow-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-red-100">Access</p>
                    <p class="mt-2 text-lg font-bold">Class Based</p>
                </div>
                <div class="rounded-2xl bg-orange-500 px-4 py-4 text-white shadow-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-orange-100">Mode</p>
                    <p class="mt-2 text-lg font-bold">Timed CBT</p>
                </div>
                <div class="rounded-2xl bg-amber-500 px-4 py-4 text-slate-950 shadow-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-amber-900/70">Visibility</p>
                    <p class="mt-2 text-lg font-bold">Published Papers</p>
                </div>
                <div class="rounded-2xl bg-lime-500 px-4 py-4 text-slate-950 shadow-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-lime-900/70">Progress</p>
                    <p class="mt-2 text-lg font-bold">Auto Saved</p>
                </div>
            </div>
        </div>
    </section>

    @if (session()->has('error'))
        <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-red-800 shadow-sm">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    @if (session()->has('warning'))
        <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-amber-900 shadow-sm">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <span>{{ session('warning') }}</span>
            </div>
        </div>
    @endif

    @if($availableAssessments->count() > 0)
        <div class="mt-8 grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
            @foreach($availableAssessments as $assessment)
                <div class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-lg">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold leading-tight text-slate-900">{{ $assessment->title }}</h3>
                            @if($assessment->description)
                                <p class="mt-2 text-sm text-slate-600 line-clamp-2">{{ $assessment->description }}</p>
                            @endif
                        </div>

                        @if($assessment->user_result)
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $assessment->user_result['passed'] ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                {{ $assessment->user_result['passed'] ? 'PASSED' : 'FAILED' }}
                            </span>
                        @elseif($assessment->has_submitted_attempt && !$assessment->results_visible)
                            <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">
                                RESULT PENDING
                            </span>
                        @elseif(!$assessment->can_take)
                            <span class="inline-flex items-center rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-800">
                                LOCKED
                            </span>
                        @endif
                    </div>

                    <div class="mt-5 grid grid-cols-3 gap-3">
                        <div class="rounded-2xl bg-sky-500 px-3 py-4 text-center text-white">
                            <div class="text-[11px] font-semibold uppercase tracking-[0.24em] text-sky-100">Questions</div>
                            <div class="mt-2 text-xl font-bold">{{ $assessment->questions->count() }}</div>
                        </div>
                        <div class="rounded-2xl bg-violet-500 px-3 py-4 text-center text-white">
                            <div class="text-[11px] font-semibold uppercase tracking-[0.24em] text-violet-100">Duration</div>
                            <div class="mt-2 text-xl font-bold">{{ $assessment->formatted_duration }}</div>
                        </div>
                        <div class="rounded-2xl bg-emerald-500 px-3 py-4 text-center text-white">
                            <div class="text-[11px] font-semibold uppercase tracking-[0.24em] text-emerald-100">Pass %</div>
                            <div class="mt-2 text-xl font-bold">{{ $assessment->pass_percentage }}%</div>
                        </div>
                    </div>

                    <div class="mt-5 rounded-2xl border border-orange-200 bg-orange-50 p-4 text-sm">
                        <div class="flex justify-between gap-4">
                            <span class="text-slate-600">Class</span>
                            <span class="text-right font-semibold text-slate-900">{{ $assessment->course?->name ?? $assessment->course?->title ?? 'Not assigned' }}</span>
                        </div>
                        <div class="mt-2 flex justify-between gap-4">
                            <span class="text-slate-600">Subject</span>
                            <span class="text-right font-semibold text-slate-900">{{ $assessment->lesson?->name ?? 'General CBT' }}</span>
                        </div>
                    </div>

                    <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-600">Attempts Used</span>
                            <span class="font-semibold text-slate-900">{{ $assessment->attempts_count }}</span>
                        </div>
                        <div class="mt-2 flex items-center justify-between text-sm">
                            <span class="text-slate-600">Remaining</span>
                            <span class="font-semibold {{ $assessment->remaining_attempts === 'Unlimited' ? 'text-blue-700' : ($assessment->remaining_attempts > 0 ? 'text-emerald-700' : 'text-red-700') }}">
                                {{ $assessment->remaining_attempts === 'Unlimited' ? 'Unlimited' : $assessment->remaining_attempts }}
                            </span>
                        </div>

                        @if(!$assessment->can_take)
                            <div class="mt-3 border-t border-slate-200 pt-3 text-xs text-red-700">
                                <i class="fas fa-ban mr-1"></i>
                                <span>{{ $assessment->attempt_message ?: 'This CBT is not available to you right now.' }}</span>
                            </div>
                        @elseif($assessment->has_active_attempt)
                            <div class="mt-3 border-t border-slate-200 pt-3 text-xs text-blue-700">
                                <i class="fas fa-rotate-left mr-1"></i>
                                <span>You already started this exam. Resume to continue from where you stopped.</span>
                            </div>
                        @endif
                    </div>

                    @if($assessment->user_result)
                        <div class="mt-4 rounded-2xl bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                            <div class="flex items-center justify-between">
                                <span>Best Score</span>
                                <span class="font-semibold">{{ $assessment->user_result['percentage'] }}%</span>
                            </div>
                        </div>
                    @elseif($assessment->has_submitted_attempt && !$assessment->results_visible)
                        <div class="mt-4 rounded-2xl bg-amber-50 px-4 py-3 text-xs text-amber-800">
                            <i class="fas fa-hourglass-half mr-1"></i>
                            You have completed attempt(s). Results will appear after school publishes them.
                        </div>
                    @endif

                    <div class="mt-5 space-y-2">
                        @if($assessment->can_take)
                            <button wire:click="startExam({{ $assessment->id }})"
                                wire:confirm="Are you ready to start this exam? Once started, the timer will begin immediately."
                                class="flex w-full items-center justify-center rounded-xl bg-blue-600 px-4 py-3 font-medium text-white transition-colors hover:bg-blue-700">
                                <i class="fas fa-play mr-2"></i>
                                {{ $assessment->has_active_attempt ? 'Resume Exam' : ($assessment->user_result ? 'Retake Exam' : 'Start Exam') }}
                            </button>
                        @else
                            <button disabled
                                class="flex w-full cursor-not-allowed items-center justify-center rounded-xl bg-slate-300 px-4 py-3 font-medium text-white opacity-70">
                                <i class="fas fa-lock mr-2"></i>
                                Unavailable
                            </button>
                        @endif

                        @if($assessment->user_result)
                            <button wire:click="viewResults({{ $assessment->id }})"
                                class="flex w-full items-center justify-center rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-3 font-medium text-indigo-700 transition-colors hover:bg-indigo-100">
                                <i class="fas fa-chart-line mr-2"></i>View Results
                            </button>
                        @endif
                    </div>

                    <div class="mt-4 border-t border-slate-200 pt-4 text-sm text-slate-500">
                        <i class="fas fa-user-shield mr-2 text-slate-400"></i>
                        Access is limited to students assigned to this class and subject.
                    </div>
                </div>
            @endforeach
        </div>

        @php
            $totalAssessments = $availableAssessments->count();
            $completedAssessments = $availableAssessments->filter(fn($a) => $a->user_result)->count();
            $passedAssessments = $availableAssessments->filter(fn($a) => $a->user_result && $a->user_result['passed'])->count();
            $exhaustedAttempts = $availableAssessments->filter(fn($a) => !$a->can_take)->count();
        @endphp

        @if($completedAssessments > 0)
            <div class="mt-8 rounded-[1.75rem] border border-lime-200 bg-lime-50 p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-900">Your Progress Summary</h3>
                <div class="mt-4 grid grid-cols-2 gap-4 md:grid-cols-4">
                    <div class="rounded-2xl bg-blue-500 p-4 text-center text-white">
                        <div class="text-2xl font-bold">{{ $completedAssessments }}</div>
                        <div class="mt-1 text-sm text-blue-100">Completed</div>
                    </div>
                    <div class="rounded-2xl bg-emerald-500 p-4 text-center text-white">
                        <div class="text-2xl font-bold">{{ $passedAssessments }}</div>
                        <div class="mt-1 text-sm text-emerald-100">Passed</div>
                    </div>
                    <div class="rounded-2xl bg-amber-500 p-4 text-center text-slate-950">
                        <div class="text-2xl font-bold">{{ $totalAssessments - $completedAssessments }}</div>
                        <div class="mt-1 text-sm text-amber-900/70">Remaining</div>
                    </div>
                    <div class="rounded-2xl bg-rose-500 p-4 text-center text-white">
                        <div class="text-2xl font-bold">{{ $exhaustedAttempts }}</div>
                        <div class="mt-1 text-sm text-rose-100">Exhausted</div>
                    </div>
                </div>
            </div>
        @endif
    @else
        <div class="mt-8 rounded-[1.75rem] border border-slate-200 bg-white p-12 text-center shadow-sm">
            <div class="mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-slate-100">
                <i class="fas fa-clipboard-list text-4xl text-slate-400"></i>
            </div>
            <h3 class="text-xl font-semibold text-slate-800">
                {{ $isAuthorizedStudent ? 'No CBT Assessments Available' : 'CBT Access Restricted' }}
            </h3>
            <p class="mx-auto mt-3 max-w-xl text-slate-500">
                {{ $isAuthorizedStudent
                    ? 'There are currently no computer-based tests available for your active academic session and term.'
                    : 'Only authorised students assigned to an approved class can access CBT exams.' }}
            </p>
            <a href="{{ route('dashboard') }}"
                class="mt-6 inline-flex items-center rounded-xl bg-blue-600 px-5 py-3 text-white transition-colors hover:bg-blue-700">
                <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
            </a>
        </div>
    @endif

    <div class="mt-8 rounded-[1.75rem] border border-cyan-200 bg-cyan-50 p-6 shadow-sm">
        <h3 class="mb-3 flex items-center text-lg font-semibold text-cyan-900">
            <i class="fas fa-info-circle mr-2"></i>Important Notes
        </h3>
        <ul class="space-y-2 text-sm text-cyan-900">
            <li class="flex items-start">
                <span class="mr-2">•</span>
                <span>Each assessment has a limited number of attempts. Use them wisely.</span>
            </li>
            <li class="flex items-start">
                <span class="mr-2">•</span>
                <span>Exams are conducted in fullscreen mode for security purposes.</span>
            </li>
            <li class="flex items-start">
                <span class="mr-2">•</span>
                <span>Your progress is automatically saved as you answer questions.</span>
            </li>
            <li class="flex items-start">
                <span class="mr-2">•</span>
                <span>Avoid switching tabs or minimizing the window during an exam.</span>
            </li>
            <li class="flex items-start">
                <span class="mr-2">•</span>
                <span>Contact support if you experience any technical difficulties.</span>
            </li>
        </ul>
    </div>

    <style>
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        * {
            transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
        }
    </style>

</div>
