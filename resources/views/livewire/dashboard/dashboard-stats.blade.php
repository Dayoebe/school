<div class="space-y-8 pb-4">
    @if ($loading)
        <div class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-slate-600">Loading dashboard data...</p>
        </div>
    @else
        @php
            $user = auth()->user();

            $roleTheme = match (true) {
                $isSuperAdmin => [
                    'badge' => 'bg-amber-300 text-slate-950',
                    'panel' => 'border-amber-200 bg-amber-50',
                    'soft' => 'bg-amber-100 text-amber-900',
                ],
                $isStaff => [
                    'badge' => 'bg-emerald-300 text-slate-950',
                    'panel' => 'border-emerald-200 bg-emerald-50',
                    'soft' => 'bg-emerald-100 text-emerald-900',
                ],
                $isStudent => [
                    'badge' => 'bg-sky-300 text-slate-950',
                    'panel' => 'border-sky-200 bg-sky-50',
                    'soft' => 'bg-sky-100 text-sky-900',
                ],
                $isParent => [
                    'badge' => 'bg-rose-300 text-slate-950',
                    'panel' => 'border-rose-200 bg-rose-50',
                    'soft' => 'bg-rose-100 text-rose-900',
                ],
                default => [
                    'badge' => 'bg-slate-300 text-slate-950',
                    'panel' => 'border-slate-200 bg-slate-50',
                    'soft' => 'bg-slate-100 text-slate-900',
                ],
            };

            $roleSummary = match (true) {
                $isSuperAdmin => 'You are controlling the school-wide setup, operations, and reporting from one place.',
                $isRestrictedTeacher => 'Your dashboard is limited to your assigned classes, subjects, and core teacher workflows only.',
                $isTeacher => 'Your dashboard shows the classes and subjects assigned to you, together with your teacher tools.',
                $isStaff => 'Your dashboard is focused on the classes, records, and workflows you are allowed to manage.',
                $isStudent => 'Your dashboard is focused on your current class work, results, and exam access only.',
                $isParent => 'Your dashboard keeps your linked children, results, and welfare information in one place.',
                default => 'Your dashboard shows the tools and information available to this account.',
            };

            $actionGroups = collect($quickActions)
                ->groupBy(fn ($action) => $action['group'] ?? 'Other');

            $groupMeta = [
                'Academic' => [
                    'icon' => 'fas fa-book-open',
                    'description' => 'Results, subject access, and academic records.',
                    'card' => 'bg-emerald-600 text-white',
                    'surface' => 'border-emerald-200 bg-emerald-50',
                    'chip' => 'border border-emerald-200 bg-emerald-100 text-emerald-800',
                ],
                'Assessment' => [
                    'icon' => 'fas fa-clipboard-check',
                    'description' => 'Exams, CBT, uploads, and assessment workflows.',
                    'card' => 'bg-blue-600 text-white',
                    'surface' => 'border-sky-200 bg-sky-50',
                    'chip' => 'border border-sky-200 bg-sky-100 text-sky-800',
                ],
                'People' => [
                    'icon' => 'fas fa-users',
                    'description' => 'Student, teacher, and parent administration.',
                    'card' => 'bg-orange-500 text-white',
                    'surface' => 'border-orange-200 bg-orange-50',
                    'chip' => 'border border-orange-200 bg-orange-100 text-orange-800',
                ],
                'Operations' => [
                    'icon' => 'fas fa-compass',
                    'description' => 'Settings, notices, finance, timetables, and analytics.',
                    'card' => 'bg-rose-600 text-white',
                    'surface' => 'border-rose-200 bg-rose-50',
                    'chip' => 'border border-rose-200 bg-rose-100 text-rose-800',
                ],
                'Account' => [
                    'icon' => 'fas fa-user-shield',
                    'description' => 'Your profile, password, and account security.',
                    'card' => 'bg-slate-700 text-white',
                    'surface' => 'border-slate-200 bg-slate-50',
                    'chip' => 'border border-slate-200 bg-slate-100 text-slate-700',
                ],
            ];

            $pulseCards = array_values(array_filter([
                [
                    'label' => 'Active Notices',
                    'value' => $snapshot['active_notices'] ?? 0,
                    'helper' => 'Current school announcements',
                    'icon' => 'fas fa-bullhorn',
                    'tone' => 'bg-amber-500 text-slate-950',
                ],
                [
                    'label' => 'Ongoing Exams',
                    'value' => $snapshot['ongoing_exams'] ?? 0,
                    'helper' => 'Exam windows active right now',
                    'icon' => 'fas fa-hourglass-half',
                    'tone' => 'bg-rose-500 text-white',
                ],
                [
                    'label' => 'Upcoming Exams',
                    'value' => $snapshot['upcoming_exams'] ?? 0,
                    'helper' => 'Scheduled next in the exam calendar',
                    'icon' => 'fas fa-calendar-alt',
                    'tone' => 'bg-cyan-500 text-slate-950',
                ],
                [
                    'label' => 'Published Exams',
                    'value' => $snapshot['published_exams'] ?? 0,
                    'helper' => 'Exams with visible results',
                    'icon' => 'fas fa-check-circle',
                    'tone' => 'bg-emerald-500 text-white',
                ],
                [
                    'label' => 'Term Results',
                    'value' => $snapshot['term_results'] ?? 0,
                    'helper' => 'Current-term result entries',
                    'icon' => 'fas fa-chart-line',
                    'tone' => 'bg-violet-500 text-white',
                ],
            ], fn ($card) => !($isStudent || $isParent) || $card['label'] !== 'Term Results' || ($card['value'] ?? 0) > 0));

            $staffMetrics = collect([
                [
                    'label' => 'Schools',
                    'value' => $stats['schools'] ?? 0,
                    'route' => 'schools.index',
                    'visible' => $isSuperAdmin,
                    'permissions' => ['read school', 'create school', 'manage school settings'],
                ],
                [
                    'label' => 'Students',
                    'value' => $stats['active_students'] ?? 0,
                    'route' => 'students.index',
                    'visible' => $isStaff,
                    'permissions' => ['read student'],
                ],
                [
                    'label' => 'Teachers',
                    'value' => $stats['teachers'] ?? 0,
                    'route' => 'teachers.index',
                    'visible' => $isStaff,
                    'permissions' => ['read teacher'],
                ],
                [
                    'label' => 'Parents',
                    'value' => $stats['parents'] ?? 0,
                    'route' => 'parents.index',
                    'visible' => $isStaff,
                    'permissions' => ['read parent'],
                ],
                [
                    'label' => 'Subjects',
                    'value' => $stats['subjects'] ?? 0,
                    'route' => 'subjects.index',
                    'visible' => $isStaff,
                    'permissions' => ['read subject'],
                ],
                [
                    'label' => 'Exams',
                    'value' => $stats['total_exams'] ?? 0,
                    'route' => 'exams.index',
                    'visible' => $isStaff,
                    'permissions' => ['read exam', 'read exam record'],
                ],
                [
                    'label' => 'Notices',
                    'value' => $stats['total_notices'] ?? 0,
                    'route' => 'notices.index',
                    'visible' => $isStaff,
                    'permissions' => ['read notice', 'create notice', 'update notice'],
                ],
                [
                    'label' => 'Graduated',
                    'value' => $stats['graduated_students'] ?? 0,
                    'route' => 'students.graduations',
                    'visible' => $isStaff,
                    'permissions' => ['read student'],
                ],
            ])->filter(function ($metric) use ($user) {
                if (($metric['visible'] ?? true) === false) {
                    return false;
                }

                if (!empty($metric['permissions'])) {
                    foreach ($metric['permissions'] as $permission) {
                        if (is_string($permission) && $user->can($permission)) {
                            return true;
                        }
                    }

                    return false;
                }

                return true;
            })->values();

            $studentHighlights = [
                ['label' => 'Admission No.', 'value' => $studentPanel['admission_number'] ?? 'N/A'],
                ['label' => 'Class', 'value' => $studentPanel['class_name'] ?? 'Not assigned'],
                ['label' => 'Section', 'value' => $studentPanel['section_name'] ?? 'Not assigned'],
                ['label' => 'Subjects', 'value' => $studentPanel['subject_count'] ?? 0],
                ['label' => 'Result Entries', 'value' => $studentPanel['result_count'] ?? 0],
                ['label' => 'Approved Results', 'value' => $studentPanel['approved_result_count'] ?? 0],
                ['label' => 'Average Score', 'value' => $studentPanel['average_score'] ?? '0.0'],
            ];

            $staffMetricTones = [
                'bg-violet-500 text-white',
                'bg-cyan-500 text-slate-950',
                'bg-emerald-500 text-white',
                'bg-amber-500 text-slate-950',
                'bg-fuchsia-500 text-white',
                'bg-lime-500 text-slate-950',
                'bg-blue-500 text-white',
                'bg-rose-500 text-white',
            ];

            $studentHighlightTones = [
                'bg-sky-500 text-white',
                'bg-cyan-500 text-slate-950',
                'bg-indigo-500 text-white',
                'bg-violet-500 text-white',
                'bg-purple-500 text-white',
                'bg-emerald-500 text-white',
                'bg-amber-500 text-slate-950',
            ];

            $parentChildTones = [
                'bg-rose-500 text-white',
                'bg-pink-500 text-white',
                'bg-orange-500 text-white',
                'bg-sky-500 text-white',
                'bg-emerald-500 text-white',
                'bg-teal-500 text-white',
            ];

            $teacherManagedClasses = collect($teacherPanel['managed_classes'] ?? []);
            $teacherSubjectAssignments = collect($teacherPanel['subject_assignments'] ?? []);
            $teacherFocusItems = $teacherPanel['focus_items'] ?? [];
            $teacherHighlights = [
                ['label' => 'Managed Classes', 'value' => $teacherPanel['class_teacher_classes'] ?? 0],
                ['label' => 'Teaching Classes', 'value' => $teacherPanel['teaching_classes'] ?? 0],
                ['label' => 'Assigned Subjects', 'value' => $teacherPanel['assigned_subjects'] ?? 0],
                ['label' => 'Subject-Class Loads', 'value' => $teacherPanel['teaching_assignments'] ?? 0],
                ['label' => 'Managed Students', 'value' => $teacherPanel['managed_students'] ?? 0],
                ['label' => 'Teacher Tools', 'value' => $teacherPanel['teacher_tools'] ?? 0],
            ];

            $teacherHighlightTones = [
                'bg-emerald-500 text-white',
                'bg-blue-500 text-white',
                'bg-amber-500 text-slate-950',
                'bg-violet-500 text-white',
                'bg-cyan-500 text-slate-950',
                'bg-slate-800 text-white',
            ];
        @endphp

        <section class="overflow-hidden rounded-[2rem] border border-slate-200 bg-slate-900 px-6 py-8 text-white shadow-2xl">
            <div class="grid gap-8 xl:grid-cols-[1.35fr,0.95fr]">
                <div class="space-y-5">
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="rounded-full px-4 py-1.5 text-xs font-bold uppercase tracking-[0.25em] {{ $roleTheme['badge'] }}">
                            {{ $roleLabel }} Portal
                        </span>
                        <span class="rounded-full px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.25em] {{ $roleTheme['soft'] }}">
                            Main Dashboard
                        </span>
                    </div>

                    <div>
                        <p class="text-sm uppercase tracking-[0.35em] text-white/55">Welcome Back</p>
                        <h2 class="mt-3 max-w-3xl text-3xl font-black leading-tight md:text-5xl">
                            {{ $academicContext['school_name'] ?? config('app.name') }}
                        </h2>
                        <p class="mt-4 max-w-2xl text-base leading-7 text-white/80 md:text-lg">
                            {{ $roleSummary }}
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <div class="rounded-2xl bg-red-500 px-4 py-3 text-white">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.25em] text-white/70">User</p>
                            <p class="mt-1 text-sm font-semibold">{{ $user->name }}</p>
                        </div>
                        <div class="rounded-2xl bg-orange-500 px-4 py-3 text-white">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.25em] text-white/70">Academic Year</p>
                            <p class="mt-1 text-sm font-semibold">{{ $academicContext['academic_year'] ?? 'Not set' }}</p>
                        </div>
                        <div class="rounded-2xl bg-amber-500 px-4 py-3 text-slate-950">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.25em] text-slate-800/70">Term</p>
                            <p class="mt-1 text-sm font-semibold">{{ $academicContext['semester'] ?? 'Not set' }}</p>
                        </div>
                        <div class="rounded-2xl bg-yellow-400 px-4 py-3 text-slate-950">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.25em] text-slate-800/70">Today</p>
                            <p class="mt-1 text-sm font-semibold">{{ $academicContext['today'] ?? now()->format('D, M j, Y') }}</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @foreach ($pulseCards as $pulseCard)
                        <div class="rounded-[1.5rem] p-4 shadow-lg {{ $pulseCard['tone'] }}">
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-semibold uppercase tracking-[0.24em] opacity-75">{{ $pulseCard['label'] }}</p>
                                <i class="{{ $pulseCard['icon'] }} opacity-80"></i>
                            </div>
                            <p class="mt-4 text-3xl font-black">{{ $pulseCard['value'] }}</p>
                            <p class="mt-2 text-sm leading-6 opacity-80">{{ $pulseCard['helper'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        @if ($isTeacher && $teacherPanel !== [])
            <section class="rounded-[1.9rem] border border-emerald-200 bg-white p-6 shadow-[0_24px_80px_-32px_rgba(16,185,129,0.34)]">
                <div class="flex flex-col gap-4 border-b border-emerald-100 pb-5 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-emerald-700">Teacher Overview</p>
                        <h3 class="mt-2 text-3xl font-black text-slate-900">Your assigned classes and subjects</h3>
                        <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-600">
                            View the classes you manage, the subjects you teach, and the tools available to your account.
                        </p>
                    </div>
                    <div class="rounded-2xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900">
                        {{ $teacherManagedClasses->count() }} managed class{{ $teacherManagedClasses->count() === 1 ? '' : 'es' }} •
                        {{ $teacherSubjectAssignments->count() }} teaching assignment{{ $teacherSubjectAssignments->count() === 1 ? '' : 's' }}
                    </div>
                </div>

                <div class="mt-6 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($teacherHighlights as $highlight)
                        @php($teacherTone = $teacherHighlightTones[$loop->index % count($teacherHighlightTones)])
                        <div class="rounded-[1.4rem] px-4 py-4 shadow-md {{ $teacherTone }}">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.24em] opacity-70">{{ $highlight['label'] }}</p>
                            <p class="mt-2 text-2xl font-black">{{ $highlight['value'] }}</p>
                        </div>
                    @endforeach
                </div>

                @if ($teacherFocusItems !== [])
                    <div class="mt-6 rounded-[1.6rem] border border-slate-200 bg-slate-50 p-5">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Quick Actions</p>
                                <h4 class="mt-2 text-xl font-bold text-slate-900">Available teacher actions</h4>
                            </div>
                            <p class="text-sm text-slate-500">Only actions available to your account are shown.</p>
                        </div>

                        <div class="mt-4 grid gap-4 lg:grid-cols-2 xl:grid-cols-3">
                            @foreach ($teacherFocusItems as $item)
                                <a
                                    href="{{ route($item['route']) }}"
                                    class="group rounded-[1.4rem] p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-xl {{ $item['tone'] }}"
                                    wire:navigate
                                >
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/15 shadow-sm">
                                            <i class="{{ $item['icon'] }} text-lg"></i>
                                        </div>
                                        <span class="text-xs font-semibold uppercase tracking-[0.22em] opacity-75">{{ $item['cta'] }}</span>
                                    </div>
                                    <h5 class="mt-5 text-lg font-semibold">{{ $item['title'] }}</h5>
                                    <p class="mt-2 text-sm leading-6 opacity-85">{{ $item['description'] }}</p>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="mt-6 grid gap-5 xl:grid-cols-2">
                    <div class="rounded-[1.6rem] border border-emerald-100 bg-emerald-50 p-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-emerald-700">Managed Classes</p>
                                <h4 class="mt-2 text-xl font-bold text-slate-900">Classes you manage</h4>
                            </div>
                            <span class="rounded-full bg-emerald-600 px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-white">
                                {{ $teacherManagedClasses->count() }}
                            </span>
                        </div>

                        @if ($teacherManagedClasses->isNotEmpty())
                            <div class="mt-4 space-y-3">
                                @foreach ($teacherManagedClasses as $class)
                                    <div class="rounded-2xl bg-white p-4 shadow-sm">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <p class="text-lg font-semibold text-slate-900">{{ $class['name'] }}</p>
                                                <p class="mt-1 text-sm text-slate-500">{{ $class['class_group'] }}</p>
                                            </div>
                                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-emerald-800">
                                                Class Teacher
                                            </span>
                                        </div>
                                        <div class="mt-4 flex flex-wrap gap-2">
                                            <span class="rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold text-white">
                                                {{ $class['student_count'] }} students
                                            </span>
                                            <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800">
                                                {{ $class['section_count'] }} section{{ $class['section_count'] === 1 ? '' : 's' }}
                                            </span>
                                            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">
                                                {{ $class['subject_count'] }} class subject{{ $class['subject_count'] === 1 ? '' : 's' }}
                                            </span>
                                            <span class="rounded-full bg-violet-100 px-3 py-1 text-xs font-semibold text-violet-800">
                                                {{ $class['teaching_subject_count'] }} of your subject{{ $class['teaching_subject_count'] === 1 ? '' : 's' }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="mt-4 rounded-2xl border border-dashed border-emerald-200 bg-white p-5 text-sm text-slate-600">
                                No class teacher assignment was found for this account.
                            </div>
                        @endif
                    </div>

                    <div class="rounded-[1.6rem] border border-blue-100 bg-blue-50 p-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-blue-700">Teaching Load</p>
                                <h4 class="mt-2 text-xl font-bold text-slate-900">Subjects and classes you teach</h4>
                            </div>
                            <span class="rounded-full bg-blue-600 px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-white">
                                {{ $teacherSubjectAssignments->count() }}
                            </span>
                        </div>

                        @if ($teacherSubjectAssignments->isNotEmpty())
                            <div class="mt-4 space-y-3">
                                @foreach ($teacherSubjectAssignments as $assignment)
                                    <div class="rounded-2xl bg-white p-4 shadow-sm">
                                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                            <div>
                                                <p class="text-lg font-semibold text-slate-900">{{ $assignment['subject_name'] }}</p>
                                                <p class="mt-1 text-sm text-slate-500">
                                                    {{ $assignment['class_name'] }}{{ !empty($assignment['class_group']) ? ' • ' . $assignment['class_group'] : '' }}
                                                </p>
                                            </div>
                                            <div class="flex flex-wrap gap-2">
                                                <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-blue-800">
                                                    {{ $assignment['assignment_scope'] }}
                                                </span>
                                                @if ($assignment['is_managed_class'])
                                                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-emerald-800">
                                                        Class teacher
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="mt-4 flex flex-wrap gap-2">
                                            <span class="rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold text-white">
                                                {{ $assignment['student_count'] }} students in class
                                            </span>
                                            @if (!empty($assignment['subject_short_name']))
                                                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">
                                                    {{ $assignment['subject_short_name'] }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="mt-4 rounded-2xl border border-dashed border-blue-200 bg-white p-5 text-sm text-slate-600">
                                No subject assignment was found for this account.
                            </div>
                        @endif
                    </div>
                </div>
            </section>
        @endif

        <div class="grid gap-6 xl:grid-cols-[1.15fr,0.85fr]">
            <section class="rounded-[1.75rem] border border-stone-200 bg-stone-50 p-6 shadow-[0_24px_80px_-32px_rgba(15,23,42,0.28)]">
                <div class="flex flex-col gap-3 border-b border-slate-200/80 pb-5 md:flex-row md:items-end md:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Navigation Hub</p>
                        <h3 class="mt-2 text-2xl font-bold text-slate-900">Role-based actions</h3>
                        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
                            Only the tools that match this account's role and permissions are shown here.
                        </p>
                    </div>
                    <div class="rounded-2xl {{ $roleTheme['panel'] }} px-4 py-3 text-sm font-medium text-slate-700">
                        {{ count($quickActions) }} action{{ count($quickActions) === 1 ? '' : 's' }} available
                    </div>
                </div>

                @if ($quickActions !== [])
                    <div class="mt-6 space-y-8">
                        @foreach ($groupMeta as $groupKey => $group)
                            @php($actions = $actionGroups->get($groupKey, collect()))
                            @if ($actions->isEmpty())
                                @continue
                            @endif

                            <div class="rounded-[1.6rem] border p-5 shadow-sm {{ $group['surface'] }}">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                                    <div>
                                        <div class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em] {{ $group['chip'] }}">
                                            <i class="{{ $group['icon'] }}"></i>
                                            <span>{{ $groupKey }}</span>
                                        </div>
                                        <p class="mt-3 text-sm leading-6 text-slate-600">{{ $group['description'] }}</p>
                                    </div>
                                    <span class="text-sm font-medium text-slate-500">{{ $actions->count() }} available</span>
                                </div>

                                <div class="mt-4 grid gap-4 md:grid-cols-2">
                                    @foreach ($actions as $action)
                                        <a
                                            href="{{ route($action['route']) }}"
                                            class="group rounded-[1.4rem] p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-xl {{ $group['card'] }}"
                                            wire:navigate
                                        >
                                            <div class="flex items-start justify-between gap-4">
                                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/15 text-white shadow-sm">
                                                    <i class="{{ $action['icon'] }} text-lg"></i>
                                                </div>
                                                <i class="fas fa-arrow-right text-sm text-white/70 transition group-hover:translate-x-0.5 group-hover:text-white"></i>
                                            </div>
                                            <h4 class="mt-5 text-lg font-semibold">{{ $action['title'] }}</h4>
                                            <p class="mt-2 text-sm leading-6 text-white/85">{{ $action['description'] }}</p>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="mt-6 rounded-[1.4rem] border border-dashed border-slate-200 bg-slate-50 p-6 text-sm text-slate-600">
                        No dashboard actions are available for this account yet.
                    </div>
                @endif
            </section>

            <div class="space-y-6">
                @if ($isStaff && !$isRestrictedTeacher && $staffMetrics->isNotEmpty())
                    <section class="rounded-[1.75rem] border border-lime-200 bg-lime-50 p-6 shadow-[0_18px_60px_-30px_rgba(101,163,13,0.35)]">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">School Snapshot</p>
                                <h3 class="mt-2 text-2xl font-bold text-slate-900">Operational totals</h3>
                            </div>
                            <span class="rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-white">
                                Staff
                            </span>
                        </div>

                        <div class="mt-5 space-y-3">
                            @foreach ($staffMetrics as $metric)
                                @php($metricTone = $staffMetricTones[$loop->index % count($staffMetricTones)])
                                <a
                                    href="{{ route($metric['route']) }}"
                                    class="flex items-center justify-between rounded-2xl px-4 py-4 shadow-md transition hover:-translate-y-0.5 hover:shadow-lg {{ $metricTone }}"
                                    wire:navigate
                                >
                                    <div>
                                        <p class="text-sm font-semibold">{{ $metric['label'] }}</p>
                                        <p class="mt-1 text-xs uppercase tracking-[0.22em] opacity-75">Current count</p>
                                    </div>
                                    <span class="text-2xl font-black">{{ $metric['value'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($isStudent && $studentPanel !== [])
                    <section class="rounded-[1.75rem] border border-sky-200 bg-sky-50 p-6 shadow-[0_18px_60px_-30px_rgba(14,165,233,0.35)]">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Student Overview</p>
                                <h3 class="mt-2 text-2xl font-bold text-slate-900">Your current standing</h3>
                            </div>
                            <span class="rounded-full bg-sky-600 px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-white">
                                Student
                            </span>
                        </div>

                        <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
                            @foreach ($studentHighlights as $highlight)
                                @php($studentTone = $studentHighlightTones[$loop->index % count($studentHighlightTones)])
                                <div class="rounded-2xl px-4 py-4 shadow-md {{ $studentTone }}">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.24em] opacity-70">{{ $highlight['label'] }}</p>
                                    <p class="mt-2 text-xl font-bold">{{ $highlight['value'] }}</p>
                                </div>
                            @endforeach
                        </div>

                        @if ($user->can('view result'))
                            <div class="mt-5 rounded-2xl border border-sky-200 bg-white p-4">
                                <div class="mb-3">
                                    <h4 class="text-sm font-semibold text-slate-900">Result Period</h4>
                                    <p class="mt-1 text-sm text-slate-600">Pick the academic year and term before opening your result.</p>
                                </div>

                                <livewire:result.academic-period-selector />
                            </div>
                        @endif
                    </section>
                @endif

                @if ($isParent && $parentPanel !== [])
                    <section class="rounded-[1.75rem] border border-rose-200 bg-rose-50 p-6 shadow-[0_18px_60px_-30px_rgba(244,63,94,0.3)]">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Parent Overview</p>
                                <h3 class="mt-2 text-2xl font-bold text-slate-900">Linked children</h3>
                            </div>
                            <span class="rounded-full bg-rose-600 px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-white">
                                {{ $parentPanel['total_children'] ?? 0 }} Child(ren)
                            </span>
                        </div>

                        @if (($parentPanel['total_children'] ?? 0) > 0)
                            <div class="mt-5 space-y-3">
                                @foreach ($parentPanel['children'] as $child)
                                    @php($childTone = $parentChildTones[$loop->index % count($parentChildTones)])
                                    <div class="rounded-2xl px-4 py-4 shadow-md {{ $childTone }}">
                                        <div class="flex items-start justify-between gap-4">
                                            <div>
                                                <p class="text-base font-semibold">{{ $child['name'] }}</p>
                                                <p class="mt-1 text-sm opacity-80">Admission: {{ $child['admission_number'] }}</p>
                                            </div>
                                            <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em]">
                                                {{ $child['class_name'] }}
                                            </span>
                                        </div>
                                        <p class="mt-2 text-sm opacity-80">Section: {{ $child['section_name'] }}</p>
                                    </div>
                                @endforeach
                            </div>

                            @if (($parentPanel['hidden_count'] ?? 0) > 0)
                                <p class="mt-3 text-sm text-slate-500">
                                    +{{ $parentPanel['hidden_count'] }} more child(ren) linked to this account.
                                </p>
                            @endif
                        @else
                            <div class="mt-5 rounded-2xl border border-dashed border-rose-200 bg-white p-5 text-sm text-slate-600">
                                No student records are currently linked to this parent account.
                            </div>
                        @endif

                        @if ($user->can('view result'))
                            <div class="mt-5 rounded-2xl border border-rose-200 bg-white p-4">
                                <div class="mb-3">
                                    <h4 class="text-sm font-semibold text-slate-900">Result Period</h4>
                                    <p class="mt-1 text-sm text-slate-600">Choose the academic year and term before opening a child result.</p>
                                </div>

                                <livewire:result.academic-period-selector />
                            </div>
                        @endif
                    </section>
                @endif

                <section class="rounded-[1.75rem] border border-zinc-200 bg-zinc-50 p-6 shadow-[0_18px_60px_-30px_rgba(63,63,70,0.28)]">
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Account</p>
                    <h3 class="mt-2 text-2xl font-bold text-slate-900">Current context</h3>
                    <div class="mt-5 grid grid-cols-1 gap-3">
                        <div class="rounded-2xl bg-purple-500 px-4 py-4 text-white shadow-md">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-white/70">School</p>
                            <p class="mt-2 text-base font-semibold">{{ $academicContext['school_name'] ?? config('app.name') }}</p>
                        </div>
                        <div class="rounded-2xl bg-indigo-500 px-4 py-4 text-white shadow-md">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-white/70">Active Academic Year</p>
                            <p class="mt-2 text-base font-semibold">{{ $academicContext['academic_year'] ?? 'Not set' }}</p>
                        </div>
                        <div class="rounded-2xl bg-teal-500 px-4 py-4 text-white shadow-md">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-white/70">Active Term</p>
                            <p class="mt-2 text-base font-semibold">{{ $academicContext['semester'] ?? 'Not set' }}</p>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    @endif
</div>
