<div x-data="{ activeTab: @entangle('activeTab') }" class="space-y-6">
    
    <!-- Header Card -->
    <div class="bg-teal-600 rounded-lg shadow-lg overflow-hidden">
        <div class="px-6 py-8">
            <div class="flex flex-col md:flex-row items-center md:items-start gap-6">
                <img src="{{ $teacher->profile_photo_url }}" alt="{{ $teacher->name }}" 
                     class="w-32 h-32 rounded-full border-4 border-white shadow-lg object-cover">
                
                <div class="flex-1 text-white text-center md:text-left">
                    <h1 class="text-3xl font-bold mb-2">{{ $teacher->name }}</h1>
                    <div class="flex flex-wrap gap-4 justify-center md:justify-start text-sm">
                        <span class="flex items-center">
                            <i class="fas fa-envelope mr-2"></i>{{ $teacher->email }}
                        </span>
                        @if($teacher->phone)
                            <span class="flex items-center">
                                <i class="fas fa-phone mr-2"></i>{{ $teacher->phone }}
                            </span>
                        @endif
                        <span class="flex items-center">
                            <i class="fas fa-chalkboard-teacher mr-2"></i>
                            {{ $teacher->subjects->count() }} Subject{{ $teacher->subjects->count() !== 1 ? 's' : '' }}
                        </span>
                        @if($teacher->gender)
                            <span class="flex items-center">
                                <i class="fas fa-{{ $teacher->gender === 'male' ? 'male' : 'female' }} mr-2"></i>
                                {{ ucfirst($teacher->gender) }}
                            </span>
                        @endif
                    </div>
                </div>
                
                <div class="flex gap-3">
                    <a href="{{ route('teachers.index') }}" 
                       class="px-4 py-2 bg-white/20 backdrop-blur-sm hover:bg-white/30 text-white rounded-lg transition">
                        <i class="fas fa-arrow-left mr-2"></i>Back
                    </a>
                    <a href="{{ route('teachers.edit', $teacher->id) }}" 
                       class="px-4 py-2 bg-white text-indigo-600 rounded-lg font-semibold shadow hover:shadow-lg transition">
                        <i class="fas fa-edit mr-2"></i>Edit Profile
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="flex border-b overflow-x-auto">
            <button @click="activeTab = 'profile'" 
                    :class="activeTab === 'profile' ? 'border-b-2 border-indigo-600 text-indigo-600' : 'text-gray-600 hover:text-indigo-600'"
                    class="px-6 py-4 font-semibold transition whitespace-nowrap">
                <i class="fas fa-user mr-2"></i>Profile
            </button>
            <button @click="activeTab = 'subjects'" 
                    :class="activeTab === 'subjects' ? 'border-b-2 border-indigo-600 text-indigo-600' : 'text-gray-600 hover:text-indigo-600'"
                    class="px-6 py-4 font-semibold transition whitespace-nowrap">
                <i class="fas fa-book mr-2"></i>Subjects ({{ $teacher->subjects->count() }})
            </button>
            <button @click="activeTab = 'timetable'" 
                    :class="activeTab === 'timetable' ? 'border-b-2 border-indigo-600 text-indigo-600' : 'text-gray-600 hover:text-indigo-600'"
                    class="px-6 py-4 font-semibold transition whitespace-nowrap">
                <i class="fas fa-calendar-alt mr-2"></i>Timetable
            </button>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
            <!-- Profile Tab -->
            <div x-show="activeTab === 'profile'" x-transition>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Personal Information</h3>
                        
                        <div class="flex justify-between border-b pb-3">
                            <span class="font-semibold text-gray-600">Full Name:</span>
                            <span class="text-gray-900">{{ $teacher->name }}</span>
                        </div>
                        <div class="flex justify-between border-b pb-3">
                            <span class="font-semibold text-gray-600">Email:</span>
                            <span class="text-gray-900">{{ $teacher->email }}</span>
                        </div>
                        <div class="flex justify-between border-b pb-3">
                            <span class="font-semibold text-gray-600">Gender:</span>
                            <span class="text-gray-900">{{ ucfirst($teacher->gender ?? 'N/A') }}</span>
                        </div>
                        <div class="flex justify-between border-b pb-3">
                            <span class="font-semibold text-gray-600">Birthday:</span>
                            <span class="text-gray-900">
                                @if($teacher->birthday)
                                    {{ $teacher->birthday->format('M d, Y') }}
                                @else
                                    N/A
                                @endif
                            </span>
                        </div>
                        <div class="flex justify-between border-b pb-3">
                            <span class="font-semibold text-gray-600">Phone:</span>
                            <span class="text-gray-900">{{ $teacher->phone ?? 'N/A' }}</span>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Additional Information</h3>
                        
                        <div class="flex justify-between border-b pb-3">
                            <span class="font-semibold text-gray-600">Blood Group:</span>
                            <span class="text-gray-900">{{ $teacher->blood_group ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between border-b pb-3">
                            <span class="font-semibold text-gray-600">Religion:</span>
                            <span class="text-gray-900">{{ $teacher->religion ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between border-b pb-3">
                            <span class="font-semibold text-gray-600">Nationality:</span>
                            <span class="text-gray-900">{{ $teacher->nationality ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between border-b pb-3">
                            <span class="font-semibold text-gray-600">State:</span>
                            <span class="text-gray-900">{{ $teacher->state ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between border-b pb-3">
                            <span class="font-semibold text-gray-600">City:</span>
                            <span class="text-gray-900">{{ $teacher->city ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between border-b pb-3">
                            <span class="font-semibold text-gray-600">Address:</span>
                            <span class="text-gray-900 text-right">{{ $teacher->address ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subjects Tab -->
            <div x-show="activeTab === 'subjects'" x-transition>
                <div class="space-y-6">
                    <!-- Assign New Subject -->
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-6 rounded-lg">
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Assign Subjects</h3>
                        
                        <div class="mb-4">
                            <input type="text" wire:model.live="subjectSearch" 
                                   placeholder="Search subjects to assign..." 
                                   class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-indigo-500">
                        </div>
                        
                        @if($subjectSearch)
                            <div class="bg-white rounded-lg border p-4 max-h-64 overflow-y-auto">
                                @forelse($availableSubjects as $subject)
                                    <div class="flex items-center justify-between p-3 border-b last:border-b-0 hover:bg-gray-50">
                                        <div>
                                            <span class="font-medium">{{ $subject->name }}</span>
                                            <span class="text-sm text-gray-600 ml-2">({{ $subject->short_name }})</span>
                                            <div class="text-sm text-gray-500">
                                                Class: {{ $subject->myClass->name ?? 'N/A' }}
                                            </div>
                                        </div>
                                        <button wire:click="assignSubject({{ $subject->id }})" 
                                                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-sm">
                                            <i class="fas fa-plus mr-1"></i>Assign
                                        </button>
                                    </div>
                                @empty
                                    <p class="text-gray-500 text-center py-4">No subjects found</p>
                                @endforelse
                            </div>
                        @endif
                    </div>

                    <!-- Current Subjects -->
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Assigned Subjects ({{ $teacher->subjects->count() }})</h3>
                        
                        @if($teacher->subjects->count() > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($teacher->subjects as $subject)
                                    <div class="bg-white border rounded-lg p-4 shadow-sm hover:shadow-md transition">
                                        <div class="flex justify-between items-start mb-3">
                                            <div>
                                                <h4 class="font-bold text-gray-900">{{ $subject->name }}</h4>
                                                <p class="text-sm text-gray-600">{{ $subject->short_name }}</p>
                                            </div>
                                            <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded">
                                                {{ $subject->myClass->name ?? 'N/A' }}
                                            </span>
                                        </div>
                                        
                                        <div class="flex justify-between items-center mt-4 pt-4 border-t">
                                            <a href="{{ route('subjects.show', $subject->id) }}" 
                                               class="text-sm text-indigo-600 hover:text-indigo-800">
                                                <i class="fas fa-external-link-alt mr-1"></i>View Subject
                                            </a>
                                            <button wire:click="removeSubject({{ $subject->id }})" 
                                                    wire:confirm="Are you sure you want to remove this subject from this teacher?"
                                                    class="text-sm text-red-600 hover:text-red-800">
                                                <i class="fas fa-trash mr-1"></i>Remove
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8 bg-gray-50 rounded-lg">
                                <i class="fas fa-book text-gray-300 text-4xl mb-3"></i>
                                <p class="text-gray-500">No subjects assigned to this teacher</p>
                                <p class="text-gray-400 text-sm mt-1">Use the search above to assign subjects</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Timetable Tab -->
            <div x-show="activeTab === 'timetable'" x-transition>
                <div class="text-center py-12 bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg">
                    <i class="fas fa-calendar-alt text-gray-300 text-5xl mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-700 mb-2">Teacher Timetable</h3>
                    <p class="text-gray-500 max-w-md mx-auto">
                        This teacher's timetable will be displayed here once classes and schedules are assigned.
                    </p>
                    <a href="{{ route('timetables.index') }}" 
                       class="inline-block mt-4 px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                        <i class="fas fa-calendar-plus mr-2"></i>Manage Timetables
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>