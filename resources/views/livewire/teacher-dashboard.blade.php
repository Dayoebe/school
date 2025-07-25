@extends('layouts.pages')

@section('title')
    {{ auth()->user()->first_name }} - Teacher Dashboard
@endsection

@section('page_heading')
    {{ auth()->user()->name }} Dashboard
@endsection

<div class="teacher-dashboard-wrapper bg-gray-50 min-h-screen p-6">
    <!-- Header with animated gradient background -->
    <header class="bg-gradient-to-r from-purple-600 to-blue-600 text-white p-6 rounded-2xl shadow-xl mb-6 animate__animated animate__fadeIn">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="flex items-center space-x-4">
                <img src="{{ auth()->user()->profile_photo_url }}" alt="{{ auth()->user()->name }}" 
                    class="w-16 h-16 rounded-full border-4 border-white/80 shadow-lg hover:scale-105 transition-transform duration-300"
                    onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=E8F5E9&color=4CAF50' length=3">
                <div>
                    <h1 class="text-2xl font-bold">Welcome, {{ auth()->user()->first_name }}!</h1>
                    <p class="text-white/90">Here's your teaching summary and analytics</p>
                </div>
            </div>
            <div class="flex items-center space-x-3 bg-white/20 px-4 py-2 rounded-full backdrop-blur-sm">
                <i class="fas fa-calendar-alt text-yellow-300"></i>
                <span class="font-medium">{{ $academicYear?->name ?? 'N/A' }}</span>
                <span class="text-white/50">|</span>
                <i class="fas fa-flag text-green-300"></i>
                <span class="font-medium">{{ $semester?->name ?? 'N/A' }}</span>
            </div>
        </div>
    </header>

    <!-- Main content area with animated tabs -->
    <div x-data="{ activeTab: @entangle('activeTab') }" class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden animate__animated animate__fadeInUp">
        <!-- Tabs Navigation with modern design -->
        <div class="flex flex-wrap border-b border-gray-200 bg-gray-50 px-4">
            <button @click="activeTab = 'overview'" 
                :class="{ 
                    'border-purple-600 text-purple-600 bg-white shadow-sm': activeTab === 'overview', 
                    'text-gray-600 hover:text-purple-600 hover:bg-gray-100': activeTab !== 'overview' 
                }"
                class="py-3 px-6 text-sm font-medium focus:outline-none border-b-2 border-transparent transition-all duration-200 flex items-center">
                <i class="fas fa-chart-pie mr-2"></i> Overview
            </button>
            <button @click="activeTab = 'my-subjects'" 
                :class="{ 
                    'border-purple-600 text-purple-600 bg-white shadow-sm': activeTab === 'my-subjects', 
                    'text-gray-600 hover:text-purple-600 hover:bg-gray-100': activeTab !== 'my-subjects' 
                }"
                class="py-3 px-6 text-sm font-medium focus:outline-none border-b-2 border-transparent transition-all duration-200 flex items-center">
                <i class="fas fa-book-open mr-2"></i> My Subjects
            </button>
            <button @click="activeTab = 'my-classes'" 
                :class="{ 
                    'border-purple-600 text-purple-600 bg-white shadow-sm': activeTab === 'my-classes', 
                    'text-gray-600 hover:text-purple-600 hover:bg-gray-100': activeTab !== 'my-classes' 
                }"
                class="py-3 px-6 text-sm font-medium focus:outline-none border-b-2 border-transparent transition-all duration-200 flex items-center">
                <i class="fas fa-users mr-2"></i> My Classes
            </button>
            <button @click="activeTab = 'view-results'" 
                :class="{ 
                    'border-purple-600 text-purple-600 bg-white shadow-sm': activeTab === 'view-results', 
                    'text-gray-600 hover:text-purple-600 hover:bg-gray-100': activeTab !== 'view-results' 
                }"
                class="py-3 px-6 text-sm font-medium focus:outline-none border-b-2 border-transparent transition-all duration-200 flex items-center">
                <i class="fas fa-poll mr-2"></i> View Results
            </button>
            <button @click="activeTab = 'upload-results'" 
                :class="{ 
                    'border-purple-600 text-purple-600 bg-white shadow-sm': activeTab === 'upload-results', 
                    'text-gray-600 hover:text-purple-600 hover:bg-gray-100': activeTab !== 'upload-results' 
                }"
                class="py-3 px-6 text-sm font-medium focus:outline-none border-b-2 border-transparent transition-all duration-200 flex items-center">
                <i class="fas fa-upload mr-2"></i> Upload Results
            </button>
            <button @click="activeTab = 'comments'" 
                :class="{ 
                    'border-purple-600 text-purple-600 bg-white shadow-sm': activeTab === 'comments', 
                    'text-gray-600 hover:text-purple-600 hover:bg-gray-100': activeTab !== 'comments' 
                }"
                class="py-3 px-6 text-sm font-medium focus:outline-none border-b-2 border-transparent transition-all duration-200 flex items-center">
                <i class="fas fa-comments mr-2"></i> Comments
            </button>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
            {{-- Overview Tab --}}
            <div x-show="activeTab === 'overview'" x-transition:enter="animate__animated animate__fadeIn" class="space-y-6">
                <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-chart-pie text-purple-600 mr-3"></i> Dashboard Overview
                </h2>
                
                <!-- Stats Cards with hover effects -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6 shadow-sm border border-purple-200 hover:shadow-md transition-shadow duration-300">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-purple-800">Assigned Subjects</h3>
                            <i class="fas fa-book text-purple-500 text-2xl"></i>
                        </div>
                        <p class="text-4xl font-bold text-purple-900 mb-2">{{ $assignedSubjectsCount }}</p>
                        <p class="text-sm text-purple-600">Total subjects you teach</p>
                        <div class="mt-4 h-2 bg-purple-200 rounded-full">
                            <div class="h-full bg-purple-500 rounded-full" style="width: 100%"></div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 shadow-sm border border-blue-200 hover:shadow-md transition-shadow duration-300">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-blue-800">Classes Taught</h3>
                            <i class="fas fa-chalkboard-teacher text-blue-500 text-2xl"></i>
                        </div>
                        <p class="text-4xl font-bold text-blue-900 mb-2">{{ $classesTaughtCount }}</p>
                        <p class="text-sm text-blue-600">Number of classes you teach</p>
                        <div class="mt-4 h-2 bg-blue-200 rounded-full">
                            <div class="h-full bg-blue-500 rounded-full" style="width: 100%"></div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 shadow-sm border border-green-200 hover:shadow-md transition-shadow duration-300">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-green-800">Students</h3>
                            <i class="fas fa-users text-green-500 text-2xl"></i>
                        </div>
                        <p class="text-4xl font-bold text-green-900 mb-2">{{ $totalStudentsInMyClasses }}</p>
                        <p class="text-sm text-green-600">Total students under your instruction</p>
                        <div class="mt-4 h-2 bg-green-200 rounded-full">
                            <div class="h-full bg-green-500 rounded-full" style="width: 100%"></div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-xl p-6 shadow-sm border border-amber-200 hover:shadow-md transition-shadow duration-300">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-amber-800">Upcoming Events</h3>
                            <i class="fas fa-calendar-check text-amber-500 text-2xl"></i>
                        </div>
                        <p class="text-2xl font-bold text-amber-900 mb-2">3</p>
                        <p class="text-sm text-amber-600">Meetings & deadlines this week</p>
                        <div class="mt-4 h-2 bg-amber-200 rounded-full">
                            <div class="h-full bg-amber-500 rounded-full" style="width: 75%"></div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity Section -->
                <div class="mt-8 bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-bell text-purple-600 mr-2"></i> Recent Activity
                    </h3>
                    <div class="space-y-4">
                        <div class="flex items-start p-3 hover:bg-gray-50 rounded-lg transition-colors duration-200">
                            <div class="bg-purple-100 p-2 rounded-full mr-3">
                                <i class="fas fa-check-circle text-purple-600"></i>
                            </div>
                            <div>
                                <p class="font-medium">Results uploaded for JSS 2A Mathematics</p>
                                <p class="text-sm text-gray-500">2 hours ago</p>
                            </div>
                        </div>
                        <div class="flex items-start p-3 hover:bg-gray-50 rounded-lg transition-colors duration-200">
                            <div class="bg-blue-100 p-2 rounded-full mr-3">
                                <i class="fas fa-comment text-blue-600"></i>
                            </div>
                            <div>
                                <p class="font-medium">New comment from Principal on Term Report</p>
                                <p class="text-sm text-gray-500">Yesterday</p>
                            </div>
                        </div>
                        <div class="flex items-start p-3 hover:bg-gray-50 rounded-lg transition-colors duration-200">
                            <div class="bg-green-100 p-2 rounded-full mr-3">
                                <i class="fas fa-book text-green-600"></i>
                            </div>
                            <div>
                                <p class="font-medium">New subject material uploaded for SSS 1 English</p>
                                <p class="text-sm text-gray-500">3 days ago</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- My Subjects Tab --}}
            <div x-show="activeTab === 'my-subjects'" x-transition:enter="animate__animated animate__fadeIn">
                @include('livewire.teacher-dashboard.my-subjects')
            </div>

            {{-- My Classes Tab --}}
            <div x-show="activeTab === 'my-classes'" x-transition:enter="animate__animated animate__fadeIn">
                @include('livewire.teacher-dashboard.my-classes')
            </div>

            {{-- View Results Tab --}}
            <div x-show="activeTab === 'view-results'" x-transition:enter="animate__animated animate__fadeIn">
                @include('livewire.teacher-dashboard.view-results')
            </div>

            {{-- Upload Results Tab --}}
            <div x-show="activeTab === 'upload-results'" x-transition:enter="animate__animated animate__fadeIn">
                @include('livewire.teacher-dashboard.upload-results')
            </div>

            {{-- Comments Tab --}}
            <div x-show="activeTab === 'comments'" x-transition:enter="animate__animated animate__fadeIn">
                @include('livewire.teacher-dashboard.comments')
            </div>
        </div>
    </div>
</div>

@endsection