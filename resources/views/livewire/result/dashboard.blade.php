<div class="space-y-6">
    @if(empty($stats))
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 text-center">
            <i class="fas fa-info-circle text-yellow-600 text-3xl mb-3"></i>
            <h3 class="text-lg font-semibold text-yellow-800">Select Academic Period</h3>
            <p class="text-yellow-600">Please select an academic year and term to view statistics</p>
        </div>
    @else
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Students -->
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-6 text-white shadow-xl hover:shadow-2xl transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm font-medium">Total Students</p>
                        <p class="text-4xl font-bold mt-2">{{ $stats['total_students'] }}</p>
                    </div>
                    <div class="bg-white/20 p-4 rounded-xl">
                        <i class="fas fa-users text-3xl"></i>
                    </div>
                </div>
            </div>

            <!-- Students with Results -->
            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl p-6 text-white shadow-xl hover:shadow-2xl transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm font-medium">Results Uploaded</p>
                        <p class="text-4xl font-bold mt-2">{{ $stats['students_with_results'] }}</p>
                    </div>
                    <div class="bg-white/20 p-4 rounded-xl">
                        <i class="fas fa-check-circle text-3xl"></i>
                    </div>
                </div>
            </div>

            <!-- Pending -->
            <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-2xl p-6 text-white shadow-xl hover:shadow-2xl transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-yellow-100 text-sm font-medium">Pending Upload</p>
                        <p class="text-4xl font-bold mt-2">{{ $stats['pending_students'] }}</p>
                    </div>
                    <div class="bg-white/20 p-4 rounded-xl">
                        <i class="fas fa-clock text-3xl"></i>
                    </div>
                </div>
            </div>

            <!-- Average Score -->
            <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl p-6 text-white shadow-xl hover:shadow-2xl transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-100 text-sm font-medium">Average Score</p>
                        <p class="text-4xl font-bold mt-2">{{ $stats['average_score'] }}%</p>
                    </div>
                    <div class="bg-white/20 p-4 rounded-xl">
                        <i class="fas fa-chart-line text-3xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="bg-white rounded-2xl shadow-lg p-6">
            <div class="flex justify-between items-center mb-3">
                <h3 class="text-lg font-semibold text-gray-800">Results Upload Progress</h3>
                <span class="text-2xl font-bold text-indigo-600">{{ $stats['completion_rate'] }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden">
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 h-4 rounded-full transition-all duration-500" 
                     style="width: {{ $stats['completion_rate'] }}%"></div>
            </div>
            <p class="text-sm text-gray-600 mt-2">
                {{ $stats['students_with_results'] }} out of {{ $stats['total_students'] }} students have results uploaded
            </p>
        </div>

        <!-- Recently Uploaded -->
      <!-- Recently Uploaded -->
<div class="bg-white rounded-2xl shadow-lg p-6">
    <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
        <i class="fas fa-clock text-indigo-600 mr-2"></i>
        Recently Uploaded Results
    </h3>
    
    @if($stats['recently_uploaded']->isEmpty())
        <p class="text-gray-500 text-center py-4">No results uploaded yet</p>
    @else
        <div class="space-y-3">
            @foreach($stats['recently_uploaded'] as $result)
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors">
                    <div class="flex items-center space-x-4">
                        <!-- Fix: Use studentRecord.user instead of student.user -->
                        <img src="{{ $result->studentRecord?->user?->profile_photo_url ?? asset('images/default-avatar.png') }}" 
                             alt="{{ $result->studentRecord?->user?->name ?? 'Unknown Student' }}"
                             class="w-10 h-10 rounded-full object-cover">
                        <div>
                            <!-- Fix: Use studentRecord.user instead of student.user -->
                            <p class="font-medium text-gray-900">{{ $result->studentRecord?->user?->name ?? 'Unknown Student' }}</p>
                            <p class="text-sm text-gray-600">{{ $result->subject?->name ?? 'Unknown Subject' }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-indigo-600">{{ $result->total_score }}</p>
                        <p class="text-xs text-gray-500">{{ $result->created_at->diffForHumans() }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border-2 border-blue-100 rounded-2xl p-6 hover:shadow-lg transition-shadow cursor-pointer"
                 onclick="document.querySelector('[x-data] button:nth-child(2)').click()">
                <div class="flex items-center mb-4">
                    <div class="bg-blue-100 p-3 rounded-xl mr-4">
                        <i class="fas fa-upload text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800">Upload Results</h3>
                </div>
                <p class="text-gray-600">Upload individual or bulk student results</p>
            </div>

            <div class="bg-gradient-to-br from-green-50 to-teal-50 border-2 border-green-100 rounded-2xl p-6 hover:shadow-lg transition-shadow cursor-pointer"
                 onclick="document.querySelector('[x-data] button:nth-child(3)').click()">
                <div class="flex items-center mb-4">
                    <div class="bg-green-100 p-3 rounded-xl mr-4">
                        <i class="fas fa-eye text-green-600 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800">View Results</h3>
                </div>
                <p class="text-gray-600">View and analyze student performance</p>
            </div>

            <div class="bg-gradient-to-br from-purple-50 to-violet-50 border-2 border-purple-100 rounded-2xl p-6 hover:shadow-lg transition-shadow cursor-pointer"
                 onclick="document.querySelector('[x-data] button:nth-child(4)').click()">
                <div class="flex items-center mb-4">
                    <div class="bg-purple-100 p-3 rounded-xl mr-4">
                        <i class="fas fa-history text-purple-600 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800">Student History</h3>
                </div>
                <p class="text-gray-600">Track academic progress over time</p>
            </div>
        </div>
    @endif
</div>