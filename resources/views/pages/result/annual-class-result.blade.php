@extends('layouts.app', [
    'breadcrumbs' => [
        ['href' => route('dashboard'), 'text' => 'Dashboard'],
        ['href' => route('result'), 'text' => 'Results'],
        ['href' => route('result.annual-class'), 'text' => 'Annual Class Results', 'active' => true]
    ],
])

@section('title', __('Annual Class Results'))
@section('page_heading', __('Annual Class Results Summary'))

@section('content')
<div class="container mx-auto px-4 py-6" x-data="annualClassResults">
    <!-- Filter Section -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6 animate__animated animate__fadeIn">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-4">
            <h2 class="text-xl font-bold text-blue-800">Select Class and Academic Year</h2>
            
            @if(isset($class) && isset($academicYear))
            <div class="flex flex-wrap gap-2">
                <div class="relative" x-data="{ open: false }" @click.away="open = false">
                    <button @click="open = !open"
                        class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                        <span>Export</span>
                        <i class="fas fa-chevron-down ml-2 text-sm"></i>
                    </button>
                    <div x-show="open" x-transition
                        class="absolute z-10 right-0 mt-1 bg-white shadow-lg rounded-md w-48">
                        <a href="{{ route('result.annual-class.export', ['classId' => $class->id, 'academicYearId' => $academicYear->id]) }}"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-file-csv mr-2"></i> Export CSV
                        </a>
                        <a href="{{ route('result.annual-class.export.pdf', ['classId' => $class->id, 'academicYearId' => $academicYear->id]) }}"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-file-pdf mr-2"></i> Export PDF
                        </a>
                    </div>
                </div>
                <button @click="window.print()"
                    class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-print mr-2"></i> Print
                </button>
            </div>
            @endif
        </div>

        <!-- Selection Form -->
        <form method="GET" action="{{ route('result.annual-class') }}" class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 p-6 bg-white shadow-xl rounded-2xl">
                <!-- Class Selection -->
                <div>
                    <label class="block text-sm font-semibold text-blue-700 mb-2">Select Class</label>
                    <select name="classId" required
                        class="w-full px-4 py-2 border border-blue-300 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none bg-blue-50 text-gray-800">
                        <option value="">-- Choose a Class --</option>
                        @foreach ($classes as $classOption)
                            <option value="{{ $classOption->id }}" @selected(isset($class) && $classOption->id == $class->id)>
                                {{ $classOption->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            
                <!-- Academic Year Selection -->
                <div>
                    <label class="block text-sm font-semibold text-green-700 mb-2">Academic Year</label>
                    <select name="academicYearId" required
                        class="w-full px-4 py-2 border border-green-300 rounded-xl shadow-sm focus:ring-2 focus:ring-green-500 focus:outline-none bg-green-50 text-gray-800">
                        <option value="">-- Choose Year --</option>
                        @foreach ($academicYears as $year)
                            <option value="{{ $year->id }}" @selected(isset($academicYear) && $year->id == $academicYear->id)>
                                {{ $year->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            
                <!-- Submit Button -->
                <div class="flex items-end">
                    <button type="submit"
                        class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-semibold rounded-xl shadow-md hover:from-indigo-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <i class="fas fa-search"></i> Show Results
                    </button>
                </div>
            </div>
        </form>

        @if(isset($class) && isset($academicYear))
            <!-- Term/Semester Navigation -->
            <div class="mb-6">
                <div class="flex flex-wrap gap-2">
                    @foreach($semesters as $sem)
                        <button 
                            @click="currentTerm = {{ $sem->id }}; $dispatch('term-changed')"
                            :class="{
                                'bg-blue-600 text-white': currentTerm === {{ $sem->id }},
                                'bg-gray-200 text-gray-800': currentTerm !== {{ $sem->id }}
                            }"
                            class="px-4 py-2 rounded-lg font-medium transition-colors">
                            {{ $sem->name }}
                        </button>
                    @endforeach
                    <button 
                        @click="currentTerm = 'annual'; $dispatch('term-changed')"
                        :class="{
                            'bg-blue-600 text-white': currentTerm === 'annual',
                            'bg-gray-200 text-gray-800': currentTerm !== 'annual'
                        }"
                        class="px-4 py-2 rounded-lg font-medium transition-colors">
                        Annual Summary
                    </button>
                </div>
            </div>
        @endif
    </div>

    @if(isset($class) && isset($academicYear))
        <!-- Print Header -->
        <div class="print-only mb-4">
            <h1 class="text-2xl font-bold text-center">{{ config('app.name') }}</h1>
            <h2 class="text-xl text-center">Annual Results - {{ $class->name }} ({{ $academicYear->name }})</h2>
            <p class="text-center text-sm">Generated on {{ now()->format('F j, Y \a\t h:i A') }}</p>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6 animate__animated animate__fadeIn">
            <x-summary-card icon="users" title="Class" value="{{ $class->name }}" color="blue" />
            <x-summary-card icon="calendar-alt" title="Academic Year" value="{{ $academicYear->name }}" color="blue" />
            <x-summary-card icon="chart-bar" title="Students" value="{{ $stats['total_students'] }}" color="blue" />
            <x-summary-card icon="book" title="Subjects" value="{{ $stats['subjects_count'] }}" color="blue" />
        </div>

        <!-- Current Term View -->
        <template x-if="currentTerm !== 'annual'">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6 animate__animated animate__fadeIn">
                <h2 class="text-xl font-bold text-blue-800 mb-4">
                    {{ $semesters->firstWhere('id', $semesters->first()->id)?->name }} Results
                </h2>
                
                <!-- Term Statistics -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <x-stat-card 
                        :value="number_format($termStats[$semesters->first()->id]['average_percentage'] ?? 0, 1).'%'" 
                        label="Term Average" 
                        color="blue" />
                    <x-stat-card 
                        :value="number_format($termStats[$semesters->first()->id]['pass_rate'] ?? 0, 1).'%'" 
                        label="Pass Rate" 
                        color="green" />
                    <x-stat-card 
                        :value="$termStats[$semesters->first()->id]['top_student'] ?? 'N/A'" 
                        label="Top Student" 
                        color="purple" />
                    <x-stat-card 
                        :value="$termStats[$semesters->first()->id]['top_score'] ?? 0" 
                        label="Top Score" 
                        color="yellow" />
                </div>

                <!-- Term Performance Chart -->
                <div class="bg-white p-4 rounded-lg shadow mb-6">
                    <h3 class="text-lg font-medium text-gray-800 mb-3">Subject Performance</h3>
                    <canvas id="termPerformanceChart" height="150"></canvas>
                </div>

                <!-- Term Results Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-blue-50">
                            <tr>
                                <th @click="sortBy('rank')" class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider cursor-pointer">
                                    Rank <i class="fas" :class="sortField === 'rank' ? (sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down') : 'fa-sort'"></i>
                                </th>
                                <th @click="sortBy('name')" class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider cursor-pointer">
                                    Student <i class="fas" :class="sortField === 'name' ? (sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down') : 'fa-sort'"></i>
                                </th>
                                @foreach($subjects as $subject)
                                    <th class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">
                                        {{ $subject->name }}
                                    </th>
                                @endforeach
                                <th @click="sortBy('total')" class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider cursor-pointer">
                                    Total <i class="fas" :class="sortField === 'total' ? (sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down') : 'fa-sort'"></i>
                                </th>
                                <th @click="sortBy('average')" class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider cursor-pointer">
                                    Average <i class="fas" :class="sortField === 'average' ? (sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down') : 'fa-sort'"></i>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="report in sortedTermReports" :key="report.student.id">
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900" x-text="report.rank"></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <img class="h-10 w-10 rounded-full" :src="report.student.user.profile_photo_url" :alt="report.student.user.name">
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900" x-text="report.student.user.name"></div>
                                                <div class="text-sm text-gray-500" x-text="report.student.admission_number"></div>
                                            </div>
                                        </div>
                                    </td>
                                    @foreach($subjects as $subject)
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                            <div x-show="report.results[{{ $subject->id }}]" class="flex flex-col">
                                                <span class="text-xs text-gray-500">Test: <span x-text="report.results[{{ $subject->id }}].test_score"></span></span>
                                                <span class="text-xs text-gray-500">Exam: <span x-text="report.results[{{ $subject->id }}].exam_score"></span></span>
                                                <span class="font-medium" x-text="report.results[{{ $subject->id }}].total_score"></span>
                                            </div>
                                            <div x-show="!report.results[{{ $subject->id }}]" class="text-gray-400">-</div>
                                        </td>
                                    @endforeach
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-center" x-text="report.total_score"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center" x-text="report.percentage + '%'"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </template>

        <!-- Annual Summary View -->
        <template x-if="currentTerm === 'annual'">
            <div class="space-y-6 animate__animated animate__fadeIn">
                <!-- Annual Performance Summary -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-xl font-bold text-blue-800 mb-4">Annual Performance Summary</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <x-stat-card 
                            :value="number_format(array_sum(array_map(function($r) { return $r['average_percentage']; }, $annualReports)) / count($annualReports), 1).'%'" 
                            label="Annual Average" 
                            color="blue" />
                        <x-stat-card 
                            :value="max(array_map(function($r) { return $r['average_percentage']; }, $annualReports)).'%'" 
                            label="Highest Score" 
                            color="green" />
                        <x-stat-card 
                            :value="min(array_map(function($r) { return $r['average_percentage']; }, $annualReports)).'%'" 
                            label="Lowest Score" 
                            color="yellow" />
                        <x-stat-card 
                            :value="count(array_filter($annualReports, function($r) { return $r['average_percentage'] >= 50; }))" 
                            label="Passing Students" 
                            color="purple" />
                    </div>

                    <!-- Performance Trends Chart -->
                    <div class="bg-white p-4 rounded-lg shadow mb-6">
                        <h3 class="text-lg font-medium text-gray-800 mb-3">Performance Trends Across Terms</h3>
                        <canvas id="performanceTrendsChart" height="150"></canvas>
                    </div>

                    <!-- Top/Bottom Performers -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <x-performer-section 
                            title="Top 5 Performers" 
                            icon="trophy" 
                            color="green" 
                            :reports="array_slice($annualReports, 0, 5)" />
                        <x-performer-section 
                            title="Bottom 5 Performers" 
                            icon="exclamation-triangle" 
                            color="red" 
                            :reports="array_slice(array_reverse($annualReports), 0, 5)" />
                    </div>
                </div>

                <!-- Detailed Annual Results -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-xl font-bold text-blue-800 mb-4">Detailed Annual Results</h2>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-blue-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">
                                        Rank</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">
                                        Student</th>
                                    @foreach($semesters as $semester)
                                        <th class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">
                                            {{ $semester->name }}</th>
                                    @endforeach
                                    <th class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">
                                        Annual Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">
                                        Average</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($annualReports as $report)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                            {{ $report['rank'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <img class="h-10 w-10 rounded-full"
                                                        src="{{ $report['student']->user->profile_photo_url }}"
                                                        alt="{{ $report['student']->user->name }}">
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ $report['student']->user->name }}</div>
                                                    <div class="text-sm text-gray-500">
                                                        {{ $report['student']->admission_number }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        @foreach($semesters as $semester)
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                {{ $report['term_totals'][$semester->id] ?? '-' }}
                                            </td>
                                        @endforeach
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-center">
                                            {{ $report['grand_total'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-center">
                                            {{ $report['average_percentage'] }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </template>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('annualClassResults', () => ({
        currentTerm: @json(isset($semesters) && $semesters->count() ? $semesters->first()->id : 'annual'),
        sortField: 'rank',
        sortDirection: 'asc',
        
        get sortedTermReports() {
            if (this.currentTerm === 'annual') return [];
            const reports = @json($termReports ?? [])[this.currentTerm] || [];
            return [...reports].sort((a, b) => {
                const valA = this.getSortValue(a, this.sortField);
                const valB = this.getSortValue(b, this.sortField);
                return this.sortDirection === 'asc' ? valA > valB ? 1 : -1 : valA < valB ? 1 : -1;
            });
        },
        
        getSortValue(report, field) {
            switch(field) {
                case 'name': return report.student.user.name.toLowerCase();
                case 'rank': return report.rank;
                case 'total': return report.total_score;
                case 'average': return report.percentage;
                default: return report.rank;
            }
        },
        
        sortBy(field) {
            if (this.sortField === field) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortField = field;
                this.sortDirection = 'asc';
            }
        },
        
        initCharts() {
            if (this.currentTerm === 'annual') {
                this.initAnnualCharts();
            } else {
                this.initTermCharts();
            }
        },
        
        initAnnualCharts() {
            if (document.getElementById('performanceTrendsChart') && @json(isset($semesters))) {
                const ctx = document.getElementById('performanceTrendsChart').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: @json(isset($semesters) ? $semesters->pluck('name') : []),
                        datasets: [
                            {
                                label: 'Class Average',
                                data: @json(isset($semesters) ? array_map(function($s) use ($termStats) { return $termStats[$s->id]['average_percentage'] ?? 0; }, $semesters->all()) : []),
                                borderColor: 'rgba(59, 130, 246, 1)',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                tension: 0.3,
                                fill: true
                            },
                            {
                                label: 'Top Student',
                                data: @json(isset($semesters) ? array_map(function($s) use ($termStats) { return $termStats[$s->id]['top_score'] ?? 0; }, $semesters->all()) : []),
                                borderColor: 'rgba(16, 185, 129, 1)',
                                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                tension: 0.3,
                                fill: true
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                title: {
                                    display: true,
                                    text: 'Percentage'
                                }
                            }
                        }
                    }
                });
            }
        },
        
        initTermCharts() {
            if (document.getElementById('termPerformanceChart') && @json(isset($subjects) && isset($termStats))) {
                const ctx = document.getElementById('termPerformanceChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: @json(isset($subjects) ? $subjects->pluck('name') : []),
                        datasets: [{
                            label: 'Class Average',
                            data: @json(isset($subjects) ? array_map(function($s) use ($termStats) { 
                                return $termStats[this.currentTerm]?.subject_averages[$s->id] ?? 0; 
                            }, $subjects->all()) : []),
                            backgroundColor: 'rgba(59, 130, 246, 0.7)',
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                title: {
                                    display: true,
                                    text: 'Score'
                                }
                            }
                        }
                    }
                });
            }
        }
    }));
});
</script>
@endpush

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<style>
    @media print {
        .print-only {
            display: block !important;
        }
        .no-print {
            display: none !important;
        }
        body {
            background: white !important;
            font-size: 11pt !important;
        }
        .container {
            width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        table {
            width: 100% !important;
            font-size: 10pt !important;
        }
        th, td {
            padding: 4px 6px !important;
            border: 1px solid #ddd !important;
        }
        th {
            background-color: #f8f8f8 !important;
        }
        .shadow {
            box-shadow: none !important;
        }
    }
</style>
@endpush
@endsection