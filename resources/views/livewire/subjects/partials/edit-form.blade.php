<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="bg-gradient-to-r from-yellow-600 to-amber-600 px-6 py-4">
        <div class="flex items-center justify-between">
            <h3 class="text-xl font-bold text-white">
                <i class="fas fa-lightbulb mr-2"></i>Edit Subject
            </h3>
            <button wire:click="switchMode('list')" class="text-white hover:text-gray-200">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>
    </div>

    <form wire:submit="updateSubject" class="p-6">
        <div class="space-y-6">
            <!-- Basic Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Subject Name *</label>
                    <input type="text" wire:model="name" 
                           class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-yellow-500"
                           placeholder="e.g., Mathematics">
                    @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Short Name/Code *</label>
                    <input type="text" wire:model="short_name" 
                           class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-yellow-500"
                           placeholder="e.g., MATH">
                    @error('short_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Assign to Classes (UPDATED - Now Optional) -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-3">
                    Assign to Classes 
                    <span class="text-gray-500 font-normal">(Optional - Select classes that will take this subject)</span>
                </label>
                
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 p-4 bg-gray-50 rounded-lg max-h-80 overflow-y-auto">
                    @foreach($classes as $class)
                        <div class="flex items-center">
                            <button type="button" 
                                    wire:click="toggleClass({{ $class->id }})"
                                    class="w-full text-left px-4 py-3 rounded-lg border-2 transition {{ in_array($class->id, $selectedClasses) ? 'bg-yellow-100 border-yellow-500 text-yellow-800' : 'bg-white border-gray-300 text-gray-700 hover:border-yellow-300' }}">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium">{{ $class->name }}</span>
                                    @if(in_array($class->id, $selectedClasses))
                                        <i class="fas fa-check-circle text-yellow-600"></i>
                                    @endif
                                </div>
                                @if($class->classGroup)
                                    <span class="text-xs text-gray-600">{{ $class->classGroup->name }}</span>
                                @endif
                            </button>
                        </div>
                    @endforeach
                </div>
                
                @error('selectedClasses') <span class="text-red-500 text-sm mt-2 block">{{ $message }}</span> @enderror
                
                @if(count($selectedClasses) > 0)
                    <p class="text-sm text-yellow-600 mt-2">
                        <i class="fas fa-check mr-1"></i>{{ count($selectedClasses) }} class{{ count($selectedClasses) !== 1 ? 'es' : '' }} selected
                    </p>
                @else
                    <p class="text-sm text-gray-500 mt-2">
                        <i class="fas fa-info-circle mr-1"></i>No classes selected - This subject won't be assigned to any class
                    </p>
                @endif
            </div>

            <!-- Assign Teachers (IMPROVED) -->
            <div class="border-t pt-6">
                <label class="block text-sm font-semibold text-gray-700 mb-3">
                    Assign Teachers 
                    <span class="text-gray-500 font-normal">(Optional - Can be general or class-specific)</span>
                </label>
                
                @if(count($selectedClasses) === 0)
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                        <p class="text-sm text-blue-800">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Note:</strong> Since no classes are selected, teacher assignments will be general only.
                        </p>
                    </div>
                @endif
                
                <div class="mb-4">
                    <input type="text" wire:model.live="teacherSearch" 
                           class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-yellow-500"
                           placeholder="Search teachers by name...">
                </div>
                
                @if($teacherSearch)
                    <div class="bg-gray-50 p-4 rounded-lg mb-4 max-h-64 overflow-y-auto">
                        @forelse($this->teachers as $teacher)
                            <div class="flex items-center justify-between p-3 hover:bg-white rounded border-b last:border-b-0">
                                <div>
                                    <span class="font-medium">{{ $teacher->name }}</span>
                                    <span class="text-sm text-gray-600 ml-2">{{ $teacher->email }}</span>
                                </div>
                                <button type="button" 
                                        wire:click="toggleTeacher({{ $teacher->id }})"
                                        class="px-4 py-2 rounded-lg {{ in_array($teacher->id, $selectedTeachers) ? 'bg-yellow-100 text-yellow-800 border-2 border-yellow-500' : 'bg-gray-200 text-gray-800 hover:bg-gray-300' }}">
                                    {{ in_array($teacher->id, $selectedTeachers) ? '✓ Selected' : 'Select' }}
                                </button>
                            </div>
                        @empty
                            <p class="text-gray-500 text-center py-4">No teachers found</p>
                        @endforelse
                    </div>
                @endif

                <!-- Selected Teachers with Assignment Options (IMPROVED) -->
                @if(count($selectedTeachers) > 0)
                    <div class="mt-4 space-y-3">
                        <h4 class="text-sm font-semibold text-gray-700">Selected Teachers & Their Assignments:</h4>
                        
                        @foreach($selectedTeachers as $teacherId)
                            @php
                                $teacher = $this->teachers->firstWhere('id', $teacherId);
                                $assignment = $teacherAssignments[$teacherId] ?? ['class_id' => null, 'is_general' => true];
                            @endphp
                            @if($teacher)
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <div class="flex items-start justify-between mb-3">
                                        <div>
                                            <span class="font-medium text-gray-900">{{ $teacher->name }}</span>
                                            <p class="text-xs text-gray-600">{{ $teacher->email }}</p>
                                        </div>
                                        <button type="button" 
                                                wire:click="removeTeacher({{ $teacherId }})"
                                                class="text-red-500 hover:text-red-700">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    
                                    <div class="space-y-2">
                                        <label class="text-xs font-semibold text-gray-700">Teaching Assignment:</label>
                                        
                                        <!-- General Assignment Option -->
                                        <div>
                                            <button type="button"
                                                    wire:click="setTeacherAsGeneral({{ $teacherId }})"
                                                    class="w-full text-left px-3 py-2 rounded border-2 transition {{ $assignment['is_general'] ? 'bg-purple-100 border-purple-500 text-purple-800' : 'bg-white border-gray-300 text-gray-700 hover:border-purple-300' }}">
                                                <i class="fas fa-users mr-2"></i>
                                                <span class="font-medium">All Classes</span>
                                                @if(count($selectedClasses) > 0)
                                                    <span class="text-xs ml-2">(General teacher for all {{ count($selectedClasses) }} classes)</span>
                                                @else
                                                    <span class="text-xs ml-2">(General assignment - no classes selected)</span>
                                                @endif
                                            </button>
                                        </div>
                                        
                                        <!-- Class-Specific Options -->
                                        @if(count($selectedClasses) > 0)
                                            <details class="mt-2" {{ !$assignment['is_general'] ? 'open' : '' }}>
                                                <summary class="cursor-pointer text-xs text-blue-600 hover:text-blue-800 font-medium">
                                                    Or assign to specific class
                                                </summary>
                                                <div class="mt-2 space-y-1 pl-4">
                                                    @foreach($selectedClasses as $classId)
                                                        @php
                                                            $class = $classes->firstWhere('id', $classId);
                                                        @endphp
                                                        @if($class)
                                                            <button type="button"
                                                                    wire:click="setTeacherClassAssignment({{ $teacherId }}, {{ $classId }})"
                                                                    class="block w-full text-left px-3 py-2 rounded border text-sm {{ !$assignment['is_general'] && $assignment['class_id'] == $classId ? 'bg-blue-100 border-blue-500 text-blue-800' : 'bg-white border-gray-300 text-gray-700 hover:border-blue-300' }}">
                                                                <i class="fas fa-chalkboard mr-2"></i>
                                                                {{ $class->name }}
                                                                @if($class->classGroup)
                                                                    <span class="text-xs text-gray-600">({{ $class->classGroup->name }})</span>
                                                                @endif
                                                            </button>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </details>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="flex justify-end gap-3 mt-8 pt-6 border-t">
            <button type="button" wire:click="switchMode('list')"
                    class="px-6 py-2.5 border-2 border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-100">
                Cancel
            </button>
            <button type="submit" 
                    wire:loading.attr="disabled"
                    class="px-6 py-2.5 bg-gradient-to-r from-yellow-600 to-amber-600 text-white font-semibold rounded-lg hover:from-yellow-700 hover:to-amber-700 shadow-lg disabled:opacity-50">
                <i class="fas fa-save mr-2"></i>
                <span wire:loading.remove>Update Subject</span>
                <span wire:loading>Updating...</span>
            </button>
        </div>
    </form>
</div>