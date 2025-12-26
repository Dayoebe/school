<div x-data="{ 
    showSubjectModal: @entangle('showSubjectModal')
}" class="space-y-6">

    <!-- Header Card -->
    <div class="bg-gradient-to-r from-blue-600 to-cyan-600 rounded-lg shadow-lg overflow-hidden">
        <div class="px-6 py-8">
            <div class="flex flex-col md:flex-row md:justify-between md:items-center space-y-4 md:space-y-0">
                <div class="text-white">
                    <h1 class="text-3xl font-bold mb-2">
                        <i class="fas fa-layer-group mr-3"></i>{{ $section->name }}
                    </h1>
                    <div class="flex items-center space-x-4 text-blue-100">
                        <span class="flex items-center">
                            <i class="fas fa-school mr-2"></i>{{ $section->myClass->name }}
                        </span>
                        <span class="flex items-center">
                            <i class="fas fa-users mr-2"></i>{{ $students->count() }} students
                        </span>
                    </div>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('sections.index') }}" 
                       class="bg-white/20 backdrop-blur-sm hover:bg-white/30 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center space-x-2 border border-white/30">
                        <i class="fas fa-arrow-left"></i>
                        <span>Back</span>
                    </a>
                    <button 
                        @click="showSubjectModal = true"
                        wire:click="openSubjectModal"
                        class="bg-white text-blue-600 px-6 py-3 rounded-lg font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 flex items-center space-x-2">
                        <i class="fas fa-book"></i>
                        <span>Manage Subjects</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if (session()->has('success'))
        <div x-data="{ show: true }" 
             x-show="show" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-90"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-init="setTimeout(() => show = false, 5000)"
             class="bg-green-50 border-l-4 border-green-500 p-4 rounded-r-lg flex items-center justify-between shadow-lg">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-500 text-xl mr-3"></i>
                <p class="text-green-800 font-medium">{{ session('success') }}</p>
            </div>
            <button @click="show = false" class="text-green-500 hover:text-green-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Students Panel -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden border border-gray-200 hover:shadow-xl transition-shadow duration-300">
            <div class="bg-gradient-to-r from-blue-500 to-indigo-500 px-6 py-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-users mr-3"></i>Students in Section
                    </h3>
                    <span class="bg-white/30 backdrop-blur-sm text-white px-4 py-1 rounded-full text-sm font-semibold border border-white/40">
                        {{ $students->count() }} students
                    </span>
                </div>
            </div>

            <div class="p-6">
                @if($students->isNotEmpty())
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        @foreach($students as $student)
                            <div class="flex items-center justify-between p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200 hover:shadow-md transition-all duration-200 hover:scale-[1.02]">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0 h-12 w-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center text-white font-bold shadow-md">
                                        {{ substr($student->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-900">{{ $student->name }}</h4>
                                        <p class="text-sm text-gray-600 flex items-center">
                                            <i class="fas fa-envelope mr-1 text-blue-500"></i>{{ $student->email }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex space-x-2">
                                    <a href="{{ route('students.show', $student->id) }}" 
                                       class="p-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-all duration-200 transform hover:scale-110 shadow"
                                       title="View Student">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('students.edit', $student->id) }}" 
                                       class="p-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg transition-all duration-200 transform hover:scale-110 shadow"
                                       title="Edit Student">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="inline-flex items-center justify-center w-20 h-20 bg-blue-100 rounded-full mb-4">
                            <i class="fas fa-user-slash text-blue-400 text-3xl"></i>
                        </div>
                        <h4 class="text-lg font-semibold text-gray-700 mb-2">No Students Yet</h4>
                        <p class="text-gray-500">Students will appear here once they are assigned to this section</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Subjects Panel -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden border border-gray-200 hover:shadow-xl transition-shadow duration-300">
            <div class="bg-gradient-to-r from-green-500 to-emerald-500 px-6 py-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-book mr-3"></i>Section Subjects
                    </h3>
                    <span class="bg-white/30 backdrop-blur-sm text-white px-4 py-1 rounded-full text-sm font-semibold border border-white/40">
                        {{ $section->subjects->count() }} subjects
                    </span>
                </div>
            </div>

            <div class="p-6">
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @forelse($section->subjects as $subject)
                        <div class="p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg border border-green-200 hover:shadow-md transition-all duration-200 group">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900 mb-2 flex items-center">
                                        <i class="fas fa-book-open mr-2 text-green-500"></i>{{ $subject->name }}
                                    </h4>
                                    <div class="flex items-center text-sm text-gray-600">
                                        <i class="fas fa-chalkboard-teacher mr-2 text-emerald-500"></i>
                                        <span class="font-medium">Teachers:</span>
                                        <span class="ml-2">
                                            @forelse($subject->teachers as $teacher)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-emerald-100 text-emerald-800 mr-1">
                                                    {{ $teacher->name }}
                                                </span>
                                            @empty
                                                <span class="text-gray-400 italic">No teacher assigned</span>
                                            @endforelse
                                        </span>
                                    </div>
                                </div>
                                <button 
                                    wire:click="detachSubject({{ $subject->id }})" 
                                    onclick="return confirm('Remove this subject from the section?')"
                                    class="ml-4 p-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition-all duration-200 transform hover:scale-110 shadow opacity-0 group-hover:opacity-100"
                                    title="Remove Subject">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12 bg-gradient-to-br from-yellow-50 to-orange-50 rounded-lg border-2 border-dashed border-yellow-300">
                            <div class="inline-flex items-center justify-center w-20 h-20 bg-yellow-100 rounded-full mb-4">
                                <i class="fas fa-book-open text-yellow-400 text-3xl"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-700 mb-2">No Subjects Assigned</h4>
                            <p class="text-gray-600 mb-4">Add subjects to this section to get started</p>
                            <button 
                                @click="showSubjectModal = true"
                                wire:click="openSubjectModal"
                                class="inline-flex items-center px-6 py-2.5 bg-gradient-to-r from-yellow-500 to-orange-500 text-white font-semibold rounded-lg hover:from-yellow-600 hover:to-orange-600 transition-all duration-200 transform hover:scale-105 shadow-lg">
                                <i class="fas fa-plus mr-2"></i>Add Subjects Now
                            </button>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Subject Management Modal -->
    <div x-show="showSubjectModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto" 
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-75" @click="showSubjectModal = false"></div>

            <div x-show="showSubjectModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95"
                 class="inline-block w-full max-w-5xl my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-2xl">
                
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-purple-600 to-pink-600 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold text-white flex items-center">
                            <i class="fas fa-book mr-3"></i>Manage Subjects for {{ $section->name }}
                        </h3>
                        <button @click="showSubjectModal = false" wire:click="closeSubjectModal" class="text-white hover:text-gray-200 transition-colors">
                            <i class="fas fa-times text-2xl"></i>
                        </button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <!-- Current Subjects Column -->
                        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg p-4 border border-blue-200">
                            <h4 class="font-bold text-gray-800 mb-4 flex items-center text-lg">
                                <i class="fas fa-list mr-2 text-blue-500"></i>
                                Current Subjects
                                <span class="ml-2 px-2 py-1 bg-blue-500 text-white rounded-full text-xs">
                                    {{ $section->subjects->count() }}
                                </span>
                            </h4>
                            <div class="space-y-2 max-h-96 overflow-y-auto">
                                @forelse($section->subjects as $subject)
                                    <div class="flex justify-between items-center p-3 bg-white rounded-lg border border-blue-200 hover:shadow-md transition-all duration-200">
                                        <span class="font-medium text-gray-800">{{ $subject->name }}</span>
                                        <button 
                                            wire:click="detachSubject({{ $subject->id }})" 
                                            onclick="return confirm('Remove this subject?')"
                                            class="p-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition-all duration-200 transform hover:scale-110">
                                            <i class="fas fa-trash text-sm"></i>
                                        </button>
                                    </div>
                                @empty
                                    <div class="text-center py-8 text-gray-500">
                                        <i class="fas fa-exclamation-circle text-3xl mb-2"></i>
                                        <p>No subjects assigned</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Add Subjects Column -->
                        <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-4 border border-green-200">
                            <h4 class="font-bold text-gray-800 mb-4 flex items-center text-lg">
                                <i class="fas fa-plus-circle mr-2 text-green-500"></i>
                                Add Subjects
                            </h4>
                            <form wire:submit.prevent="attachSubjects">
                                <!-- Search Input -->
                                <div class="mb-4">
                                    <div class="relative">
                                        <input type="text" 
                                               wire:model.live="subjectSearch" 
                                               placeholder="Search subjects..."
                                               class="w-full px-4 py-3 pl-10 border-2 border-green-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200">
                                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                    </div>
                                </div>

                                <!-- Subject Checkboxes -->
                                <div class="border-2 border-green-200 rounded-lg p-3 bg-white max-h-80 overflow-y-auto space-y-2">
                                    @if($this->filteredSubjects->isNotEmpty())
                                        @foreach($this->filteredSubjects as $subject)
                                            <label class="flex items-start p-3 hover:bg-green-50 rounded-lg cursor-pointer transition-colors duration-150 border border-transparent hover:border-green-200">
                                                <input type="checkbox" 
                                                       wire:model="selectedSubjects" 
                                                       value="{{ $subject->id }}" 
                                                       class="mt-1 h-5 w-5 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                                                <div class="ml-3 flex-1">
                                                    <span class="font-medium text-gray-900 block">{{ $subject->name }}</span>
                                                    <span class="text-xs text-gray-600 flex items-center mt-1">
                                                        <i class="fas fa-chalkboard-teacher mr-1 text-emerald-500"></i>
                                                        @forelse($subject->teachers as $teacher)
                                                            {{ $teacher->name }}@if(!$loop->last), @endif
                                                        @empty
                                                            <span class="text-gray-400 italic">No teacher</span>
                                                        @endforelse
                                                    </span>
                                                </div>
                                            </label>
                                        @endforeach
                                    @else
                                        <div class="text-center py-8 text-gray-500">
                                            <i class="fas fa-search text-3xl mb-2"></i>
                                            <p>No subjects found</p>
                                        </div>
                                    @endif
                                </div>

                                @error('selectedSubjects') 
                                    <p class="mt-2 text-sm text-red-600 flex items-center">
                                        <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                    </p>
                                @enderror

                                <!-- Action Buttons -->
                                <div class="flex justify-between mt-6 space-x-3">
                                    <button type="button" 
                                            @click="showSubjectModal = false"
                                            wire:click="closeSubjectModal"
                                            class="flex-1 px-6 py-3 border-2 border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-100 transition-all duration-200 flex items-center justify-center">
                                        <i class="fas fa-times mr-2"></i>Cancel
                                    </button>
                                    <button type="submit" 
                                            class="flex-1 px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-500 text-white font-semibold rounded-lg hover:from-green-600 hover:to-emerald-600 transition-all duration-200 transform hover:scale-105 shadow-lg flex items-center justify-center">
                                        <i class="fas fa-save mr-2"></i>Add Selected Subjects
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>