@php
    $formatPercent = static fn ($value): string => number_format((float) $value, 1) . '%';
    $formatChange = static fn ($value): string => ((float) $value > 0 ? '+' : '') . number_format((float) $value, 1);
    $changeTone = static fn ($value): string => (float) $value > 0
        ? 'text-emerald-700'
        : ((float) $value < 0 ? 'text-rose-700' : 'text-slate-700');

    $summaryCards = [
        [
            'label' => 'Latest Average',
            'value' => number_format((float) $summary['latest_average'], 1),
            'helper' => 'Change ' . $formatChange($summary['change']) . ' points',
            'tone' => 'border-sky-200 bg-sky-50 text-sky-950',
            'icon' => 'fas fa-chart-line',
        ],
        [
            'label' => 'Pass Rate',
            'value' => $formatPercent($summary['latest_pass_rate']),
            'helper' => number_format((int) $summary['latest_at_risk_students']) . ' at risk',
            'tone' => 'border-emerald-200 bg-emerald-50 text-emerald-950',
            'icon' => 'fas fa-check-circle',
        ],
        [
            'label' => 'Coverage',
            'value' => $formatPercent($summary['latest_coverage_rate']),
            'helper' => number_format((int) $summary['active_students']) . ' active students',
            'tone' => 'border-amber-200 bg-amber-50 text-amber-950',
            'icon' => 'fas fa-users',
        ],
        [
            'label' => 'Approvals',
            'value' => $formatPercent($summary['latest_approval_rate']),
            'helper' => number_format((int) $summary['result_entries']) . ' result entries',
            'tone' => 'border-rose-200 bg-rose-50 text-rose-950',
            'icon' => 'fas fa-file-signature',
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
            .performance-trends { color: #111827; }
            .print-panel { break-inside: avoid; page-break-inside: avoid; box-shadow: none !important; }
            main { padding: 0 !important; }
        }
    </style>
@endpush

<div class="performance-trends space-y-6">
    <div class="print-panel rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
            <div>
                <p class="text-sm font-semibold text-slate-500">Performance Trends</p>
                <h2 class="mt-1 text-2xl font-bold text-slate-950">{{ $school?->name ?? config('app.name') }}</h2>
                <p class="mt-2 text-sm text-slate-600">
                    Term-by-term academic movement from uploaded result records.
                </p>
                <p class="mt-1 text-xs text-slate-500">Generated {{ $generatedAt->format('M j, Y g:i A') }}</p>
            </div>

            <div class="no-print grid grid-cols-1 gap-3 sm:grid-cols-3 xl:w-[42rem]">
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
        <h3 class="text-lg font-bold text-slate-950">Trend Signals</h3>
        <p class="mt-1 text-sm text-slate-600">Action points from the selected academic year and class filter.</p>

        <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($signals as $signal)
                <div class="rounded-lg border p-4 {{ $signalTones[$signal['tone']] ?? $signalTones['sky'] }}">
                    <p class="text-sm font-bold">{{ $signal['title'] }}</p>
                    <p class="mt-1 text-sm opacity-90">{{ $signal['body'] }}</p>
                </div>
            @endforeach
        </div>
    </div>

    <div class="print-panel rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-lg font-bold text-slate-950">Term Trend</h3>
                <p class="mt-1 text-sm text-slate-600">Average score, pass rate, coverage, and approval movement by term.</p>
            </div>
            <div class="text-sm font-semibold text-slate-700">
                {{ number_format((int) $summary['terms_with_results']) }} term(s) with results
            </div>
        </div>

        <div class="mt-5 space-y-4">
            @forelse ($termTrends as $term)
                @php($averageWidth = min(100, max(0, (float) $term['average_score'])))
                @php($passWidth = min(100, max(0, (float) $term['pass_rate'])))
                @php($coverageWidth = min(100, max(0, (float) $term['coverage_rate'])))
                <div class="rounded-lg border border-slate-200 p-4">
                    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                        <div>
                            <p class="font-bold text-slate-950">{{ $term['label'] }}</p>
                            <p class="text-xs text-slate-500">
                                {{ number_format((int) $term['entries']) }} entries |
                                {{ number_format((int) $term['students_with_results']) }} students |
                                Approval {{ $formatPercent($term['approval_rate']) }}
                            </p>
                        </div>
                        <div class="text-sm font-bold text-slate-900">
                            Avg {{ number_format((float) $term['average_score'], 1) }}
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-3 lg:grid-cols-3">
                        <div>
                            <div class="mb-1 flex justify-between text-xs font-semibold text-slate-600">
                                <span>Average</span>
                                <span>{{ number_format((float) $term['average_score'], 1) }}</span>
                            </div>
                            <div class="h-2.5 rounded-lg bg-slate-100">
                                <div class="h-2.5 rounded-lg bg-sky-500" style="width: {{ $averageWidth }}%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="mb-1 flex justify-between text-xs font-semibold text-slate-600">
                                <span>Pass Rate</span>
                                <span>{{ $formatPercent($term['pass_rate']) }}</span>
                            </div>
                            <div class="h-2.5 rounded-lg bg-slate-100">
                                <div class="h-2.5 rounded-lg bg-emerald-500" style="width: {{ $passWidth }}%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="mb-1 flex justify-between text-xs font-semibold text-slate-600">
                                <span>Coverage</span>
                                <span>{{ $formatPercent($term['coverage_rate']) }}</span>
                            </div>
                            <div class="h-2.5 rounded-lg bg-slate-100">
                                <div class="h-2.5 rounded-lg bg-amber-500" style="width: {{ $coverageWidth }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <p class="rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                    No semesters are available for the selected academic year.
                </p>
            @endforelse
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <div class="print-panel rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="text-lg font-bold text-slate-950">Class Movement</h3>
            <p class="mt-1 text-sm text-slate-600">Class averages and movement between the first and latest recorded terms.</p>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-3 py-3">Class</th>
                            <th class="px-3 py-3">Year Avg</th>
                            <th class="px-3 py-3">Latest</th>
                            <th class="px-3 py-3">Change</th>
                            <th class="px-3 py-3">Students</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($classMovement as $class)
                            <tr>
                                <td class="px-3 py-3 font-semibold text-slate-950">{{ $class['class_name'] }}</td>
                                <td class="px-3 py-3 text-slate-700">{{ number_format((float) $class['average_score'], 1) }}</td>
                                <td class="px-3 py-3 text-slate-700">{{ $class['latest_average'] === null ? '-' : number_format((float) $class['latest_average'], 1) }}</td>
                                <td class="px-3 py-3 font-bold {{ $changeTone($class['change'] ?? 0) }}">
                                    {{ $class['change'] === null ? '-' : $formatChange($class['change']) }}
                                </td>
                                <td class="px-3 py-3 text-slate-700">{{ number_format((int) $class['students']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-3 py-8 text-center text-slate-500">No class movement data is available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="print-panel rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="text-lg font-bold text-slate-950">Subject Movement</h3>
            <p class="mt-1 text-sm text-slate-600">Subject averages, pass rate, and movement between recorded terms.</p>

            <div class="mt-4 max-h-[32rem] overflow-y-auto pr-1">
                <div class="space-y-3">
                    @forelse ($subjectMovement as $subject)
                        @php($averageWidth = min(100, max(0, (float) $subject['average_score'])))
                        <div class="rounded-lg border border-slate-200 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-bold text-slate-950">{{ $subject['subject_name'] }}</p>
                                    <p class="text-xs text-slate-500">
                                        {{ number_format((int) $subject['entries']) }} entries |
                                        Pass {{ $formatPercent($subject['pass_rate']) }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-black text-slate-950">{{ number_format((float) $subject['average_score'], 1) }}</p>
                                    <p class="text-xs font-bold {{ $changeTone($subject['change'] ?? 0) }}">
                                        {{ $subject['change'] === null ? 'No movement' : $formatChange($subject['change']) . ' pts' }}
                                    </p>
                                </div>
                            </div>
                            <div class="mt-3 h-2.5 rounded-lg bg-slate-100">
                                <div class="h-2.5 rounded-lg bg-rose-500" style="width: {{ $averageWidth }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                            No subject movement data is available.
                        </p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
