@extends('layouts.app', [
    'breadcrumbs' => [
        ['href' => route('dashboard'), 'text' => 'Dashboard'],
        ['href' => route('result'), 'text' => 'Results'],
        ['href' => route('result.annual'), 'text' => 'Annual Results', 'active' => true],
    ]
])

@section('title', __('Annual Results'))

@section('page_heading', __('Annual Results Summary'))

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6">

 @if($class && $academicYear)
            <!-- Print Header -->
            <div class="print-only mb-4">
                <h1 class="text-2xl font-bold text-center">{{ config('app.name') }}</h1>
                <h2 class="text-xl text-center">Annual Results - {{ $class->name }} ({{ $academicYear->name }})</h2>
                <p class="text-center text-sm">Generated on {{ now()->format('F j, Y \a\t h:i A') }}</p>
            </div>

        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-blue-800">Select Class and Academic Year</h2>
            @if($class && $academicYear)
            <div class="flex space-x-2">
                <div class="relative group">
                    <button type="button" 
                            class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                        <span>Export</span>
                        <i class="fas fa-chevron-down ml-2"></i>
                    </button>
                    <div class="absolute z-10 hidden group-hover:block bg-white shadow-lg rounded-md mt-1 w-48 right-0">
                        <a href="{{ route('result.annual.export', ['classId' => $class->id, 'academicYearId' => $academicYear->id]) }}" 
                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            Export as CSV
                        </a>
                        <a href="{{ route('result.annual.export.pdf', ['classId' => $class->id, 'academicYearId' => $academicYear->id]) }}" 
                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            Export as PDF
                        </a>
                    </div>
                </div>
                <button type="button" onclick="window.print()" 
                        class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-print"></i> Print
                </button>
                <button type="button" onclick="saveAsImage()" 
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-image"></i> Save as Image
                </button>
            </div>
            @endif
        </div>

        <!-- Replace other SVG icons with FontAwesome icons similarly -->
        <!-- Example for replacing icons in the summary cards -->
        <div
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <!-- Class Selector -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Class</label>
                    <select name="classId" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select Class</option>
                        @foreach($classes as $classOption)
                            <option value="{{ $classOption->id }}" @selected($classOption->id == ($class->id ?? null))>
                                {{ $classOption->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Academic Year Selector -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Academic Year</label>
                    <select name="academicYearId" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select Year</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" @selected($year->id == ($academicYear->id ?? null))>
                                {{ $year->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Action Button -->
                <div class="flex items-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded w-full">
                        Show Results
                    </button>
                </div>
            </div>
        </form>
        
       
            
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200 shadow-sm">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-3">
                            <i class="fas fa-chalkboard-teacher fa-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-blue-800">Class</h3>
                            <p class="text-gray-700">{{ $class->name }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200 shadow-sm">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-3">
                            <i class="fas fa-calendar-alt fa-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-blue-800">Academic Year</h3>
                            <p class="text-gray-700">{{ $academicYear->name }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200 shadow-sm">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-3">
                            <i class="fas fa-chart-bar fa-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-blue-800">Statistics</h3>
                            <p class="text-gray-700">{{ $stats['total_students'] }} Students | {{ $stats['subjects_count'] }} Subjects</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Search and Filter -->
            <div class="mb-6 bg-gray-50 p-4 rounded-lg">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search Students</label>
                        <input type="text" id="studentSearch" placeholder="Search by name or admission number" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Performance</label>
                        <select id="performanceFilter" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="all">All Students</option>
                            <option value="top10">Top 10 Students</option>
                            <option value="bottom10">Bottom 10 Students</option>
                            <option value="above80">Above 80%</option>
                            <option value="below50">Below 50%</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button id="resetFilters" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded w-full">
                            Reset Filters
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Results Table -->
            <div class="mb-6">
                <div class="overflow-x-auto print:overflow-visible">
                    <table class="min-w-full divide-y divide-gray-200" id="resultsTable">
                        <thead class="bg-blue-50 print:bg-white">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider cursor-pointer sortable" data-sort="rank">
                                    Rank <span class="sort-icon">↓</span>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider cursor-pointer sortable" data-sort="name">
                                    Student <span class="sort-icon"></span>
                                </th>
                                @foreach($subjects as $subject)
                                    <th class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">
                                        {{ $subject->name }}
                                    </th>
                                @endforeach
                                <th class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider cursor-pointer sortable" data-sort="total">
                                    Total <span class="sort-icon"></span>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider cursor-pointer sortable" data-sort="average">
                                    Average % <span class="sort-icon"></span>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider print-hidden">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($annualReports as $report)
                            <tr class="hover:bg-gray-50 print:hover:bg-white" 
                                data-rank="{{ $report['rank'] }}" 
                                data-name="{{ $report['student']->user->name }}"
                                data-total="{{ $report['grand_total'] }}"
                                data-average="{{ $report['average_percentage'] }}">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                    {{ $report['rank'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 print:hidden">
                                            <img class="h-10 w-10 rounded-full" src="{{ $report['student']->user->profile_photo_url }}" alt="">
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $report['student']->user->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $report['student']->admission_number }}</div>
                                        </div>
                                    </div>
                                </td>
                                @foreach($subjects as $subject)
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div class="flex items-center">
                                            <span>{{ $report['subject_totals'][$subject->id]['total'] ?? '-' }}</span>
                                            @if(isset($report['subject_totals'][$subject->id]['average']))
                                                @php
                                                    $average = $report['subject_totals'][$subject->id]['average'];
                                                    $color = $average >= 80 ? 'text-green-500' : ($average >= 50 ? 'text-yellow-500' : 'text-red-500');
                                                @endphp
                                                <span class="ml-1 text-xs {{ $color }} print-hidden">
                                                    ({{ number_format($average, 1) }}%)
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                @endforeach
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                    {{ $report['grand_total'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @php
                                        $avgColor = $report['average_percentage'] >= 80 ? 'text-green-600 font-bold' : 
                                                   ($report['average_percentage'] >= 50 ? 'text-blue-600' : 'text-red-600');
                                    @endphp
                                    <span class="{{ $avgColor }}">{{ $report['average_percentage'] }}%</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 print-hidden">
                                    <a href="{{ route('students.show', $report['student']->user_id) }}" 
                                       class="text-blue-600 hover:text-blue-900 mr-3" title="View Profile">
                                        <i class="fas fa-user fa-fw"></i>
                                    </a>
                                    <a href="{{ route('result.student', ['studentId' => $report['student']->user_id, 'academicYearId' => $academicYear->id]) }}" 
                                       class="text-purple-600 hover:text-purple-900" title="View Full Results">
                                        <i class="fas fa-file-alt fa-fw"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Performance Summary -->
            <div class="mb-8 bg-gray-50 p-6 rounded-lg shadow-sm">
                <h3 class="text-xl font-semibold text-blue-800 mb-4">Performance Analysis</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Score Distribution Chart -->
                    <div class="bg-white p-4 rounded-lg shadow">
                        <h4 class="text-lg font-medium text-gray-800 mb-3">Score Distribution</h4>
                        <canvas id="scoreDistributionChart" height="250"></canvas>
                    </div>
                    
                    <!-- Subject Performance Chart -->
                    <div class="bg-white p-4 rounded-lg shadow">
                        <h4 class="text-lg font-medium text-gray-800 mb-3">Subject Averages</h4>
                        <canvas id="subjectPerformanceChart" height="250"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Top/Bottom Performers -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Top Performers -->
                <div class="bg-white p-4 rounded-lg shadow">
                    <h4 class="text-lg font-medium text-green-700 mb-3 flex items-center">
                        <i class="fas fa-trophy mr-2"></i>
                        Top 5 Performers
                    </h4>
                    <div class="space-y-3">
                        @foreach(array_slice($annualReports, 0, 5) as $top)
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                            <div class="flex items-center">
                                <div class="bg-green-100 text-green-800 rounded-full w-8 h-8 flex items-center justify-center mr-3">
                                    {{ $top['rank'] }}
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">{{ $top['student']->user->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $top['student']->admission_number }}</div>
                                </div>
                            </div>
                            <div class="text-lg font-bold text-green-700">
                                {{ $top['average_percentage'] }}%
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                
                <!-- Bottom Performers -->
                <div class="bg-white p-4 rounded-lg shadow">
                    <h4 class="text-lg font-medium text-red-700 mb-3 flex items-center">
                        <i class="fas fa-user-times mr-2"></i>
                        Bottom 5 Performers
                    </h4>
                    <div class="space-y-3">
                        @foreach(array_slice(array_reverse($annualReports), 0, 5) as $bottom)
                        <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                            <div class="flex items-center">
                                <div class="bg-red-100 text-red-800 rounded-full w-8 h-8 flex items-center justify-center mr-3">
                                    {{ $bottom['rank'] }}
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">{{ $bottom['student']->user->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $bottom['student']->admission_number }}</div>
                                </div>
                            </div>
                            <div class="text-lg font-bold text-red-700">
                                {{ $bottom['average_percentage'] }}%
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            
            <!-- Class Statistics -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-xl font-semibold text-blue-800 mb-4">Class Statistics</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-blue-50 p-4 rounded-lg text-center">
                        <div class="text-3xl font-bold text-blue-700 mb-1">
                            {{ number_format(collect($annualReports)->avg('average_percentage'), 1) }}%
                        </div>
                        <div class="text-sm text-gray-600">Class Average</div>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg text-center">
                        <div class="text-3xl font-bold text-green-700 mb-1">
                            {{ collect($annualReports)->max('average_percentage') }}%
                        </div>
                        <div class="text-sm text-gray-600">Highest Score</div>
                    </div>
                    <div class="bg-yellow-50 p-4 rounded-lg text-center">
                        <div class="text-3xl font-bold text-yellow-700 mb-1">
                            {{ collect($annualReports)->min('average_percentage') }}%
                        </div>
                        <div class="text-sm text-gray-600">Lowest Score</div>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg text-center">
                        <div class="text-3xl font-bold text-purple-700 mb-1">
                            {{ count(array_filter($annualReports, function($report) { return $report['average_percentage'] >= 50; })) }}
                        </div>
                        <div class="text-sm text-gray-600">Passing Students</div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<style>
    @media print {
        .print-hidden {
            display: none !important;
        }
        .print-only {
            display: block !important;
        }
        body, html {
            background: white !important;
            font-size: 11pt !important;
        }
        .container {
            width: 100% !important;
            max-width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        table {
            width: 100% !important;
            border-collapse: collapse !important;
            font-size: 10pt !important;
        }
        th, td {
            padding: 4px 6px !important;
            border: 1px solid #ddd !important;
        }
        th {
            background-color: #f8f8f8 !important;
        }
        .bg-blue-50, .bg-gray-50, .bg-white {
            background-color: white !important;
        }
        .shadow, .shadow-lg, .shadow-sm {
            box-shadow: none !important;
        }
        .grid, .flex {
            display: block !important;
        }
        .gap-4, .gap-6, .gap-8 {
            gap: 0 !important;
        }
        .p-4, .p-6, .px-4, .py-4, .px-6, .py-6 {
            padding: 0.5rem !important;
        }
        .mb-4, .mb-6, .mb-8 {
            margin-bottom: 1rem !important;
        }
        .rounded-lg, .rounded-xl {
            border-radius: 0 !important;
        }
        canvas {
            max-width: 100% !important;
            height: auto !important;
        }
    }
</style>

@push('scripts')
<!-- Add FontAwesome CDN if not already present -->
<script src="https://kit.fontawesome.com/yourkitid.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize variables for sorting
    let currentSort = 'rank';
    let sortDirection = 'desc';
    
    // Sortable table headers
    document.querySelectorAll('.sortable').forEach(header => {
        header.addEventListener('click', function() {
            const sortBy = this.dataset.sort;
            
            if (currentSort === sortBy) {
                sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                currentSort = sortBy;
                sortDirection = 'asc';
            }
            
            // Update sort indicators
            document.querySelectorAll('.sort-icon').forEach(icon => {
                icon.textContent = '';
            });
            
            const icon = this.querySelector('.sort-icon');
            icon.textContent = sortDirection === 'asc' ? '↑' : '↓';
            
            // Sort the table
            sortTable();
        });
    });
    
    function sortTable() {
        const tbody = document.querySelector('#resultsTable tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        
        rows.sort((a, b) => {
            let aValue, bValue;
            
            switch(currentSort) {
                case 'rank':
                case 'total':
                    aValue = parseInt(a.dataset[currentSort]);
                    bValue = parseInt(b.dataset[currentSort]);
                    break;
                case 'average':
                    aValue = parseFloat(a.dataset[currentSort]);
                    bValue = parseFloat(b.dataset[currentSort]);
                    break;
                case 'name':
                    aValue = a.dataset[currentSort].toLowerCase();
                    bValue = b.dataset[currentSort].toLowerCase();
                    break;
            }
            
            if (aValue < bValue) {
                return sortDirection === 'asc' ? -1 : 1;
            }
            if (aValue > bValue) {
                return sortDirection === 'asc' ? 1 : -1;
            }
            return 0;
        });
        
        // Reattach sorted rows
        rows.forEach(row => tbody.appendChild(row));
    }
    
    // Student search functionality
    document.getElementById('studentSearch').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('#resultsTable tbody tr');
        
        rows.forEach(row => {
            const name = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
            const admissionNo = row.querySelector('td:nth-child(2) div:nth-child(2)').textContent.toLowerCase();
            
            if (name.includes(searchTerm) || admissionNo.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
    
    // Performance filter
    document.getElementById('performanceFilter').addEventListener('change', function(e) {
        const filterValue = e.target.value;
        const rows = document.querySelectorAll('#resultsTable tbody tr');
        
        rows.forEach(row => {
            const average = parseFloat(row.dataset.average);
            let showRow = true;
            
            switch(filterValue) {
                case 'top10':
                    showRow = parseInt(row.dataset.rank) <= 10;
                    break;
                case 'bottom10':
                    const totalRows = rows.length;
                    showRow = parseInt(row.dataset.rank) > totalRows - 10;
                    break;
                case 'above80':
                    showRow = average >= 80;
                    break;
                case 'below50':
                    showRow = average < 50;
                    break;
            }
            
            row.style.display = showRow ? '' : 'none';
        });
    });
    
    // Reset filters
    document.getElementById('resetFilters').addEventListener('click', function() {
        document.getElementById('studentSearch').value = '';
        document.getElementById('performanceFilter').value = 'all';
        
        const rows = document.querySelectorAll('#resultsTable tbody tr');
        rows.forEach(row => {
            row.style.display = '';
        });
    });
    
    // Save as image functionality
    window.saveAsImage = function() {
        // Hide elements we don't want in the image
        const elementsToHide = document.querySelectorAll('.print-hidden');
        elementsToHide.forEach(el => el.style.display = 'none');
        
        // Show print-only elements
        const printOnlyElements = document.querySelectorAll('.print-only');
        printOnlyElements.forEach(el => el.style.display = 'block');
        
        // Capture the main content
        html2canvas(document.querySelector('.container'), {
            scale: 2,
            logging: false,
            useCORS: true
        }).then(canvas => {
            // Restore hidden elements
            elementsToHide.forEach(el => el.style.display = '');
            printOnlyElements.forEach(el => el.style.display = 'none');
            
            // Create download link
            const link = document.createElement('a');
            link.download = `annual-results-{{ $class->name ?? 'class' }}-{{ $academicYear->name ?? 'year' }}.png`;
            link.href = canvas.toDataURL('image/png');
            link.click();
        });
    };
    
    // Initialize charts if we have data
    @if($class && $academicYear && count($annualReports) > 0)
        // Score Distribution Chart
        const scoreCtx = document.getElementById('scoreDistributionChart').getContext('2d');
        const scores = @json(array_column($annualReports, 'average_percentage'));
        
        new Chart(scoreCtx, {
            type: 'bar',
            data: {
                labels: scores.map((_, i) => `#${i+1}`),
                datasets: [{
                    label: 'Average Percentage',
                    data: scores,
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Percentage'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Student Rank'
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.parsed.y}% (Rank ${context.dataIndex + 1})`;
                            }
                        }
                    }
                }
            }
        });
@endif

