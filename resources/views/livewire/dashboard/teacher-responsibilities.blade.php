@php
    $managedClasses = collect($teacherPanel['managed_classes'] ?? []);
    $subjectAssignments = collect($teacherPanel['subject_assignments'] ?? []);
    $responsibilityMap = collect($teacherPanel['responsibility_map'] ?? []);
    $studentSubjects = collect($studentPanel['subjects'] ?? []);
    $parentChildren = collect($parentPanel['children'] ?? []);
    $staffActions = collect($staffPanel['actions'] ?? [])->reject(fn ($action) => ($action['route'] ?? '') === 'dashboard')->values();

    $roleSummary = match (true) {
        $isTeacher => 'This page shows the classes you manage, the subjects you teach, and the tools available to your account.',
        $isStudent => 'This page shows your class, your subjects, and the academic tools available to your account.',
        $isParent => 'This page shows your linked children and the parent tools available to your account.',
        $isStaff => 'This page shows the areas and tools available to your role.',
        default => 'This page shows the information relevant to your account.',
    };

    $summaryCards = match (true) {
        $isTeacher => [
            ['label' => 'Managed Classes', 'value' => $teacherPanel['class_teacher_classes'] ?? 0, 'tone' => 'bg-emerald-500 text-white'],
            ['label' => 'Teaching Classes', 'value' => $teacherPanel['teaching_classes'] ?? 0, 'tone' => 'bg-blue-500 text-white'],
            ['label' => 'Assigned Subjects', 'value' => $teacherPanel['assigned_subjects'] ?? 0, 'tone' => 'bg-amber-500 text-slate-950'],
            ['label' => 'Subject-Class Loads', 'value' => $teacherPanel['teaching_assignments'] ?? 0, 'tone' => 'bg-violet-500 text-white'],
            ['label' => 'Managed Students', 'value' => $teacherPanel['managed_students'] ?? 0, 'tone' => 'bg-cyan-500 text-slate-950'],
        ],
        $isStudent => [
            ['label' => 'Class', 'value' => $studentPanel['class_name'] ?? 'Not assigned', 'tone' => 'bg-emerald-500 text-white'],
            ['label' => 'Section', 'value' => $studentPanel['section_name'] ?? 'Not assigned', 'tone' => 'bg-blue-500 text-white'],
            ['label' => 'Subjects', 'value' => $studentPanel['subject_count'] ?? 0, 'tone' => 'bg-amber-500 text-slate-950'],
            ['label' => 'Result Entries', 'value' => $studentPanel['result_count'] ?? 0, 'tone' => 'bg-violet-500 text-white'],
            ['label' => 'Approved Results', 'value' => $studentPanel['approved_result_count'] ?? 0, 'tone' => 'bg-cyan-500 text-slate-950'],
        ],
        $isParent => [
            ['label' => 'Linked Children', 'value' => $parentPanel['total_children'] ?? 0, 'tone' => 'bg-emerald-500 text-white'],
            ['label' => 'Actions Available', 'value' => count($pageActions), 'tone' => 'bg-blue-500 text-white'],
            ['label' => 'Academic Year', 'value' => $academicContext['academic_year'] ?? 'Not set', 'tone' => 'bg-amber-500 text-slate-950'],
            ['label' => 'Term', 'value' => $academicContext['semester'] ?? 'Not set', 'tone' => 'bg-violet-500 text-white'],
        ],
        $isStaff => [
            ['label' => 'Role', 'value' => $roleLabel ?? 'User', 'tone' => 'bg-emerald-500 text-white'],
            ['label' => 'Actions Available', 'value' => count($pageActions), 'tone' => 'bg-blue-500 text-white'],
            ['label' => 'Academic Year', 'value' => $academicContext['academic_year'] ?? 'Not set', 'tone' => 'bg-amber-500 text-slate-950'],
            ['label' => 'Term', 'value' => $academicContext['semester'] ?? 'Not set', 'tone' => 'bg-violet-500 text-white'],
        ],
        default => [
            ['label' => 'Role', 'value' => $roleLabel ?? 'User', 'tone' => 'bg-slate-900 text-white'],
        ],
    };
@endphp

<div class="space-y-8 pb-6">
    <section class="overflow-hidden rounded-[2rem] border border-slate-200 bg-slate-900 px-6 py-8 text-white shadow-2xl">
        <div class="grid gap-8 xl:grid-cols-[1.25fr,0.75fr]">
            <div class="space-y-5">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="rounded-full bg-emerald-300 px-4 py-1.5 text-xs font-bold uppercase tracking-[0.25em] text-slate-950">
                        Responsibilities
                    </span>
                    <span class="rounded-full bg-sky-100 px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.25em] text-sky-900">
                        {{ $roleLabel }}
                    </span>
                </div>

                <div>
                    <p class="text-sm uppercase tracking-[0.35em] text-white/55">Role Overview</p>
                    <h2 class="mt-3 max-w-3xl text-3xl font-black leading-tight md:text-5xl">
                        {{ $academicContext['user_name'] ?? auth()->user()->name }}
                    </h2>
                    <p class="mt-4 max-w-3xl text-base leading-7 text-white/80 md:text-lg">
                        {{ $roleSummary }}
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <div class="rounded-2xl bg-emerald-500 px-4 py-3 text-white">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-white/70">School</p>
                        <p class="mt-1 text-sm font-semibold">{{ $academicContext['school_name'] ?? config('app.name') }}</p>
                    </div>
                    <div class="rounded-2xl bg-blue-500 px-4 py-3 text-white">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-white/70">Academic Year</p>
                        <p class="mt-1 text-sm font-semibold">{{ $academicContext['academic_year'] ?? 'Not set' }}</p>
                    </div>
                    <div class="rounded-2xl bg-amber-800 px-4 py-3 text-slate-950">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-900/70">Term</p>
                        <p class="mt-1 text-sm font-semibold">{{ $academicContext['semester'] ?? 'Not set' }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-100 px-4 py-3 text-slate-900">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Today</p>
                        <p class="mt-1 text-sm font-semibold">{{ $academicContext['today'] ?? now()->format('D, M j, Y') }}</p>
                    </div>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-1">
                <div class="rounded-[1.5rem] bg-white p-5 text-slate-900 shadow-lg">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Role</p>
                    <p class="mt-3 text-3xl font-black">{{ $roleLabel }}</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">The information on this page is filtered for your account.</p>
                </div>
                <div class="rounded-[1.5rem] bg-emerald-500 p-5 text-white shadow-lg">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-emerald-100">Actions Available</p>
                    <p class="mt-3 text-3xl font-black">{{ count($pageActions) }}</p>
                    <p class="mt-2 text-sm leading-6 text-emerald-50">Only links available to your role and permissions are shown.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="rounded-[1.75rem] border border-stone-200 bg-stone-50 p-5 shadow-sm">
        <div class="flex flex-col gap-3 border-b border-slate-200 pb-5 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Summary</p>
                <h3 class="mt-2 text-2xl font-bold text-slate-900">Relevant information</h3>
            </div>
            <div class="rounded-2xl bg-white px-4 py-3 text-sm font-medium text-slate-700 shadow-sm">
                {{ count($summaryCards) }} item{{ count($summaryCards) === 1 ? '' : 's' }}
            </div>
        </div>

        <div class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-5">
            @foreach ($summaryCards as $card)
                <div class="rounded-[1.35rem] px-4 py-4 shadow-md {{ $card['tone'] }}">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.24em] opacity-70">{{ $card['label'] }}</p>
                    <p class="mt-2 text-2xl font-black">{{ $card['value'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    @if ($pageActions !== [])
        <section class="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-lg">
            <div class="flex flex-col gap-3 border-b border-slate-200 pb-5 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Quick Actions</p>
                    <h3 class="mt-2 text-2xl font-bold text-slate-900">Available actions</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600">These links show the tools available to your account.</p>
                </div>
                <div class="rounded-2xl bg-slate-100 px-4 py-3 text-sm font-medium text-slate-700">
                    {{ count($pageActions) }} action{{ count($pageActions) === 1 ? '' : 's' }}
                </div>
            </div>

            <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($pageActions as $action)
                    <a href="{{ route($action['route']) }}" class="group rounded-[1.4rem] p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-xl {{ $action['tone'] }}" wire:navigate>
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/15 shadow-sm">
                                <i class="{{ $action['icon'] }} text-lg"></i>
                            </div>
                            <i class="fas fa-arrow-right text-sm opacity-75 transition group-hover:translate-x-0.5"></i>
                        </div>
                        <h4 class="mt-5 text-lg font-semibold">{{ $action['title'] }}</h4>
                        <p class="mt-2 text-sm leading-6 opacity-85">{{ $action['description'] }}</p>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @if ($isTeacher)
        <section class="rounded-[1.75rem] border border-emerald-200 bg-emerald-50 p-5 shadow-[0_18px_60px_-30px_rgba(16,185,129,0.3)]">
            <div class="flex flex-col gap-3 border-b border-emerald-100 pb-5 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-emerald-700">Class Responsibility Summary</p>
                    <h3 class="mt-2 text-2xl font-bold text-slate-900">Classes and subjects assigned to you</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600">This section combines classes you manage and classes where you teach at least one subject.</p>
                </div>
                <div class="rounded-2xl bg-white px-4 py-3 text-sm font-medium text-slate-700 shadow-sm">
                    {{ $responsibilityMap->count() }} class{{ $responsibilityMap->count() === 1 ? '' : 'es' }}
                </div>
            </div>

            @if ($responsibilityMap->isNotEmpty())
                <div class="mt-5 grid gap-4 xl:grid-cols-2">
                    @foreach ($responsibilityMap as $class)
                        <article class="rounded-[1.5rem] border border-emerald-100 bg-white p-5 shadow-sm">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <p class="text-xl font-bold text-slate-900">{{ $class['class_name'] }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $class['class_group'] }}</p>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    @if ($class['is_class_teacher'])
                                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-emerald-800">
                                            Class Teacher
                                        </span>
                                    @endif
                                    @if ($class['teaching_subject_count'] > 0)
                                        <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-blue-800">
                                            {{ $class['teaching_subject_count'] }} subject{{ $class['teaching_subject_count'] === 1 ? '' : 's' }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-4 flex flex-wrap gap-2">
                                <span class="rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold text-white">{{ $class['student_count'] }} students</span>
                                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">{{ $class['section_count'] }} section{{ $class['section_count'] === 1 ? '' : 's' }}</span>
                                <span class="rounded-full bg-violet-100 px-3 py-1 text-xs font-semibold text-violet-800">{{ $class['class_subject_count'] }} class subject{{ $class['class_subject_count'] === 1 ? '' : 's' }}</span>
                            </div>

                            <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">Subjects assigned in this class</p>

                                @if (!empty($class['subjects']))
                                    <div class="mt-3 space-y-2 text-sm text-slate-600">
                                        @foreach ($class['subjects'] as $subject)
                                            <div class="flex flex-col gap-1 rounded-xl bg-white px-3 py-3 shadow-sm sm:flex-row sm:items-center sm:justify-between">
                                                <div>
                                                    <span class="font-semibold text-slate-900">{{ $subject['subject_name'] }}</span>
                                                    @if (!empty($subject['subject_short_name']))
                                                        <span class="text-slate-500">({{ $subject['subject_short_name'] }})</span>
                                                    @endif
                                                </div>
                                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-slate-700">
                                                    {{ $subject['assignment_scope'] }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="mt-3 text-sm text-slate-600">No subject assignment was found for this class.</p>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="mt-5 rounded-[1.5rem] border border-dashed border-emerald-200 bg-white p-6 text-sm text-slate-600">
                    No class or subject assignment was found for this account.
                </div>
            @endif
        </section>

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="rounded-[1.75rem] border border-blue-200 bg-blue-50 p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-blue-700">Managed Classes</p>
                        <h3 class="mt-2 text-2xl font-bold text-slate-900">Classes you manage</h3>
                    </div>
                    <span class="rounded-full bg-blue-600 px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-white">
                        {{ $managedClasses->count() }}
                    </span>
                </div>

                @if ($managedClasses->isNotEmpty())
                    <div class="mt-5 space-y-3">
                        @foreach ($managedClasses as $class)
                            <div class="rounded-2xl bg-white p-4 shadow-sm">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-lg font-semibold text-slate-900">{{ $class['name'] }}</p>
                                        <p class="mt-1 text-sm text-slate-500">{{ $class['class_group'] }}</p>
                                    </div>
                                    <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-blue-800">
                                        Class Teacher
                                    </span>
                                </div>
                                <div class="mt-4 flex flex-wrap gap-2">
                                    <span class="rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold text-white">{{ $class['student_count'] }} students</span>
                                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">{{ $class['section_count'] }} section{{ $class['section_count'] === 1 ? '' : 's' }}</span>
                                    <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">{{ $class['subject_count'] }} class subject{{ $class['subject_count'] === 1 ? '' : 's' }}</span>
                                    <span class="rounded-full bg-violet-100 px-3 py-1 text-xs font-semibold text-violet-800">{{ $class['teaching_subject_count'] }} of your subject{{ $class['teaching_subject_count'] === 1 ? '' : 's' }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="mt-5 rounded-2xl border border-dashed border-blue-200 bg-white p-5 text-sm text-slate-600">
                        No class teacher assignment was found for this account.
                    </div>
                @endif
            </section>

            <section class="rounded-[1.75rem] border border-amber-200 bg-amber-50 p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-amber-700">Subject Assignments</p>
                        <h3 class="mt-2 text-2xl font-bold text-slate-900">Subjects and classes taught</h3>
                    </div>
                    <span class="rounded-full bg-amber-500 px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-slate-950">
                        {{ $subjectAssignments->count() }}
                    </span>
                </div>

                @if ($subjectAssignments->isNotEmpty())
                    <div class="mt-5 space-y-3">
                        @foreach ($subjectAssignments as $assignment)
                            <div class="rounded-2xl bg-white p-4 shadow-sm">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <p class="text-lg font-semibold text-slate-900">{{ $assignment['subject_name'] }}</p>
                                        <p class="mt-1 text-sm text-slate-500">{{ $assignment['class_name'] }}{{ !empty($assignment['class_group']) ? ' • ' . $assignment['class_group'] : '' }}</p>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-amber-800">
                                            {{ $assignment['assignment_scope'] }}
                                        </span>
                                        @if ($assignment['is_managed_class'])
                                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-emerald-800">
                                                Class Teacher
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="mt-4 flex flex-wrap gap-2">
                                    <span class="rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold text-white">{{ $assignment['student_count'] }} students</span>
                                    <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800">{{ $assignment['section_count'] }} section{{ $assignment['section_count'] === 1 ? '' : 's' }}</span>
                                    @if (!empty($assignment['subject_short_name']))
                                        <span class="rounded-full bg-violet-100 px-3 py-1 text-xs font-semibold text-violet-800">{{ $assignment['subject_short_name'] }}</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="mt-5 rounded-2xl border border-dashed border-amber-200 bg-white p-5 text-sm text-slate-600">
                        No subject assignment was found for this account.
                    </div>
                @endif
            </section>
        </div>
    @endif

    @if ($isStudent && $studentPanel !== [])
        <section class="rounded-[1.75rem] border border-sky-200 bg-sky-50 p-5 shadow-sm">
            <div class="flex flex-col gap-3 border-b border-sky-100 pb-5 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-sky-700">Academic Details</p>
                    <h3 class="mt-2 text-2xl font-bold text-slate-900">Your current class information</h3>
                </div>
                <div class="rounded-2xl bg-white px-4 py-3 text-sm font-medium text-slate-700 shadow-sm">
                    Admission No: {{ $studentPanel['admission_number'] ?? 'N/A' }}
                </div>
            </div>

            <div class="mt-5 grid gap-4 xl:grid-cols-[0.8fr,1.2fr]">
                <div class="rounded-[1.5rem] border border-sky-100 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Student Record</p>
                    <div class="mt-4 space-y-3 text-sm text-slate-700">
                        <div class="rounded-xl bg-slate-50 px-4 py-3">
                            <span class="font-semibold text-slate-900">Class:</span> {{ $studentPanel['class_name'] ?? 'Not assigned' }}
                        </div>
                        <div class="rounded-xl bg-slate-50 px-4 py-3">
                            <span class="font-semibold text-slate-900">Section:</span> {{ $studentPanel['section_name'] ?? 'Not assigned' }}
                        </div>
                        <div class="rounded-xl bg-slate-50 px-4 py-3">
                            <span class="font-semibold text-slate-900">Average Score:</span> {{ $studentPanel['average_score'] ?? '0.0' }}
                        </div>
                    </div>
                </div>

                <div class="rounded-[1.5rem] border border-sky-100 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Assigned Subjects</p>
                    @if ($studentSubjects->isNotEmpty())
                        <div class="mt-4 grid gap-3 md:grid-cols-2">
                            @foreach ($studentSubjects as $subject)
                                <div class="rounded-xl bg-slate-50 px-4 py-4">
                                    <p class="font-semibold text-slate-900">{{ $subject['name'] }}</p>
                                    @if (!empty($subject['short_name']))
                                        <p class="mt-1 text-sm text-slate-500">{{ $subject['short_name'] }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="mt-4 rounded-2xl border border-dashed border-sky-200 bg-slate-50 p-5 text-sm text-slate-600">
                            No subjects were found for this account.
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @endif

    @if ($isParent && $parentPanel !== [])
        <section class="rounded-[1.75rem] border border-rose-200 bg-rose-50 p-5 shadow-sm">
            <div class="flex flex-col gap-3 border-b border-rose-100 pb-5 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-rose-700">Linked Children</p>
                    <h3 class="mt-2 text-2xl font-bold text-slate-900">Students connected to your account</h3>
                </div>
                <div class="rounded-2xl bg-white px-4 py-3 text-sm font-medium text-slate-700 shadow-sm">
                    {{ $parentPanel['total_children'] ?? 0 }} child{{ ($parentPanel['total_children'] ?? 0) === 1 ? '' : 'ren' }}
                </div>
            </div>

            @if ($parentChildren->isNotEmpty())
                <div class="mt-5 grid gap-4 xl:grid-cols-2">
                    @foreach ($parentChildren as $child)
                        <div class="rounded-[1.5rem] border border-rose-100 bg-white p-5 shadow-sm">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-lg font-semibold text-slate-900">{{ $child['name'] }}</p>
                                    <p class="mt-1 text-sm text-slate-500">Admission No: {{ $child['admission_number'] }}</p>
                                </div>
                                <span class="rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-rose-800">
                                    Child
                                </span>
                            </div>
                            <div class="mt-4 flex flex-wrap gap-2">
                                <span class="rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold text-white">{{ $child['class_name'] }}</span>
                                <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800">{{ $child['section_name'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="mt-5 rounded-2xl border border-dashed border-rose-200 bg-white p-5 text-sm text-slate-600">
                    No linked student record was found for this account.
                </div>
            @endif
        </section>
    @endif

    @if ($isStaff && !$isTeacher)
        <section class="rounded-[1.75rem] border border-lime-200 bg-lime-50 p-5 shadow-sm">
            <div class="flex flex-col gap-3 border-b border-lime-100 pb-5 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-lime-700">Role Access</p>
                    <h3 class="mt-2 text-2xl font-bold text-slate-900">Areas available to your role</h3>
                </div>
                <div class="rounded-2xl bg-white px-4 py-3 text-sm font-medium text-slate-700 shadow-sm">
                    {{ $staffActions->count() }} area{{ $staffActions->count() === 1 ? '' : 's' }}
                </div>
            </div>

            @if ($staffActions->isNotEmpty())
                <div class="mt-5 grid gap-4 xl:grid-cols-2">
                    @foreach ($staffActions as $action)
                        <div class="rounded-[1.5rem] border border-lime-100 bg-white p-5 shadow-sm">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-900 text-white shadow-sm">
                                    <i class="{{ $action['icon'] }} text-lg"></i>
                                </div>
                                <span class="rounded-full bg-lime-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-lime-800">
                                    Access
                                </span>
                            </div>
                            <h4 class="mt-5 text-lg font-semibold text-slate-900">{{ $action['title'] }}</h4>
                            <p class="mt-2 text-sm leading-6 text-slate-600">{{ $action['description'] }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="mt-5 rounded-2xl border border-dashed border-lime-200 bg-white p-5 text-sm text-slate-600">
                    No additional role access items were found for this account.
                </div>
            @endif
        </section>
    @endif
</div>
