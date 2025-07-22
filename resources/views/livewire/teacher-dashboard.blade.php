{{-- This file is a Livewire component view. It must have only ONE root HTML element. --}}
{{-- The layout is handled by the parent Blade view (e.g., dashboard.blade.php) --}}

<div x-data="{
    // Alpine.js only for UI state, Livewire handles data and core logic
    formatDate(dateString) {
        if (!dateString) return 'N/A';
        const options = { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: 'numeric', hour12: true };
        return new Date(dateString).toLocaleString('en-US', options);
    },
    // Helper functions for score calculation (replicated from Livewire for immediate client-side feedback)
    calculateTotal(student) {
        const ca1 = parseFloat(student.ca1_score) || 0;
        const ca2 = parseFloat(student.ca2_score) || 0;
        const ca3 = parseFloat(student.ca3_score) || 0;
        const ca4 = parseFloat(student.ca4_score) || 0;
        const exam = parseFloat(student.exam_score) || 0;

        const validCa1 = Math.min(Math.max(ca1, 0), 10);
        const validCa2 = Math.min(Math.max(ca2, 0), 10);
        const validCa3 = Math.min(Math.max(ca3, 0), 10);
        const validCa4 = Math.min(Math.max(ca4, 0), 10);
        const validExam = Math.min(Math.max(exam, 0), 60);

        return validCa1 + validCa2 + validCa3 + validCa4 + validExam;
    },
    calculateGrade(total) {
        if (total >= 75) return 'A1';
        if (total >= 70) return 'B2';
        if (total >= 65) return 'B3';
        if (total >= 60) return 'C4';
        if (total >= 55) return 'C5';
        if (total >= 50) return 'C6';
        if (total >= 45) return 'D7';
        if (total >= 40) return 'E8';
        return 'F9';
    },
}" class="min-h-screen bg-gray-50 p-4 sm:p-6 lg:p-8">
    <div class="max-w-7xl mx-auto">

        <!-- Header Section -->
        <header class="bg-white/80 backdrop-blur-sm p-6 rounded-2xl shadow-lg border border-gray-200 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="flex items-center space-x-4">
                <img src="{{ auth()->user()->profile_photo_url }}" alt="{{ auth()->user()->name }}" class="w-16 h-16 rounded-full border-4 border-white shadow-md" onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=E8F5E9&color=4CAF50'">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Welcome, {{ auth()->user()->first_name }}!</h1>
                    <p class="text-gray-600">Here's your summary for today.</p>
                </div>
            </div>
            <div class="flex items-center space-x-2 text-sm text-gray-500 bg-gray-100 px-4 py-2 rounded-full">
                <i class="fas fa-calendar-alt text-purple-500"></i>
                <span>{{ \App\Models\AcademicYear::find($academicYearId)->name ?? 'N/A' }}</span>
                <span class="text-gray-300">|</span>
                <i class="fas fa-flag text-blue-500"></i>
                <span>{{ \App\Models\Semester::find($semesterId)->name ?? 'N/A' }}</span>
            </div>
        </header>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 my-8">
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white p-6 rounded-2xl shadow-lg flex items-center justify-between">
                <div><p class="text-sm font-medium opacity-80">My Subjects</p><p class="text-4xl font-bold mt-1">{{ count($subjects) }}</p></div>
                <i class="fas fa-book-open text-5xl opacity-20"></i>
            </div>
            <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white p-6 rounded-2xl shadow-lg flex items-center justify-between">
                <div><p class="text-sm font-medium opacity-80">My Classes</p><p class="text-4xl font-bold mt-1">{{ count($teacherClasses) }}</p></div>
                <i class="fas fa-chalkboard-teacher text-5xl opacity-20"></i>
            </div>
            <div class="bg-gradient-to-br from-green-500 to-green-600 text-white p-6 rounded-2xl shadow-lg flex items-center justify-between">
                <div><p class="text-sm font-medium opacity-80">Upcoming Events</p><p class="text-4xl font-bold mt-1">{{ count($upcomingEvents) }}</p></div>
                <i class="fas fa-calendar-check text-5xl opacity-20"></i>
            </div>
        </div>

        <!-- Main Content with Tabs -->
        <main class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-gray-200">
            <!-- Tab Navigation -->
            <nav class="border-b border-gray-200">
                <div class="flex space-x-4 px-6 overflow-x-auto">
                    <button wire:click="$set('activeTab', 'subjects')" :class="$wire.activeTab === 'subjects' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="py-4 px-1 border-b-2 font-medium flex items-center space-x-2 transition whitespace-nowrap"><i class="fas fa-book"></i><span>My Subjects</span></button>
                    <button wire:click="$set('activeTab', 'classes')" :class="$wire.activeTab === 'classes' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="py-4 px-1 border-b-2 font-medium flex items-center space-x-2 transition whitespace-nowrap"><i class="fas fa-chalkboard"></i><span>My Classes</span></button>
                    <button wire:click="$set('activeTab', 'upload')" :class="$wire.activeTab === 'upload' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="py-4 px-1 border-b-2 font-medium flex items-center space-x-2 transition whitespace-nowrap"><i class="fas fa-upload"></i><span>Upload Results</span></button>
                    <button wire:click="$set('activeTab', 'view_results')" :class="$wire.activeTab === 'view_results' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="py-4 px-1 border-b-2 font-medium flex items-center space-x-2 transition whitespace-nowrap"><i class="fas fa-eye"></i><span>View Results</span></button>
                    {{-- Removed Analytics Tab --}}
                    <button wire:click="$set('activeTab', 'events')" :class="$wire.activeTab === 'events' ? 'border-gray-500 text-gray-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="py-4 px-1 border-b-2 font-medium flex items-center space-x-2 transition whitespace-nowrap"><i class="fas fa-calendar-alt"></i><span>Upcoming Events</span></button>
                </div>
            </nav>
            <div class="p-6">
                <!-- Subjects Tab Content -->
                <div x-show="$wire.activeTab === 'subjects'" class="animate__animated animate__fadeInUp">
                    <h3 class="text-xl font-semibold mb-4 text-gray-700">Subjects You Teach</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @forelse($subjects as $subject)
                            <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 hover:shadow-md hover:border-blue-300 transition-all duration-300 flex flex-col justify-between">
                                <div>
                                    <h4 class="font-bold text-lg text-gray-800">{{ $subject['name'] }}</h4>
                                    <p class="text-sm text-gray-500 mb-3">{{ $subject['my_class']['name'] ?? 'No Class Assigned' }}</p>
                                    <div class="flex items-center text-sm text-gray-600 bg-gray-100 px-3 py-1 rounded-full w-fit">
                                        <i class="fas fa-user-graduate mr-2 text-gray-400"></i>
                                        <span>{{ $subject['student_records_count'] }} students</span>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <button wire:click="openStudentsModal({{ $subject['id'] }})" class="w-full text-center px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition font-medium text-sm flex items-center justify-center space-x-2">
                                        <i class="fas fa-users"></i><span>View Students</span>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500 col-span-full text-center py-10">You are not assigned to any subjects yet.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Classes Tab Content -->
                <div x-show="$wire.activeTab === 'classes'" class="animate__animated animate__fadeInUp">
                    <h3 class="text-xl font-semibold mb-4 text-gray-700">Classes You Manage</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @forelse($teacherClasses as $classItem)
                            <div class="bg-purple-50 border border-purple-200 rounded-xl p-5">
                                <h4 class="font-bold text-lg text-purple-800">{{ $classItem['name'] }}</h4>
                                <p class="text-sm text-purple-600 mb-3">{{ $classItem['student_records_count'] ?? 'N/A' }} students</p>
                                <div class="border-t border-purple-200 pt-3 mt-3">
                                    <h5 class="font-semibold text-sm text-purple-700 mb-2">Subjects in this class:</h5>
                                    <ul class="space-y-1 text-sm text-purple-600">
                                        @php
                                            $classSubjects = collect($subjects)->where('my_class_id', $classItem['id']);
                                        @endphp
                                        @forelse($classSubjects as $subject)
                                            <li class="flex items-center"><i class="fas fa-chevron-right fa-xs mr-2"></i><span>{{ $subject['name'] }}</span></li>
                                        @empty
                                            <li>No subjects assigned to this class yet.</li>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500 col-span-full text-center py-10">You are not assigned to any classes yet.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Upload Results Tab Content -->
                <div x-show="$wire.activeTab === 'upload'" class="animate__animated animate__fadeInUp">
                    <h3 class="text-xl font-semibold mb-4 text-gray-700">Upload Student Results</h3>
                    <div class="bg-gray-50 p-6 rounded-xl border border-gray-200">
                        <div class="mb-4">
                            <label for="upload-subject-select" class="block text-sm font-medium text-gray-700 mb-1">Select Subject</label>
                            <select wire:model.live="uploadSubjectId" id="upload-subject-select" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">-- Please choose a subject --</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject['id'] }}">{{ $subject['name'] }} ({{ $subject['my_class']['name'] ?? 'N/A' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <button wire:click="openUploadModal()" :disabled="!$wire.uploadSubjectId || !$wire.academicYearId || !$wire.semesterId" class="w-full bg-red-600 text-white py-2 px-4 rounded-lg hover:bg-red-700 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center space-x-2">
                            <i class="fas fa-edit"></i><span>Enter Scores</span>
                        </button>
                        @if(!$academicYearId || !$semesterId)
                            <p class="text-xs text-red-600 mt-2">Cannot upload results because the academic year or term is not set. Please contact an administrator.</p>
                        @endif
                    </div>
                </div>

                <!-- View Results Tab Content -->
                <div x-show="$wire.activeTab === 'view_results'" class="animate__animated animate__fadeInUp">
                    <h3 class="text-xl font-semibold mb-4 text-gray-700">View Previous Results</h3>
                    <div class="bg-gray-50 p-6 rounded-xl border border-gray-200 mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="view-academic-year-select" class="block text-sm font-medium text-gray-700 mb-1">Academic Year</label>
                                <select wire:model.live="selectedAcademicYearForView" wire:change="filterSemestersForView(); fetchHistoricalResults()" id="view-academic-year-select" class="w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                                    <option value="">-- Select Academic Year --</option>
                                    @foreach($allAcademicYears as $year)
                                        <option value="{{ $year['id'] }}">{{ $year['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="view-semester-select" class="block text-sm font-medium text-gray-700 mb-1">Semester</label>
                                <select wire:model.live="selectedSemesterForView" wire:change="fetchHistoricalResults()" id="view-semester-select" :disabled="!$wire.selectedAcademicYearForView || $wire.semestersForSelectedYear.length === 0" class="w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <option value="">-- Select Semester --</option>
                                    @foreach($semestersForSelectedYear as $semester)
                                        <option value="{{ $semester['id'] }}">{{ $semester['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="view-subject-select" class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                                <select wire:model.live="selectedSubjectForView" wire:change="fetchHistoricalResults()" id="view-subject-select" class="w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                                    <option value="">-- All My Subjects --</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject['id'] }}">{{ $subject['name'] }} ({{ $subject['my_class']['name'] ?? 'N/A' }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        @if(!$selectedAcademicYearForView || !$selectedSemesterForView)
                            <p class="text-xs text-orange-600 mt-2">Please select both academic year and semester to view results.</p>
                        @endif
                    </div>

                    <div x-show="$wire.viewResultsLoading" class="text-center py-12"><i class="fas fa-spinner fa-spin text-3xl text-orange-500"></i><p class="mt-2 text-gray-600">Loading results...</p></div>
                    <div x-show="!$wire.viewResultsLoading && $wire.viewedResults.length > 0">
                        <div class="overflow-x-auto bg-white rounded-lg shadow border border-gray-200">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Position</th>
                                        <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                        <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                                        <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Class</th>
                                        <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">1st CA</th>
                                        <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">2nd CA</th>
                                        <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">3rd CA</th>
                                        <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">4th CA</th>
                                        <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Exam</th>
                                        <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                        <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Grade</th>
                                        <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Comment</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($viewedResults as $result)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-2 font-bold text-center">{{ $result['position'] }}</td>
                                            <td class="px-4 py-2 whitespace-nowrap"><div class="font-medium text-gray-900">{{ $result['student_name'] }}</div><div class="text-xs text-gray-500">{{ $result['admission_number'] }}</div></td>
                                            <td class="px-2 py-2">{{ $result['subject_name'] }}</td>
                                            <td class="px-2 py-2">{{ $result['class_name'] }}</td>
                                            <td class="px-2 py-2 text-center">{{ $result['ca1_score'] ?? 'N/A' }}</td>
                                            <td class="px-2 py-2 text-center">{{ $result['ca2_score'] ?? 'N/A' }}</td>
                                            <td class="px-2 py-2 text-center">{{ $result['ca3_score'] ?? 'N/A' }}</td>
                                            <td class="px-2 py-2 text-center">{{ $result['ca4_score'] ?? 'N/A' }}</td>
                                            <td class="px-2 py-2 text-center">{{ $result['exam_score'] ?? 'N/A' }}</td>
                                            <td class="px-2 py-2 text-center font-bold">{{ number_format($result['total_score'], 1) }}</td>
                                            <td class="px-2 py-2 text-center font-medium">{{ $result['grade'] }}</td>
                                            <td class="px-4 py-2">{{ $result['comment'] ?? 'No comment' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div x-show="!$wire.viewResultsLoading && $wire.viewedResults.length === 0 && $wire.selectedAcademicYearForView && $wire.selectedSemesterForView && $wire.selectedSubjectForView" class="text-center py-12 bg-gray-50 rounded-lg">
                        <i class="fas fa-exclamation-circle text-4xl text-gray-400 mb-3"></i>
                        <h4 class="text-gray-700">No Results Found</h4>
                        <p class="text-sm text-gray-500 mt-1">No results have been uploaded for the selected academic year, semester, and subject.</p>
                    </div>
                    <div x-show="!$wire.viewResultsLoading && $wire.viewedResults.length === 0 && $wire.selectedAcademicYearForView && $wire.selectedSemesterForView && !$wire.selectedSubjectForView" class="text-center py-12 bg-gray-50 rounded-lg">
                        <i class="fas fa-exclamation-circle text-4xl text-gray-400 mb-3"></i>
                        <h4 class="text-gray-700">No Results Found</h4>
                        <p class="text-sm text-gray-500 mt-1">No results have been uploaded for the selected academic year and semester across your assigned subjects.</p>
                    </div>
                    <div x-show="!$wire.viewResultsLoading && $wire.viewedResults.length === 0 && (!$wire.selectedAcademicYearForView || !$wire.selectedSemesterForView)" class="text-center py-12 bg-gray-50 rounded-lg">
                        <i class="fas fa-info-circle text-4xl text-gray-400 mb-3"></i>
                        <h4 class="text-gray-700">Select Options</h4>
                        <p class="text-sm text-gray-500 mt-1">Please select an academic year and semester to view results.</p>
                    </div>
                </div>

                {{-- Removed Analytics Content --}}

                <!-- Upcoming Events Tab Content -->
                <div x-show="$wire.activeTab === 'events'" class="animate__animated animate__fadeInUp">
                    <h3 class="text-xl font-semibold mb-4 text-gray-700">Your Upcoming Events</h3>
                    <div class="space-y-4">
                        {{-- @forelse($upcomingEvents as $event)
                            <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 flex items-center space-x-4">
                                @if($event['type'] == 'exam')
                                    <div class="flex-shrink-0 bg-red-100 text-red-600 rounded-full p-3"><i class="fas fa-file-alt text-xl"></i></div>
                                @elseif($event['type'] == 'meeting')
                                    <div class="flex-shrink-0 bg-blue-100 text-blue-600 rounded-full p-3"><i class="fas fa-users text-xl"></i></div>
                                @else
                                    <div class="flex-shrink-0 bg-green-100 text-green-600 rounded-full p-3"><i class="fas fa-calendar-day text-xl"></i></div>
                                @endif
                                <div>
                                    <p class="font-semibold text-gray-800">{{ $event['title'] }}</p>
                                    <p class="text-sm text-gray-600">{{ $event['date']->format('M d, Y') }} at {{ $event['date']->format('h:i A') }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500 text-center py-10">No upcoming events at the moment.</p>
                        @endforelse --}}
                    </div>
                </div>

            </div>
        </main>
    </div>

    <!-- Modals (Students, Upload Results) -->
    <!-- Student List Modal -->
    <div x-show="$wire.showStudentsModal" class="fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full z-50 flex justify-center items-center" x-transition.opacity>
        <div @click.away="$wire.showStudentsModal = false" class="relative p-8 bg-white w-full max-w-2xl mx-auto rounded-lg shadow-xl animate__animated animate__zoomIn">
            <h3 class="text-2xl font-bold mb-4 text-gray-800">Students in {{ $currentSubjectName }}</h3>
            <div class="mb-4">
                <input type="text" wire:model.live.debounce.300ms="studentSearch" placeholder="Search students..." class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            @if ($loading)
                <div class="text-center py-10"><i class="fas fa-spinner fa-spin text-3xl text-blue-500"></i><p class="mt-2 text-gray-600">Loading students...</p></div>
            @else
                @if ($studentList->isEmpty())
                    <p class="text-gray-500 text-center py-10">No students found for this subject.</p>
                @else
                    <div class="max-h-96 overflow-y-auto border border-gray-200 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 sticky top-0">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Admission No.</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Class</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Section</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($studentList as $student)
                                    <tr>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $student['admission_number'] }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $student['user']['name'] }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $student['my_class']['name'] }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $student['section']['name'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @endif
            <div class="mt-6 flex justify-end">
                <button @click="$wire.showStudentsModal = false" class="px-6 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition font-medium">Close</button>
            </div>
        </div>
    </div>

    <!-- Upload Results Modal -->
    <div x-show="$wire.showUploadModal" class="fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full z-50 flex justify-center items-center" x-transition.opacity>
        <div @click.away="$wire.showUploadModal = false" class="relative p-8 bg-white w-full max-w-4xl mx-auto rounded-lg shadow-xl animate__animated animate__zoomIn">
            <h3 class="text-2xl font-bold mb-4 text-gray-800">Enter Results for {{ $currentSubjectName }}</h3>

            @if (session()->has('message'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                    <p class="font-bold">Success!</p>
                    <p>{{ session('message') }}</p>
                </div>
            @endif

            @error('studentResults.*.*')
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                    <p class="font-bold">Error!</p>
                    <p>There was an issue with one or more of your entered scores. Please check and try again.</p>
                    <ul class="mt-2 list-disc list-inside text-sm">
                        @foreach($errors->get('studentResults.*.*') as $message)
                            <li>{{ $message[0] }}</li>
                        @endforeach
                    </ul>
                </div>
            @enderror

            <div class="mb-4 flex space-x-4">
                <div class="flex-1">
                    <label for="principal_comment" class="block text-sm font-medium text-gray-700 mb-1">Principal's Comment</label>
                    <input type="text" wire:model.live="principalComment" id="principal_comment" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Enter principal's overall comment">
                    @error('principalComment') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div class="flex-1">
                    <label for="overall_teacher_comment" class="block text-sm font-medium text-gray-700 mb-1">Overall Teacher's Comment</label>
                    <input type="text" wire:model.live="overallTeacherComment" id="overall_teacher_comment" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Enter overall teacher's comment">
                    @error('overallTeacherComment') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="mb-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Total Days Present</label>
                    <input type="number" wire:model.live="presentDays" class="w-full rounded-md border-gray-300 shadow-sm" placeholder="Days Present">
                    @error('presentDays') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Total Days Absent</label>
                    <input type="number" wire:model.live="absentDays" class="w-full rounded-md border-gray-300 shadow-sm" placeholder="Days Absent">
                    @error('absentDays') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <h4 class="text-lg font-semibold mb-3 text-gray-700">Psychomotor Traits (Scores out of 5)</h4>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-4">
                @foreach($psychomotorScores as $trait => $score)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ $trait }}</label>
                        <input type="number" wire:model.live="psychomotorScores.{{ $trait }}" min="0" max="5" class="w-full rounded-md border-gray-300 shadow-sm">
                        @error('psychomotorScores.' . $trait) <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                @endforeach
            </div>

            <h4 class="text-lg font-semibold mb-3 text-gray-700">Affective Traits (Scores out of 5)</h4>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-4">
                @foreach($affectiveScores as $trait => $score)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ $trait }}</label>
                        <input type="number" wire:model.live="affectiveScores.{{ $trait }}" min="0" max="5" class="w-full rounded-md border-gray-300 shadow-sm">
                        @error('affectiveScores.' . $trait) <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                @endforeach
            </div>

            <h4 class="text-lg font-semibold mb-3 text-gray-700">Co-Curricular Activities (Scores out of 5)</h4>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-6">
                @foreach($coCurricularScores as $activity => $score)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ $activity }}</label>
                        <input type="number" wire:model.live="coCurricularScores.{{ $activity }}" min="0" max="5" class="w-full rounded-md border-gray-300 shadow-sm">
                        @error('coCurricularScores.' . $activity) <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                @endforeach
            </div>

            <div x-show="$wire.loading" class="text-center py-10"><i class="fas fa-spinner fa-spin text-3xl text-blue-500"></i><p class="mt-2 text-gray-600">Loading student list...</p></div>

            <div x-show="!$wire.loading && $wire.studentResults.length > 0">
                <div class="overflow-x-auto bg-white rounded-lg shadow border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student Name</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">1st CA (10)</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">2nd CA (10)</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">3rd CA (10)</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">4th CA (10)</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Exam (60)</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total (100)</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grade</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comment</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($studentResults as $index => $studentResult)
                                <tr>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $studentResult['student_name'] }}</td>
                                    <td class="px-2 py-2">
                                        <input type="number" wire:model.live="studentResults.{{ $index }}.ca1_score" min="0" max="10" class="w-20 rounded-md border-gray-300 shadow-sm text-center text-sm">
                                    </td>
                                    <td class="px-2 py-2">
                                        <input type="number" wire:model.live="studentResults.{{ $index }}.ca2_score" min="0" max="10" class="w-20 rounded-md border-gray-300 shadow-sm text-center text-sm">
                                    </td>
                                    <td class="px-2 py-2">
                                        <input type="number" wire:model.live="studentResults.{{ $index }}.ca3_score" min="0" max="10" class="w-20 rounded-md border-gray-300 shadow-sm text-center text-sm">
                                    </td>
                                    <td class="px-2 py-2">
                                        <input type="number" wire:model.live="studentResults.{{ $index }}.ca4_score" min="0" max="10" class="w-20 rounded-md border-gray-300 shadow-sm text-center text-sm">
                                    </td>
                                    <td class="px-2 py-2">
                                        <input type="number" wire:model.live="studentResults.{{ $index }}.exam_score" min="0" max="60" class="w-20 rounded-md border-gray-300 shadow-sm text-center text-sm">
                                    </td>
                                    <td class="px-2 py-2 text-center font-bold text-sm">{{ $this->calculateTotal($studentResult) }}</td>
                                    <td class="px-2 py-2 text-center font-bold text-sm">{{ $this->calculateGrade($this->calculateTotal($studentResult)) }}</td>
                                    <td class="px-4 py-2">
                                        <input type="text" wire:model.live="studentResults.{{ $index }}.comment" class="w-full rounded-md border-gray-300 shadow-sm text-sm" placeholder="Teacher's comment">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div x-show="!$wire.loading && $wire.studentResults.length === 0" class="text-center py-10">
                <p class="text-gray-500">No students available for this subject or current academic year/semester.</p>
            </div>

            <div class="mt-6 flex justify-end space-x-4">
                <button @click="$wire.showUploadModal = false" class="px-6 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition font-medium">Cancel</button>
                <button wire:click="saveResults" wire:loading.attr="disabled" :disabled="$wire.loading || $wire.isSaving" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium flex items-center space-x-2">
                    <span wire:loading wire:target="saveResults"><i class="fas fa-spinner fa-spin"></i></span>
                    <span>{{ $isSaving ? 'Saving...' : 'Save Results' }}</span>
                </button>
            </div>
        </div>
    </div>
</div>
