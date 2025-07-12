@php
    $breadcrumbs = [
        ['href'=> route('dashboard'), 'text'=> 'Dashboard', 'active' => true],
    ];
@endphp

@extends('layouts.app')

@section('title', __('Teacher Dashboard'))
@section('page_heading', 'Dashboard')

@section('content')
<div x-data="teacherDashboard()" x-init="init()" class="min-h-screen bg-gradient-to-br from-blue-50 to-purple-50 p-4">
    <!-- Header Section -->
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 p-6 bg-white rounded-xl shadow-md">
            <!-- Teacher Profile -->
            <div class="flex items-center space-x-4">
                <div class="relative">
                    <img 
                        src="{{ auth()->user()->profile_photo_url }}" 
                        alt="{{ auth()->user()->name }}"
                        class="w-16 h-16 rounded-full border-4 border-white shadow"
                        onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=random'"
                    >
                    <span class="absolute bottom-0 right-0 bg-green-500 rounded-full w-4 h-4 border-2 border-white"></span>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Welcome, {{ auth()->user()->name }}</h1>
                    <div class="flex items-center space-x-2 text-sm text-gray-600">
                        <span>Teacher</span>
                        <span class="w-1 h-1 bg-gray-400 rounded-full"></span>
                        <span>{{ $academicYear?->name ?? 'N/A' }}</span>
                        <span class="w-1 h-1 bg-gray-400 rounded-full"></span>
                        <span>{{ $semester?->name ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="flex space-x-2">
                <button @click="activeTab = 'upload'" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-upload mr-2"></i> Upload Results
                </button>
                <button class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                    <i class="fas fa-calendar-alt mr-2"></i> View Calendar
                </button>
            </div>
        </div>

        <!-- Dashboard Tabs -->
        <div class="mt-6 bg-white rounded-xl shadow-md overflow-hidden">
            <div class="border-b border-gray-200">
                <nav class="flex -mb-px overflow-x-auto">
                    <button @click="activeTab = 'overview'" 
                            :class="{'border-blue-500 text-blue-600': activeTab === 'overview', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'overview'}" 
                            class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm flex items-center">
                        <i class="fas fa-tachometer-alt mr-2"></i> Overview
                    </button>
                    <button @click="activeTab = 'classes'" 
                            :class="{'border-blue-500 text-blue-600': activeTab === 'classes', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'classes'}" 
                            class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm flex items-center">
                        <i class="fas fa-chalkboard mr-2"></i> My Classes
                    </button>
                    <button @click="activeTab = 'subjects'" 
                            :class="{'border-blue-500 text-blue-600': activeTab === 'subjects', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'subjects'}" 
                            class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm flex items-center">
                        <i class="fas fa-book-open mr-2"></i> My Subjects
                    </button>
                    <button @click="activeTab = 'students'" 
                            :class="{'border-blue-500 text-blue-600': activeTab === 'students', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'students'}" 
                            class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm flex items-center">
                        <i class="fas fa-users mr-2"></i> My Students
                    </button>
                    <button @click="activeTab = 'results'" 
                            :class="{'border-blue-500 text-blue-600': activeTab === 'results', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'results'}" 
                            class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm flex items-center">
                        <i class="fas fa-chart-bar mr-2"></i> View Results
                    </button>
                    <button @click="activeTab = 'upload'" 
                            :class="{'border-blue-500 text-blue-600': activeTab === 'upload', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'upload'}" 
                            class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm flex items-center">
                        <i class="fas fa-upload mr-2"></i> Upload Results
                    </button>
                </nav>
            </div>

            <!-- Tab Contents -->
            <div class="p-6">
                <!-- Overview Tab -->
                <div x-show="activeTab === 'overview'" x-transition>
                    <!-- Stats Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-6 rounded-xl shadow">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium">My Subjects</p>
                                    <p class="text-3xl font-bold mt-2">{{ $teacherSubjects->count() }}</p>
                                </div>
                                <i class="fas fa-book-open text-2xl opacity-70"></i>
                            </div>
                        </div>
                        
                        <div class="bg-gradient-to-r from-green-500 to-green-600 text-white p-6 rounded-xl shadow">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium">My Classes</p>
                                    <p class="text-3xl font-bold mt-2">{{ $teacherClasses->count() }}</p>
                                </div>
                                <i class="fas fa-users text-2xl opacity-70"></i>
                            </div>
                        </div>
                        
                        <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white p-6 rounded-xl shadow">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium">Current Term</p>
                                    <p class="text-2xl font-bold mt-2">
                                        {{ $semester?->name ?? 'N/A' }} Term
                                    </p>
                                </div>
                                <i class="fas fa-calendar-alt text-2xl opacity-70"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Upcoming Events -->
                    @if(count($upcomingEvents) > 0)
                    <div class="mb-8">
                        <h3 class="text-xl font-semibold mb-4 text-gray-800">Upcoming Events</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($upcomingEvents as $event)
                            <div class="bg-white p-4 rounded-lg border-l-4 border-blue-500 shadow-sm hover:shadow-md transition">
                                <div class="flex items-start">
                                    <div class="bg-blue-100 p-2 rounded-full mr-3">
                                        <i class="fas fa-calendar text-blue-600"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900">{{ $event['title'] }}</h4>
                                        <p class="text-sm text-gray-600 mt-1">
                                            <i class="far fa-clock mr-1"></i>
                                            {{ \Carbon\Carbon::parse($event['date'])->format('D, M j, Y \\a\\t g:i A') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Subject Performance -->
                    @if(count($subjectPerformance) > 0)
                    <div>
                        <h3 class="text-xl font-semibold mb-4 text-gray-800">Subject Performance</h3>
                        <div class="bg-white rounded-lg shadow overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Class</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Students</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Score</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($subjectPerformance as $subjectId => $data)
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="font-medium text-gray-900">{{ $data['subject_name'] }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ $data['class_name'] }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ count($data['students_performance']) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $avg = collect($data['students_performance'])->avg('average_score');
                                                @endphp
                                                <span class="px-3 py-1 text-sm rounded-full {{ $avg >= 60 ? 'bg-green-100 text-green-800' : ($avg >= 40 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                    {{ number_format($avg, 1) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('teacher.results.subject', $subjectId) }}" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                                                <button @click="activeTab = 'upload'; selectedSubject = {{ $subjectId }}" class="text-green-600 hover:text-green-900">Upload</button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>






<!-- Student List Section -->
<div x-data="{
    showStudentsModal: false,
    currentSubject: null,
    subjectStudents: [],
    loadingStudents: false,
    
    async openStudentsModal(subjectId) {
        this.currentSubject = subjectId;
        this.loadingStudents = true;
        this.showStudentsModal = true;
        
        try {
            const response = await fetch(`/api/teacher/subjects/${subjectId}/students`);
            this.subjectStudents = await response.json();
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to load students');
        } finally {
            this.loadingStudents = false;
        }
    }
}" x-show="activeTab === 'students'" x-transition>

<div class="bg-white rounded-lg shadow-md p-6 animate__animated animate__fadeIn">
    <h3 class="text-xl font-semibold mb-6 text-gray-800 flex items-center">
        <i class="fas fa-users mr-2 text-blue-500"></i>
        My Students
    </h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <template x-for="subject in {{ json_encode($teacherSubjects ?? []) }}" :key="subject.id">
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition">
                <div class="p-4">
                    <h4 class="font-bold text-lg text-gray-800" x-text="subject.name"></h4>
                    <p class="text-sm text-gray-600 mb-3" x-text="subject.myClass?.name || 'No Class Assigned'"></p>
                    
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-700">
                            <i class="fas fa-user-graduate mr-1"></i>
                            <span x-text="subject.student_count || 0"></span> students
                        </span>
                        <button @click="openStudentsModal(subject.id)" 
                            class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center">
                            <i class="fas fa-eye mr-1"></i> View All
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>

<!-- Students Modal -->
<div x-show="showStudentsModal" x-transition class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true" @click="showStudentsModal = false">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full h-[80vh] flex flex-col"
            x-on:click.away="showStudentsModal = false">

            <!-- Modal header -->
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            <i class="fas fa-users mr-2 text-blue-500"></i>
                            <span x-text="subjectStudents.length ? subjectStudents[0].subject_name : 'Loading...'"></span> Students
                        </h3>
                        <p class="mt-1 text-sm text-gray-500" x-text="subjectStudents.length + ' students found'"></p>
                    </div>
                    <button @click="showStudentsModal = false" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <!-- Scrollable content area -->
            <div class="flex-1 overflow-y-auto px-4 sm:px-6">
                <div class="mt-6">
                    <template x-if="loadingStudents">
                        <div class="text-center py-8">
                            <i class="fas fa-spinner fa-spin text-2xl text-blue-500 mr-2"></i>
                            <span class="text-gray-600">Loading students...</span>
                        </div>
                    </template>
                    
                    <template x-if="!loadingStudents && subjectStudents.length > 0">
                        <div class="space-y-4 animate__animated animate__fadeIn">
                            <template x-for="student in subjectStudents" :key="student.id">
                                <div class="flex items-center p-3 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                                    <div class="flex-shrink-0 h-12 w-12 rounded-full overflow-hidden mr-3 bg-gray-200 flex items-center justify-center">
                                        <template x-if="student.profile_photo_url">
                                            <img class="h-12 w-12 object-cover" :src="student.profile_photo_url" :alt="student.name">
                                        </template>
                                        <template x-if="!student.profile_photo_url">
                                            <i class="fas fa-user text-gray-400 text-2xl"></i>
                                        </template>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate" x-text="student.name"></p>
                                        <p class="text-sm text-gray-500 truncate" x-text="student.admission_number"></p>
                                    </div>
                                    <div class="ml-4">
                                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Active</span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                    
                    <template x-if="!loadingStudents && subjectStudents.length === 0">
                        <div class="text-center py-8 bg-gray-50 rounded-lg animate__animated animate__fadeIn">
                            <i class="fas fa-user-slash text-4xl text-gray-300 mb-3"></i>
                            <h4 class="text-gray-600">No students found for this subject</h4>
                            <p class="text-sm text-gray-500 mt-1">Students may not be assigned yet</p>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Fixed footer with action buttons -->
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200 sticky bottom-0">
                <button @click="showStudentsModal = false" type="button"
                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
</div>







<!-- Results Viewer Section -->
<div x-data="{
    viewMode: false,
    selectedViewSubject: null,
    academicYears: @json($academicYears ?? []),
    selectedAcademicYear: {{ $academicYear?->id ?? 'null' }},
    semesters: @json($semesters ?? []),
    selectedSemester: {{ $semester?->id ?? 'null' }},
    resultData: [],
    loadingResults: false,
    
    async fetchResults() {
        if (!this.selectedViewSubject || !this.selectedAcademicYear || !this.selectedSemester) return;
        
        this.loadingResults = true;
        
        try {
            const response = await fetch(`/api/teacher/subjects/${this.selectedViewSubject}/results?academic_year=${this.selectedAcademicYear}&semester=${this.selectedSemester}`);
            this.resultData = await response.json();
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to load results');
        } finally {
            this.loadingResults = false;
        }
    },
    
    getSemesters() {
        if (!this.selectedAcademicYear) return [];
        const year = this.academicYears.find(y => y.id == this.selectedAcademicYear);
        return year ? year.semesters : [];
    }
}" x-show="activeTab === 'results'" x-transition>

<div class="bg-white rounded-lg shadow-md p-6 animate__animated animate__fadeIn">
    <h3 class="text-xl font-semibold mb-6 text-gray-800 flex items-center">
        <i class="fas fa-chart-bar mr-2 text-blue-500"></i>
        View Results
    </h3>
    
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <!-- Subject Selection -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
            <select x-model="selectedViewSubject" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Select Subject</option>
                <template x-for="subject in {{ json_encode($teacherSubjects ?? []) }}" :key="subject.id">
                    <option :value="subject.id" x-text="subject.name + ' (' + (subject.myClass?.name || 'N/A') + ')'"></option>
                </template>
            </select>
        </div>
        
        <!-- Academic Year Selection -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Academic Year</label>
            <select x-model="selectedAcademicYear" @change="selectedSemester = null" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Select Year</option>
                <template x-for="year in academicYears" :key="year.id">
                    <option :value="year.id" x-text="year.name"></option>
                </template>
            </select>
        </div>
        
        <!-- Semester Selection -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Term</label>
            <select x-model="selectedSemester" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" :disabled="!selectedAcademicYear">
                <option value="">Select Term</option>
                <template x-for="semester in getSemesters()" :key="semester.id">
                    <option :value="semester.id" x-text="semester.name"></option>
                </template>
            </select>
        </div>
        
        <!-- Fetch Button -->
        <div class="flex items-end">
            <button @click="fetchResults()" :disabled="!selectedViewSubject || !selectedAcademicYear || !selectedSemester" 
                class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md disabled:opacity-50 disabled:cursor-not-allowed">
                <i class="fas fa-search mr-2"></i> View Results
            </button>
        </div>
    </div>
    
    <!-- Results Display -->
    <div x-show="resultData.length > 0" class="mt-6 animate__animated animate__fadeIn">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">1st CA</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">2nd CA</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">3rd CA</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">4th CA</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Exam</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grade</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comment</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="result in resultData" :key="result.id">
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 rounded-full overflow-hidden mr-3 bg-gray-200 flex items-center justify-center">
                                        <template x-if="result.student.profile_photo_url">
                                            <img class="h-10 w-10 object-cover" :src="result.student.profile_photo_url" :alt="result.student.name">
                                        </template>
                                        <template x-if="!result.student.profile_photo_url">
                                            <i class="fas fa-user text-gray-400 text-xl"></i>
                                        </template>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900" x-text="result.student.name"></div>
                                        <div class="text-xs text-gray-500" x-text="result.student.admission_number"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap" x-text="result.ca1_score"></td>
                            <td class="px-6 py-4 whitespace-nowrap" x-text="result.ca2_score"></td>
                            <td class="px-6 py-4 whitespace-nowrap" x-text="result.ca3_score"></td>
                            <td class="px-6 py-4 whitespace-nowrap" x-text="result.ca4_score"></td>
                            <td class="px-6 py-4 whitespace-nowrap" x-text="result.exam_score"></td>
                            <td class="px-6 py-4 whitespace-nowrap font-medium" x-text="result.total_score"></td>
                            <td class="px-6 py-4 whitespace-nowrap font-medium" x-text="result.grade"></td>
                            <td class="px-6 py-4 whitespace-nowrap" x-text="result.comment"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Loading State -->
    <div x-show="loadingResults" class="mt-6 text-center py-8">
        <i class="fas fa-spinner fa-spin text-2xl text-blue-500 mr-2"></i>
        <span class="text-gray-600">Loading results...</span>
    </div>
    
    <!-- Empty State -->
    <div x-show="!loadingResults && resultData.length === 0 && selectedViewSubject && selectedAcademicYear && selectedSemester" 
         class="mt-6 text-center py-8 bg-gray-50 rounded-lg animate__animated animate__fadeIn">
        <i class="fas fa-info-circle text-2xl text-gray-400 mb-3"></i>
        <h4 class="text-gray-600">No results found for selected criteria</h4>
        <p class="text-sm text-gray-500 mt-1">Try different academic year or term</p>
    </div>
</div>
</div>









                <!-- Classes Tab -->
                <div x-show="activeTab === 'classes'" x-transition>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @forelse($teacherClasses as $class)
                        <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                            <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-4 text-white">
                                <h3 class="font-bold text-lg">{{ $class->name }}</h3>
                                <p class="text-sm opacity-90">{{ $class->studentRecords()->count() }} students</p>
                            </div>
                            <div class="p-4">
                                <h4 class="font-medium text-gray-800 mb-2">Subjects Taught:</h4>
                                <ul class="space-y-1">
                                    @foreach($teacherSubjects->where('my_class_id', $class->id) as $subject)
                                    <li class="flex items-center">
                                        <span class="w-2 h-2 bg-blue-500 rounded-full mr-2"></span>
                                        <span>{{ $subject->name }}</span>
                                    </li>
                                    @endforeach
                                </ul>
                                <div class="mt-4 pt-4 border-t border-gray-100">
                                    <a href="{{ route('teacher.classes.show', $class) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View Class Details</a>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-span-3 bg-yellow-50 border-l-4 border-yellow-400 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-circle text-yellow-400"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        You haven't been assigned to any classes yet.
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Subjects Tab -->
                <div x-show="activeTab === 'subjects'" x-transition>




                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($teacherSubjects as $subject)
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <div class="bg-gradient-to-r from-purple-500 to-purple-600 p-4 text-white">
                                <h3 class="font-bold text-lg">{{ $subject->name }}</h3>
                                <p class="text-sm opacity-90">{{ $subject->myClass->name ?? 'No Class Assigned' }}</p>
                            </div>
                            <div class="p-4">
                                <div class="flex justify-between items-center mb-3">
                                    <span class="text-sm font-medium text-gray-600">Students: {{ $subject->studentRecords->count() }}</span>
                                    <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">Active</span>
                                </div>
                                <div class="flex space-x-2 mt-4">
                                    <a href="{{ route('teacher.results.subject', $subject) }}" class="flex-1 text-center px-3 py-2 bg-blue-50 text-blue-700 rounded hover:bg-blue-100 transition">
                                        <i class="fas fa-chart-bar mr-1"></i> View Results
                                    </a>
                                    <button @click="activeTab = 'upload'; selectedSubject = {{ $subject->id }}" class="flex-1 text-center px-3 py-2 bg-green-50 text-green-700 rounded hover:bg-green-100 transition">
                                        <i class="fas fa-upload mr-1"></i> Upload
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Results Upload Tab -->
                <div x-show="activeTab === 'upload'" x-transition>
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-xl font-semibold mb-6 text-gray-800">Upload Results</h3>
                        
                        
                        
                        <!-- Add this to your teacher.blade.php -->
<div x-data="{
    showBulkModal: false,
    selectedSubject: null,
    academicYearId: {{ $academicYear?->id ?? 'null' }},
    semesterId: {{ $semester?->id ?? 'null' }},
    students: [],
    results: {},
    loading: false,
    
    async openBulkModal(subjectId) {
        this.selectedSubject = subjectId;
        this.loading = true;
        this.showBulkModal = true;
        
        try {
            const response = await fetch(`/api/teacher/subjects/${subjectId}/students?academic_year=${this.academicYearId}&semester=${this.semesterId}`);
            const data = await response.json();
            
            this.students = data.students;
            this.results = data.results.reduce((acc, result) => {
                acc[result.student_id] = {
                    ca1_score: result.ca1_score,
                    ca2_score: result.ca2_score,
                    ca3_score: result.ca3_score,
                    ca4_score: result.ca4_score,
                    exam_score: result.exam_score,
                    comment: result.teacher_comment
                };
                return acc;
            }, {});
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to load student data');
        } finally {
            this.loading = false;
        }
    },
    
    calculateTotal(studentId) {
        const result = this.results[studentId] || {};
        return (parseInt(result.ca1_score) || 0) + 
               (parseInt(result.ca2_score) || 0) + 
               (parseInt(result.ca3_score) || 0) + 
               (parseInt(result.ca4_score) || 0) + 
               (parseInt(result.exam_score) || 0);
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
    
    async saveResults() {
        this.loading = true;
        
        try {
            const response = await fetch('/api/teacher/results/bulk', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    subject_id: this.selectedSubject,
                    academic_year_id: this.academicYearId,
                    semester_id: this.semesterId,
                    results: this.results
                })
            });
            
            const data = await response.json();
            
            if (response.ok) {
                alert('Results saved successfully!');
                this.showBulkModal = false;
            } else {
                throw new Error(data.message || 'Failed to save results');
            }
        } catch (error) {
            console.error('Error:', error);
            alert(error.message);
        } finally {
            this.loading = false;
        }
    }
}">

<!-- Bulk Results Modal -->
<div x-show="showBulkModal" x-transition class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true" @click="showBulkModal = false">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-6xl sm:w-full h-[90vh] flex flex-col"
            x-on:click.away="showBulkModal = false">

            <!-- Modal header -->
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            <i class="fas fa-edit mr-2 text-blue-500"></i>
                            Bulk Edit - 
                            <span x-text="students.length ? students[0].subject_name : 'Loading...'"></span>
                        </h3>
                        <div class="mt-1 flex space-x-4">
                            <div class="text-sm text-gray-500">
                                <i class="fas fa-calendar-alt mr-1"></i>
                                <span x-text="academicYearId ? 'Academic Year: '+academicYearId : 'No Academic Year'"></span>
                            </div>
                            <div class="text-sm text-gray-500">
                                <i class="fas fa-clock mr-1"></i>
                                <span x-text="semesterId ? 'Term: '+semesterId : 'No Term Selected'"></span>
                            </div>
                        </div>
                    </div>
                    <button @click="showBulkModal = false" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <!-- Scrollable content area -->
            <div class="flex-1 overflow-y-auto px-4 sm:px-6">
                <div class="mt-6 overflow-x-auto animate__animated animate__fadeIn">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 sticky top-0">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Student
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    1st CA (10)
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    2nd CA (10)
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    3rd CA (10)
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    4th CA (10)
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Exam (60)
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Total
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Grade
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Comment
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="student in students" :key="student.id">
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 rounded-full overflow-hidden mr-3 bg-gray-200 flex items-center justify-center">
                                                <template x-if="student.profile_photo_url">
                                                    <img class="h-10 w-10 object-cover" :src="student.profile_photo_url" :alt="student.name">
                                                </template>
                                                <template x-if="!student.profile_photo_url">
                                                    <i class="fas fa-user text-gray-400 text-xl"></i>
                                                </template>
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900" x-text="student.name"></div>
                                                <div class="text-xs text-gray-500" x-text="student.admission_number"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input x-model="results[student.id]?.ca1_score" type="number" min="0" max="10" step="0.5"
                                            class="w-16 border rounded px-2 py-1 focus:ring-blue-500 focus:border-blue-500"
                                            @input.debounce.500ms="results[student.id] = results[student.id] || {}; results[student.id].ca1_score = $event.target.value">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input x-model="results[student.id]?.ca2_score" type="number" min="0" max="10" step="0.5"
                                            class="w-16 border rounded px-2 py-1 focus:ring-blue-500 focus:border-blue-500"
                                            @input.debounce.500ms="results[student.id] = results[student.id] || {}; results[student.id].ca2_score = $event.target.value">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input x-model="results[student.id]?.ca3_score" type="number" min="0" max="10" step="0.5"
                                            class="w-16 border rounded px-2 py-1 focus:ring-blue-500 focus:border-blue-500"
                                            @input.debounce.500ms="results[student.id] = results[student.id] || {}; results[student.id].ca3_score = $event.target.value">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input x-model="results[student.id]?.ca4_score" type="number" min="0" max="10" step="0.5"
                                            class="w-16 border rounded px-2 py-1 focus:ring-blue-500 focus:border-blue-500"
                                            @input.debounce.500ms="results[student.id] = results[student.id] || {}; results[student.id].ca4_score = $event.target.value">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input x-model="results[student.id]?.exam_score" type="number" min="0" max="60" step="0.5"
                                            class="w-20 border rounded px-2 py-1 focus:ring-blue-500 focus:border-blue-500"
                                            @input.debounce.500ms="results[student.id] = results[student.id] || {}; results[student.id].exam_score = $event.target.value">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center font-medium" 
                                        x-text="calculateTotal(student.id)">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center font-medium" 
                                        x-text="calculateGrade(calculateTotal(student.id))">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input x-model="results[student.id]?.comment" type="text"
                                            class="w-full border rounded px-2 py-1 focus:ring-blue-500 focus:border-blue-500"
                                            @input.debounce.500ms="results[student.id] = results[student.id] || {}; results[student.id].comment = $event.target.value">
                                    </td>
                                </tr>
                            </template>
                            <template x-if="students.length === 0 && !loading">
                                <tr>
                                    <td colspan="9" class="px-6 py-8 text-center">
                                        <div class="text-gray-500 animate__animated animate__fadeIn">
                                            <i class="fas fa-user-slash text-4xl text-gray-300 mb-3"></i>
                                            <h3 class="text-lg font-medium text-gray-700">No students found</h3>
                                            <p class="mt-1 text-sm">
                                                No students are assigned to this subject
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Fixed footer with action buttons -->
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200 sticky bottom-0">
                <button @click="saveResults()" type="button" :disabled="loading"
                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                    <template x-if="!loading">
                        <span><i class="fas fa-save mr-2"></i> Save All Results</span>
                    </template>
                    <template x-if="loading">
                        <span><i class="fas fa-spinner fa-spin mr-2"></i> Saving...</span>
                    </template>
                </button>
                <button @click="showBulkModal = false" type="button" :disabled="loading"
                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>








                </div>



            </div>






            
        </div>
    </div>
</div>

@push('scripts')
<script>
    function teacherDashboard() {
        return {
            loading: false,
            activeTab: 'overview',
            selectedSubject: null,
            uploadInProgress: false,
            uploadProgress: 0,
            uploadStatus: '',
            showManualEntry: false,
            students: [],
            
            init() {
                // Check URL hash for active tab
                if (window.location.hash) {
                    const tab = window.location.hash.substring(1);
                    if (['overview', 'classes', 'subjects', 'upload'].includes(tab)) {
                        this.activeTab = tab;
                    }
                }
                
                // Watch for tab changes to update URL
                this.$watch('activeTab', (value) => {
                    window.location.hash = value;
                });
                
                // Watch for subject selection to load students
                this.$watch('selectedSubject', (value) => {
                    if (value && this.activeTab === 'upload') {
                        this.fetchStudentsForSubject(value);
                    }
                });
            },
            
            fetchStudentsForSubject(subjectId) {
                this.loading = true;
                fetch(`/api/teacher/subjects/${subjectId}/students`)
                    .then(response => response.json())
                    .then(data => {
                        this.students = data.map(student => ({
                            id: student.id,
                            name: student.name,
                            ca_score: student.ca_score || 0,
                            exam_score: student.exam_score || 0
                        }));
                    })
                    .catch(error => {
                        console.error('Error fetching students:', error);
                    })
                    .finally(() => {
                        this.loading = false;
                    });
            },
            
            handleFileSelect(event) {
                const file = event.target.files[0];
                if (!file) return;
                
                this.uploadInProgress = true;
                this.uploadStatus = 'Preparing upload...';
                this.uploadProgress = 0;
                
                // Simulate upload progress
                const interval = setInterval(() => {
                    this.uploadProgress += 10;
                    this.uploadStatus = `Uploading... ${this.uploadProgress}%`;
                    
                    if (this.uploadProgress >= 100) {
                        clearInterval(interval);
                        this.uploadStatus = 'Processing results...';
                        
                        // Simulate processing delay
                        setTimeout(() => {
                            this.uploadInProgress = false;
                            this.uploadStatus = '';
                            alert('Results uploaded successfully!');
                        }, 1500);
                    }
                }, 300);
            },
            
            calculateTotal(student) {
                return (parseInt(student.ca_score) || 0) + (parseInt(student.exam_score) || 0);
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
            
            submitManualResults() {
                if (!this.selectedSubject) return;
                
                const results = this.students.map(student => ({
                    student_id: student.id,
                    ca_score: student.ca_score,
                    exam_score: student.exam_score,
                    total_score: this.calculateTotal(student),
                    grade: this.calculateGrade(this.calculateTotal(student))
                }));
                
                fetch('/api/teacher/results', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        subject_id: this.selectedSubject,
                        results: results
                    })
                })
                .then(response => response.json())
                .then(data => {
                    alert('Results saved successfully!');
                    this.showManualEntry = false;
                })
                .catch(error => {
                    console.error('Error saving results:', error);
                    alert('Error saving results. Please try again.');
                });
            }
        }
    }
</script>
@endpush
@endsection