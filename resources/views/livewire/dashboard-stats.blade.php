<div x-data="{ loading: @entangle('loading') }">
    @hasanyrole('admin|super_admin')
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6">
            <!-- Super Admin Section -->
            @can('read school')
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 text-center mb-6">
                        Multi-School Management
                    </h2>
                    <div class="max-w-sm mx-auto">
                        <a href="{{ route('schools.index') }}" 
                           class="block bg-red-600 hover:bg-red-700 text-white rounded-lg p-6 transition-all duration-200 transform hover:scale-105 shadow-md">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-4xl font-bold mb-1">{{ $stats['schools'] }}</div>
                                    <div class="text-sm font-medium opacity-90">Schools</div>
                                </div>
                                <div class="text-5xl opacity-20">
                                    <i class="fas fa-school"></i>
                                </div>
                            </div>
                            <div class="mt-4 text-sm opacity-75 flex items-center">
                                <span>View all schools</span>
                                <i class="fas fa-arrow-right ml-2"></i>
                            </div>
                        </a>
                    </div>
                </div>
            @endcan

            <!-- School Statistics -->
            @can('manage school settings')
                <div class="mb-6">
                    <h3 class="text-xl font-bold text-gray-800 dark:text-gray-200 text-center mb-4">
                        School Statistics
                    </h3>
                </div>
            @endcan

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <!-- Class Groups -->
                @can('read class group')
                    <a href="{{ route('class-groups.index') }}" 
                       class="block bg-orange-600 hover:bg-orange-700 text-white rounded-lg p-5 transition-all duration-200 transform hover:scale-105 shadow-md">
                        <div class="flex items-center justify-between mb-3">
                            <div class="text-3xl font-bold">{{ $stats['class_groups'] }}</div>
                            <div class="text-4xl opacity-20">
                                <i class="fas fa-layer-group"></i>
                            </div>
                        </div>
                        <div class="text-sm font-medium mb-2">Class Groups</div>
                        <div class="text-xs opacity-75 flex items-center">
                            <span>View details</span>
                            <i class="fas fa-arrow-right ml-2 text-xs"></i>
                        </div>
                    </a>
                @endcan

                <!-- Classes -->
                @can('read class')
                    <a href="{{ route('classes.index') }}" 
                       class="block bg-green-600 hover:bg-green-700 text-white rounded-lg p-5 transition-all duration-200 transform hover:scale-105 shadow-md">
                        <div class="flex items-center justify-between mb-3">
                            <div class="text-3xl font-bold">{{ $stats['classes'] }}</div>
                            <div class="text-4xl opacity-20">
                                <i class="fas fa-chalkboard"></i>
                            </div>
                        </div>
                        <div class="text-sm font-medium mb-2">Classes</div>
                        <div class="text-xs opacity-75 flex items-center">
                            <span>View details</span>
                            <i class="fas fa-arrow-right ml-2 text-xs"></i>
                        </div>
                    </a>
                @endcan

                <!-- Sections -->
                @can('read section')
                    <a href="{{ route('sections.index') }}" 
                       class="block bg-lime-600 hover:bg-lime-700 text-white rounded-lg p-5 transition-all duration-200 transform hover:scale-105 shadow-md">
                        <div class="flex items-center justify-between mb-3">
                            <div class="text-3xl font-bold">{{ $stats['sections'] }}</div>
                            <div class="text-4xl opacity-20">
                                <i class="fas fa-users-class"></i>
                            </div>
                        </div>
                        <div class="text-sm font-medium mb-2">Sections</div>
                        <div class="text-xs opacity-75 flex items-center">
                            <span>View details</span>
                            <i class="fas fa-arrow-right ml-2 text-xs"></i>
                        </div>
                    </a>
                @endcan

                <!-- Active Students -->
                @can('read student')
                    <a href="{{ route('students.index') }}" 
                       class="block bg-blue-600 hover:bg-blue-700 text-white rounded-lg p-5 transition-all duration-200 transform hover:scale-105 shadow-md">
                        <div class="flex items-center justify-between mb-3">
                            <div class="text-3xl font-bold">{{ $stats['active_students'] }}</div>
                            <div class="text-4xl opacity-20">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                        </div>
                        <div class="text-sm font-medium mb-2">Active Students</div>
                        <div class="text-xs opacity-75 flex items-center">
                            <span>View students</span>
                            <i class="fas fa-arrow-right ml-2 text-xs"></i>
                        </div>
                    </a>
                @endcan

                <!-- Graduated Students (Alumni) -->
                @can('read student')
                    <a href="{{ route('students.graduations') }}" 
                       class="block bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg p-5 transition-all duration-200 transform hover:scale-105 shadow-md">
                        <div class="flex items-center justify-between mb-3">
                            <div class="text-3xl font-bold">{{ $stats['graduated_students'] }}</div>
                            <div class="text-4xl opacity-20">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                        </div>
                        <div class="text-sm font-medium mb-2">Alumni</div>
                        <div class="text-xs opacity-75 flex items-center">
                            <span>View alumni</span>
                            <i class="fas fa-arrow-right ml-2 text-xs"></i>
                        </div>
                    </a>
                @endcan

                <!-- Teachers -->
                @can('read teacher')
                    <a href="{{ route('teachers.index') }}" 
                       class="block bg-teal-600 hover:bg-teal-700 text-white rounded-lg p-5 transition-all duration-200 transform hover:scale-105 shadow-md">
                        <div class="flex items-center justify-between mb-3">
                            <div class="text-3xl font-bold">{{ $stats['teachers'] }}</div>
                            <div class="text-4xl opacity-20">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                        </div>
                        <div class="text-sm font-medium mb-2">Teachers</div>
                        <div class="text-xs opacity-75 flex items-center">
                            <span>View teachers</span>
                            <i class="fas fa-arrow-right ml-2 text-xs"></i>
                        </div>
                    </a>
                @endcan

                <!-- Parents -->
                @can('read parent')
                    <a href="{{ route('parents.index') }}" 
                       class="block bg-pink-600 hover:bg-pink-700 text-white rounded-lg p-5 transition-all duration-200 transform hover:scale-105 shadow-md">
                        <div class="flex items-center justify-between mb-3">
                            <div class="text-3xl font-bold">{{ $stats['parents'] }}</div>
                            <div class="text-4xl opacity-20">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <div class="text-sm font-medium mb-2">Parents</div>
                        <div class="text-xs opacity-75 flex items-center">
                            <span>View parents</span>
                            <i class="fas fa-arrow-right ml-2 text-xs"></i>
                        </div>
                    </a>
                @endcan
            </div>
        </div>
    </div>
    @endhasanyrole
</div>