@extends('layouts.app', [
    'breadcrumbs' => [
        ['href' => route('dashboard'), 'text' => 'Dashboard'],
        ['href' => route('result'), 'text' => 'Results', 'active' => true],
    ]
])

@section('title', __('Results'))

@section('page_heading', __('Student Results History'))

@section('content')
<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- Student Profile Header with Back Button -->
    <div class="bg-gradient-to-r from-indigo-600 to-blue-700 rounded-2xl shadow-2xl p-6 mb-8 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent"></div>
        <div class="flex flex-col md:flex-row justify-between items-center relative z-10">
            <div class="flex items-center mb-4 md:mb-0">
                <a href="{{ url()->previous() }}" class="mr-4 text-white hover:text-gray-200 transition-colors flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i> Back
                </a>
                <div class="relative">
                    <img class="w-24 h-24 rounded-full border-4 border-white/40 shadow-xl" 
                         src="{{ $studentRecord->user->profile_photo_url ?? asset('images/default-avatar.png') }}" 
                         alt="{{ $studentRecord->user->name }}">
                    <div class="absolute bottom-0 right-0 w-7 h-7 bg-emerald-400 rounded-full border-2 border-white flex items-center justify-center">
                        <i class="fas fa-graduation-cap text-white text-xs"></i>
                    </div>
                </div>
                <div class="ml-5 text-white">
                    <h1 class="text-2xl md:text-3xl font-bold flex items-center">
                        {{ $studentRecord->user->name }}
                        @if($overallStats['average_score'] >= 70)
                            <span class="ml-3 bg-yellow-400/90 text-blue-900 px-3 py-1 rounded-full text-sm flex items-center shadow-md">
                                <i class="fas fa-medal mr-2"></i> Top Performer
                            </span>
                        @endif
                    </h1>
                    <div class="flex flex-wrap gap-2 mt-3">
                        <span class="bg-white/20 backdrop-blur-sm px-3 py-1.5 rounded-full text-sm flex items-center shadow-sm">
                            <i class="fas fa-layer-group mr-2"></i>
                            {{ $studentRecord->myClass->name ?? 'N/A' }}
                        </span>
                        <span class="bg-white/20 backdrop-blur-sm px-3 py-1.5 rounded-full text-sm flex items-center shadow-sm">
                            <i class="fas fa-id-badge mr-2"></i>
                            {{ $studentRecord->admission_number ?? 'N/A' }}
                        </span>
                        <span class="bg-white/20 backdrop-blur-sm px-3 py-1.5 rounded-full text-sm flex items-center shadow-sm">
                            <i class="fas fa-calendar-star mr-2"></i>
                            Joined: {{ $studentRecord->created_at->format('M Y') }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="flex space-x-3">
                <button onclick="window.print()"
                    class="bg-white text-indigo-700 hover:bg-indigo-50 px-4 py-2.5 rounded-xl font-medium flex items-center transition-all hover:scale-[1.03] shadow-md hover:shadow-lg">
                    <i class="fas fa-print mr-2"></i> Print Report
                </button>
                <button wire:click="loadStudentHistory"
                    class="bg-white/20 text-white hover:bg-white/30 px-4 py-2.5 rounded-xl font-medium flex items-center transition-all hover:scale-[1.03] shadow-md hover:shadow-lg backdrop-blur-sm">
                    <i class="fas fa-sync-alt mr-2"></i> Refresh
                </button>
            </div>
        </div>
    </div>

    @if($loading)
        <div class="flex justify-center py-16">
            <div class="flex flex-col items-center">
                <div class="animate-spin rounded-full h-16 w-16 border-t-4 border-b-4 border-indigo-600 mb-4"></div>
                <p class="text-gray-600">Loading academic history...</p>
            </div>
        </div>
    @else
        <!-- Performance Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
            <!-- Overall Performance -->
            <div class="bg-gradient-to-br from-indigo-500 to-indigo-700 text-white rounded-2xl shadow-xl p-6 transform transition-transform hover:scale-[1.02]">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-lg font-semibold mb-2">Overall Performance</h3>
                        <div class="text-4xl font-bold">{{ $overallStats['average_score'] }}%</div>
                        <div class="mt-2 text-indigo-100">Average Score</div>
                    </div>
                    <div class="bg-white/20 p-3 rounded-xl">
                        <i class="fas fa-chart-line text-2xl"></i>
                    </div>
                </div>
                <div class="mt-5 pt-4 border-t border-indigo-400/50">
                    <div class="flex justify-between text-sm">
                        <span class="text-indigo-100">Terms Completed</span>
                        <span class="font-medium">{{ $overallStats['total_terms'] }}</span>
                    </div>
                </div>
            </div>

            <!-- Best Subject -->
            <div class="bg-gradient-to-br from-emerald-500 to-emerald-700 text-white rounded-2xl shadow-xl p-6 transform transition-transform hover:scale-[1.02]">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-lg font-semibold mb-2">Best Subject</h3>
                        <div class="text-2xl font-bold truncate">{{ $overallStats['best_subject'] }}</div>
                        <div class="mt-2 text-emerald-100">{{ $overallStats['best_subject_avg'] }}% Average</div>
                    </div>
                    <div class="bg-white/20 p-3 rounded-xl">
                        <i class="fas fa-medal text-2xl"></i>
                    </div>
                </div>
                <div class="mt-5 pt-4 border-t border-emerald-400/50">
                    <div class="flex justify-between text-sm">
                        <span class="text-emerald-100">Performance</span>
                        <span class="font-medium">
                            @if($overallStats['best_subject_avg'] >= 75) Excellent @else Good @endif
                        </span>
                    </div>
                </div>
            </div>

            <!-- Worst Subject -->
            <div class="bg-gradient-to-br from-rose-500 to-rose-700 text-white rounded-2xl shadow-xl p-6 transform transition-transform hover:scale-[1.02]">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-lg font-semibold mb-2">Worst Subject</h3>
                        <div class="text-2xl font-bold truncate">{{ $overallStats['worst_subject'] }}</div>
                        <div class="mt-2 text-rose-100">{{ $overallStats['worst_subject_avg'] }}% Average</div>
                    </div>
                    <div class="bg-white/20 p-3 rounded-xl">
                        <i class="fas fa-exclamation-triangle text-2xl"></i>
                    </div>
                </div>
                <div class="mt-5 pt-4 border-t border-rose-400/50">
                    <div class="flex justify-between text-sm">
                        <span class="text-rose-100">Performance</span>
                        <span class="font-medium">
                            @if($overallStats['worst_subject_avg'] >= 50) Satisfactory @else Needs Improvement @endif
                        </span>
                    </div>
                </div>
            </div>

            <!-- Completion Status -->
            <div class="bg-gradient-to-br from-blue-500 to-blue-700 text-white rounded-2xl shadow-xl p-6 transform transition-transform hover:scale-[1.02]">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-lg font-semibold mb-2">Completion</h3>
                        <div class="text-2xl font-bold">{{ count($academicYears) }} Years</div>
                        <div class="mt-2 text-blue-100">Academic History</div>
                    </div>
                    <div class="bg-white/20 p-3 rounded-xl">
                        <i class="fas fa-calendar-check text-2xl"></i>
                    </div>
                </div>
                <div class="mt-5 pt-4 border-t border-blue-400/50">
                    <div class="flex justify-between text-sm">
                        <span class="text-blue-100">Current Year</span>
                        <span class="font-medium">
                            {{ $academicYears->first()->name ?? 'N/A' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters & Subject Performance -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
            <!-- Filters -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-5 flex items-center">
                        <i class="fas fa-filter text-indigo-600 mr-3"></i>
                        Filter Results
                    </h2>
                    
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Academic Year</label>
                            <div class="relative">
                                <select wire:model.live="selectedAcademicYear" 
                                        class="w-full border border-gray-300 rounded-xl px-4 py-3 pl-12 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 appearance-none shadow-sm">
                                    <option value="all">All Academic Years</option>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}" 
                                            @if($year->id == $selectedAcademicYear) selected @endif>
                                            {{ $year->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <i class="fas fa-calendar text-gray-500 absolute left-4 top-3.5"></i>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Term</label>
                            <div class="relative">
                                <select wire:model.live="selectedSemester" 
                                        class="w-full border border-gray-300 rounded-xl px-4 py-3 pl-12 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 appearance-none shadow-sm">
                                    <option value="all">All Terms</option>
                                    @foreach($academicYears as $year)
                                        @foreach($year->semesters as $semester)
                                            <option value="{{ $semester->id }}" 
                                                @if($semester->id == $selectedSemester) selected @endif>
                                                {{ $semester->name }}
                                            </option>
                                        @endforeach
                                    @endforeach
                                </select>
                                <i class="fas fa-bookmark text-gray-500 absolute left-4 top-3.5"></i>
                            </div>
                        </div>
                        
                        <div>
                            <button wire:click="resetFilters"
                                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-3 rounded-xl font-medium flex items-center justify-center transition-colors shadow-md hover:shadow-lg">
                                <i class="fas fa-redo mr-3"></i> Reset Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subject Performance Bar Charts -->
            <div class="lg:col-span-3">
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-5 flex items-center">
                        <i class="fas fa-chart-bar text-indigo-600 mr-3"></i>
                        Subject Performance
                    </h2>
                    
                    <div class="space-y-6">
                        @foreach($subjectPerformance as $subject)
                            @php
                                $barColor = $subject['average'] >= 75 ? 'bg-emerald-500' :
                                            ($subject['average'] >= 50 ? 'bg-amber-500' : 'bg-rose-500');
                                $icon = match(true) {
                                    $subject['average'] >= 80 => 'fas fa-star text-yellow-300',
                                    $subject['average'] >= 60 => 'fas fa-check-circle text-emerald-400',
                                    default => 'fas fa-book text-indigo-400'
                                };
                                $barWidth = min(100, max(5, $subject['average']));
                            @endphp
                            <div class="border border-gray-200 rounded-xl p-4 hover:shadow-md transition-shadow">
                                <div class="flex justify-between mb-2">
                                    <div class="flex items-center">
                                        <i class="{{ $icon }} text-lg mr-3"></i>
                                        <span class="font-medium text-gray-800">{{ $subject['subject']->name }}</span>
                                    </div>
                                    <span class="font-bold text-gray-800">{{ round($subject['average'], 1) }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-6 overflow-hidden">
                                    <div class="h-6 flex items-center justify-end {{ $barColor }} pr-3 text-xs font-medium text-white" 
                                         style="width: {{ $barWidth }}%">
                                        {{ round($subject['average'], 1) }}%
                                    </div>
                                </div>
                                <div class="mt-1 text-xs text-gray-500 flex justify-between">
                                    <span>0%</span>
                                    <span>100%</span>
                                </div>
                                <div class="mt-1 text-xs text-gray-500">
                                    @if($subject['average'] >= 75)
                                        Excellent performance
                                    @elseif($subject['average'] >= 50)
                                        Good performance
                                    @else
                                        Needs improvement
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Results Timeline -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden mb-10">
            <div class="bg-gradient-to-r from-indigo-600 to-blue-700 px-6 py-5">
                <h3 class="text-xl font-bold text-white flex items-center">
                    <i class="fas fa-history mr-3"></i>
                    Academic Performance Timeline
                </h3>
            </div>
            
            @if(count($filteredData) > 0)
                <div class="divide-y divide-gray-200/60">
                    @foreach($filteredData as $yearId => $yearData)
                        <div x-data="{ expanded: {{ $loop->first ? 'true' : 'false' }} }" class="py-5 px-6">
                            <button @click="expanded = !expanded" class="w-full flex justify-between items-center group">
                                <div class="flex items-center">
                                    <div class="bg-indigo-100 text-indigo-800 w-10 h-10 rounded-lg flex items-center justify-center mr-4">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-lg font-bold text-left text-gray-800 group-hover:text-indigo-700">
                                            {{ $yearData['year']->name }}
                                        </h4>
                                        <div class="text-sm text-gray-500 mt-1">
                                            {{ count($yearData['semesters']) }} terms • Average: {{ $yearData['year_avg'] }}%
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center">
                                    <span class="text-sm text-gray-500 mr-3 hidden md:block" x-text="expanded ? 'Collapse' : 'Expand'">
                                    </span>
                                    <i :class="expanded ? 'fas fa-chevron-up' : 'fas fa-chevron-down'" 
                                        class="text-gray-500 group-hover:text-indigo-600"></i>
                                </div>
                            </button>
                            
                            <div x-show="expanded" x-collapse class="mt-6 animate__animated animate__fadeIn">
                                <div class="space-y-6">
                                    @foreach($yearData['semesters'] as $semesterId => $semesterData)
                                        <div x-data="{ expandedTerm: true }" class="bg-gray-50 rounded-xl p-5 border border-gray-200">
                                            <button @click="expandedTerm = !expandedTerm" class="w-full flex justify-between items-center mb-3">
                                                <div class="flex items-center">
                                                    <div class="bg-blue-100 text-blue-800 w-8 h-8 rounded-lg flex items-center justify-center mr-3">
                                                        <i class="fas fa-bookmark text-sm"></i>
                                                    </div>
                                                    <h5 class="font-semibold text-gray-800">
                                                        {{ $semesterData['semester']->name }}
                                                        <span class="text-gray-500 font-normal ml-2">
                                                            (Avg: {{ $semesterData['semester_avg'] }}%)
                                                        </span>
                                                    </h5>
                                                </div>
                                                <i :class="expandedTerm ? 'fas fa-chevron-up' : 'fas fa-chevron-down'" 
                                                    class="text-gray-500"></i>
                                            </button>
                                            
                                            <div x-show="expandedTerm" x-collapse class="mt-4 animate__animated animate__fadeIn">
                                                <div class="overflow-x-auto">
                                                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                                                        <thead class="bg-gray-100">
                                                            <tr>
                                                                <th class="px-4 py-3 text-left font-medium text-gray-700">Subject</th>
                                                                <th class="px-4 py-3 text-center font-medium text-gray-700">CA1</th>
                                                                <th class="px-4 py-3 text-center font-medium text-gray-700">CA2</th>
                                                                <th class="px-4 py-3 text-center font-medium text-gray-700">CA3</th>
                                                                <th class="px-4 py-3 text-center font-medium text-gray-700">CA4</th>
                                                                <th class="px-4 py-3 text-center font-medium text-gray-700">Exam</th>
                                                                <th class="px-4 py-3 text-center font-medium text-gray-700">Total</th>
                                                                <th class="px-4 py-3 text-center font-medium text-gray-700">Grade</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-gray-200">
                                                            @foreach($semesterData['results'] as $result)
                                                                @php
                                                                    $gradeColor = match($result['grade']) {
                                                                        'A1', 'B2', 'B3' => 'bg-green-100 text-green-800',
                                                                        'C4', 'C5', 'C6' => 'bg-yellow-100 text-yellow-800',
                                                                        'D7', 'E8'       => 'bg-orange-100 text-orange-800',
                                                                        default          => 'bg-red-100 text-red-800',
                                                                    };
                                                                @endphp
                                                                <tr class="hover:bg-gray-50 transition-colors">
                                                                    <td class="px-4 py-3 whitespace-nowrap font-medium text-gray-900">
                                                                        {{ $result['subject']->name }}
                                                                    </td>
                                                                    <td class="px-4 py-3 text-center">{{ $result['scores']['ca1'] ?? '-' }}</td>
                                                                    <td class="px-4 py-3 text-center">{{ $result['scores']['ca2'] ?? '-' }}</td>
                                                                    <td class="px-4 py-3 text-center">{{ $result['scores']['ca3'] ?? '-' }}</td>
                                                                    <td class="px-4 py-3 text-center">{{ $result['scores']['ca4'] ?? '-' }}</td>
                                                                    <td class="px-4 py-3 text-center font-semibold">{{ $result['scores']['exam'] ?? '-' }}</td>
                                                                    <td class="px-4 py-3 text-center font-bold">{{ $result['total'] }}</td>
                                                                    <td class="px-4 py-3 text-center">
                                                                        <span class="px-2.5 py-1 text-xs font-bold rounded-full {{ $gradeColor }}">
                                                                            {{ $result['grade'] }}
                                                                        </span>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                                
                                                @if($semesterData['term_report'])
                                                    <div class="mt-6 pt-5 border-t border-gray-200">
                                                        <h5 class="text-md font-semibold text-gray-800 mb-4 flex items-center">
                                                            <i class="fas fa-file-alt text-blue-600 mr-2"></i>
                                                            Term Report Comments
                                                        </h5>
                                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                            <div class="bg-blue-50/50 p-4 rounded-xl">
                                                                <h6 class="text-sm font-medium text-gray-700 mb-2 flex items-center">
                                                                    <i class="fas fa-user-tie text-blue-600 mr-2"></i>
                                                                    Teacher's Comment
                                                                </h6>
                                                                <p class="text-gray-600 italic">"{{ $semesterData['term_report']->class_teacher_comment }}"</p>
                                                            </div>
                                                            <div class="bg-indigo-50/50 p-4 rounded-xl">
                                                                <h6 class="text-sm font-medium text-gray-700 mb-2 flex items-center">
                                                                    <i class="fas fa-user-shield text-indigo-600 mr-2"></i>
                                                                    Principal's Comment
                                                                </h6>
                                                                <p class="text-gray-600 italic">"{{ $semesterData['term_report']->principal_comment }}"</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-16">
                    <div class="inline-block bg-gray-100 p-6 rounded-full mb-5">
                        <i class="fas fa-book-open text-4xl text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-700">No academic records found</h3>
                    <p class="text-gray-500 mt-2 max-w-md mx-auto">
                        This student doesn't have any results recorded yet. Check back later or contact administration.
                    </p>
                </div>
            @endif
        </div>
        
        <div class="text-center text-gray-500 text-sm mb-8">
            <i class="fas fa-info-circle mr-1"></i> Generated on {{ now()->format('M d, Y h:i A') }}
        </div>
    @endif
</div>

<!-- Print Styles -->
<style>
    @media print {
        body * {
            visibility: hidden;
        }
        .max-w-7xl, .max-w-7xl * {
            visibility: visible;
        }
        .max-w-7xl {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            padding: 0;
            margin: 0;
        }
        button, [x-data] > button, [x-cloak] {
            display: none !important;
        }
        .bg-gradient-to-r, .bg-gradient-to-br {
            -webkit-print-color-adjust: exact;
            color-adjust: exact;
        }
        .shadow-xl, .shadow-lg, .shadow-md {
            box-shadow: none !important;
        }
        .rounded-2xl, .rounded-xl {
            border-radius: 0.25rem !important;
        }
        .px-4, .py-6, .p-6 {
            padding: 1rem !important;
        }
        .grid, .flex {
            display: block !important;
        }
        .divide-y > :not([hidden]) ~ :not([hidden]) {
            border-top-width: 1px;
        }
    }
</style>

<!-- Animation Library -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

@endsection