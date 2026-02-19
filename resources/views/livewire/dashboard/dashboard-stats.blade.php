<div class="space-y-6">
    @if ($loading)
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <p class="text-gray-600 dark:text-gray-300">Loading dashboard data...</p>
        </div>
    @else
        <section class="rounded-2xl bg-slate-800 p-6 text-white shadow-xl">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-wide text-slate-300">Unified Dashboard</p>
                    <h2 class="mt-1 text-2xl font-bold md:text-3xl">
                        Welcome, {{ auth()->user()->name }}
                    </h2>
                    <p class="mt-2 text-sm text-slate-200 md:text-base">
                        Signed in as {{ $roleLabel }}. Access is filtered by your role and permissions.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:w-[480px]">
                    <div class="rounded-xl border border-white/20 bg-white/10 p-3">
                        <p class="text-xs uppercase tracking-wide text-slate-200">School</p>
                        <p class="mt-1 font-semibold">{{ $academicContext['school_name'] ?? config('app.name') }}</p>
                    </div>
                    <div class="rounded-xl border border-white/20 bg-white/10 p-3">
                        <p class="text-xs uppercase tracking-wide text-slate-200">Date</p>
                        <p class="mt-1 font-semibold">{{ $academicContext['today'] ?? now()->format('D, M j, Y') }}</p>
                    </div>
                    <div class="rounded-xl border border-white/20 bg-white/10 p-3">
                        <p class="text-xs uppercase tracking-wide text-slate-200">Academic Year</p>
                        <p class="mt-1 font-semibold">{{ $academicContext['academic_year'] ?? 'Not set' }}</p>
                    </div>
                    <div class="rounded-xl border border-white/20 bg-white/10 p-3">
                        <p class="text-xs uppercase tracking-wide text-slate-200">Term</p>
                        <p class="mt-1 font-semibold">{{ $academicContext['semester'] ?? 'Not set' }}</p>
                    </div>
                </div>
            </div>
        </section>

        @if ($quickActions !== [])
            @php
                $quickActionPalette = [
                    'bg-red-700',
                    'bg-orange-700',
                    'bg-amber-700',
                    'bg-yellow-700',
                    'bg-lime-700',
                    'bg-green-700',
                    'bg-emerald-700',
                    'bg-teal-700',
                    'bg-cyan-700',
                    'bg-sky-700',
                    'bg-blue-700',
                    'bg-indigo-700',
                    'bg-violet-700',
                    'bg-purple-700',
                    'bg-fuchsia-700',
                    'bg-pink-700',
                    'bg-rose-700',
                    'bg-slate-700',
                    'bg-gray-700',
                    'bg-zinc-700',
                    'bg-neutral-700',
                    'bg-stone-700',
                ];
            @endphp

            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Quick Actions</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-300">{{ count($quickActions) }} available</p>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($quickActions as $action)
                        @php
                            $actionTone = $quickActionPalette[$loop->index % count($quickActionPalette)];
                        @endphp

                        <a href="{{ route($action['route']) }}"
                            class="group rounded-xl {{ $actionTone }} p-4 text-white shadow-md transition hover:-translate-y-0.5 hover:brightness-110 hover:shadow-lg"
                            wire:navigate>
                            <div class="mb-2 flex items-center justify-between">
                                <i class="{{ $action['icon'] }} text-xl"></i>
                                <i class="fas fa-arrow-right text-sm opacity-70 transition group-hover:translate-x-0.5"></i>
                            </div>
                            <h4 class="text-lg font-semibold">{{ $action['title'] }}</h4>
                            <p class="mt-1 text-sm text-white/90">{{ $action['description'] }}</p>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        @php
            $overviewCards = [
                [
                    'label' => 'Active Notices',
                    'value' => $snapshot['active_notices'] ?? 0,
                    'helper' => 'Announcements currently visible',
                    'icon' => 'fas fa-bullhorn',
                ],
                [
                    'label' => 'Ongoing Exams',
                    'value' => $snapshot['ongoing_exams'] ?? 0,
                    'helper' => 'Exam windows active today',
                    'icon' => 'fas fa-hourglass-half',
                ],
                [
                    'label' => 'Upcoming Exams',
                    'value' => $snapshot['upcoming_exams'] ?? 0,
                    'helper' => 'Scheduled future exams',
                    'icon' => 'fas fa-calendar-alt',
                ],
                [
                    'label' => 'Published Exams',
                    'value' => $snapshot['published_exams'] ?? 0,
                    'helper' => 'Exams with published results',
                    'icon' => 'fas fa-check-circle',
                ],
                [
                    'label' => 'Term Results',
                    'value' => $snapshot['term_results'] ?? 0,
                    'helper' => 'Result entries this term',
                    'icon' => 'fas fa-chart-bar',
                ],
            ];

            $overviewPalette = [
                'bg-red-700',
                'bg-orange-700',
                'bg-amber-700',
                'bg-yellow-800',
                'bg-lime-800',
            ];
        @endphp

        <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
            @foreach ($overviewCards as $card)
                @php
                    $overviewTone = $overviewPalette[$loop->index % count($overviewPalette)];
                @endphp

                <div class="rounded-xl {{ $overviewTone }} p-4 text-white shadow-md">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-white/90">{{ $card['label'] }}</p>
                        <i class="{{ $card['icon'] }} text-white/90"></i>
                    </div>
                    <p class="mt-3 text-3xl font-bold">{{ $card['value'] }}</p>
                    <p class="mt-1 text-xs text-white/80">{{ $card['helper'] }}</p>
                </div>
            @endforeach
        </section>

        @if ($isStaff && $stats !== [])
            @php
                $user = auth()->user();
                $staffMetrics = [
                    [
                        'label' => 'Schools',
                        'value' => $stats['schools'] ?? 0,
                        'icon' => 'fas fa-school',
                        'route' => 'schools.index',
                        'visible' => $isSuperAdmin,
                        'permissions' => ['read school', 'create school', 'manage school settings'],
                    ],
                    [
                        'label' => 'Class Groups',
                        'value' => $stats['class_groups'] ?? 0,
                        'icon' => 'fas fa-layer-group',
                        'route' => 'class-groups.index',
                        'permissions' => ['read class group'],
                    ],
                    [
                        'label' => 'Classes',
                        'value' => $stats['classes'] ?? 0,
                        'icon' => 'fas fa-chalkboard',
                        'route' => 'classes.index',
                        'permissions' => ['read class'],
                    ],
                    [
                        'label' => 'Sections',
                        'value' => $stats['sections'] ?? 0,
                        'icon' => 'fas fa-users',
                        'route' => 'sections.index',
                        'permissions' => ['read section'],
                    ],
                    [
                        'label' => 'Subjects',
                        'value' => $stats['subjects'] ?? 0,
                        'icon' => 'fas fa-book-open',
                        'route' => 'subjects.index',
                        'permissions' => ['read subject'],
                    ],
                    [
                        'label' => 'Active Students',
                        'value' => $stats['active_students'] ?? 0,
                        'icon' => 'fas fa-user-graduate',
                        'route' => 'students.index',
                        'permissions' => ['read student'],
                    ],
                    [
                        'label' => 'Graduated Students',
                        'value' => $stats['graduated_students'] ?? 0,
                        'icon' => 'fas fa-graduation-cap',
                        'route' => 'students.graduations',
                        'permissions' => ['read student'],
                    ],
                    [
                        'label' => 'Teachers',
                        'value' => $stats['teachers'] ?? 0,
                        'icon' => 'fas fa-chalkboard-teacher',
                        'route' => 'teachers.index',
                        'permissions' => ['read teacher'],
                    ],
                    [
                        'label' => 'Parents',
                        'value' => $stats['parents'] ?? 0,
                        'icon' => 'fas fa-users',
                        'route' => 'parents.index',
                        'permissions' => ['read parent'],
                    ],
                    [
                        'label' => 'Notices',
                        'value' => $stats['total_notices'] ?? 0,
                        'icon' => 'fas fa-bullhorn',
                        'route' => 'notices.index',
                        'permissions' => ['read notice', 'create notice', 'update notice'],
                    ],
                    [
                        'label' => 'Exams',
                        'value' => $stats['total_exams'] ?? 0,
                        'icon' => 'fas fa-file-signature',
                        'route' => 'exams.index',
                        'permissions' => ['read exam', 'read exam record'],
                    ],
                ];

                $staffMetricPalette = [
                    'bg-green-700',
                    'bg-emerald-700',
                    'bg-teal-700',
                    'bg-cyan-700',
                    'bg-sky-700',
                    'bg-blue-700',
                    'bg-indigo-700',
                    'bg-violet-700',
                    'bg-purple-700',
                    'bg-fuchsia-700',
                    'bg-pink-700',
                    'bg-rose-700',
                    'bg-slate-700',
                    'bg-gray-700',
                    'bg-zinc-700',
                    'bg-neutral-700',
                    'bg-stone-700',
                    'bg-red-700',
                    'bg-orange-700',
                    'bg-amber-700',
                    'bg-yellow-800',
                    'bg-lime-800',
                ];
            @endphp

            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <h3 class="mb-4 text-xl font-semibold text-gray-800 dark:text-gray-100">School Metrics</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($staffMetrics as $metric)
                        @if (($metric['visible'] ?? true) === false)
                            @continue
                        @endif

                        @php
                            $hasMetricPermission = true;
                            if (!empty($metric['permissions']) && is_array($metric['permissions'])) {
                                $hasMetricPermission = false;
                                foreach ($metric['permissions'] as $permission) {
                                    if (is_string($permission) && $user->can($permission)) {
                                        $hasMetricPermission = true;
                                        break;
                                    }
                                }
                            }

                            if (!$hasMetricPermission) {
                                continue;
                            }

                            $metricTone = $staffMetricPalette[$loop->index % count($staffMetricPalette)];
                            $isLink = !empty($metric['route']) && \Illuminate\Support\Facades\Route::has($metric['route']);
                        @endphp

                        @if ($isLink)
                            <a href="{{ route($metric['route']) }}"
                                class="rounded-xl {{ $metricTone }} p-4 text-white shadow-md transition hover:-translate-y-0.5 hover:brightness-110 hover:shadow-lg"
                                wire:navigate>
                        @else
                            <div class="rounded-xl {{ $metricTone }} p-4 text-white shadow-md">
                        @endif
                            <div class="flex items-center justify-between">
                                <p class="text-sm text-white/90">{{ $metric['label'] }}</p>
                                <i class="{{ $metric['icon'] }} text-white/90"></i>
                            </div>
                            <p class="mt-2 text-2xl font-bold">{{ $metric['value'] }}</p>
                        @if ($isLink)
                            </a>
                        @else
                            </div>
                        @endif
                    @endforeach
                </div>
            </section>
        @endif

        @if ($isStudent && $studentPanel !== [])
            @php
                $studentCardPalette = [
                    'bg-red-700',
                    'bg-orange-700',
                    'bg-amber-700',
                    'bg-yellow-800',
                    'bg-lime-800',
                    'bg-green-700',
                    'bg-emerald-700',
                ];

                $studentCards = [
                    ['label' => 'Admission No.', 'value' => $studentPanel['admission_number'], 'icon' => 'fas fa-id-card'],
                    ['label' => 'Class', 'value' => $studentPanel['class_name'], 'icon' => 'fas fa-school'],
                    ['label' => 'Section', 'value' => $studentPanel['section_name'], 'icon' => 'fas fa-users'],
                    ['label' => 'Subjects', 'value' => $studentPanel['subject_count'], 'icon' => 'fas fa-book-open'],
                    ['label' => 'Result Entries', 'value' => $studentPanel['result_count'], 'icon' => 'fas fa-list-check'],
                    ['label' => 'Approved Results', 'value' => $studentPanel['approved_result_count'], 'icon' => 'fas fa-circle-check'],
                    ['label' => 'Average Score', 'value' => $studentPanel['average_score'], 'icon' => 'fas fa-chart-line'],
                ];
            @endphp

            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Student Overview</h3>
                    <span class="rounded-full bg-indigo-700 px-3 py-1 text-xs font-semibold text-white">
                        Student
                    </span>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    @foreach ($studentCards as $studentCard)
                        @php
                            $studentTone = $studentCardPalette[$loop->index % count($studentCardPalette)];
                        @endphp

                        <div class="rounded-xl {{ $studentTone }} p-4 text-white shadow-md">
                            <div class="flex items-center justify-between">
                                <p class="text-xs uppercase tracking-wide text-white/90">{{ $studentCard['label'] }}</p>
                                <i class="{{ $studentCard['icon'] }} text-white/90"></i>
                            </div>
                            <p class="mt-2 text-2xl font-bold">{{ $studentCard['value'] }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="mt-5 flex flex-wrap gap-3">
                    @if (\Illuminate\Support\Facades\Route::has('cbt.exams'))
                        <a href="{{ route('cbt.exams') }}"
                            class="rounded-lg bg-sky-700 px-4 py-2 text-sm font-medium text-white transition hover:brightness-110"
                            wire:navigate>
                            Take CBT Exams
                        </a>
                    @endif
                    @if (\Illuminate\Support\Facades\Route::has('cbt.viewer'))
                        <a href="{{ route('cbt.viewer') }}"
                            class="rounded-lg bg-violet-700 px-4 py-2 text-sm font-medium text-white transition hover:brightness-110"
                            wire:navigate>
                            View CBT Results
                        </a>
                    @endif
                    @if (auth()->user()->can('view result') && \Illuminate\Support\Facades\Route::has('result.history'))
                        <a href="{{ route('result.history') }}"
                            class="rounded-lg bg-rose-700 px-4 py-2 text-sm font-medium text-white transition hover:brightness-110"
                            wire:navigate>
                            Academic History
                        </a>
                    @endif
                </div>
            </section>
        @endif

        @if ($isParent && $parentPanel !== [])
            @php
                $parentChildPalette = [
                    'bg-cyan-700',
                    'bg-blue-700',
                    'bg-indigo-700',
                    'bg-purple-700',
                    'bg-fuchsia-700',
                    'bg-pink-700',
                    'bg-rose-700',
                ];
            @endphp

            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Parent Overview</h3>
                    <span class="rounded-full bg-teal-700 px-3 py-1 text-xs font-semibold text-white">
                        {{ $parentPanel['total_children'] ?? 0 }} Child(ren)
                    </span>
                </div>

                @if (($parentPanel['total_children'] ?? 0) > 0)
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($parentPanel['children'] as $child)
                            @php
                                $childTone = $parentChildPalette[$loop->index % count($parentChildPalette)];
                            @endphp

                            <div class="rounded-xl {{ $childTone }} p-4 text-white shadow-md">
                                <p class="text-base font-semibold">{{ $child['name'] }}</p>
                                <p class="mt-1 text-sm text-white/90">Admission: {{ $child['admission_number'] }}</p>
                                <p class="mt-1 text-sm text-white/90">Class: {{ $child['class_name'] }}</p>
                                <p class="text-sm text-white/90">Section: {{ $child['section_name'] }}</p>
                            </div>
                        @endforeach
                    </div>

                    @if (($parentPanel['hidden_count'] ?? 0) > 0)
                        <p class="mt-3 text-sm text-gray-500 dark:text-gray-300">
                            +{{ $parentPanel['hidden_count'] }} more child(ren) linked to your account.
                        </p>
                    @endif
                @else
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        No student records are currently linked to this parent account.
                    </p>
                @endif

                <div class="mt-5 flex flex-wrap gap-3">
                    @if (\Illuminate\Support\Facades\Route::has('profile.edit'))
                        <a href="{{ route('profile.edit') }}"
                            class="rounded-lg bg-gray-700 px-4 py-2 text-sm font-medium text-white transition hover:brightness-110"
                            wire:navigate>
                            Update Profile
                        </a>
                    @endif
                    @if (\Illuminate\Support\Facades\Route::has('password.change'))
                        <a href="{{ route('password.change') }}"
                            class="rounded-lg bg-zinc-700 px-4 py-2 text-sm font-medium text-white transition hover:brightness-110"
                            wire:navigate>
                            Change Password
                        </a>
                    @endif
                </div>
            </section>
        @endif
    @endif
</div>
