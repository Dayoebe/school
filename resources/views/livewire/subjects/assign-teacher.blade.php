<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <h2 class="text-2xl font-bold text-gray-800">
                <i class="fas fa-user-plus text-indigo-600 mr-2"></i>Assign Teachers to Subjects
            </h2>
            
            <div class="flex gap-3">
                <button wire:click="toggleBulkMode" 
                        class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition">
                    @if($bulkMode)
                        <i class="fas fa-times mr-2"></i>Exit Bulk Mode
                    @else
                        <i class="fas fa-users mr-2"></i>Bulk Assign
                    @endif
                </button>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-400 text-xl mr-3"></i>
                <p class="text-green-700">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if($bulkMode)
        <!-- Bulk Assignment Mode -->
        <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Bulk Assign Teacher</h3>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Teacher Selection -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Select Teacher *</label>
                    <div class="mb-4">
                        <input type="text" wire:model.live="searchTeacher" 
                               placeholder="Search teachers..."
                               class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-purple-500">
                    </div>
                    
                    <div class="max-h-60 overflow-y-auto bg-white rounded-lg border">
                        @foreach($teachers as $teacher)
                            <div class="flex items-center justify-between p-3 border-b hover:bg-gray-50">
                                <div>
                                    <span class="font-medium">{{ $teacher->name }}</span>
                                    <p class="text-sm text-gray-600">{{ $teacher->email }}</p>
                                </div>
                                <button type="button"
                                        wire:click="$set('bulkTeacher', {{ $teacher->id }})"
                                        class="px-3 py-1 rounded {{ $bulkTeacher == $teacher->id ? 'bg-purple-600 text-white' : 'bg-gray-200 text-gray-800' }}">
                                    {{ $bulkTeacher == $teacher->id ? '✓ Selected' : 'Select' }}
                                </button>
                            </div>
                        @endforeach
                    </div>
                    
                    @error('bulkTeacher') 
                        <span class="text-red-500 text-sm mt-2">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Assignment Type -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Assignment Type *</label>
                    
                    <div class="space-y-3">
                        <button type="button"
                                wire:click="$set('bulkIsGeneral', true)"
                                class="w-full text-left p-4 rounded-lg border-2 {{ $bulkIsGeneral ? 'bg-purple-100 border-purple-500' : 'bg-white border-gray-300' }}">
                            <div class="flex items-center">
                                <i class="fas fa-users text-2xl mr-3 {{ $bulkIsGeneral ? 'text-purple-600' : 'text-gray-400' }}"></i>
                                <div>
                                    <p class="font-semibold">General Assignment</p>
                                    <p class="text-xs text-gray-600">Teacher teaches ALL classes for selected subjects</p>
                                </div>
                            </div>
                        </button>
                        
                        <button type="button"
                                wire:click="$set('bulkIsGeneral', false)"
                                class="w-full text-left p-4 rounded-lg border-2 {{ !$bulkIsGeneral ? 'bg-blue-100 border-blue-500' : 'bg-white border-gray-300' }}">
                            <div class="flex items-center">
                                <i class="fas fa-chalkboard text-2xl mr-3 {{ !$bulkIsGeneral ? 'text-blue-600' : 'text-gray-400' }}"></i>
                                <div>
                                    <p class="font-semibold">Class-Specific</p>
                                    <p class="text-xs text-gray-600">Teacher teaches only ONE class</p>
                                </div>
                            </div>
                        </button>
                    </div>
                    
                    <!-- Class Selection (if class-specific) -->
                    @if(!$bulkIsGeneral)
                        <div class="mt-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Select Class *</label>
                            <select wire:model="bulkClass" 
                                    class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-blue-500">
                                <option value="">Choose a class...</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                            @error('bulkClass') 
                                <span class="text-red-500 text-sm mt-2">{{ $message }}</span>
                            @enderror
                        </div>
                    @endif
                </div>

                <!-- Subjects List -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Select Subjects *</label>
                    <div class="mb-4">
                        <input type="text" wire:model.live="searchSubject" 
                               placeholder="Search subjects..."
                               class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-purple-500">
                    </div>
                    
                    <div class="max-h-60 overflow-y-auto bg-white rounded-lg border">
                        @foreach($subjects as $subject)
                            <div class="p-3 border-b hover:bg-gray-50">
                                <div class="flex items-start justify-between mb-2">
                                    <div class="flex-1">
                                        <span class="font-medium">{{ $subject->name }}</span>
                                        <p class="text-xs text-gray-600">{{ $subject->short_name }}</p>
                                    </div>
                                    <button type="button"
                                            wire:click="toggleBulkSubject({{ $subject->id }})"
                                            class="px-3 py-1 rounded {{ in_array($subject->id, $bulkSubjects) ? 'bg-purple-600 text-white' : 'bg-gray-200 text-gray-800' }}">
                                        {{ in_array($subject->id, $bulkSubjects) ? '✓' : '+' }}
                                    </button>
                                </div>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($subject->classes as $class)
                                        <span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded text-xs">
                                            {{ $class->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    @error('bulkSubjects') 
                        <span class="text-red-500 text-sm mt-2">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Bulk Action Buttons -->
            <div class="flex justify-end gap-3 mt-6 pt-6 border-t">
                <button wire:click="toggleBulkMode" 
                        class="px-6 py-2.5 border-2 border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-100">
                    Cancel
                </button>
                <button wire:click="bulkAssignTeacher" 
                        wire:loading.attr="disabled"
                        class="px-6 py-2.5 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 shadow-lg">
                    <i class="fas fa-users mr-2"></i>
                    Assign to {{ count($bulkSubjects) }} Subject{{ count($bulkSubjects) !== 1 ? 's' : '' }}
                </button>
            </div>
        </div>
    @else
        <!-- Individual Assignment Mode -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-gradient-to-r from-indigo-600 to-blue-600 px-6 py-4">
                <h3 class="text-xl font-bold text-white">
                    <i class="fas fa-link mr-2"></i>Individual Teacher Assignment
                </h3>
            </div>

            <form wire:submit.prevent="assign" class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Subject Selection -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Select Subject *</label>
                        <div class="mb-4">
                            <input type="text" wire:model.live="searchSubject" 
                                   placeholder="Search subjects..."
                                   class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-indigo-500">
                        </div>
                        
                        <div class="max-h-60 overflow-y-auto bg-gray-50 rounded-lg border">
                            @foreach($subjects as $subject)
                                <div class="p-3 border-b hover:bg-white">
                                    <div class="flex items-start justify-between mb-2">
                                        <div class="flex-1">
                                            <span class="font-medium">{{ $subject->name }}</span>
                                            <p class="text-xs text-gray-600">{{ $subject->short_name }}</p>
                                        </div>
                                        <button type="button" 
                                                wire:click="$set('selectedSubject', {{ $subject->id }})"
                                                class="px-3 py-1 rounded {{ $selectedSubject == $subject->id ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-800' }}">
                                            {{ $selectedSubject == $subject->id ? '✓' : 'Select' }}
                                        </button>
                                    </div>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($subject->classes as $class)
                                            <span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded text-xs">
                                                {{ $class->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        @error('selectedSubject') 
                            <span class="text-red-500 text-sm mt-2">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Assignment Type & Class -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Assignment Type *</label>
                        
                        <div class="space-y-3">
                            <button type="button"
                                    wire:click="$set('isGeneralAssignment', true)"
                                    class="w-full text-left p-4 rounded-lg border-2 {{ $isGeneralAssignment ? 'bg-purple-100 border-purple-500' : 'bg-white border-gray-300' }}">
                                <div class="flex items-center">
                                    <i class="fas fa-users text-2xl mr-3 {{ $isGeneralAssignment ? 'text-purple-600' : 'text-gray-400' }}"></i>
                                    <div>
                                        <p class="font-semibold">All Classes</p>
                                        <p class="text-xs text-gray-600">General assignment</p>
                                    </div>
                                </div>
                            </button>
                            
                            <button type="button"
                                    wire:click="$set('isGeneralAssignment', false)"
                                    class="w-full text-left p-4 rounded-lg border-2 {{ !$isGeneralAssignment ? 'bg-blue-100 border-blue-500' : 'bg-white border-gray-300' }}">
                                <div class="flex items-center">
                                    <i class="fas fa-chalkboard text-2xl mr-3 {{ !$isGeneralAssignment ? 'text-blue-600' : 'text-gray-400' }}"></i>
                                    <div>
                                        <p class="font-semibold">Specific Class</p>
                                        <p class="text-xs text-gray-600">Class-specific</p>
                                    </div>
                                </div>
                            </button>
                        </div>
                        
                        @if(!$isGeneralAssignment && $selectedSubject)
                            @php
                                $selectedSubjectModel = $subjects->firstWhere('id', $selectedSubject);
                            @endphp
                            @if($selectedSubjectModel)
                                <div class="mt-4">
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Select Class *</label>
                                    <select wire:model="selectedClass" 
                                            class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-blue-500">
                                        <option value="">Choose a class...</option>
                                        @foreach($selectedSubjectModel->classes as $class)
                                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('selectedClass') 
                                        <span class="text-red-500 text-sm mt-2">{{ $message }}</span>
                                    @enderror
                                </div>
                            @endif
                        @endif
                    </div>

                    <!-- Teacher Selection -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Select Teacher *</label>
                        <div class="mb-4">
                            <input type="text" wire:model.live="searchTeacher" 
                                   placeholder="Search teachers..."
                                   class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-indigo-500">
                        </div>
                        
                        <div class="max-h-60 overflow-y-auto bg-gray-50 rounded-lg border">
                            @foreach($teachers as $teacher)
                                <div class="flex items-center justify-between p-3 border-b hover:bg-white">
                                    <div>
                                        <span class="font-medium">{{ $teacher->name }}</span>
                                        <p class="text-sm text-gray-600">{{ $teacher->email }}</p>
                                    </div>
                                    <button type="button" 
                                            wire:click="$set('selectedTeacher', {{ $teacher->id }})"
                                            class="px-3 py-1 rounded {{ $selectedTeacher == $teacher->id ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-800' }}">
                                        {{ $selectedTeacher == $teacher->id ? '✓' : 'Select' }}
                                    </button>
                                </div>
                            @endforeach
                        </div>
                        
                        @error('selectedTeacher') 
                            <span class="text-red-500 text-sm mt-2">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end mt-6 pt-6 border-t">
                    <button type="submit" 
                            wire:loading.attr="disabled"
                            class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 text-white font-semibold rounded-lg hover:from-indigo-700 hover:to-blue-700 shadow-lg">
                        <i class="fas fa-link mr-2"></i>Assign Teacher
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- Current Assignments -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="bg-gradient-to-r from-green-600 to-teal-600 px-6 py-4">
            <h3 class="text-xl font-bold text-white">
                <i class="fas fa-list-check mr-2"></i>Current Assignments
            </h3>
        </div>

        <div class="p-6">
            @if(count($subjects) > 0)
                <div class="space-y-4">
                    @foreach($subjects as $subject)
                        @if($subject->teachers->count() > 0)
                            <div class="border rounded-lg p-4 hover:shadow-md transition">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <h4 class="font-bold text-gray-900">{{ $subject->name }}</h4>
                                        <div class="flex flex-wrap gap-2 mt-2">
                                            @foreach($subject->classes as $class)
                                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">
                                                    {{ $class->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold">
                                        {{ $subject->teachers->count() }} teacher{{ $subject->teachers->count() !== 1 ? 's' : '' }}
                                    </span>
                                </div>
                                
                                <div class="space-y-2">
                                    @foreach($subject->teachers as $teacher)
                                        <div class="flex items-center justify-between bg-gray-50 px-3 py-2 rounded-lg">
                                            <div class="flex items-center gap-3">
                                                <i class="fas fa-user text-gray-600"></i>
                                                <div>
                                                    <span class="text-gray-800 font-medium">{{ $teacher->name }}</span>
                                                    @if($teacher->pivot->is_general)
                                                        <span class="ml-2 px-2 py-0.5 bg-purple-100 text-purple-800 rounded text-xs">
                                                            <i class="fas fa-users mr-1"></i>All Classes
                                                        </span>
                                                    @else
                                                        <span class="ml-2 px-2 py-0.5 bg-blue-100 text-blue-800 rounded text-xs">
                                                            <i class="fas fa-chalkboard mr-1"></i>
                                                            {{ $classes->firstWhere('id', $teacher->pivot->my_class_id)?->name ?? 'Unknown' }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <button wire:click="removeTeacher({{ $subject->id }}, {{ $teacher->id }}, {{ $teacher->pivot->my_class_id }})" 
                                                    wire:confirm="Remove this teacher assignment?"
                                                    class="text-red-500 hover:text-red-700">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <i class="fas fa-lightbulb text-gray-300 text-4xl mb-3"></i>
                    <p class="text-gray-500">No subjects found</p>
                </div>
            @endif
        </div>
    </div>
</div>