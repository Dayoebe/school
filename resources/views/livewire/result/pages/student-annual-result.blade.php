@extends('layouts.app', [
    'breadcrumbs' => [
        ['href' => route('dashboard'), 'text' => 'Dashboard'],
        ['href' => route('result.annual'), 'text' => 'Annual Results'],
        ['text' => 'Student Annual Result']
    ]
])

@section('title', 'Student Annual Result')

@section('page_heading', 'Student Annual Result')

@section('content')
<div class="container mx-auto px-4 py-6" x-data="studentAnnualResult">
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <!-- Student Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <div class="flex items-center">
                <img class="h-16 w-16 rounded-full mr-4" src="{{ $studentRecord->user->profile_photo_url }}" alt="Student Photo">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">{{ $studentRecord->user->name }}</h2>
                    <p class="text-gray-600">{{ $studentRecord->myClass->name ?? 'N/A' }} - {{ $academicYear->name }}</p>
                </div>
            </div>
            <div class="flex gap-2">
                <button @click="window.print()" class="btn-primary">
                    <i class="fas fa-print mr-2"></i> Print
                </button>
                <a href="{{ route('result.annual.export.pdf', ['classId' => $studentRecord->my_class_id, 'academicYearId' => $academicYear->id]) }}" 
                   class="btn-secondary">
                    <i class="fas fa-file-pdf mr-2"></i> Export PDF
                </a>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <x-stat-card 
                value="{{ $averagePercentage }}%"
                label="Annual Average"
                color="blue"
                icon="percent" />
                
            <x-stat-card 
                value="{{ $grandTotal }}"
                label="Total Score"
                color="green"
                icon="calculator" />
                
            <x-stat-card 
                value="{{ $classPosition }}"
                label="Class Position"
                color="purple"
                icon="trophy" />
                
            <x-stat-card 
                value="{{ $semesters->count() }}"
                label="Terms"
                color="yellow"
                icon="calendar-alt" />
        </div>

        <!-- Results Table -->
        <div class="overflow-x-auto mb-6">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-blue-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">Subject</th>
                        @foreach($semesters as $semester)
                        <th class="px-6 py-3 text-center text-xs font-medium text-blue-800 uppercase tracking-wider">{{ $semester->name }}</th>
                        @endforeach
                        <th class="px-6 py-3 text-center text-xs font-medium text-blue-800 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-blue-800 uppercase tracking-wider">Average</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($subjects as $subject)
                    @php
                        $subjectData = $annualResults[$subject->id] ?? null;
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $subject->name }}
                        </td>
                        @foreach($semesters as $semester)
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900">
                            @if($subjectData)
                                {{ $subjectData['results']->firstWhere('semester_id', $semester->id)->total_score ?? '-' }}
                            @else
                                -
                            @endif
                        </td>
                        @endforeach
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-bold text-gray-900">
                            {{ $subjectData['total'] ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900">
                            @if($subjectData)
                                {{ number_format($subjectData['average'], 1) }}%
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">Totals</th>
                        @foreach($semesters as $semester)
                        <th class="px-6 py-3 text-center text-xs font-medium text-blue-800 uppercase tracking-wider">
                            {{ $annualResults->sum(fn($item) => $item['results']->firstWhere('semester_id', $semester->id)->total_score ?? 0) }}
                        </th>
                        @endforeach
                        <th class="px-6 py-3 text-center text-xs font-bold text-blue-800 uppercase tracking-wider">
                            {{ $grandTotal }}
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-blue-800 uppercase tracking-wider">
                            {{ $averagePercentage }}%
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Performance Summary -->
        <div class="bg-gray-50 p-6 rounded-lg mb-6">
            <h3 class="text-lg font-semibold text-blue-800 mb-4">Performance Summary</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="font-medium text-gray-700 mb-2">Best Subjects</h4>
                    <div class="space-y-2">
                        @foreach($annualResults->sortByDesc('average')->take(3) as $subject)
                        <div class="flex justify-between items-center bg-green-50 p-3 rounded">
                            <span class="font-medium">{{ $subject['subject']->name }}</span>
                            <span class="font-bold text-green-700">{{ number_format($subject['average'], 1) }}%</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div>
                    <h4 class="font-medium text-gray-700 mb-2">Need Improvement</h4>
                    <div class="space-y-2">
                        @foreach($annualResults->sortBy('average')->take(3) as $subject)
                        <div class="flex justify-between items-center bg-red-50 p-3 rounded">
                            <span class="font-medium">{{ $subject['subject']->name }}</span>
                            <span class="font-bold text-red-700">{{ number_format($subject['average'], 1) }}%</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Comments Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="border border-gray-200 p-4 rounded-lg">
                <h4 class="font-bold text-blue-800 border-b border-blue-200 pb-2 mb-3">Class Teacher's Comment</h4>
                <p class="text-gray-700 italic">Overall performance analysis and recommendations for the academic year...</p>
            </div>
            <div class="border border-gray-200 p-4 rounded-lg">
                <h4 class="font-bold text-blue-800 border-b border-blue-200 pb-2 mb-3">Principal's Comment</h4>
                <p class="text-gray-700 italic">Annual academic summary and commendations...</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('studentAnnualResult', () => ({
        printReport() {
            window.print();
        }
    }));
});
</script>
@endpush
@endsection