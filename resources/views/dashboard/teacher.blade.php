@extends('layouts.app')

@section('title')
{{ auth()->user()->first_name }} - Teacher Dashboard
@endsection

@section('page_heading')
{{ auth()->user()->name }} Dashboard
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<style>
    .flower-bg {
        background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d4d4d8' fill-opacity='0.1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }
    [x-cloak] { display: none !important; }
    .table-input {
        width: 60px;
        padding: 4px 8px;
        border-radius: 6px;
        border: 1px solid #d1d5db;
        text-align: center;
    }
    /* Basic modal styling - replace with a dedicated modal component for production */
    .custom-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }
    .custom-modal-content {
        background-color: white;
        padding: 2rem;
        border-radius: 0.75rem;
        box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        max-width: 500px;
        width: 90%;
        text-align: center;
    }
    .custom-modal-content button {
        margin-top: 1rem;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        background-color: #4CAF50;
        color: white;
        border: none;
        cursor: pointer;
    }
    .custom-modal-content .error-button {
        background-color: #F44336;
    }
</style>
@endpush

@section('content')
<div x-data="teacherDashboard({{ $teacherSubjects->toJson() }}, {{ $academicYear->id ?? 'null' }}, {{ $semester->id ?? 'null' }}, {{ $teacherClasses->toJson() }}, {{ json_encode($upcomingEvents) }}, {{ $academicYears->toJson() }}, {{ $semesters->toJson() }})" x-init="init()" class="min-h-screen bg-gray-50 flower-bg p-4 sm:p-6 lg:p-8" x-cloak>
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
                <span>{{ $academicYear?->name ?? 'N/A' }}</span>
                <span class="text-gray-300">|</span>
                <i class="fas fa-flag text-blue-500"></i>
                <span>{{ $semester?->name ?? 'N/A' }}</span>
            </div>
        </header>
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 my-8">
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white p-6 rounded-2xl shadow-lg flex items-center justify-between">
                <div><p class="text-sm font-medium opacity-80">My Subjects</p><p class="text-4xl font-bold mt-1" x-text="subjects.length"></p></div>
                <i class="fas fa-book-open text-5xl opacity-20"></i>
            </div>
            <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white p-6 rounded-2xl shadow-lg flex items-center justify-between">
                <div><p class="text-sm font-medium opacity-80">My Classes</p><p class="text-4xl font-bold mt-1" x-text="teacherClasses.length"></p></div>
                <i class="fas fa-chalkboard-teacher text-5xl opacity-20"></i>
            </div>
            <div class="bg-gradient-to-br from-green-500 to-green-600 text-white p-6 rounded-2xl shadow-lg flex items-center justify-between">
                <div><p class="text-sm font-medium opacity-80">Upcoming Events</p><p class="text-4xl font-bold mt-1" x-text="upcomingEvents.length"></p></div>
                <i class="fas fa-calendar-check text-5xl opacity-20"></i>
            </div>
        </div>

        <!-- Main Content with Tabs -->
        <main class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-gray-200">
            <!-- Tab Navigation -->
            <nav class="border-b border-gray-200">
                <div class="flex space-x-4 px-6 overflow-x-auto">
                    <button @click="activeTab = 'subjects'" :class="activeTab === 'subjects' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="py-4 px-1 border-b-2 font-medium flex items-center space-x-2 transition whitespace-nowrap"><i class="fas fa-book"></i><span>My Subjects</span></button>
                    <button @click="activeTab = 'classes'" :class="activeTab === 'classes' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="py-4 px-1 border-b-2 font-medium flex items-center space-x-2 transition whitespace-nowrap"><i class="fas fa-chalkboard"></i><span>My Classes</span></button>
                    <button @click="activeTab = 'upload'" :class="activeTab === 'upload' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="py-4 px-1 border-b-2 font-medium flex items-center space-x-2 transition whitespace-nowrap"><i class="fas fa-upload"></i><span>Upload Results</span></button>
                    <button @click="activeTab = 'view_results'" :class="activeTab === 'view_results' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="py-4 px-1 border-b-2 font-medium flex items-center space-x-2 transition whitespace-nowrap"><i class="fas fa-eye"></i><span>View Results</span></button>
                    <button @click="activeTab = 'events'" :class="activeTab === 'events' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="py-4 px-1 border-b-2 font-medium flex items-center space-x-2 transition whitespace-nowrap"><i class="fas fa-calendar-alt"></i><span>Upcoming Events</span></button>
                </div>
            </nav>
            <div class="p-6">
                <!-- Subjects Tab Content -->
                <div x-show="activeTab === 'subjects'" class="animate__animated animate__fadeInUp">
                    <h3 class="text-xl font-semibold mb-4 text-gray-700">Subjects You Teach</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <template x-for="subject in subjects" :key="subject.id">
                            <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 hover:shadow-md hover:border-blue-300 transition-all duration-300 flex flex-col justify-between">
                                <div>
                                    <h4 class="font-bold text-lg text-gray-800" x-text="subject.name"></h4>
                                    <p class="text-sm text-gray-500 mb-3" x-text="subject.my_class ? subject.my_class.name : 'No Class Assigned'"></p>
                                    <div class="flex items-center text-sm text-gray-600 bg-gray-100 px-3 py-1 rounded-full w-fit"><i class="fas fa-user-graduate mr-2 text-gray-400"></i><span x-text="subject.student_records_count + ' students'"></span></div>
                                </div>
                                <div class="mt-4">
                                    <button @click="openStudentsModal(subject.id)" class="w-full text-center px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition font-medium text-sm flex items-center justify-center space-x-2">
                                        <i class="fas fa-users"></i><span>View Students</span>
                                    </button>
                                </div>
                            </div>
                        </template>
                        <template x-if="subjects.length === 0">
                            <p class="text-gray-500 col-span-full text-center py-10">You are not assigned to any subjects yet.</p>
                        </template>
                    </div>
                </div>

                <!-- Classes Tab Content -->
                <div x-show="activeTab === 'classes'" class="animate__animated animate__fadeInUp">
                    <h3 class="text-xl font-semibold mb-4 text-gray-700">Classes You Manage</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <template x-for="classItem in teacherClasses" :key="classItem.id">
                            <div class="bg-purple-50 border border-purple-200 rounded-xl p-5">
                                <h4 class="font-bold text-lg text-purple-800" x-text="classItem.name"></h4>
                                <!-- Corrected: Use classItem.student_records_count if available, otherwise default -->
                                <p class="text-sm text-purple-600 mb-3" x-text="classItem.student_records_count !== undefined ? classItem.student_records_count + ' students' : 'Student count not available'"></p>
                                <div class="border-t border-purple-200 pt-3 mt-3">
                                    <h5 class="font-semibold text-sm text-purple-700 mb-2">Subjects in this class:</h5>
                                    <ul class="space-y-1 text-sm text-purple-600">
                                        <template x-for="subject in subjects.filter(s => s.my_class_id === classItem.id)" :key="subject.id">
                                            <li class="flex items-center"><i class="fas fa-chevron-right fa-xs mr-2"></i><span x-text="subject.name"></span></li>
                                        </template>
                                    </ul>
                                </div>
                            </div>
                        </template>
                        <template x-if="teacherClasses.length === 0">
                            <p class="text-gray-500 col-span-full text-center py-10">You are not assigned to any classes yet.</p>
                        </template>
                    </div>
                </div>

                <!-- Upload Results Tab Content -->
                <div x-show="activeTab === 'upload'" class="animate__animated animate__fadeInUp">
                    <h3 class="text-xl font-semibold mb-4 text-gray-700">Upload Student Results</h3>
                    <div class="bg-gray-50 p-6 rounded-xl border border-gray-200">
                        <div class="mb-4">
                            <label for="upload-subject-select" class="block text-sm font-medium text-gray-700 mb-1">Select Subject</label>
                            <select x-model="uploadSubjectId" id="upload-subject-select" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">-- Please choose a subject --</option>
                                <template x-for="subject in subjects" :key="subject.id">
                                    <option :value="subject.id" x-text="subject.name + ' (' + (subject.my_class ? subject.my_class.name : 'N/A') + ')'"></option>
                                </template>
                            </select>
                        </div>
                        <button @click="openUploadModal()" :disabled="!uploadSubjectId || !academicYearId || !semesterId" class="w-full bg-red-600 text-white py-2 px-4 rounded-lg hover:bg-red-700 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center space-x-2">
                            <i class="fas fa-edit"></i><span>Enter Scores</span>
                        </button>
                        <p x-show="!academicYearId || !semesterId" class="text-xs text-red-600 mt-2">Cannot upload results because the academic year or term is not set.</p>
                    </div>
                </div>

                <!-- View Results Tab Content -->
                <div x-show="activeTab === 'view_results'" class="animate__animated animate__fadeInUp">
                    <h3 class="text-xl font-semibold mb-4 text-gray-700">View Previous Results</h3>
                    <div class="bg-gray-50 p-6 rounded-xl border border-gray-200 mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="view-academic-year-select" class="block text-sm font-medium text-gray-700 mb-1">Academic Year</label>
                                <select x-model="selectedAcademicYearForView" @change="filterSemestersForView(); fetchHistoricalResults()" id="view-academic-year-select" class="w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                                    <option value="">-- Select Academic Year --</option>
                                    <template x-for="year in allAcademicYears" :key="year.id">
                                        <option :value="year.id" x-text="year.name"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label for="view-semester-select" class="block text-sm font-medium text-gray-700 mb-1">Semester</label>
                                <select x-model="selectedSemesterForView" @change="fetchHistoricalResults()" id="view-semester-select" :disabled="!selectedAcademicYearForView || semestersForSelectedYear.length === 0" class="w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <option value="">-- Select Semester --</option>
                                    <template x-for="semester in semestersForSelectedYear" :key="semester.id">
                                        <option :value="semester.id" x-text="semester.name"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label for="view-subject-select" class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                                <select x-model="selectedSubjectForView" @change="fetchHistoricalResults()" id="view-subject-select" class="w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                                    <option value="">-- All My Subjects --</option>
                                    <template x-for="subject in subjects" :key="subject.id">
                                        <option :value="subject.id" x-text="subject.name + ' (' + (subject.my_class ? subject.my_class.name : 'N/A') + ')'"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                        <p x-show="!selectedAcademicYearForView || !selectedSemesterForView" class="text-xs text-orange-600 mt-2">Please select both academic year and semester to view results.</p>
                    </div>

                    <template x-if="viewResultsLoading"><div class="text-center py-12"><i class="fas fa-spinner fa-spin text-3xl text-orange-500"></i><p class="mt-2 text-gray-600">Loading results...</p></div></template>
                    <template x-if="!viewResultsLoading && viewedResults.length > 0">
                        <!-- Results Table Section -->
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
                                    <template x-for="(result, index) in viewedResults" :key="result.student_record_id + '-' + result.subject_id">
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-2 font-bold text-center" x-text="result.position"></td>
                                            <td class="px-4 py-2 whitespace-nowrap"><div class="font-medium text-gray-900" x-text="result.name"></div><div class="text-xs text-gray-500" x-text="result.admission_number"></div></td>
                                            <td class="px-2 py-2" x-text="result.subject_name"></td>
                                            <td class="px-2 py-2" x-text="result.class_name"></td>
                                            <td class="px-2 py-2" x-text="result.ca1_score ?? 'N/A'"></td>
                                            <td class="px-2 py-2" x-text="result.ca2_score ?? 'N/A'"></td>
                                            <td class="px-2 py-2" x-text="result.ca3_score ?? 'N/A'"></td>
                                            <td class="px-2 py-2" x-text="result.ca4_score ?? 'N/A'"></td>
                                            <td class="px-2 py-2" x-text="result.exam_score ?? 'N/A'"></td>
                                            <td class="px-2 py-2 font-bold" x-text="calculateTotal(result).toFixed(1)"></td>
                                            <td class="px-2 py-2 font-medium" x-text="calculateGrade(calculateTotal(result))"></td>
                                            <td class="px-4 py-2" x-text="result.teacher_comment ?? 'No comment'"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </template>
                    <template x-if="!viewResultsLoading && viewedResults.length === 0 && selectedAcademicYearForView && selectedSemesterForView && selectedSubjectForView">
                        <div class="text-center py-12 bg-gray-50 rounded-lg">
                            <i class="fas fa-exclamation-circle text-4xl text-gray-400 mb-3"></i>
                            <h4 class="text-gray-700">No Results Found</h4>
                            <p class="text-sm text-gray-500 mt-1">No results have been uploaded for the selected academic year, semester, and subject.</p>
                        </div>
                    </template>
                    <template x-if="!viewResultsLoading && viewedResults.length === 0 && selectedAcademicYearForView && selectedSemesterForView && !selectedSubjectForView">
                        <div class="text-center py-12 bg-gray-50 rounded-lg">
                            <i class="fas fa-exclamation-circle text-4xl text-gray-400 mb-3"></i>
                            <h4 class="text-gray-700">No Results Found</h4>
                            <p class="text-sm text-gray-500 mt-1">No results have been uploaded for the selected academic year and semester across your assigned subjects.</p>
                        </div>
                    </template>
                     <template x-if="!viewResultsLoading && viewedResults.length === 0 && (!selectedAcademicYearForView || !selectedSemesterForView)">
                        <div class="text-center py-12 bg-gray-50 rounded-lg">
                            <i class="fas fa-info-circle text-4xl text-gray-400 mb-3"></i>
                            <h4 class="text-gray-700">Select Options</h4>
                            <p class="text-sm text-gray-500 mt-1">Please select an academic year and semester to view results.</p>
                        </div>
                    </template>
                </div>

                <!-- Upcoming Events Tab Content -->
                <div x-show="activeTab === 'events'" class="animate__animated animate__fadeInUp">
                    <h3 class="text-xl font-semibold mb-4 text-gray-700">Upcoming Events</h3>
                    <div class="space-y-4">
                        <template x-for="event in upcomingEvents" :key="event.title + event.date">
                            <div class="bg-green-50 border-l-4 border-green-400 p-4 flex items-center space-x-4 rounded-r-lg">
                                <div class="bg-green-100 p-3 rounded-full"><i class="fas fa-calendar text-green-600"></i></div>
                                <div>
                                    <h4 class="font-medium text-gray-900" x-text="event.title"></h4>
                                    <p class="text-sm text-gray-600" x-text="formatDate(event.date)"></p>
                                </div>
                            </div>
                        </template>
                        <template x-if="upcomingEvents.length === 0">
                            <p class="text-gray-500 text-center py-10">No upcoming events.</p>
                        </template>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Students Modal -->
    <div x-show="showStudentsModal" @keydown.escape.window="showStudentsModal = false" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showStudentsModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity" aria-hidden="true" @click="showStudentsModal = false"><div class="absolute inset-0 bg-gray-700 opacity-75"></div></div>
            <div x-show="showStudentsModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full h-[80vh] flex flex-col">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-gray-200">
                    <div class="flex justify-between items-start">
                        <div><h3 class="text-lg leading-6 font-medium text-gray-900"><i class="fas fa-users mr-2 text-blue-500"></i>Students for <span x-text="currentSubjectName" class="font-bold"></span></h3><p class="mt-1 text-sm text-gray-500" x-text="studentList.length + ' students found'"></p></div>
                        <button @click="showStudentsModal = false" class="text-gray-400 hover:text-gray-500"><i class="fas fa-times text-xl"></i></button>
                    </div>
                </div>
                <div class="flex-1 overflow-y-auto p-6">
                    <template x-if="loading"><div class="text-center py-12"><i class="fas fa-spinner fa-spin text-3xl text-blue-500"></i><p class="mt-2 text-gray-600">Loading students...</p></div></template>
                    <template x-if="!loading && studentList.length > 0">
                        <ul class="space-y-3">
                            <template x-for="student in studentList" :key="student.id">
                                <li class="flex items-center p-3 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 transition">
                                    <img :src="student.profile_photo_url" :alt="student.name" class="h-12 w-12 rounded-full object-cover mr-4">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900" x-text="student.name"></p>
                                        <p class="text-xs text-gray-500" x-text="'ID: ' + student.admission_number"></p>
                                    </div>
                                    <span class="px-3 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Enrolled</span>
                                </li>
                            </template>
                        </ul>
                    </template>
                    <template x-if="!loading && studentList.length === 0">
                        <div class="text-center py-12 bg-gray-50 rounded-lg">
                            <i class="fas fa-user-slash text-4xl text-gray-400 mb-3"></i>
                            <h4 class="text-gray-700">No Students Found</h4>
                            <p class="text-sm text-gray-500 mt-1">There are no students currently enrolled in this subject.</p>
                        </div>
                    </template>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200">
                    <button @click="showStudentsModal = false" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Results Modal -->
    <div x-show="showUploadModal" @keydown.escape.window="showUploadModal = false" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showUploadModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity" aria-hidden="true" @click="showUploadModal = false"><div class="absolute inset-0 bg-gray-700 opacity-75"></div></div>
            <div x-show="showUploadModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full h-[90vh] flex flex-col">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-gray-200">
                    <div class="flex justify-between items-start">
                        <div><h3 class="text-lg leading-6 font-medium text-gray-900"><i class="fas fa-edit mr-2 text-red-500"></i>Bulk Edit Scores for <span x-text="currentSubjectName" class="font-bold"></span></h3><p class="mt-1 text-sm text-gray-500" x-text="studentResults.length + ' students found'"></p></div>
                        <button @click="showUploadModal = false" class="text-gray-400 hover:text-gray-500"><i class="fas fa-times text-xl"></i></button>
                    </div>
                </div>
                <div class="flex-1 overflow-y-auto p-2 sm:p-6">
                    <template x-if="loading"><div class="text-center py-12"><i class="fas fa-spinner fa-spin text-3xl text-red-500"></i><p class="mt-2 text-gray-600">Loading data...</p></div></template>
                    <template x-if="!loading && studentResults.length > 0">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50 sticky top-0">
                                    <tr>
                                        <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                        <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">1st CA (10)</th>
                                        <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">2nd CA (10)</th>
                                        <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">3rd CA (10)</th>
                                        <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">4th CA (10)</th>
                                        <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Exam (60)</th>
                                        <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                        <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Grade</th>
                                        <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Comment</th>
                                        <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <template x-for="(student, index) in studentResults" :key="student.student_record_id">
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-2 whitespace-nowrap"><div class="font-medium text-gray-900" x-text="student.name"></div><div class="text-xs text-gray-500" x-text="student.admission_number"></div></td>
                                            <td><input type="number" x-model.number="student.ca1_score" class="table-input" min="0" max="10"></td>
                                            <td><input type="number" x-model.number="student.ca2_score" class="table-input" min="0" max="10"></td>
                                            <td><input type="number" x-model.number="student.ca3_score" class="table-input" min="0" max="10"></td>
                                            <td><input type="number" x-model.number="student.ca4_score" class="table-input" min="0" max="10"></td>
                                            <td><input type="number" x-model.number="student.exam_score" class="table-input" min="0" max="60"></td>
                                            <td class="px-2 py-2 text-center font-bold" x-text="calculateTotal(student).toFixed(1)"></td>
                                            <td class="px-2 py-2 text-center font-medium" x-text="calculateGrade(calculateTotal(student))"></td>
                                            <td class="px-4 py-2"><input type="text" x-model="student.teacher_comment" class="w-full border-gray-300 rounded-md shadow-sm text-sm"></td>
                                            <td class="px-4 py-2 text-center">
                                                <button @click="clearStudentScores(index)" class="text-red-500 hover:text-red-700" title="Clear Scores">
                                                    <i class="fas fa-eraser"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </template>
                    <template x-if="!loading && studentResults.length === 0">
                        <div class="text-center py-12 bg-gray-50 rounded-lg">
                            <i class="fas fa-user-slash text-4xl text-gray-400 mb-3"></i>
                            <h4 class="text-gray-700">No Students Found</h4>
                            <p class="text-sm text-gray-500 mt-1">Could not find any students for this subject.</p>
                        </div>
                    </template>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200">
                    <button @click="saveResults()" type="button" :disabled="loading" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                        <span x-show="!loading"><i class="fas fa-save mr-2"></i>Saving All Results</span>
                        <span x-show="loading"><i class="fas fa-spinner fa-spin mr-2"></i>Saving...</span>
                    </button>
                    <button @click="showUploadModal = false" type="button" :disabled="loading" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Alert/Modal Component -->
    <div x-show="modal.show" class="custom-modal" style="display: none;">
        <div class="custom-modal-content">
            <h3 class="text-xl font-bold mb-4" :class="modal.type === 'error' ? 'text-red-600' : 'text-green-600'" x-text="modal.title"></h3>
            <p x-text="modal.message" class="text-gray-700 mb-6"></p>
            <button @click="modal.show = false" :class="modal.type === 'error' ? 'error-button' : ''">OK</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function teacherDashboard(subjects, academicYearId, semesterId, teacherClasses, upcomingEvents, allAcademicYears, allSemesters) {
        return {
            subjects: subjects,
            academicYearId: academicYearId, // Current academic year ID
            semesterId: semesterId,     // Current semester ID
            teacherClasses: teacherClasses,
            upcomingEvents: upcomingEvents,
            allAcademicYears: allAcademicYears, // All academic years from backend
            allSemesters: allSemesters,     // All semesters from backend

            activeTab: 'subjects',
            showStudentsModal: false,
            showUploadModal: false,
            loading: false,
            studentList: [],
            studentResults: [],
            currentSubjectName: '',
            uploadSubjectId: null,

            // Properties for 'View Results' tab (Class Performance)
            selectedAcademicYearForView: academicYearId, // Default to current
            selectedSemesterForView: semesterId,       // Default to current
            selectedSubjectForView: '', // New property for filtering by subject
            semestersForSelectedYear: [],
            viewedResults: [],
            viewResultsLoading: false,
            // performanceChartInstance: null, // Removed chart instance

            // Custom modal state
            modal: {
                show: false,
                title: '',
                message: '',
                type: 'success' // 'success' or 'error'
            },

            init() {
                const hash = window.location.hash.replace('#', '');
                // Ensure 'view_results' is included in the initial active tab check
                if (['subjects', 'classes', 'events', 'upload', 'view_results'].includes(hash)) {
                    this.activeTab = hash;
                } else {
                    // Default to 'subjects' if no valid hash is present
                    this.activeTab = 'subjects';
                }

                this.$watch('activeTab', value => {
                    window.location.hash = value;
                    // If switching to view_results, always attempt to fetch and render
                    if (value === 'view_results') {
                        this.fetchHistoricalResults(); // Call fetchHistoricalResults directly
                    }
                    // Removed chart destruction logic as chart is no longer present
                });

                // Initialize semesters for the current academic year in the view results tab
                this.filterSemestersForView();
                // Only fetch historical results on init if current academic year and semester are set
                if (this.selectedAcademicYearForView && this.selectedSemesterForView) {
                    if (this.activeTab === 'view_results') {
                        this.fetchHistoricalResults();
                    }
                } else {
                    console.warn('Initial academic year or semester not set. Cannot fetch historical results on init.');
                }
            },

            // Function to show custom modal
            showModal(title, message, type = 'success') {
                this.modal.title = title;
                this.modal.message = message;
                this.modal.type = type;
                this.modal.show = true;
            },

            formatDate(dateString) {
                const options = { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: 'numeric', hour12: true };
                return new Date(dateString).toLocaleString('en-US', options);
            },

            filterSemestersForView() {
                if (this.selectedAcademicYearForView) {
                    this.semestersForSelectedYear = this.allSemesters.filter(semester => semester.academic_year_id == this.selectedAcademicYearForView);
                    if (!this.semestersForSelectedYear.some(s => s.id == this.selectedSemesterForView)) {
                        this.selectedSemesterForView = '';
                    }
                } else {
                    this.semestersForSelectedYear = [];
                    this.selectedSemesterForView = '';
                }
            },

            async openStudentsModal(subjectId) {
                const subject = this.subjects.find(s => s.id === subjectId);
                this.currentSubjectName = subject ? subject.name : 'Subject';
                this.loading = true;
                this.showStudentsModal = true;
                this.studentList = [];
                try {
                    const response = await fetch(`/teacher/subjects/${subjectId}/students`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest', // Signal AJAX request
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content // Include CSRF token
                        }
                    });
                    if (!response.ok) {
                        let errorData;
                        try {
                            errorData = await response.json();
                        } catch (e) {
                            errorData = { message: await response.text() }; // Get raw text if not JSON
                        }
                        // Use custom modal instead of alert
                        this.showModal('Error', errorData.message || 'Failed to fetch students', 'error');
                        throw new Error(errorData.message || 'Failed to fetch students');
                    }
                    this.studentList = await response.json();
                    // Sort students alphabetically by name, handling null names
                    this.studentList.sort((a, b) => (a.name || '').localeCompare(b.name || ''));
                } catch (error) {
                    console.error('Error fetching students:', error);
                    // Error is already shown by showModal, no need for another alert
                } finally {
                    this.loading = false;
                }
            },

            async openUploadModal() {
                if (!this.uploadSubjectId) {
                    this.showModal('Warning', 'Please select a subject first.', 'error');
                    return;
                }
                if (!this.academicYearId || !this.semesterId) {
                    this.showModal('Warning', 'Academic year or semester is not set. Please contact an administrator.', 'error');
                    return;
                }

                const subject = this.subjects.find(s => s.id == this.uploadSubjectId);
                this.currentSubjectName = subject ? subject.name : 'Subject';
                this.loading = true;
                this.showUploadModal = true;
                this.studentResults = []; // Clear previous results
                try {
                    const response = await fetch(`/teacher/subjects/${this.uploadSubjectId}/results-for-upload?academic_year_id=${this.academicYearId}&semester_id=${this.semesterId}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest', // Signal AJAX request
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content // Include CSRF token
                        }
                    });
                    if (!response.ok) {
                        let errorData;
                        try {
                            errorData = await response.json();
                        } catch (e) {
                            errorData = { message: await response.text() }; // Get raw text if not JSON
                        }
                        this.showModal('Error', errorData.message || 'Failed to fetch student results', 'error');
                        throw new Error(errorData.message || 'Failed to fetch student results');
                    }
                    const fetchedResults = await response.json();
                    // Ensure scores are null if not present, for empty input fields
                    this.studentResults = fetchedResults.map(student => ({
                        ...student,
                        ca1_score: student.ca1_score ?? null,
                        ca2_score: student.ca2_score ?? null,
                        ca3_score: student.ca3_score ?? null,
                        ca4_score: student.ca4_score ?? null,
                        exam_score: student.exam_score ?? null,
                        teacher_comment: student.teacher_comment ?? '',
                    }));
                    // Sort students alphabetically by name, handling null names
                    this.studentResults.sort((a, b) => (a.name || '').localeCompare(b.name || ''));
                } catch (error) {
                    console.error('Error fetching student results:', error);
                    // Error is already shown by showModal, no need for another alert
                } finally {
                    this.loading = false;
                }
            },
            
            clearStudentScores(index) {
                // Set scores to null instead of 0 to distinguish between no score and a 0 score
                this.studentResults[index].ca1_score = null;
                this.studentResults[index].ca2_score = null;
                this.studentResults[index].ca3_score = null;
                this.studentResults[index].ca4_score = null;
                this.studentResults[index].exam_score = null;
                this.studentResults[index].teacher_comment = '';
            },

            async fetchHistoricalResults() {
                console.log('Fetching historical results...');
                console.log('Selected Academic Year:', this.selectedAcademicYearForView);
                console.log('Selected Semester:', this.selectedSemesterForView);
                console.log('Selected Subject:', this.selectedSubjectForView);

                // Only fetch if both academic year and semester are selected
                if (!this.selectedAcademicYearForView || !this.selectedSemesterForView) {
                    console.log('Academic year or semester not selected, returning early.');
                    this.viewedResults = [];
                    return; // Exit if prerequisites are not met
                }

                this.viewResultsLoading = true;
                this.viewedResults = []; // Clear previous results

                let subjectsToFetch = this.subjects;
                if (this.selectedSubjectForView) {
                    subjectsToFetch = this.subjects.filter(s => s.id == this.selectedSubjectForView);
                }
                console.log('Subjects to fetch:', subjectsToFetch.map(s => s.name));

                let allFetchedResults = [];
                for (const subject of subjectsToFetch) {
                    try {
                        const url = `/teacher/subjects/${subject.id}/results-for-upload?academic_year_id=${this.selectedAcademicYearForView}&semester_id=${this.selectedSemesterForView}`;
                        console.log('Fetching from URL:', url);
                        const response = await fetch(url, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });
                        console.log('Response status for subject', subject.name, ':', response.status);
                        if (!response.ok) {
                            let errorData;
                            try {
                                errorData = await response.json();
                                console.error(`Error data for subject ${subject.name}:`, errorData);
                            } catch (e) {
                                errorData = { message: await response.text() };
                                console.error(`Raw error text for subject ${subject.name}:`, errorData.message);
                            }
                            console.error(`Error fetching results for subject ${subject.name}:`, errorData.message);
                            continue; // Continue to the next subject
                        }
                        const subjectResults = await response.json();
                        console.log('Fetched results for subject', subject.name, ':', subjectResults);

                        // Re-introducing the filter to show only students with at least one score
                        const filteredSubjectResults = subjectResults.filter(res => 
                            (res.ca1_score !== null && res.ca1_score !== 0) ||
                            (res.ca2_score !== null && res.ca2_score !== 0) ||
                            (res.ca3_score !== null && res.ca3_score !== 0) ||
                            (res.ca4_score !== null && res.ca4_score !== 0) ||
                            (res.exam_score !== null && res.exam_score !== 0)
                        );
                        console.log('Filtered results (only with scores) for subject', subject.name, ':', filteredSubjectResults);

                        allFetchedResults = allFetchedResults.concat(filteredSubjectResults.map(res => ({
                            ...res,
                            subject_name: subject.name,
                            class_name: subject.my_class ? subject.my_class.name : 'N/A'
                        })));
                    } catch (error) {
                        console.error(`Network error fetching results for subject ${subject.name}:`, error);
                    }
                }
                console.log('All fetched and processed results before sorting:', allFetchedResults);
                
                // Calculate total score and then sort by total score for "position"
                allFetchedResults.forEach(result => {
                    result.total_score = this.calculateTotal(result);
                });

                // Sort by total score (descending) and then by student name (ascending) for ties
                allFetchedResults.sort((a, b) => {
                    if (b.total_score !== a.total_score) {
                        return b.total_score - a.total_score;
                    }
                    return (a.name || '').localeCompare(b.name || ''); // Handle null names here too
                });

                // Assign positions based on the sorted order
                let currentPosition = 1;
                let previousTotal = -1; // Initialize with a value that won't match any score
                allFetchedResults.forEach((result, index) => {
                    if (result.total_score !== previousTotal) {
                        currentPosition = index + 1;
                    }
                    result.position = currentPosition;
                    previousTotal = result.total_score;
                });


                this.viewedResults = allFetchedResults;
                this.viewResultsLoading = false;

                // No chart rendering here anymore
            },
            
            calculateTotal(student) {
                const ca1 = parseFloat(student.ca1_score) || 0;
                const ca2 = parseFloat(student.ca2_score) || 0;
                const ca3 = parseFloat(student.ca3_score) || 0;
                const ca4 = parseFloat(student.ca4_score) || 0;
                const exam = parseFloat(student.exam_score) || 0;

                // Ensure scores are within valid ranges
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

            // Removed renderPerformanceChart() method
            // renderPerformanceChart() { ... }

            async saveResults() {
                this.loading = true;
                try {
                    const payload = {
                        subject_id: this.uploadSubjectId,
                        academic_year_id: this.academicYearId,
                        semester_id: this.semesterId,
                        results: this.studentResults.map(student => ({
                            student_record_id: student.student_record_id,
                            // Send null if the field is empty, otherwise parse as float
                            ca1_score: student.ca1_score === null ? null : parseFloat(student.ca1_score),
                            ca2_score: student.ca2_score === null ? null : parseFloat(student.ca2_score),
                            ca3_score: student.ca3_score === null ? null : parseFloat(student.ca3_score),
                            ca4_score: student.ca4_score === null ? null : parseFloat(student.ca4_score),
                            exam_score: student.exam_score === null ? null : parseFloat(student.exam_score),
                            teacher_comment: student.teacher_comment || null, // Send null for empty comments
                        }))
                    };

                    const response = await fetch('/teacher/results/bulk-upload', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(payload)
                    });
                    const data = await response.json();
                    if (!response.ok) {
                        if (data.errors) {
                            let errorMessage = 'Validation Errors:\n';
                            for (const key in data.errors) {
                                errorMessage += `- ${data.errors[key].join(', ')}\n`;
                            }
                            this.showModal('Validation Error', errorMessage, 'error');
                            throw new Error(errorMessage); // Re-throw to be caught by outer catch
                        } else {
                            this.showModal('Error', data.message || 'Failed to save results', 'error');
                            throw new Error(data.message || 'Failed to save results');
                        }
                    }
                    this.showModal('Success', data.message); // Use custom modal
                    this.showUploadModal = false;
                    // Refresh the 'View Results' tab if it's active and conditions match
                    if (this.activeTab === 'view_results' && this.uploadSubjectId && this.academicYearId && this.semesterId) {
                         this.fetchHistoricalResults();
                    }
                } catch (error) {
                    console.error('Error saving results:', error);
                    // Error is already shown by showModal, no need for another alert
                } finally {
                    this.loading = false;
                }
            }
        }
    }
</script>
@endpush

@endsection
