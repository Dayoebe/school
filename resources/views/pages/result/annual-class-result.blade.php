@extends('layouts.app', [
    'breadcrumbs' => [
        ['href' => route('dashboard'), 'text' => 'Dashboard'],
        ['href' => route('result'), 'text' => 'Results'],
        ['href' => route('result.annual'), 'text' => 'Annual Class Results', 'active' => true]
    ],
])

@section('title', __('Annual Class Results'))
@section('page_heading', __('Annual Class Results Summary'))

@section('content')
<div class="container mx-auto px-4 py-6" x-data="annualClassResults()">
    <!-- Filter Section -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-4">
            <h2 class="text-xl font-bold text-blue-800">Select Class and Academic Year</h2>
            
            @if(isset($class) && isset($academicYear))
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('result.annual.export', ['classId' => $class->id, 'academicYearId' => $academicYear->id]) }}"
                    class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                    <i class="fas fa-file-csv mr-2"></i> Export CSV
                </a>
                <button @click="window.print()"
                    class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-print mr-2"></i> Print
                </button>
            </div>
            @endif
        </div>

        <!-- Selection Form -->
        <form method="GET" action="{{ route('result.annual') }}" class="mb-6">
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
                            @click="currentTerm = '{{ $sem->id }}'"
                            :class="{
                                'bg-blue-600 text-white': currentTerm === '{{ $sem->id }}',
                                'bg-gray-200 text-gray-800': currentTerm !== '{{ $sem->id }}'
                            }"
                            class="px-4 py-2 rounded-lg font-medium transition-colors">
                            {{ $sem->name }}
                        </button>
                    @endforeach
                    <button 
                        @click="currentTerm = 'annual'"
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
        <div class="hidden print:block mb-4">
            <h1 class="text-2xl font-bold text-center">{{ config('app.name') }}</h1>
            <h2 class="text-xl text-center">Annual Results - {{ $class->name }} ({{ $academicYear->name }})</h2>
            <p class="text-center text-sm">Generated on {{ now()->format('F j, Y \a\t h:i A') }}</p>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                <div class="flex items-center">
                    <i class="fas fa-users text-blue-600 text-2xl mr-3"></i>
                    <div>
                        <h3 class="text-sm font-semibold text-blue-800">Class</h3>
                        <p class="text-gray-700">{{ $class->name }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                <div class="flex items-center">
                    <i class="fas fa-calendar-alt text-blue-600 text-2xl mr-3"></i>
                    <div>
                        <h3 class="text-sm font-semibold text-blue-800">Academic Year</h3>
                        <p class="text-gray-700">{{ $academicYear->name }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                <div class="flex items-center">
                    <i class="fas fa-user-graduate text-blue-600 text-2xl mr-3"></i>
                    <div>
                        <h3 class="text-sm font-semibold text-blue-800">Students</h3>
                        <p class="text-gray-700">{{ $stats['total_students'] }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                <div class="flex items-center">
                    <i class="fas fa-book text-blue-600 text-2xl mr-3"></i>
                    <div>
                        <h3 class="text-sm font-semibold text-blue-800">Subjects</h3>
                        <p class="text-gray-700">{{ $stats['subjects_count'] }}</p>
                    </div>
                </div>
            </div>
        </div>

      <!-- Current Term View -->
<div x-show="currentTerm !== 'annual'">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-xl font-bold text-blue-800 mb-4">
            <template x-for="semester in semesters" :key="semester.id">
                <span x-show="currentTerm == semester.id" x-text="semester.name + ' Results'"></span>
            </template>
        </h2>
        
        <template x-for="semester in semesters" :key="'sem-'+semester.id">
            <div x-show="currentTerm == semester.id">
                <div x-data="{ termId: semester.id }">
                    @if(isset($termReports) && count($termReports) > 0)
                        <!-- Term Statistics -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                                <h3 class="text-sm font-semibold text-blue-800">Term Average</h3>
                                <p class="text-xl font-bold" x-text="termStats[termId]?.average_percentage?.toFixed(1) + '%' || '0%'"></p>
                            </div>
                            
                            <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                                <h3 class="text-sm font-semibold text-green-800">Pass Rate</h3>
                                <p class="text-xl font-bold" x-text="termStats[termId]?.pass_rate?.toFixed(1) + '%' || '0%'"></p>
                            </div>
                            
                            <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                                <h3 class="text-sm font-semibold text-purple-800">Top Student</h3>
                                <p class="text-xl font-bold" x-text="termStats[termId]?.top_student || 'N/A'"></p>
                            </div>
                            
                            <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                                <h3 class="text-sm font-semibold text-yellow-800">Top Score</h3>
                                <p class="text-xl font-bold" x-text="termStats[termId]?.top_score?.toFixed(1) + '%' || '0%'"></p>
                            </div>
                        </div>
        
                        <!-- Term Results Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-blue-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">
                                            Rank
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">
                                            Student
                                        </th>
                                        @foreach($subjects as $subject)
                                            <th class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">
                                                {{ $subject->name }}
                                            </th>
                                        @endforeach
                                        <th class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">
                                            Total
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">
                                            Average
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <template x-for="report in termReports[termId]" :key="report.student.id">
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900" x-text="report.rank"></td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10">
                                                        <img class="h-10 w-10 rounded-full" 
                                                             :src="report.student.user.profile_photo_url" 
                                                             :alt="report.student.user.name">
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900" x-text="report.student.user.name"></div>
                                                        <div class="text-sm text-gray-500" x-text="report.student.admission_number"></div>
                                                    </div>
                                                </div>
                                            </td>
                                            @foreach($subjects as $subject)
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                    <template x-if="report.results[{{ $subject->id }}]">
                                                        <div class="flex flex-col">
                                                            <span class="text-xs text-gray-500" x-text="'Test: ' + report.results[{{ $subject->id }}].test_score"></span>
                                                            <span class="text-xs text-gray-500" x-text="'Exam: ' + report.results[{{ $subject->id }}].exam_score"></span>
                                                            <span class="font-medium" x-text="report.results[{{ $subject->id }}].total_score"></span>
                                                        </div>
                                                    </template>
                                                    <template x-if="!report.results[{{ $subject->id }}]">
                                                        <div class="text-gray-400">-</div>
                                                    </template>
                                                </td>
                                            @endforeach
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-center" x-text="report.total_score"></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center" x-text="report.percentage + '%'"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="bg-yellow-50 p-4 rounded-lg text-center">
                            <p class="text-yellow-600">No results found for this term</p>
                        </div>
                    @endif
                </div>
            </div>
        </template>
    </div>
</div>

        <!-- Annual Summary View -->
        <template x-if="currentTerm === 'annual'">
            <div class="space-y-6">
                <!-- Annual Performance Summary -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-xl font-bold text-blue-800 mb-4">Annual Performance Summary</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                            <h3 class="text-sm font-semibold text-blue-800">Annual Average</h3>
                            <p class="text-xl font-bold">
                                {{ number_format(array_sum(array_map(function($r) { return $r['average_percentage']; }, $annualReports)) / count($annualReports), 1) }}%
                            </p>
                        </div>
                        
                        <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                            <h3 class="text-sm font-semibold text-green-800">Highest Score</h3>
                            <p class="text-xl font-bold">
                                {{ max(array_map(function($r) { return $r['average_percentage']; }, $annualReports)) }}%
                            </p>
                        </div>
                        
                        <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                            <h3 class="text-sm font-semibold text-yellow-800">Lowest Score</h3>
                            <p class="text-xl font-bold">
                                {{ min(array_map(function($r) { return $r['average_percentage']; }, $annualReports)) }}%
                            </p>
                        </div>
                        
                        <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                            <h3 class="text-sm font-semibold text-purple-800">Passing Students</h3>
                            <p class="text-xl font-bold">
                                {{ count(array_filter($annualReports, function($r) { return $r['average_percentage'] >= 50; })) }}
                            </p>
                        </div>
                    </div>

                    <!-- Top/Bottom Performers -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                            <h3 class="text-lg font-semibold text-green-800 mb-3">
                                <i class="fas fa-trophy mr-2"></i> Top 5 Performers
                            </h3>
                            <div class="space-y-2">
                                @foreach(array_slice($annualReports, 0, 5) as $report)
                                <div class="flex items-center justify-between bg-white p-2 rounded">
                                    <div class="flex items-center">
                                        <span class="font-bold text-gray-700 mr-2">{{ $report['rank'] }}</span>
                                        <img class="h-8 w-8 rounded-full mr-2" 
                                             src="{{ $report['student']->user->profile_photo_url }}" 
                                             alt="{{ $report['student']->user->name }}">
                                        <span>{{ $report['student']->user->name }}</span>
                                    </div>
                                    <span class="font-bold text-green-600">{{ $report['average_percentage'] }}%</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        
                        <div class="bg-red-50 p-4 rounded-lg border border-red-200">
                            <h3 class="text-lg font-semibold text-red-800 mb-3">
                                <i class="fas fa-exclamation-triangle mr-2"></i> Bottom 5 Performers
                            </h3>
                            <div class="space-y-2">
                                @foreach(array_slice(array_reverse($annualReports), 0, 5) as $report)
                                <div class="flex items-center justify-between bg-white p-2 rounded">
                                    <div class="flex items-center">
                                        <span class="font-bold text-gray-700 mr-2">{{ $report['rank'] }}</span>
                                        <img class="h-8 w-8 rounded-full mr-2" 
                                             src="{{ $report['student']->user->profile_photo_url }}" 
                                             alt="{{ $report['student']->user->name }}">
                                        <span>{{ $report['student']->user->name }}</span>
                                    </div>
                                    <span class="font-bold text-red-600">{{ $report['average_percentage'] }}%</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
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
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('annualClassResults', () => ({
        currentTerm: 'annual',
        semesters: @json($semesters ?? []),
        termReports: @json($termReports ?? []),
        termStats: @json($termStats ?? []),
        annualReports: @json($annualReports ?? []),
        init() {
            // Initialize any additional data or logic here
        }
    }));
});
</script>
@endpush

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<style>
    @media print {
        .print\:block {
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