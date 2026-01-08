<div class="space-y-6">
    
    <!-- Header -->
    <div class="bg-teal-600 rounded-lg shadow-lg overflow-hidden">
        <div class="px-6 py-8">
            <div class="flex flex-col md:flex-row items-center md:items-start gap-6">
                <img src="{{ $parent->profile_photo_url }}" alt="{{ $parent->name }}" 
                     class="w-24 h-24 rounded-full border-4 border-white shadow-lg object-cover">
                
                <div class="flex-1 text-white text-center md:text-left">
                    <h1 class="text-3xl font-bold mb-2">Assign Students to {{ $parent->name }}</h1>
                    <p class="text-white/80">Currently assigned: {{ $assignedStudents->count() }} {{ Str::plural('student', $assignedStudents->count()) }}</p>
                </div>
                
                <a href="{{ route('parents.show', $parent->id) }}" 
                   class="px-4 py-2 bg-white/20 backdrop-blur-sm hover:bg-white/30 text-white rounded-lg transition">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Profile
                </a>
            </div>
        </div>
    </div>

    <!-- Assigned Students -->
    @if($assignedStudents->isNotEmpty())
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="bg-green-600 px-6 py-4">
                <h2 class="text-xl font-bold text-white">
                    <i class="fas fa-check-circle mr-2"></i>Assigned Students ({{ $assignedStudents->count() }})
                </h2>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($assignedStudents as $student)
                        <div class="bg-green-50 border-2 border-green-200 rounded-lg p-4">
                            <div class="flex items-start gap-3">
                                <img src="{{ $student->profile_photo_url }}" 
                                     alt="{{ $student->name }}" 
                                     class="w-12 h-12 rounded-full object-cover border-2 border-green-300">
                                <div class="flex-1">
                                    <h4 class="font-bold text-gray-900">{{ $student->name }}</h4>
                                    <p class="text-sm text-gray-600">
                                        {{ $student->studentRecord->myClass->name ?? 'N/A' }}
                                        @if($student->studentRecord && $student->studentRecord->section)
                                            - {{ $student->studentRecord->section->name }}
                                        @endif
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $student->studentRecord->admission_number ?? 'N/A' }}
                                    </p>
                                </div>
                            </div>
                            <button wire:click="removeStudent({{ $student->id }})" 
                                    wire:confirm="Are you sure you want to remove this student from {{ $parent->name }}?"
                                    class="mt-3 w-full px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm font-semibold">
                                <i class="fas fa-times mr-2"></i>Remove
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Available Students to Assign -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="bg-blue-600 px-6 py-4">
            <h2 class="text-xl font-bold text-white">
                <i class="fas fa-user-plus mr-2"></i>Available Students to Assign
            </h2>
        </div>

        <!-- Filters -->
        <div class="p-6 bg-gray-50 border-b">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <input wire:model.live.debounce.300ms="search" 
                       type="text" 
                       placeholder="Search students..." 
                       class="rounded-lg border-2 border-gray-300 px-4 py-2.5 focus:ring-2 focus:ring-blue-500">
                
                <select wire:model.live="selectedClass" 
                        class="rounded-lg border-2 border-gray-300 px-4 py-2.5 focus:ring-2 focus:ring-blue-500">
                    <option value="">All Classes</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>

                @if($sections->isNotEmpty())
                    <select wire:model.live="selectedSection" 
                            class="rounded-lg border-2 border-gray-300 px-4 py-2.5 focus:ring-2 focus:ring-blue-500">
                        <option value="">All Sections</option>
                        @foreach($sections as $section)
                            <option value="{{ $section->id }}">{{ $section->name }}</option>
                        @endforeach
                    </select>
                @endif
            </div>
        </div>

        <!-- Student List -->
        <div class="p-6">
            @if($availableStudents->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($availableStudents as $student)
                        <div class="bg-gray-50 border-2 border-gray-200 rounded-lg p-4 hover:border-blue-400 transition">
                            <div class="flex items-start gap-3">
                                <img src="{{ $student->profile_photo_url }}" 
                                     alt="{{ $student->name }}" 
                                     class="w-12 h-12 rounded-full object-cover border-2 border-gray-300">
                                <div class="flex-1">
                                    <h4 class="font-bold text-gray-900">{{ $student->name }}</h4>
                                    <p class="text-sm text-gray-600">
                                        {{ $student->studentRecord->myClass->name ?? 'N/A' }}
                                        @if($student->studentRecord && $student->studentRecord->section)
                                            - {{ $student->studentRecord->section->name }}
                                        @endif
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $student->studentRecord->admission_number ?? 'N/A' }}
                                    </p>
                                </div>
                            </div>
                            <button wire:click="assignStudent({{ $student->id }})" 
                                    class="mt-3 w-full px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-semibold">
                                <i class="fas fa-plus mr-2"></i>Assign to Parent
                            </button>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $availableStudents->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-search text-gray-300 text-5xl mb-4"></i>
                    <p class="text-lg text-gray-500">No students found</p>
                    @if($search || $selectedClass || $selectedSection)
                        <button wire:click="$set('search', ''); $set('selectedClass', ''); $set('selectedSection', '')" 
                                class="mt-4 px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                            Clear Filters
                        </button>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>