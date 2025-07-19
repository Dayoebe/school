{{-- @extends('layouts.pages') --}}

<div x-data="{ activeTab: 'grades', sidebarOpen: false }" class="min-h-screen bg-gray-50">
    <!-- Mobile sidebar toggle -->
    <div class="lg:hidden fixed top-4 left-4 z-50">
        <button @click="sidebarOpen = true" class="p-2 rounded-md bg-white shadow-md text-gray-600">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <!-- Sidebar -->
    <div x-show="sidebarOpen" @click.away="sidebarOpen = false" 
         class="fixed inset-y-0 left-0 z-40 w-64 bg-blue-800 text-white transform transition-transform duration-300 ease-in-out"
         :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen}">
        <div class="flex items-center justify-between p-4 border-b border-blue-700">
            <h2 class="text-xl font-bold">Student Dashboard</h2>
            <button @click="sidebarOpen = false" class="text-blue-200 hover:text-white">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <!-- Student Profile Summary -->
        <div class="p-4 border-b border-blue-700">
            <div class="flex items-center space-x-3">
                <img class="h-12 w-12 rounded-full object-cover border-2 border-blue-300" 
                     src="{{ $student->profile_photo_url }}" 
                     alt="{{ $student->name }}">
                <div>
                    <h3 class="font-medium">{{ $student->name }}</h3>
                    <p class="text-xs text-blue-200">
                        {{ $student->studentRecord->myClass->name ?? 'No Class' }}
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Navigation -->
        <nav class="p-4">
            <ul class="space-y-2">
                <li>
                    <button @click="activeTab = 'grades'; sidebarOpen = false" 
                            :class="{'bg-blue-700': activeTab === 'grades'}"
                            class="w-full text-left px-4 py-2 rounded-md hover:bg-blue-700 transition flex items-center space-x-2">
                        <i class="fas fa-chart-line w-5"></i>
                        <span>Grades</span>
                    </button>
                </li>
                <li>
                    <button @click="activeTab = 'attendance'; sidebarOpen = false" 
                            :class="{'bg-blue-700': activeTab === 'attendance'}"
                            class="w-full text-left px-4 py-2 rounded-md hover:bg-blue-700 transition flex items-center space-x-2">
                        <i class="fas fa-calendar-check w-5"></i>
                        <span>Attendance</span>
                    </button>
                </li>
                <li>
                    <button @click="activeTab = 'timetable'; sidebarOpen = false" 
                            :class="{'bg-blue-700': activeTab === 'timetable'}"
                            class="w-full text-left px-4 py-2 rounded-md hover:bg-blue-700 transition flex items-center space-x-2">
                        <i class="fas fa-calendar-alt w-5"></i>
                        <span>Timetable</span>
                    </button>
                </li>
                <li>
                    <button @click="activeTab = 'profile'; sidebarOpen = false" 
                            :class="{'bg-blue-700': activeTab === 'profile'}"
                            class="w-full text-left px-4 py-2 rounded-md hover:bg-blue-700 transition flex items-center space-x-2">
                        <i class="fas fa-user w-5"></i>
                        <span>Profile</span>
                    </button>
                </li>
            </ul>
        </nav>
        
        <!-- Academic Info -->
        <div class="p-4 border-t border-blue-700 mt-auto">
            <div class="text-sm text-blue-200 mb-1">Academic Year</div>
            <div class="font-medium">{{ $academicYear->name ?? 'N/A' }}</div>
            <div class="text-sm text-blue-200 mb-1 mt-2">Semester</div>
            <div class="font-medium">{{ $semester->name ?? 'N/A' }}</div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="lg:ml-64 transition-all duration-300">
        <!-- Header -->
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 py-4 sm:px-6 lg:px-8 flex justify-between items-center">
                <h1 class="text-xl font-bold text-gray-900" x-text="
                    activeTab === 'grades' ? 'Grades' : 
                    activeTab === 'attendance' ? 'Attendance' :
                    activeTab === 'timetable' ? 'Timetable' : 'Profile'
                "></h1>
                
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <button class="p-2 rounded-full hover:bg-gray-100">
                            <i class="fas fa-bell text-gray-500"></i>
                            <span class="absolute top-0 right-0 h-2 w-2 rounded-full bg-red-500"></span>
                        </button>
                    </div>
                    <div class="hidden md:flex items-center space-x-2">
                        <img class="h-8 w-8 rounded-full" 
                             src="{{ $student->profile_photo_url }}" 
                             alt="{{ $student->name }}">
                        <span class="text-sm font-medium">{{ $student->name }}</span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Stats Cards -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 animate__animated animate__fadeIn">
                <!-- Average Score Card -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden transition-transform hover:scale-105">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                                <i class="fas fa-star"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm font-medium text-gray-500">Average Score</h3>
                                <p class="text-2xl font-semibold text-gray-900">
                                    {{ $overallPerformance?->average ? number_format($overallPerformance->average, 1) : 'N/A' }}
                                </p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full bg-blue-500 rounded-full" 
                                     :style="'width: ' + ({{ $overallPerformance?->average ?? 0 }} / 100 * 100) + '%'"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Subjects Taken Card -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden transition-transform hover:scale-105">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 text-green-600">
                                <i class="fas fa-book"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm font-medium text-gray-500">Subjects Taken</h3>
                                <p class="text-2xl font-semibold text-gray-900">{{ $student->subjects->count() }}</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <span class="text-xs text-green-600">
                                <i class="fas fa-arrow-up"></i> 2 more than last term
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Attendance Rate Card -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden transition-transform hover:scale-105">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                                <i class="fas fa-user-check"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm font-medium text-gray-500">Attendance Rate</h3>
                                <p class="text-2xl font-semibold text-gray-900">92%</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="flex items-center text-xs text-gray-500">
                                <span>Last week: 100%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Classes Card -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden transition-transform hover:scale-105">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm font-medium text-gray-500">Next Class</h3>
                                <p class="text-xl font-semibold text-gray-900">Mathematics</p>
                                <p class="text-sm text-gray-500">10:00 AM - 11:00 AM</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Content -->
            <div class="mt-8 animate__animated animate__fadeIn">
                <div x-show="activeTab === 'grades'" x-transition>
                    <livewire:student-grades
                        :studentId="$student->id"
                        :academicYearId="$academicYear->id"
                        :semesterId="$semester->id"
                    />
                </div>

                <div x-show="activeTab === 'attendance'" x-transition style="display: none;">
                    <livewire:student-attendance
                        :studentId="$student->id"
                        :academicYearId="$academicYear->id"
                    />
                </div>

                <div x-show="activeTab === 'timetable'" x-transition style="display: none;">
                    <livewire:student-timetable
                        :studentId="$student->id"
                    />
                </div>

                <div x-show="activeTab === 'profile'" x-transition style="display: none;">
                    <livewire:student-profile
                        :student="$student"
                    />
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endpush