@php
    $formatMoney = static fn ($value): string => 'NGN ' . number_format(((int) $value) / 100, 2);
    $formatPercent = static fn ($value): string => number_format((float) $value, 1) . '%';

    $summaryCards = [
        [
            'label' => 'Active Students',
            'value' => number_format($people['active_students']),
            'helper' => number_format($people['new_students']) . ' new in this window',
            'tone' => 'border-emerald-200 bg-emerald-50 text-emerald-900',
            'icon' => 'fas fa-user-graduate',
        ],
        [
            'label' => 'Fee Collection',
            'value' => $formatPercent($finance['collection_rate']),
            'helper' => $formatMoney($finance['period_collected']) . ' collected',
            'tone' => 'border-amber-200 bg-amber-50 text-amber-900',
            'icon' => 'fas fa-coins',
        ],
        [
            'label' => 'Result Coverage',
            'value' => $academics['has_context'] ? $formatPercent($academics['coverage_rate']) : 'Not set',
            'helper' => number_format($academics['students_with_results']) . ' students with results',
            'tone' => 'border-sky-200 bg-sky-50 text-sky-900',
            'icon' => 'fas fa-chart-line',
        ],
        [
            'label' => 'Attendance Rate',
            'value' => $formatPercent($attendance['attendance_rate']),
            'helper' => number_format($attendance['records']) . ' marked records',
            'tone' => 'border-rose-200 bg-rose-50 text-rose-900',
            'icon' => 'fas fa-user-check',
        ],
    ];

    $signalTones = [
        'emerald' => 'border-emerald-200 bg-emerald-50 text-emerald-900',
        'sky' => 'border-sky-200 bg-sky-50 text-sky-900',
        'amber' => 'border-amber-200 bg-amber-50 text-amber-900',
        'rose' => 'border-rose-200 bg-rose-50 text-rose-900',
    ];

    $classDistributionMax = max(1, collect($studentClassDistribution)->max('total') ?? 1);
@endphp

@push('head')
    <style>
        @media print {
            .no-print { display: none !important; }
            .executive-report { color: #111827; }
            .print-panel { break-inside: avoid; page-break-inside: avoid; box-shadow: none !important; }
            main { padding: 0 !important; }
        }
    </style>
@endpush

<div class="executive-report space-y-6">
    <div class="print-panel rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-sm font-semibold text-slate-500">Executive Report</p>
                <h2 class="mt-1 text-2xl font-bold text-slate-950">{{ $school?->name ?? config('app.name') }}</h2>
                <p class="mt-2 text-sm text-slate-600">
                    {{ $start->format('M j, Y') }} to {{ $end->format('M j, Y') }}
                    <span class="mx-2 text-slate-300">|</span>
                    {{ $school?->academicYear?->name ?? 'Academic year not set' }}
                    <span class="mx-2 text-slate-300">|</span>
                    {{ $school?->semester?->name ?? 'Semester not set' }}
                </p>
                <p class="mt-1 text-xs text-slate-500">Generated {{ $generatedAt->format('M j, Y g:i A') }}</p>
            </div>

            <div class="no-print flex flex-col gap-3 sm:flex-row sm:items-end">
                <label class="block text-sm font-semibold text-slate-700">
                    Report Window
                    <select wire:model.live="months" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                        <option value="3">Last 3 months</option>
                        <option value="6">Last 6 months</option>
                        <option value="12">Last 12 months</option>
                    </select>
                </label>
                <button
                    type="button"
                    onclick="window.print()"
                    class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700"
                >
                    <i class="fas fa-print mr-2"></i>
                    Print Report
                </button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        @foreach ($summaryCards as $card)
            <div class="print-panel rounded-lg border p-4 shadow-sm {{ $card['tone'] }}">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase">{{ $card['label'] }}</p>
                        <p class="mt-2 text-2xl font-black">{{ $card['value'] }}</p>
                        <p class="mt-1 text-xs opacity-80">{{ $card['helper'] }}</p>
                    </div>
                    <i class="{{ $card['icon'] }} text-xl opacity-70"></i>
                </div>
            </div>
        @endforeach
    </div>

    <div class="print-panel rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h3 class="text-lg font-bold text-slate-950">Leadership Signals</h3>
                <p class="mt-1 text-sm text-slate-600">Items that need attention based on current records.</p>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($signals as $signal)
                <div class="rounded-lg border p-4 {{ $signalTones[$signal['tone']] ?? $signalTones['sky'] }}">
                    <p class="text-sm font-bold">{{ $signal['title'] }}</p>
                    <p class="mt-1 text-sm opacity-90">{{ $signal['body'] }}</p>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <div class="print-panel rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="text-lg font-bold text-slate-950">People And Enrollment</h3>

            <div class="mt-4 grid grid-cols-2 gap-3 md:grid-cols-4">
                <div class="rounded-lg border border-slate-200 p-3">
                    <p class="text-xs font-semibold text-slate-500">Total Students</p>
                    <p class="mt-1 text-xl font-bold text-slate-950">{{ number_format($people['total_students']) }}</p>
                </div>
                <div class="rounded-lg border border-slate-200 p-3">
                    <p class="text-xs font-semibold text-slate-500">Teachers</p>
                    <p class="mt-1 text-xl font-bold text-slate-950">{{ number_format($people['teachers']) }}</p>
                </div>
                <div class="rounded-lg border border-slate-200 p-3">
                    <p class="text-xs font-semibold text-slate-500">Parents</p>
                    <p class="mt-1 text-xl font-bold text-slate-950">{{ number_format($people['parents']) }}</p>
                </div>
                <div class="rounded-lg border border-slate-200 p-3">
                    <p class="text-xs font-semibold text-slate-500">Subjects</p>
                    <p class="mt-1 text-xl font-bold text-slate-950">{{ number_format($people['subjects']) }}</p>
                </div>
            </div>

            <div class="mt-5">
                <div class="mb-2 flex items-center justify-between text-sm font-semibold text-slate-700">
                    <span>Active Students By Class</span>
                    <span>{{ number_format($people['classes']) }} instructional classes</span>
                </div>
                <div class="space-y-3">
                    @forelse ($studentClassDistribution as $class)
                        @php($width = (int) round(($class['total'] / $classDistributionMax) * 100))
                        <div>
                            <div class="mb-1 flex items-center justify-between text-xs text-slate-600">
                                <span class="font-semibold">{{ $class['name'] }}</span>
                                <span>{{ number_format($class['total']) }}</span>
                            </div>
                            <div class="h-2.5 rounded-lg bg-slate-100">
                                <div class="h-2.5 rounded-lg bg-emerald-500" style="width: {{ $width }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">No active class distribution is available.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="print-panel rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="text-lg font-bold text-slate-950">Admissions And Fees</h3>

            <div class="mt-4 grid grid-cols-2 gap-3 md:grid-cols-4">
                <div class="rounded-lg border border-sky-200 bg-sky-50 p-3">
                    <p class="text-xs font-semibold text-sky-700">Applications</p>
                    <p class="mt-1 text-xl font-bold text-sky-950">{{ number_format($admissions['total']) }}</p>
                </div>
                <div class="rounded-lg border border-sky-200 bg-white p-3">
                    <p class="text-xs font-semibold text-sky-700">This Window</p>
                    <p class="mt-1 text-xl font-bold text-sky-950">{{ number_format($admissions['period_total']) }}</p>
                </div>
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-3">
                    <p class="text-xs font-semibold text-emerald-700">Enrolled</p>
                    <p class="mt-1 text-xl font-bold text-emerald-950">{{ number_format($admissions['enrolled']) }}</p>
                </div>
                <div class="rounded-lg border border-amber-200 bg-amber-50 p-3">
                    <p class="text-xs font-semibold text-amber-700">Pending</p>
                    <p class="mt-1 text-xl font-bold text-amber-950">{{ number_format($admissions['pending']) }}</p>
                </div>
            </div>

            <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="rounded-lg border border-slate-200 p-4">
                    <p class="text-sm font-bold text-slate-950">Fee Position</p>
                    <dl class="mt-3 space-y-2 text-sm">
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-600">Billable</dt>
                            <dd class="font-semibold text-slate-950">{{ $formatMoney($finance['billable']) }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-600">Paid</dt>
                            <dd class="font-semibold text-emerald-700">{{ $formatMoney($finance['paid']) }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-600">Outstanding</dt>
                            <dd class="font-semibold text-rose-700">{{ $formatMoney($finance['outstanding']) }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-600">Due Items</dt>
                            <dd class="font-semibold text-slate-950">{{ number_format($finance['due_items']) }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="rounded-lg border border-slate-200 p-4">
                    <p class="text-sm font-bold text-slate-950">Admission Flow</p>
                    <dl class="mt-3 space-y-2 text-sm">
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-600">Reviewed</dt>
                            <dd class="font-semibold text-slate-950">{{ number_format($admissions['reviewed']) }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-600">Approved</dt>
                            <dd class="font-semibold text-emerald-700">{{ number_format($admissions['approved']) }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-600">Rejected</dt>
                            <dd class="font-semibold text-rose-700">{{ number_format($admissions['rejected']) }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-600">Conversion</dt>
                            <dd class="font-semibold text-slate-950">{{ $formatPercent($admissions['conversion_rate']) }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <div class="print-panel rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="text-lg font-bold text-slate-950">Academic Progress</h3>

            <div class="mt-4 grid grid-cols-2 gap-3 md:grid-cols-4">
                <div class="rounded-lg border border-slate-200 p-3">
                    <p class="text-xs font-semibold text-slate-500">Entries</p>
                    <p class="mt-1 text-xl font-bold text-slate-950">{{ number_format($academics['result_entries']) }}</p>
                </div>
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-3">
                    <p class="text-xs font-semibold text-emerald-700">Approved</p>
                    <p class="mt-1 text-xl font-bold text-emerald-950">{{ number_format($academics['approved_entries']) }}</p>
                </div>
                <div class="rounded-lg border border-amber-200 bg-amber-50 p-3">
                    <p class="text-xs font-semibold text-amber-700">Pending</p>
                    <p class="mt-1 text-xl font-bold text-amber-950">{{ number_format($academics['pending_entries']) }}</p>
                </div>
                <div class="rounded-lg border border-sky-200 bg-sky-50 p-3">
                    <p class="text-xs font-semibold text-sky-700">Avg Score</p>
                    <p class="mt-1 text-xl font-bold text-sky-950">{{ number_format($academics['average_score'], 1) }}</p>
                </div>
            </div>

            <div class="mt-5 space-y-3">
                <div class="flex items-center justify-between text-sm font-semibold text-slate-700">
                    <span>Class Performance</span>
                    <span>Approval {{ $formatPercent($academics['approval_rate']) }}</span>
                </div>
                @forelse ($academics['class_performance'] as $class)
                    @php($width = min(100, max(0, (float) $class['average_score'])))
                    <div>
                        <div class="mb-1 flex items-center justify-between text-xs text-slate-600">
                            <span class="font-semibold">{{ $class['name'] }}</span>
                            <span>{{ number_format($class['average_score'], 1) }} average | {{ number_format($class['students']) }} students</span>
                        </div>
                        <div class="h-2.5 rounded-lg bg-slate-100">
                            <div class="h-2.5 rounded-lg bg-sky-500" style="width: {{ $width }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">No class performance data is available for the active term.</p>
                @endforelse
            </div>
        </div>

        <div class="print-panel rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="text-lg font-bold text-slate-950">Attendance And Welfare</h3>

            <div class="mt-4 grid grid-cols-2 gap-3 md:grid-cols-4">
                <div class="rounded-lg border border-slate-200 p-3">
                    <p class="text-xs font-semibold text-slate-500">Sessions</p>
                    <p class="mt-1 text-xl font-bold text-slate-950">{{ number_format($attendance['sessions']) }}</p>
                </div>
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-3">
                    <p class="text-xs font-semibold text-emerald-700">Present</p>
                    <p class="mt-1 text-xl font-bold text-emerald-950">{{ number_format($attendance['present']) }}</p>
                </div>
                <div class="rounded-lg border border-rose-200 bg-rose-50 p-3">
                    <p class="text-xs font-semibold text-rose-700">Absent</p>
                    <p class="mt-1 text-xl font-bold text-rose-950">{{ number_format($attendance['absent']) }}</p>
                </div>
                <div class="rounded-lg border border-amber-200 bg-amber-50 p-3">
                    <p class="text-xs font-semibold text-amber-700">Late</p>
                    <p class="mt-1 text-xl font-bold text-amber-950">{{ number_format($attendance['late']) }}</p>
                </div>
            </div>

            <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="rounded-lg border border-slate-200 p-4">
                    <p class="text-sm font-bold text-slate-950">Attendance Coverage</p>
                    <dl class="mt-3 space-y-2 text-sm">
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-600">Classes Marked</dt>
                            <dd class="font-semibold text-slate-950">{{ number_format($attendance['classes_marked']) }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-600">Excused</dt>
                            <dd class="font-semibold text-slate-950">{{ number_format($attendance['excused']) }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-600">Attendance Rate</dt>
                            <dd class="font-semibold text-emerald-700">{{ $formatPercent($attendance['attendance_rate']) }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="rounded-lg border border-slate-200 p-4">
                    <p class="text-sm font-bold text-slate-950">Discipline</p>
                    <dl class="mt-3 space-y-2 text-sm">
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-600">This Window</dt>
                            <dd class="font-semibold text-slate-950">{{ number_format($discipline['period_total']) }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-600">High Priority</dt>
                            <dd class="font-semibold text-rose-700">{{ number_format($discipline['high_priority']) }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-600">Unresolved</dt>
                            <dd class="font-semibold text-amber-700">{{ number_format($discipline['unresolved']) }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="print-panel rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
        <h3 class="text-lg font-bold text-slate-950">Communication And Engagement</h3>

        <div class="mt-4 grid grid-cols-2 gap-3 md:grid-cols-4 xl:grid-cols-7">
            <div class="rounded-lg border border-slate-200 p-3">
                <p class="text-xs font-semibold text-slate-500">Active Notices</p>
                <p class="mt-1 text-xl font-bold text-slate-950">{{ number_format($engagement['active_notices']) }}</p>
            </div>
            <div class="rounded-lg border border-slate-200 p-3">
                <p class="text-xs font-semibold text-slate-500">Notices</p>
                <p class="mt-1 text-xl font-bold text-slate-950">{{ number_format($engagement['period_notices']) }}</p>
            </div>
            <div class="rounded-lg border border-slate-200 p-3">
                <p class="text-xs font-semibold text-slate-500">Broadcasts</p>
                <p class="mt-1 text-xl font-bold text-slate-950">{{ number_format($engagement['period_broadcasts']) }}</p>
            </div>
            <div class="rounded-lg border border-slate-200 p-3">
                <p class="text-xs font-semibold text-slate-500">Portal Reach</p>
                <p class="mt-1 text-xl font-bold text-slate-950">{{ number_format($engagement['portal_reach']) }}</p>
            </div>
            <div class="rounded-lg border border-slate-200 p-3">
                <p class="text-xs font-semibold text-slate-500">Inquiries</p>
                <p class="mt-1 text-xl font-bold text-slate-950">{{ number_format($engagement['inquiries_total']) }}</p>
            </div>
            <div class="rounded-lg border border-amber-200 bg-amber-50 p-3">
                <p class="text-xs font-semibold text-amber-700">Open</p>
                <p class="mt-1 text-xl font-bold text-amber-950">{{ number_format($engagement['inquiries_open']) }}</p>
            </div>
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-3">
                <p class="text-xs font-semibold text-emerald-700">Resolved</p>
                <p class="mt-1 text-xl font-bold text-emerald-950">{{ number_format($engagement['inquiries_resolved']) }}</p>
            </div>
        </div>
    </div>
</div>
