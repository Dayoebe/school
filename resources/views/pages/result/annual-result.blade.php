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
        <h2 class="text-xl font-bold text-blue-800 mb-4">Select Class and Academic Year</h2>
        <form method="GET" action="{{ route('result.annual') }}" class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <!-- Class Selector -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Class</label>
                    <select name="classId" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
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
                    <select name="academicYearId" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        <option value="">Select Year</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" @selected($year->id == ($academicYear->id ?? null))>
                                {{ $year->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex items-end space-x-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Show Results
                    </button>
                    @if($class && $academicYear)
                        <a href="{{ route('result.annual.export', ['classId' => $class->id, 'academicYearId' => $academicYear->id]) }}" 
                           class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                            Export CSV
                        </a>
                        <button type="button" onclick="window.print()" 
                                class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                            Print
                        </button>
                    @endif
                </div>
            </div>
        </form>
        
        @if($class && $academicYear)
            <div class="print-only mb-4">
                <h1 class="text-2xl font-bold text-center">{{ config('app.name') }}</h1>
                <h2 class="text-xl text-center">Annual Results - {{ $class->name }} ({{ $academicYear->name }})</h2>
                <p class="text-center text-sm">Generated on {{ now()->format('F j, Y \a\t h:i A') }}</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Class Info Card -->
                <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <div>
                            <h3 class="text-lg font-semibold text-blue-800">Class</h3>
                            <p class="text-gray-700">{{ $class->name }}</p>
                        </div>
                    </div>
                </div>
                
                <!-- Academic Year Card -->
                <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <div>
                            <h3 class="text-lg font-semibold text-blue-800">Academic Year</h3>
                            <p class="text-gray-700">{{ $academicYear->name }}</p>
                        </div>
                    </div>
                </div>
                
                <!-- Statistics Card -->
                <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        <div>
                            <h3 class="text-lg font-semibold text-blue-800">Statistics</h3>
                            <p class="text-gray-700">{{ $stats['total_students'] }} Students | {{ $stats['subjects_count'] }} Subjects</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Student Results Table -->
            <div class="overflow-x-auto print:overflow-visible">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-blue-50 print:bg-white">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">Rank</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">Student</th>
                            @foreach($subjects as $subject)
                                <th class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">{{ $subject->name }}</th>
                            @endforeach
                            <th class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">Average %</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($annualReports as $report)
                        <tr class="hover:bg-gray-50 print:hover:bg-white">
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
                                    {{ $report['subject_totals'][$subject->id]['total'] ?? '-' }}
                                </td>
                            @endforeach
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                {{ $report['grand_total'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $report['average_percentage'] }}%
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Summary Section -->
            <div class="mt-8 bg-gray-50 p-4 rounded-lg print:bg-white">
                <h3 class="text-lg font-semibold mb-3">Performance Summary</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-white p-3 rounded shadow">
                        <h4 class="font-medium text-gray-700">Top Performers</h4>
                        <ol class="mt-2 space-y-1">
                            @foreach(array_slice($annualReports, 0, 3) as $top)
                            <li class="text-sm">{{ $top['rank'] }}. {{ $top['student']->user->name }} ({{ $top['grand_total'] }})</li>
                            @endforeach
                        </ol>
                    </div>
                    <div class="bg-white p-3 rounded shadow">
                        <h4 class="font-medium text-gray-700">Class Average</h4>
                        <p class="mt-2 text-2xl font-bold">
                            {{ number_format(collect($annualReports)->avg('average_percentage'), 2) }}%
                        </p>
                    </div>
                    <div class="bg-white p-3 rounded shadow">
                        <h4 class="font-medium text-gray-700">Subjects Summary</h4>
                        <p class="mt-2 text-sm">{{ $subjects->count() }} subjects across {{ $semesters->count() }} terms</p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<style>
    @media print {
        .print-hidden {
            display: none;
        }
        .print-only {
            display: block;
        }
        body {
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 4px;
            border: 1px solid #ddd;
        }
    }
    @media screen {
        .print-only {
            display: none;
        }
    }
</style>
@endsection