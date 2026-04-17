@php
    $formatPercent = static fn ($value): string => number_format((float) $value, 1) . '%';
    $maxMonthly = max(1, max(array_merge(array_values($monthlyTrend['students']), array_values($monthlyTrend['applications']))));
    $maxClass = max(1, collect($classDistribution)->max('total') ?? 1);
    $maxSection = max(1, collect($sectionDistribution)->max('total') ?? 1);
    $maxGender = max(1, collect($genderDistribution)->max('total') ?? 1);

    $summaryCards = [
        [
            'label' => 'Active Enrollment',
            'value' => number_format((int) $people['active_students']),
            'helper' => number_format((int) $people['total_students']) . ' total student accounts',
            'tone' => 'border-emerald-200 bg-emerald-50 text-emerald-950',
            'icon' => 'fas fa-user-graduate',
        ],
        [
            'label' => 'New Students',
            'value' => number_format((int) $people['new_students']),
            'helper' => $start->format('M j') . ' to ' . $end->format('M j, Y'),
            'tone' => 'border-sky-200 bg-sky-50 text-sky-950',
            'icon' => 'fas fa-user-plus',
        ],
        [
            'label' => 'Applications',
            'value' => number_format((int) $admissions['period_total']),
            'helper' => number_format((int) $admissions['pending']) . ' pending review',
            'tone' => 'border-amber-200 bg-amber-50 text-amber-950',
            'icon' => 'fas fa-file-alt',
        ],
        [
            'label' => 'Parent Links',
            'value' => $formatPercent($people['parent_link_rate']),
            'helper' => number_format((int) $people['linked_to_parent']) . ' linked students',
            'tone' => 'border-rose-200 bg-rose-50 text-rose-950',
            'icon' => 'fas fa-people-arrows',
        ],
    ];

    $signalTones = [
        'emerald' => 'border-emerald-200 bg-emerald-50 text-emerald-900',
        'sky' => 'border-sky-200 bg-sky-50 text-sky-900',
        'amber' => 'border-amber-200 bg-amber-50 text-amber-900',
        'rose' => 'border-rose-200 bg-rose-50 text-rose-900',
    ];
@endphp

@push('head')
    <style>
        @media print {
            .no-print { display: none !important; }
            .enrollment-analytics { color: #111827; }
            .print-panel { break-inside: avoid; page-break-inside: avoid; box-shadow: none !important; }
            main { padding: 0 !important; }
        }
    </style>
@endpush

<div class="enrollment-analytics space-y-6">
    <div class="print-panel rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
            <div>
                <p class="text-sm font-semibold text-slate-500">Enrollment Analytics</p>
                <h2 class="mt-1 text-2xl font-bold text-slate-950">{{ $school?->name ?? config('app.name') }}</h2>
                <p class="mt-2 text-sm text-slate-600">
                    Enrollment, intake, parent-link, and admission movement from {{ $start->format('M j, Y') }} to {{ $end->format('M j, Y') }}.
                </p>
                <p class="mt-1 text-xs text-slate-500">Generated {{ $generatedAt->format('M j, Y g:i A') }}</p>
            </div>

            <div class="no-print grid grid-cols-1 gap-3 sm:grid-cols-4 xl:w-[52rem]">
                <label class="block text-sm font-semibold text-slate-700">
                    Window
                    <select wire:model.live="months" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                        <option value="3">Last 3 months</option>
                        <option value="6">Last 6 months</option>
                        <option value="12">Last 12 months</option>
                    </select>
                </label>

                <label class="block text-sm font-semibold text-slate-700">
                    Academic Year
                    <select wire:model.live="selectedAcademicYearId" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                        <option value="">Select year</option>
                        @foreach ($academicYears as $academicYear)
                            <option value="{{ $academicYear->id }}">{{ $academicYear->name }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="block text-sm font-semibold text-slate-700">
                    Class
                    <select wire:model.live="selectedClassId" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                        <option value="">All classes</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                </label>

                <div class="flex items-end">
                    <button
                        type="button"
                        onclick="window.print()"
                        class="w-full rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700"
                    >
                        <i class="fas fa-print mr-2"></i>
                        Print
                    </button>
                </div>
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
        <h3 class="text-lg font-bold text-slate-950">Enrollment Signals</h3>
        <p class="mt-1 text-sm text-slate-600">Action points from active student records and admission applications.</p>

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
            <h3 class="text-lg font-bold text-slate-950">Monthly Intake</h3>
            <p class="mt-1 text-sm text-slate-600">New student records and admission applications in the selected window.</p>

            <div class="mt-5 space-y-4">
                @foreach ($monthKeys as $key)
                    @php($studentCount = (int) ($monthlyTrend['students'][$key] ?? 0))
                    @php($applicationCount = (int) ($monthlyTrend['applications'][$key] ?? 0))
                    @php($studentWidth = (int) round(($studentCount / $maxMonthly) * 100))
                    @php($applicationWidth = (int) round(($applicationCount / $maxMonthly) * 100))
                    <div>
                        <div class="mb-1 flex items-center justify-between text-xs font-semibold text-slate-600">
                            <span>{{ $monthLabels[$loop->index] }}</span>
                            <span>{{ number_format($studentCount) }} students | {{ number_format($applicationCount) }} applications</span>
                        </div>
                        <div class="grid grid-cols-1 gap-1.5">
                            <div class="h-2.5 rounded-lg bg-slate-100">
                                <div class="h-2.5 rounded-lg bg-emerald-500" style="width: {{ $studentWidth }}%"></div>
                            </div>
                            <div class="h-2.5 rounded-lg bg-slate-100">
                                <div class="h-2.5 rounded-lg bg-sky-500" style="width: {{ $applicationWidth }}%"></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="print-panel rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="text-lg font-bold text-slate-950">Admissions Funnel</h3>
            <p class="mt-1 text-sm text-slate-600">Application status and conversion into enrolled students.</p>

            <div class="mt-5 grid grid-cols-2 gap-3 md:grid-cols-3">
                <div class="rounded-lg border border-slate-200 p-4">
                    <p class="text-xs font-semibold text-slate-500">All Applications</p>
                    <p class="mt-1 text-xl font-bold text-slate-950">{{ number_format((int) $admissions['total']) }}</p>
                </div>
                <div class="rounded-lg border border-amber-200 bg-amber-50 p-4">
                    <p class="text-xs font-semibold text-amber-700">Pending</p>
                    <p class="mt-1 text-xl font-bold text-amber-950">{{ number_format((int) $admissions['pending']) }}</p>
                </div>
                <div class="rounded-lg border border-sky-200 bg-sky-50 p-4">
                    <p class="text-xs font-semibold text-sky-700">Reviewed</p>
                    <p class="mt-1 text-xl font-bold text-sky-950">{{ number_format((int) $admissions['reviewed']) }}</p>
                </div>
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4">
                    <p class="text-xs font-semibold text-emerald-700">Approved</p>
                    <p class="mt-1 text-xl font-bold text-emerald-950">{{ number_format((int) $admissions['approved']) }}</p>
                </div>
                <div class="rounded-lg border border-rose-200 bg-rose-50 p-4">
                    <p class="text-xs font-semibold text-rose-700">Rejected</p>
                    <p class="mt-1 text-xl font-bold text-rose-950">{{ number_format((int) $admissions['rejected']) }}</p>
                </div>
                <div class="rounded-lg border border-slate-200 p-4">
                    <p class="text-xs font-semibold text-slate-500">Conversion</p>
                    <p class="mt-1 text-xl font-bold text-slate-950">{{ $formatPercent($admissions['conversion_rate']) }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <div class="print-panel rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="text-lg font-bold text-slate-950">Class Distribution</h3>
            <p class="mt-1 text-sm text-slate-600">Active enrollment by class for the selected academic year.</p>

            <div class="mt-5 space-y-3">
                @forelse ($classDistribution as $class)
                    @php($width = (int) round(($class['total'] / $maxClass) * 100))
                    <div>
                        <div class="mb-1 flex justify-between text-xs font-semibold text-slate-600">
                            <span>{{ $class['name'] }}</span>
                            <span>{{ number_format((int) $class['total']) }}</span>
                        </div>
                        <div class="h-2.5 rounded-lg bg-slate-100">
                            <div class="h-2.5 rounded-lg bg-emerald-500" style="width: {{ $width }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">No class distribution data is available.</p>
                @endforelse
            </div>
        </div>

        <div class="print-panel rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="text-lg font-bold text-slate-950">Student Status</h3>
            <p class="mt-1 text-sm text-slate-600">Enrollment health across active, inactive, graduated, and parent-linked records.</p>

            <div class="mt-5 grid grid-cols-2 gap-3">
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4">
                    <p class="text-xs font-semibold text-emerald-700">Active</p>
                    <p class="mt-1 text-2xl font-black text-emerald-950">{{ number_format((int) $people['active_students']) }}</p>
                </div>
                <div class="rounded-lg border border-amber-200 bg-amber-50 p-4">
                    <p class="text-xs font-semibold text-amber-700">Inactive</p>
                    <p class="mt-1 text-2xl font-black text-amber-950">{{ number_format((int) $people['inactive_students']) }}</p>
                </div>
                <div class="rounded-lg border border-slate-200 p-4">
                    <p class="text-xs font-semibold text-slate-500">Graduated</p>
                    <p class="mt-1 text-2xl font-black text-slate-950">{{ number_format((int) $people['graduated_students']) }}</p>
                </div>
                <div class="rounded-lg border border-sky-200 bg-sky-50 p-4">
                    <p class="text-xs font-semibold text-sky-700">Linked To Parent</p>
                    <p class="mt-1 text-2xl font-black text-sky-950">{{ number_format((int) $people['linked_to_parent']) }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <div class="print-panel rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="text-lg font-bold text-slate-950">Section Distribution</h3>
            <p class="mt-1 text-sm text-slate-600">Top section counts among active students.</p>

            <div class="mt-5 space-y-3">
                @forelse ($sectionDistribution as $section)
                    @php($width = (int) round(($section['total'] / $maxSection) * 100))
                    <div>
                        <div class="mb-1 flex justify-between text-xs font-semibold text-slate-600">
                            <span>{{ $section['name'] }}</span>
                            <span>{{ number_format((int) $section['total']) }}</span>
                        </div>
                        <div class="h-2.5 rounded-lg bg-slate-100">
                            <div class="h-2.5 rounded-lg bg-amber-500" style="width: {{ $width }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">No section distribution data is available.</p>
                @endforelse
            </div>
        </div>

        <div class="print-panel rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="text-lg font-bold text-slate-950">Gender Distribution</h3>
            <p class="mt-1 text-sm text-slate-600">Recorded gender split among active students.</p>

            <div class="mt-5 space-y-3">
                @forelse ($genderDistribution as $gender)
                    @php($width = (int) round(($gender['total'] / $maxGender) * 100))
                    <div>
                        <div class="mb-1 flex justify-between text-xs font-semibold text-slate-600">
                            <span>{{ $gender['name'] }}</span>
                            <span>{{ number_format((int) $gender['total']) }}</span>
                        </div>
                        <div class="h-2.5 rounded-lg bg-slate-100">
                            <div class="h-2.5 rounded-lg bg-rose-500" style="width: {{ $width }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">No gender distribution data is available.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
