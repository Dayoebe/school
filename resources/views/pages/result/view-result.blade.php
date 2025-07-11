@extends('layouts.app', [
    'breadcrumbs' => [['href' => route('dashboard'), 'text' => 'Dashboard'], ['href' => route('result'), 'text' => 'Results'], ['text' => 'View Results', 'active' => true]],
])

@section('title', __('View Results'))

@section('page_heading', __('View Results'))

@section('content')
    <style>
        /* Custom styles for sticky columns */
        .sticky-col-header {
            position: sticky;
            left: 0;
            z-index: 10;
            background-color: #e0e7ff; /* Light indigo background for sticky header */
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1); /* Subtle shadow */
        }

        .sticky-col-cell {
            position: sticky;
            left: 0;
            z-index: 9; /* Slightly lower than header to allow header to overlap */
            background-color: #ffffff; /* White background for sticky cells */
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.05); /* Subtle shadow */
        }

        /* Ensure the background for alternating rows covers the sticky column */
        .sticky-col-cell.bg-blue-50 {
            background-color: #eef2ff; /* Tailwind blue-50 */
        }

        .sticky-col-cell:hover {
            background-color: #f3f4f6; /* Tailwind gray-50 on hover */
        }
    </style>

    <div class="space-y-6">
        <!-- Header Section -->
        <div
            class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-2xl shadow-xl p-6 transform transition duration-500 hover:scale-[1.01]">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="text-white">
                    <h1 class="text-2xl font-bold mb-2 flex items-center">
                        <i class="fas fa-chart-line mr-3 text-yellow-300 animate-pulse"></i>
                        @yield('page_heading')
                    </h1>
                    <p class="opacity-90">View and analyze student results by academic year, term, class, and subject</p>
                </div>
                <div class="mt-4 md:mt-0">
                    <div class="bg-blue-500/20 p-3 rounded-xl backdrop-blur-sm">
                        <p class="text-white flex items-center">
                            <i class="fas fa-info-circle mr-2 text-blue-200"></i>
                            Select criteria to view detailed results
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Selection Controls -->
        <div class="bg-white rounded-2xl shadow-xl p-6 transition-all duration-300 hover:shadow-2xl">
            <form action="{{ route('view-results') }}" method="GET" id="resultsForm">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Academic Year Selector -->
                    <div class="space-y-2">
                        <label class="flex text-sm font-medium text-gray-700 items-center">
                            <i class="fas fa-calendar mr-2 text-indigo-500"></i> Academic Year
                        </label>
                        <select name="academicYearId" id="academicYearId"
                            class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-sm">
                            <option value="">Select Year</option>
                            @foreach ($academicYears as $year)
                                <option value="{{ $year->id }}" {{ $academicYearId == $year->id ? 'selected' : '' }}>
                                    {{ $year->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Term Selector -->
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 flex items-center">
                            <i class="fas fa-calendar-week mr-2 text-indigo-500"></i> Term
                        </label>
                        <select name="semesterId" id="semesterId"
                            class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-sm"
                            {{ !$academicYearId ? 'disabled' : '' }}>
                            <option value="">Select Term</option>
                            @foreach ($semesters as $term)
                                <option value="{{ $term->id }}" {{ $semesterId == $term->id ? 'selected' : '' }}>
                                    {{ $term->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Class Selector -->
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 flex items-center">
                            <i class="fas fa-graduation-cap mr-2 text-indigo-500"></i> Class
                        </label>
                        <select name="classId" id="classId"
                            class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-sm">
                            <option value="">Select Class</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Subject Selector -->
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 flex items-center">
                            <i class="fas fa-book mr-2 text-indigo-500"></i> Subject
                        </label>
                        <select name="subjectId" id="subjectId"
                            class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-sm"
                            {{ !$classId ? 'disabled' : '' }}>
                            <option value="">Select Subject</option>
                            @foreach ($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ $subjectId == $subject->id ? 'selected' : '' }}>
                                    {{ $subject->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Mode Toggle and View Button -->
                <div class="flex flex-col sm:flex-row justify-between items-center mt-8 space-y-4 sm:space-y-0">
                    <div class="flex space-x-2">
                        <button type="button" onclick="setMode('subject')"
                            class="px-4 py-2 rounded-xl font-medium transition-all duration-300 flex items-center"
                            :class="mode === 'subject'
                                ?
                                'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg transform hover:scale-105' :
                                'bg-gray-100 text-gray-700 hover:bg-gray-200'">
                            <i class="fas fa-book mr-2"></i> Subject View
                        </button>
                        <button type="button" onclick="setMode('class')"
                            class="px-4 py-2 rounded-xl font-medium transition-all duration-300 flex items-center"
                            :class="mode === 'class'
                                ?
                                'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg transform hover:scale-105' :
                                'bg-gray-100 text-gray-700 hover:bg-gray-200'">
                            <i class="fas fa-users mr-2"></i> Class View
                        </button>
                        <input type="hidden" name="mode" id="modeInput" value="{{ $mode }}">
                    </div>
                    <button type="submit"
                        class="bg-gradient-to-r from-indigo-600 to-purple-700 hover:from-indigo-700 hover:to-purple-800 text-white font-medium py-3 px-8 rounded-xl transition-all duration-300 shadow-lg flex items-center transform hover:scale-105">
                        <i class="fas fa-eye mr-2"></i> View Results
                    </button>
                </div>
            </form>
        </div>

        <!-- Results Display -->
        @if ($academicYearId && $semesterId && $classId)
            <div class="bg-white rounded-2xl shadow-xl p-6 transition-all duration-500 animate-fade-in">
                <!-- Subject View -->
                @if ($mode === 'subject' && $subjectId)
                    <div>
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
                            <h2 class="text-xl font-bold text-gray-800 mb-3 md:mb-0 flex items-center">
                                <i class="fas fa-book mr-3 text-indigo-600"></i>
                                {{ $selectedSubject->name ?? 'Selected Subject' }} Results
                            </h2>
                            <div class="bg-indigo-50 px-4 py-2 rounded-lg text-indigo-700 flex items-center">
                                <i class="fas fa-calendar mr-2"></i>
                                {{ $academicYearName }} - {{ $semesterName }}
                            </div>
                        </div>

                        @if ($subjectResults->count() === 0)
                            <div class="text-center py-12">
                                <div class="inline-block bg-gray-100 rounded-full p-4 mb-4">
                                    <i class="fas fa-inbox text-4xl text-gray-400"></i>
                                </div>
                                <h3 class="text-xl font-medium text-gray-600 mb-2">No Results Found</h3>
                                <p class="text-gray-500">No results available for this subject in the selected period.</p>
                            </div>
                        @else
                            <div class="overflow-x-auto rounded-xl shadow-inner">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-indigo-50">
                                        <tr>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-indigo-700 uppercase tracking-wider sticky-col-header">
                                                Student
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-center text-xs font-medium text-indigo-700 uppercase tracking-wider">
                                                CA1
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-center text-xs font-medium text-indigo-700 uppercase tracking-wider">
                                                CA2
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-center text-xs font-medium text-indigo-700 uppercase tracking-wider">
                                                CA3
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-center text-xs font-medium text-indigo-700 uppercase tracking-wider">
                                                CA4
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-center text-xs font-medium text-indigo-700 uppercase tracking-wider">
                                                Exam
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-center text-xs font-medium text-indigo-700 uppercase tracking-wider">
                                                Total
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-center text-xs font-medium text-indigo-700 uppercase tracking-wider">
                                                Grade
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-center text-xs font-medium text-indigo-700 uppercase tracking-wider">
                                                Comment
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-center text-xs font-medium text-indigo-700 uppercase tracking-wider">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach ($subjectResults as $result)
                                            <tr class="hover:bg-gray-50 transition-colors">
                                                <td class="px-6 py-4 whitespace-nowrap sticky-col-cell">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ optional($result->student)->user->name ?? 'N/A' }}
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        {{ optional($result->student)->admission_number ?? '' }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">
                                                    {{ $result->ca1_score ?? '-' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">
                                                    {{ $result->ca2_score ?? '-' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">
                                                    {{ $result->ca3_score ?? '-' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">
                                                    {{ $result->ca4_score ?? '-' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">
                                                    {{ $result->exam_score ?? '-' }}
                                                </td>
                                                <td
                                                    class="px-6 py-4 whitespace-nowrap text-sm text-center font-bold
                                                    @php
                                                        $totalScore = $result->total_score ?? 0;
                                                        if ($totalScore >= 75) echo 'text-green-600';
                                                        elseif ($totalScore >= 60) echo 'text-blue-600';
                                                        elseif ($totalScore >= 40) echo 'text-yellow-600';
                                                        else echo 'text-red-600';
                                                    @endphp
                                                ">
                                                    {{ $result->total_score ?? '-' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    @php
                                                        $gradeClass = [
                                                            'A1' => 'bg-green-100 text-green-800',
                                                            'B2' => 'bg-blue-100 text-blue-800',
                                                            'B3' => 'bg-blue-100 text-blue-800',
                                                            'C4' => 'bg-yellow-100 text-yellow-800',
                                                            'C5' => 'bg-yellow-100 text-yellow-800',
                                                            'C6' => 'bg-yellow-100 text-yellow-800',
                                                            'D7' => 'bg-orange-100 text-orange-800',
                                                            'E8' => 'bg-orange-100 text-orange-800',
                                                            'F9' => 'bg-red-100 text-red-800',
                                                        ][$result->grade ?? ''] ?? 'bg-gray-100 text-gray-800';
                                                    @endphp
                                                    <span
                                                        class="px-2 py-1 text-xs font-semibold rounded-full {{ $gradeClass }}">
                                                        {{ $result->grade ?? '-' }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-700">
                                                    {{ $result->teacher_comment ?? '-' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                    <div class="flex space-x-2 justify-center">
                                                        <!-- View Button -->
                                                        <button wire:click="goToView({{ $result->student->id }})"
                                                            class="text-white bg-gradient-to-r from-green-500 to-teal-600 hover:from-green-600 hover:to-teal-700 px-3 py-1.5 rounded-xl transition-all duration-300 transform hover:scale-105 flex items-center shadow text-xs">
                                                            <i class="fas fa-eye mr-1"></i> View
                                                        </button>

                                                        <!-- Print Button -->
                                                        <a href="{{ route('result.print', [
                                                            'student' => $result->student->id,
                                                            'academicYearId' => $academicYearId,
                                                            'semesterId' => $semesterId,
                                                        ]) }}"
                                                            target="_blank"
                                                            class="text-blue-600 hover:text-blue-900 transition-colors duration-200"
                                                            title="Print Results">
                                                            <i class="fas fa-print"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Class View -->
                @if ($mode === 'class')
                    <div>
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
                            <h2 class="text-xl font-bold text-gray-800 mb-3 md:mb-0 flex items-center">
                                <i class="fas fa-users mr-3 text-indigo-600"></i>
                                {{ $selectedClass->name ?? 'Selected Class' }} Results
                            </h2>
                            <div class="flex space-x-3">
                                <div class="bg-indigo-50 px-4 py-2 rounded-lg text-indigo-700 flex items-center">
                                    <i class="fas fa-calendar mr-2"></i>
                                    {{ $academicYearName }} - {{ $semesterName }}
                                </div>
                                <div class="bg-green-50 px-4 py-2 rounded-lg text-green-700 flex items-center">
                                    <i class="fas fa-user-graduate mr-2"></i>
                                    {{ $classResults->count() }} Students
                                </div>
                                <div class="bg-green-50 px-4 py-2 rounded-lg flex items-center">
                                    <a href="{{ route('result.print-class', [
                                        'academicYearId' => $academicYearId,
                                        'semesterId' => $semesterId,
                                        'classId' => $classId,
                                    ]) }}"
                                        target="_blank"
                                        class="text-white bg-gradient-to-r from-purple-600 to-indigo-700 hover:from-purple-700 hover:to-indigo-800 px-4 py-2 rounded-xl transition-all duration-300 transform hover:scale-105 flex items-center shadow">
                                        <i class="fas fa-print mr-2"></i> Print All Results
                                    </a>
                                </div>
                            </div>
                        </div>

                        @if ($classResults->count() === 0)
                            <div class="text-center py-12">
                                <div class="inline-block bg-gray-100 rounded-full p-4 mb-4">
                                    <i class="fas fa-inbox text-4xl text-gray-400"></i>
                                </div>
                                <h3 class="text-xl font-medium text-gray-600 mb-2">No Results Found</h3>
                                <p class="text-gray-500">No results available for this class in the selected period.</p>
                            </div>
                        @else
                            <div class="overflow-x-auto rounded-xl shadow-inner">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-indigo-50">
                                        <tr>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-indigo-700 uppercase tracking-wider sticky-col-header">
                                                Student
                                            </th>
                                            @foreach ($subjects as $subject)
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium text-indigo-700 uppercase tracking-wider">
                                                    {{ $subject->name }}
                                                </th>
                                            @endforeach
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-indigo-700 uppercase tracking-wider">
                                                Total
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-indigo-700 uppercase tracking-wider">
                                                Average
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-indigo-700 uppercase tracking-wider">
                                                Position
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-indigo-700 uppercase tracking-wider">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach ($classResults as $index => $student)
                                            <tr
                                                class="hover:bg-gray-50 transition-colors {{ $index % 2 === 0 ? 'bg-blue-50' : '' }}">
                                                <td
                                                    class="px-6 py-4 whitespace-nowrap sticky-col-cell {{ $index % 2 === 0 ? 'bg-blue-50' : '' }}">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ $student->user->name ?? 'N/A' }}
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        {{ $student->admission_number ?? '' }}
                                                    </div>
                                                </td>
                                                @foreach ($subjects as $subject)
                                                    @php
                                                        $result = $student->results->firstWhere(
                                                            'subject_id',
                                                            $subject->id,
                                                        );
                                                        $score = $result->total_score ?? null;
                                                        $grade = $result->grade ?? null;
                                                        $gradeClass = [
                                                            'A1' => 'bg-green-100 text-green-800',
                                                            'B2' => 'bg-blue-100 text-blue-800',
                                                            'B3' => 'bg-blue-100 text-blue-800',
                                                            'C4' => 'bg-yellow-100 text-yellow-800',
                                                            'C5' => 'bg-yellow-100 text-yellow-800',
                                                            'C6' => 'bg-yellow-100 text-yellow-800',
                                                            'D7' => 'bg-orange-100 text-orange-800',
                                                            'E8' => 'bg-orange-100 text-orange-800',
                                                            'F9' => 'bg-red-100 text-red-800',
                                                        ][$grade ?? ''] ?? 'bg-gray-100 text-gray-800';
                                                    @endphp
                                                    <td
                                                        class="px-6 py-4 whitespace-nowrap text-sm text-center font-medium">
                                                        @if (!is_null($score))
                                                            <div class="flex flex-col items-center">
                                                                <span class="font-bold
                                                                    @php
                                                                        if ($score >= 75) echo 'text-green-600';
                                                                        elseif ($score >= 60) echo 'text-blue-600';
                                                                        elseif ($score >= 40) echo 'text-yellow-600';
                                                                        else echo 'text-red-600';
                                                                    @endphp
                                                                ">
                                                                    {{ $score }}
                                                                </span>
                                                                <span class="px-1 py-0.5 text-xs font-semibold rounded-full {{ $gradeClass }} mt-1">
                                                                    {{ $grade }}
                                                                </span>
                                                            </div>
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                @endforeach
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-center">
                                                    {{ $student->total_score ?? '-' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                    @php
                                                        $avgClass = match(true) {
                                                            $student->average_score >= 75 => 'bg-green-100 text-green-800',
                                                            $student->average_score >= 60 => 'bg-blue-100 text-blue-800',
                                                            $student->average_score >= 40 => 'bg-yellow-100 text-yellow-800',
                                                            default => 'bg-red-100 text-red-800'
                                                        };
                                                    @endphp
                                                    <span class="px-2 py-1 rounded-full font-medium {{ $avgClass }}">
                                                        {{ $student->average_score ? number_format($student->average_score, 1) : '-' }}%
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                    <span
                                                        class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-indigo-100 text-indigo-800 font-bold">
                                                        {{ $student->position ?? '-' }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                    <div class="flex space-x-2 justify-center">
                                                        <!-- View Button -->
                                                        <a href="{{ route('view-results', [
                                                            'academicYearId' => $academicYearId,
                                                            'semesterId' => $semesterId,
                                                            'classId' => $classId,
                                                            'studentId' => $student->id,
                                                            'mode' => 'view',
                                                        ]) }}"
                                                            class="text-indigo-600 hover:text-indigo-900 transition-colors duration-200"
                                                            title="View Results">
                                                            <i class="fas fa-eye"></i>
                                                        </a>

                                                        <!-- Print Button -->
                                                        <a href="{{ route('result.print', [
                                                            'student' => $student->id,
                                                            'academicYearId' => $academicYearId,
                                                            'semesterId' => $semesterId,
                                                        ]) }}"
                                                            target="_blank"
                                                            class="text-blue-600 hover:text-blue-900 transition-colors duration-200"
                                                            title="Print Results">
                                                            <i class="fas fa-print"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                @endif
            </div>




 <!-- Add this section AFTER the existing table -->
 <div class="mt-8 bg-white rounded-2xl shadow-xl p-6">
    <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
        <i class="fas fa-chart-bar mr-3 text-indigo-600"></i>
        Subject Performance Analysis
    </h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($subjects as $subject)
            @php
                $scores = [];
                foreach ($classResults as $student) {
                    $result = $student->results->firstWhere('subject_id', $subject->id);
                    if ($result && $result->total_score !== null) {
                        $scores[] = $result->total_score;
                    }
                }
                $avg = count($scores) ? round(array_sum($scores) / count($scores), 1) : 0;
                $max = count($scores) ? max($scores) : 0;
                $min = count($scores) ? min($scores) : 0;
                
                $performanceClass = match(true) {
                    $avg >= 75 => 'border-green-500 bg-green-50',
                    $avg >= 60 => 'border-blue-500 bg-blue-50',
                    $avg >= 40 => 'border-yellow-500 bg-yellow-50',
                    default => 'border-red-500 bg-red-50'
                };
            @endphp
            
            <div class="border-l-4 {{ $performanceClass }} rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start">
                    <div>
                        <h4 class="font-bold text-gray-800">{{ $subject->name }}</h4>
                        <div class="mt-2 flex items-center">
                            <span class="bg-indigo-100 text-indigo-800 px-2 py-1 rounded-full text-xs">
                                <i class="fas fa-users mr-1"></i> {{ count($scores) }} students
                            </span>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-2xl font-bold @if($avg >= 75) text-green-600 @elseif($avg >= 60) text-blue-600 @elseif($avg >= 40) text-yellow-600 @else text-red-600 @endif">
                            {{ $avg }}
                        </span>
                        <div class="text-xs text-gray-500">Avg Score</div>
                    </div>
                </div>
                
                <div class="mt-4 grid grid-cols-2 gap-2">
                    <div class="bg-white p-2 rounded-lg text-center">
                        <div class="text-lg font-bold text-green-600">{{ $max }}</div>
                        <div class="text-xs text-gray-500">Highest</div>
                    </div>
                    <div class="bg-white p-2 rounded-lg text-center">
                        <div class="text-lg font-bold text-red-600">{{ $min }}</div>
                        <div class="text-xs text-gray-500">Lowest</div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <div class="flex justify-between text-xs text-gray-500 mb-1">
                        <span>0</span>
                        <span>100</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div class="h-2.5 rounded-full @if($avg >= 75) bg-green-500 @elseif($avg >= 60) bg-blue-500 @elseif($avg >= 40) bg-yellow-500 @else bg-red-500 @endif" 
                             style="width: {{ $avg }}%"></div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>







        @endif
    </div>

    <script>
        // DOM Elements
        const academicYearSelect = document.getElementById('academicYearId');
        const semesterSelect = document.getElementById('semesterId');
        const classSelect = document.getElementById('classId');
        const subjectSelect = document.getElementById('subjectId');

        // Initialize dropdowns based on current selections
        document.addEventListener('DOMContentLoaded', function() {
            // Enable semester dropdown if academic year is selected
            if (academicYearSelect.value) {
                semesterSelect.disabled = false;
            }

            // Enable subject dropdown if class is selected
            if (classSelect.value) {
                subjectSelect.disabled = false;
            }
        });

        // Academic Year Change Handler
        academicYearSelect.addEventListener('change', function() {
            // Enable semester dropdown
            semesterSelect.disabled = false;

            // Clear existing options
            semesterSelect.innerHTML = '<option value="">Select Term</option>';

            // Fetch semesters for selected academic year
            fetch(`/get-semesters?academic_year_id=${this.value}`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(semester => {
                        const option = document.createElement('option');
                        option.value = semester.id;
                        option.textContent = semester.name;
                        semesterSelect.appendChild(option);
                    });
                });
        });

        // Class Change Handler
        classSelect.addEventListener('change', function() {
            // Enable subject dropdown
            subjectSelect.disabled = false;

            // Clear existing options
            subjectSelect.innerHTML = '<option value="">Select Subject</option>';

            // Fetch subjects for selected class
            fetch(`/get-subjects?class_id=${this.value}`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(subject => {
                        const option = document.createElement('option');
                        option.value = subject.id;
                        option.textContent = subject.name;
                        subjectSelect.appendChild(option);
                    });
                });
        });

        // Mode toggle function
        function setMode(mode) {
            document.getElementById('modeInput').value = mode;

            // Update button styles
            const buttons = document.querySelectorAll('button[onclick^="setMode"]');
            buttons.forEach(btn => {
                if (btn.getAttribute('onclick').includes(mode)) {
                    btn.classList.add('bg-gradient-to-r', 'from-indigo-600', 'to-purple-600', 'text-white',
                        'shadow-lg', 'transform', 'hover:scale-105');
                    btn.classList.remove('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
                } else {
                    btn.classList.remove('bg-gradient-to-r', 'from-indigo-600', 'to-purple-600', 'text-white',
                        'shadow-lg', 'transform', 'hover:scale-105');
                    btn.classList.add('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
                }
            });
        }

        // Initialize mode buttons
        setMode('{{ $mode }}');
    </script>
@endsection
