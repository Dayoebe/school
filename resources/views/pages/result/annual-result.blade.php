@extends('layouts.app', [
    'breadcrumbs' => [['href' => route('dashboard'), 'text' => 'Dashboard'], ['href' => route('result'), 'text' => 'Results'], ['href' => route('result.annual'), 'text' => 'Annual Results', 'active' => true]],
])

@section('title', __('Annual Results'))

@section('page_heading', __('Annual Results Summary'))

@section('content')
    <div class="container mx-auto px-4 py-6" x-data="annualResults">
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
            <!-- Header and Export Buttons -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-4">
                <h2 class="text-xl font-bold text-blue-800">Select Class and Academic Year</h2>

                @if ($class && $academicYear)
                    <div class="flex flex-wrap gap-2">
                        <div class="relative" x-data="{ open: false }" @click.away="open = false">
                            <button @click="open = !open"
                                class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                                <span>Export</span>
                                <i class="fas fa-chevron-down ml-2 text-sm"></i>
                            </button>
                            <div x-show="open" x-transition
                                class="absolute z-10 right-0 mt-1 bg-white shadow-lg rounded-md w-48">
                                <a href="{{ route('result.annual.export', ['classId' => $class->id, 'academicYearId' => $academicYear->id]) }}"
                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-file-csv mr-2"></i> Export CSV
                                </a>
                                <a href="{{ route('result.annual.export.pdf', ['classId' => $class->id, 'academicYearId' => $academicYear->id]) }}"
                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-file-pdf mr-2"></i> Export PDF
                                </a>
                            </div>
                        </div>
                        <button @click="window.print()"
                            class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                            <i class="fas fa-print mr-2"></i> Print
                        </button>
                        <button @click="saveAsImage()"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                            <i class="fas fa-image mr-2"></i> Save as Image
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
                                <option value="{{ $classOption->id }}" @selected($classOption->id == ($class->id ?? null))>
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
                                <option value="{{ $year->id }}" @selected($year->id == ($academicYear->id ?? null))>
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

            @if ($class && $academicYear)
                <!-- Print Header -->
                <div class="print-only mb-4">
                    <h1 class="text-2xl font-bold text-center">{{ config('app.name') }}</h1>
                    <h2 class="text-xl text-center">Annual Results - {{ $class->name }} ({{ $academicYear->name }})</h2>
                    <p class="text-center text-sm">Generated on {{ now()->format('F j, Y \a\t h:i A') }}</p>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <x-summary-card icon="users" title="Class" value="{{ $class->name }}" color="blue" />

                    <x-summary-card icon="calendar-alt" title="Academic Year" value="{{ $academicYear->name }}"
                        color="blue" />

                    <x-summary-card icon="chart-bar" title="Statistics"
                        value="{{ $stats['total_students'] }} Students | {{ $stats['subjects_count'] }} Subjects"
                        color="blue" />
                </div>

                <!-- Search and Filter -->
                <div class="mb-6 bg-gray-50 p-4 rounded-lg">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Search Students</label>
                            <input x-model="searchTerm" type="text" placeholder="Search by name or admission number"
                                class="w-full input-field">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Performance</label>
                            <select x-model="performanceFilter" class="w-full input-field">
                                <option value="all">All Students</option>
                                <option value="top10">Top 10 Students</option>
                                <option value="bottom10">Bottom 10 Students</option>
                                <option value="above80">Above 80%</option>
                                <option value="below50">Below 50%</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button @click="resetFilters()" class="w-full btn-secondary">
                                <i class="fas fa-sync-alt mr-2"></i> Reset Filters
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Results Table -->
                <div class="mb-6 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200" id="resultsTable">
                        <thead class="bg-blue-50 print:bg-white">
                            <tr>
                                <th @click="sortBy('rank')"
                                    class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider cursor-pointer">
                                    Rank <i class="fas"
                                        :class="{
                                            'fa-sort': currentSort != 'rank',
                                            'fa-sort-up': currentSort == 'rank' &&
                                                sortDirection == 'asc',
                                            'fa-sort-down': currentSort == 'rank' &&
                                                sortDirection == 'desc'
                                        }"></i>
                                </th>
                                <th @click="sortBy('name')"
                                    class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider cursor-pointer">
                                    Student <i class="fas"
                                        :class="{
                                            'fa-sort': currentSort != 'name',
                                            'fa-sort-up': currentSort == 'name' &&
                                                sortDirection == 'asc',
                                            'fa-sort-down': currentSort == 'name' &&
                                                sortDirection == 'desc'
                                        }"></i>
                                </th>
                                @foreach ($subjects as $subject)
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">
                                        {{ $subject->name }}
                                    </th>
                                @endforeach
                                <th @click="sortBy('total')"
                                    class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider cursor-pointer">
                                    Total <i class="fas"
                                        :class="{
                                            'fa-sort': currentSort != 'total',
                                            'fa-sort-up': currentSort == 'total' &&
                                                sortDirection == 'asc',
                                            'fa-sort-down': currentSort == 'total' &&
                                                sortDirection == 'desc'
                                        }"></i>
                                </th>
                                <th @click="sortBy('average')"
                                    class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider cursor-pointer">
                                    Average % <i class="fas"
                                        :class="{
                                            'fa-sort': currentSort != 'average',
                                            'fa-sort-up': currentSort == 'average' &&
                                                sortDirection == 'asc',
                                            'fa-sort-down': currentSort == 'average' &&
                                                sortDirection == 'desc'
                                        }"></i>
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider print-hidden">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="report in filteredReports" :key="report.student.id">
                                @foreach($annualReports as $report)
                                <tr class="hover:bg-gray-50 print:hover:bg-white">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900"
                                        x-text="report.rank"></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 print:hidden">
                                                <img class="h-10 w-10 rounded-full"
                                                    :src="report.student.user.profile_photo_url"
                                                    :alt="report.student.user.name">
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900"
                                                    x-text="report.student.user.name"></div>
                                                <div class="text-sm text-gray-500"
                                                    x-text="report.student.admission_number"></div>
                                            </div>
                                        </div>
                                    </td>
                                    @foreach ($subjects as $subject)
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <div class="flex items-center">
                                                <span x-text="getSubjectTotal(report, {{ $subject->id }})"></span>
                                                <template x-if="getSubjectAverage(report, {{ $subject->id }}) !== null">
                                                    <span class="ml-1 text-xs"
                                                        :class="getSubjectColor(getSubjectAverage(report, {{ $subject->id }}))"
                                                        x-text="'(' + getSubjectAverage(report, {{ $subject->id }}).toFixed(1) + '%)'"></span>
                                                </template>
                                            </div>
                                        </td>
                                    @endforeach
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900"
                                        x-text="report.grand_total"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm"
                                        :class="getAverageColor(report.average_percentage)">
                                        <span x-text="report.average_percentage + '%'"></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 print-hidden">
                                        <a href="{{ route('students.show', $report['student']->user_id) }}"
                                           class="text-blue-600 hover:text-blue-900 mr-3" 
                                           title="View Profile">
                                           <i class="fas fa-user"></i>
                                        </a>
                                        
                                        <a href="{{ route('result.student.annual', [
                                            'studentId' => $report['student']->user_id,
                                            'academicYearId' => $academicYear->id,
                                        ]) }}"
                                           class="text-purple-600 hover:text-purple-900"
                                           title="View Full Results">
                                           <i class="fas fa-poll"></i>
                                        </a>
                         
                                    </td>
                                </tr>
                                @endforeach
                            </template>
                        </tbody>
                    </table>
                </div>



                <!-- Top/Bottom Performers -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <x-performer-section title="Top 5 Performers" icon="trophy" color="green" :reports="array_slice($annualReports, 0, 5)" />

                    <x-performer-section title="Bottom 5 Performers" icon="exclamation-triangle" color="red"
                        :reports="array_slice(array_reverse($annualReports), 0, 5)" />
                </div>

                <!-- Class Statistics -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-xl font-semibold text-blue-800 mb-4">Class Statistics</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <x-stat-card value="{{ number_format(collect($annualReports)->avg('average_percentage'), 1) }}%"
                            label="Class Average" color="blue" />

                        <x-stat-card value="{{ collect($annualReports)->max('average_percentage') }}%"
                            label="Highest Score" color="green" />

                        <x-stat-card value="{{ collect($annualReports)->min('average_percentage') }}%"
                            label="Lowest Score" color="yellow" />

                        <x-stat-card
                            value="{{ count(array_filter($annualReports, function ($report) {return $report['average_percentage'] >= 50;})) }}"
                            label="Passing Students" color="purple" />
                    </div>
                </div>


                <!-- Performance Analysis -->
                <div class="mb-8 bg-gray-50 p-6 rounded-lg shadow-sm">
                    <h3 class="text-xl font-semibold text-blue-800 mb-4">Performance Analysis</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-white p-4 rounded-lg shadow">
                            <h4 class="text-lg font-medium text-gray-800 mb-3">Score Distribution</h4>
                            <canvas id="scoreDistributionChart" height="250"></canvas>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow">
                            <h4 class="text-lg font-medium text-gray-800 mb-3">Subject Averages</h4>
                            <canvas id="subjectPerformanceChart" height="250"></canvas>
                        </div>
                    </div>
                </div>

            @endif
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('annualResults', () => ({
                    searchTerm: '',
                    performanceFilter: 'all',
                    currentSort: 'rank',
                    sortDirection: 'desc',
                    reports: @json($annualReports),

                    get filteredReports() {
                        let filtered = this.reports;

                        // Apply search filter
                        if (this.searchTerm) {
                            const term = this.searchTerm.toLowerCase();
                            filtered = filtered.filter(report =>
                                report.student.user.name.toLowerCase().includes(term) ||
                                report.student.admission_number.toLowerCase().includes(term)
                            );
                        }

                        // Apply performance filter
                        switch (this.performanceFilter) {
                            case 'top10':
                                filtered = filtered.slice(0, 10);
                                break;
                            case 'bottom10':
                                filtered = filtered.slice(-10).reverse();
                                break;
                            case 'above80':
                                filtered = filtered.filter(r => r.average_percentage >= 80);
                                break;
                            case 'below50':
                                filtered = filtered.filter(r => r.average_percentage < 50);
                                break;
                        }

                        // Apply sorting
                        return this.sortReports(filtered);
                    },

                    sortBy(column) {
                        if (this.currentSort === column) {
                            this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
                        } else {
                            this.currentSort = column;
                            this.sortDirection = 'asc';
                        }
                    },

                    sortReports(reports) {
                        return [...reports].sort((a, b) => {
                            let aValue, bValue;

                            switch (this.currentSort) {
                                case 'rank':
                                case 'total':
                                    aValue = parseInt(a[this.currentSort]);
                                    bValue = parseInt(b[this.currentSort]);
                                    break;
                                case 'average':
                                    aValue = parseFloat(a.average_percentage);
                                    bValue = parseFloat(b.average_percentage);
                                    break;
                                case 'name':
                                    aValue = a.student.user.name.toLowerCase();
                                    bValue = b.student.user.name.toLowerCase();
                                    break;
                            }

                            if (aValue < bValue) {
                                return this.sortDirection === 'asc' ? -1 : 1;
                            }
                            if (aValue > bValue) {
                                return this.sortDirection === 'asc' ? 1 : -1;
                            }
                            return 0;
                        });
                    },

                    resetFilters() {
                        this.searchTerm = '';
                        this.performanceFilter = 'all';
                    },

                    getSubjectTotal(report, subjectId) {
                        return report.subject_totals[subjectId]?.total ?? '-';
                    },

                    getSubjectAverage(report, subjectId) {
                        return report.subject_totals[subjectId]?.average ?? null;
                    },

                    getSubjectColor(average) {
                        if (average === null) return '';
                        return average >= 80 ? 'text-green-500' :
                            average >= 50 ? 'text-yellow-500' : 'text-red-500';
                    },

                    getAverageColor(average) {
                        return average >= 80 ? 'text-green-600 font-bold' :
                            average >= 50 ? 'text-blue-600' : 'text-red-600';
                    },

                    saveAsImage() {
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
                            link.download =
                                `annual-results-{{ $class->name ?? 'class' }}-{{ $academicYear->name ?? 'year' }}.png`;
                            link.href = canvas.toDataURL('image/png');
                            link.click();
                        });
                    },

                    initCharts() {
                        @if ($class && $academicYear && count($annualReports) > 0)
                            // Score Distribution Chart
                            new Chart(
                                document.getElementById('scoreDistributionChart').getContext('2d'), {
                                    type: 'bar',
                                    data: {
                                        labels: @json(array_map(function ($r) {
                                                return '#' . $r['rank'];
                                            }, $annualReports)),
                                        datasets: [{
                                            label: 'Average Percentage',
                                            data: @json(array_column($annualReports, 'average_percentage')),
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
                                        }
                                    }
                                }
                            );

                            // Subject Performance Chart
                            const subjectData = @json(
                                $subjects->map(function ($subject) use ($annualReports) {
                                    $averages = [];
                                    foreach ($annualReports as $report) {
                                        $averages[] = $report['subject_totals'][$subject->id]['average'] ?? 0;
                                    }
                                    return [
                                        'subject' => $subject->name,
                                        'average' => count($averages) > 0 ? array_sum($averages) / count($averages) : 0,
                                    ];
                                }));

                            new Chart(
                                document.getElementById('subjectPerformanceChart').getContext('2d'), {
                                    type: 'doughnut',
                                    data: {
                                        labels: subjectData.map(item => item.subject),
                                        datasets: [{
                                            data: subjectData.map(item => item.average),
                                            backgroundColor: [
                                                "rgba(255, 99, 132, 0.7)",
                                                "rgba(54, 162, 235, 0.7)",
                                                "rgba(255, 206, 86, 0.7)",
                                                "rgba(75, 192, 192, 0.7)",
                                                "rgba(153, 102, 255, 0.7)",
                                                "rgba(255, 159, 64, 0.7)"
                                            ],
                                            borderWidth: 1
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: {
                                            legend: {
                                                position: 'right'
                                            }
                                        }
                                    }
                                }
                            );
                        @endif

                        new Chart(
                            document.getElementById('subjectPerformanceChart').getContext('2d'), {
                                type: 'doughnut',
                                data: {
                                    labels: subjectData.map(item => item.subject),
                                    datasets: [{
                                        data: subjectData.map(item => item.average),
                                        backgroundColor: [
                                            'rgba(255, 99, 132, 0.7)',
                                            'rgba(54, 162, 235, 0.7)',
                                            'rgba(255, 206, 86, 0.7)',
                                            'rgba(75, 192, 192, 0.7)',
                                            'rgba(153, 102, 255, 0.7)',
                                            'rgba(255, 159, 64, 0.7)'
                                        ],
                                        borderWidth: 1
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: {
                                            position: 'right'
                                        }
                                    }
                                }
                            }
                        );
                    }
                }));
            });
        </script>
    @endpush

    @push('styles')
        <style>
            @media print {
                .print-hidden {
                    display: none !important;
                }

                .print-only {
                    display: block !important;
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

                th,
                td {
                    padding: 4px 6px !important;
                    border: 1px solid #ddd !important;
                }

                th {
                    background-color: #f8f8f8 !important;
                }

                .bg-blue-50,
                .bg-white {
                    background-color: white !important;
                }

                .shadow {
                    box-shadow: none !important;
                }

                .gap-4,
                .gap-6 {
                    gap: 0 !important;
                }

                .p-4,
                .px-4,
                .py-4 {
                    padding: 0.5rem !important;
                }

                .mb-4,
                .mb-6 {
                    margin-bottom: 1rem !important;
                }

                .rounded-lg {
                    border-radius: 0 !important;
                }
            }

            .input-field {
                @apply w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500;
            }

            .btn-primary {
                @apply bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded;
            }

            .btn-secondary {
                @apply bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded;
            }
        </style>
    @endpush
@endsection
