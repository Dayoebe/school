@extends('layouts.app', [
    'breadcrumbs' => [['href' => route('dashboard'), 'text' => 'Dashboard'], ['href' => route('result'), 'text' => 'Results'], ['href' => route('result.annual'), 'text' => 'Class Annual Results', 'active' => true]],
])
@section('title', __('Class Results'))
@section('page_heading', __('Class result Summary'))

@section('content')
    @push('css')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @endpush

    <div class="container mx-auto px-4 py-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-xl font-bold text-blue-800 mb-4">Filter Results</h2>
            <form method="GET" action="{{ route('result.class') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Academic Year</label>
                    <select name="academicYearId" class="w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">Select Academic Year</option>
                        @foreach ($academicYears as $year)
                            <option value="{{ $year->id }}"
                                {{ request('academicYearId') == $year->id ? 'selected' : '' }}>
                                {{ $year->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Semester</label>
                    <select name="semesterId" class="w-full rounded-md border-gray-300 shadow-sm">
                        @foreach ($semesters as $sem)
                            <option value="{{ $sem->id }}" {{ $sem->id == $semester->id ? 'selected' : '' }}>
                                {{ $sem->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Class</label>
                    <select name="classId" class="w-full rounded-md border-gray-300 shadow-sm" required>
                        <option value="">Select Class</option>
                        @foreach ($classes as $c)
                            <option value="{{ $c->id }}" {{ request('classId') == $c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-3">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                        <i class="fas fa-filter mr-2"></i> Filter Results
                    </button>
                </div>
            </form>
        </div>

        @if ($showResults)
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <h1 class="text-2xl font-bold text-blue-800 mb-4">Class Result Overview</h1>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                        <div class="flex items-center">
                            <i class="fas fa-users text-blue-600 text-2xl mr-3"></i>
                            <div>
                                <h3 class="text-lg font-semibold text-blue-800">Class</h3>
                                <p class="text-gray-700">{{ $class->name }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                        <div class="flex items-center">
                            <i class="fas fa-calendar-alt text-blue-600 text-2xl mr-3"></i>
                            <div>
                                <h3 class="text-lg font-semibold text-blue-800">Academic Period</h3>
                                <p class="text-gray-700">{{ $academicYear->name }} - {{ $semester->name }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                        <div class="flex items-center">
                            <i class="fas fa-chart-bar text-blue-600 text-2xl mr-3"></i>
                            <div>
                                <h3 class="text-lg font-semibold text-blue-800">Statistics</h3>
                                <p class="text-gray-700">{{ $classStats['total_students'] }} Students |
                                    {{ $classStats['subjects_count'] }} Subjects</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap gap-4 mb-6">
                    <a href="{{ route('result.class.print', ['classId' => $class->id, 'academicYearId' => $academicYear->id, 'semesterId' => $semester->id]) }}"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg flex items-center">
                        <i class="fas fa-print mr-2"></i> Print All Results
                    </a>
                    <a href="{{ route('result.class.export', ['classId' => $class->id, 'academicYearId' => $academicYear->id, 'semesterId' => $semester->id]) }}"
                        class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-lg flex items-center">
                        <i class="fas fa-file-excel mr-2"></i> Export to Excel
                    </a>
                </div>
                @if (isset($studentReports) && count($studentReports) > 0)
                    <div class="bg-yellow-50 p-4 rounded-lg mb-6">
                        <p class="text-yellow-600">
                            <i class="fas fa-info-circle mr-2"></i>
                            Click on a student's name to view detailed results.
                        </p>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                        <h2 class="text-xl font-bold text-blue-800 mb-4">Class Performance Summary</h2>
                        @if (count($studentReports) > 0)
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div class="bg-blue-50 p-4 rounded-lg text-center">
                                    <p class="text-sm text-blue-600 font-semibold">Top Student</p>
                                    <p class="text-lg font-bold">
                                        {{ $studentReports[0]['student']->user->name ?? 'N/A' }}
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        {{ $studentReports[0]['total_score'] ?? 0 }}/{{ $classStats['max_total_score'] }}
                                        ({{ $studentReports[0]['percentage'] ?? 0 }}%)
                                    </p>
                                </div>
                                <div class="bg-green-50 p-4 rounded-lg text-center">
                                    <p class="text-sm text-green-600 font-semibold">Average Score</p>
                                    <p class="text-lg font-bold">
                                        {{ number_format(collect($studentReports)->avg('total_score'), 1) }}/{{ $classStats['max_total_score'] }}
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        {{ number_format(collect($studentReports)->avg('percentage'), 1) }}%
                                    </p>
                                </div>
                                <div class="bg-yellow-50 p-4 rounded-lg text-center">
                                    <p class="text-sm text-yellow-600 font-semibold">Pass Rate</p>
                                    <p class="text-lg font-bold">
                                        @php
                                            $passCount = collect($studentReports)
                                                ->filter(fn($r) => $r['percentage'] >= 40)
                                                ->count();
                                            $passRate =
                                                $classStats['total_students'] > 0
                                                    ? ($passCount / $classStats['total_students']) * 100
                                                    : 0;
                                        @endphp
                                        {{ number_format($passRate, 1) }}%
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        {{ $passCount }} of {{ $classStats['total_students'] }} students
                                    </p>
                                </div>
                                <div class="bg-red-50 p-4 rounded-lg text-center">
                                    <p class="text-sm text-red-600 font-semibold">Bottom Student</p>
                                    <p class="text-lg font-bold">
                                        {{ $studentReports[count($studentReports) - 1]['student']->user->name ?? 'N/A' }}
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        {{ $studentReports[count($studentReports) - 1]['total_score'] ?? 0 }}/{{ $classStats['max_total_score'] }}
                                        ({{ $studentReports[count($studentReports) - 1]['percentage'] ?? 0 }}%)
                                    </p>
                                </div>
                            </div>
                        @else
                            <div class="bg-yellow-50 p-4 rounded-lg text-center">
                                <p class="text-yellow-600">No student results found for the selected criteria</p>
                            </div>
                        @endif
                    </div>
                    @if (count($subjectStats) > 0)
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                            <h2 class="text-xl font-bold text-blue-800 mb-4">Subject-wise Performance</h2>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-blue-50">
                                        <tr>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">
                                                Subject</th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">
                                                Average</th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">
                                                Highest</th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">
                                                Lowest</th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">
                                                Pass Rate</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        @foreach ($subjectStats as $stat)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">{{ $stat['subject']->name }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    {{ number_format($stat['average'], 1) }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap">{{ $stat['highest'] }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap">{{ $stat['lowest'] }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    {{ number_format($stat['pass_rate'], 1) }}%</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h2 class="text-xl font-bold text-blue-800 mb-4">Individual Student Results</h2>
                        @if ($showResults && isset($students) && count($studentReports) > 0)
                            <div class="mb-4">
                                {{ $students->links() }}
                            </div>
                        @endif
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-blue-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">
                                            Rank</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">
                                            Student</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">
                                            Total Score</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">
                                            Percentage</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">
                                            Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($studentReports as $report)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                                {{ $report['rank'] }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10">
                                                        <img class="h-10 w-10 rounded-full"
                                                            src="{{ $report['student']->user->profile_photo_url ?? asset('images/default-avatar.jpg') }}"
                                                            alt="Student photo">
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ $report['student']->user->name }}</div>
                                                        <div class="text-sm text-gray-500">
                                                            {{ $report['student']->admission_number }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $report['total_score'] }}/{{ $classStats['max_total_score'] }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="w-full bg-gray-200 rounded-full h-2.5 mr-2">
                                                        <div class="bg-blue-600 h-2.5 rounded-full"
                                                            style="width: {{ min($report['percentage'], 100) }}%"></div>
                                                    </div>
                                                    <span
                                                        class="text-sm font-medium text-gray-700">{{ $report['percentage'] }}%</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <button onclick="openStudentModal({{ $report['student']->id }})"
                                                    class="text-blue-600 hover:text-blue-900 mr-3">
                                                    <i class="fas fa-eye mr-1"></i> View
                                                </button>
                                                <a href="{{ route('result.print', [
                                                    'student' => $report['student']->id,
                                                    'academicYearId' => $academicYear->id,
                                                    'semesterId' => $semester->id,
                                                ]) }}"
                                                    class="text-green-600 hover:text-green-900">
                                                    <i class="fas fa-download mr-1"></i> Download
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            {{ $students->links() }}
                        </div>
                    @else
                        <div class="bg-yellow-50 p-4 rounded-lg text-center">
                            <p class="text-yellow-600">No student results found for the selected criteria</p>
                        </div>
                @endif
            </div>
    </div>
    @if (!empty($studentReports) && count($studentReports) > 0)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-xl font-bold text-blue-800 mb-4">Performance Distribution</h2>
            <canvas id="performanceChart" height="100"></canvas>
        </div>
    @endif
    @foreach ($studentReports as $report)
        <div id="modal-{{ $report['student']->id }}"
            class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            <i class="fas fa-user-graduate mr-2"></i> {{ $report['student']->user->name }}'s Results
                        </h3>
                        <button onclick="closeModal({{ $report['student']->id }})"
                            class="text-gray-400 hover:text-gray-500">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-blue-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">
                                        Subject</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">
                                        Test</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">
                                        Exam</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">
                                        Total</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">
                                        Position</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($subjects as $subject)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $subject->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ $report['results'][$subject->id]->test_score ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ $report['results'][$subject->id]->exam_score ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ $report['results'][$subject->id]->total_score ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ $report['results'][$subject->id]->subject_position ?? '-' }}</td>
                                    </tr>
                                @endforeach
                                <tr class="bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap font-bold">Total</td>
                                    <td colspan="3" class="px-6 py-4 whitespace-nowrap font-bold">
                                        {{ $report['total_score'] }}/{{ $classStats['max_total_score'] }}
                                        ({{ $report['percentage'] }}%)
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap font-bold">
                                        {{ $report['rank'] }} of {{ $classStats['total_students'] }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="items-center px-4 py-3 mt-4 text-right">
                        <button onclick="closeModal({{ $report['student']->id }})"
                            class="px-4 py-2 bg-blue-500 text-white text-base font-medium rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                            <i class="fas fa-times mr-1"></i> Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
    @endif
    <div class="bg-blue-50 p-4 rounded-lg text-center">
        <p class="text-blue-600"><i class="fas fa-info-circle mr-2"></i> Please select filters to view results</p>
    </div>
    </div>
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            function openStudentModal(studentId) {
                document.getElementById(`modal-${studentId}`).classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }

            function closeModal(studentId) {
                document.getElementById(`modal-${studentId}`).classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
            @if ($showResults && count($studentReports) > 0)
                document.addEventListener('DOMContentLoaded', function() {
                    const ctx = document.getElementById('performanceChart').getContext('2d');
                    const performanceChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: {!! json_encode(collect($studentReports)->pluck('student.user.name')) !!},
                            datasets: [{
                                label: 'Percentage Score',
                                data: {!! json_encode(collect($studentReports)->pluck('percentage')) !!},
                                backgroundColor: 'rgba(59, 130, 246, 0.5)',
                                borderColor: 'rgba(59, 130, 246, 1)',
                                borderWidth: 1
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
                                        text: 'Percentage Score'
                                    }
                                },
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Students'
                                    }
                                }
                            }
                        }
                    });
                });
            @endif
        </script>
    @endpush
@endsection
