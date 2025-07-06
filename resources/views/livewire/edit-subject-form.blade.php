<div class="max-w-4xl mx-auto p-4">
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <!-- Card Header -->
        <div class="bg-gray-50 px-6 py-4 border-b">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-semibold text-gray-800">
                    <i class="fas fa-book text-indigo-500 mr-2"></i>
                    Edit Subject: {{ $subject->name }}
                </h3>
                <a href="{{ route('subjects.index') }}" 
                   class="text-sm text-indigo-600 hover:text-indigo-800 flex items-center">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Subjects
                </a>
            </div>
        </div>

        <!-- Success Message -->
        @if (session()->has('message'))
        <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700">{{ session('message') }}</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Card Body -->
        <div class="p-6">
            <form wire:submit.prevent="updateSubject">
                <div class="space-y-6">
                    <!-- Name Field -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                            Subject Name
                        </label>
                        <input type="text" id="name" wire:model="name"
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="Enter subject name" required>
                        @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Short Name Field -->
                    <div>
                        <label for="short_name" class="block text-sm font-medium text-gray-700 mb-1">
                            Short Name
                        </label>
                        <input type="text" id="short_name" wire:model="short_name"
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="Enter short name" required>
                        @error('short_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Teachers Select -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Assign Teachers
                        </label>
                        
                        <!-- Search Input -->
                        <div class="relative mb-2">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input type="text" 
                                   wire:model.debounce.300ms="search"
                                   class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                   placeholder="Search teachers...">
                        </div>
                        
                        <!-- Selected Teachers -->
                        <div class="flex flex-wrap gap-2 mb-3">
                            @foreach($this->getTeachersProperty()->whereIn('id', $selectedTeachers) as $teacher)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                    {{ $teacher->name }}
                                    <button type="button" 
                                            wire:click="removeTeacher({{ $teacher->id }})"
                                            class="ml-1.5 inline-flex text-indigo-400 hover:text-indigo-600 focus:outline-none">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </span>
                            @endforeach
                        </div>
                        
                        <!-- Teachers List -->
                        <div class="border rounded-md divide-y divide-gray-200 max-h-60 overflow-y-auto">
                            @forelse($this->getTeachersProperty() as $teacher)
                                <div class="p-3 hover:bg-gray-50 flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                            <i class="fas fa-user text-indigo-600"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $teacher->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $teacher->email }}</div>
                                        </div>
                                    </div>
                                    <input type="checkbox" 
                                           wire:model="selectedTeachers"
                                           value="{{ $teacher->id }}"
                                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                </div>
                            @empty
                                <div class="p-4 text-center text-gray-500">
                                    No teachers found
                                </div>
                            @endforelse
                        </div>
                        @error('selectedTeachers') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-4">
                        <button type="submit"
                                wire:loading.attr="disabled"
                                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-md transition duration-200 flex items-center justify-center">
                            <span wire:loading.remove wire:target="updateSubject">
                                <i class="fas fa-save mr-2"></i> Update Subject
                            </span>
                            <span wire:loading wire:target="updateSubject">
                                <i class="fas fa-spinner fa-spin mr-2"></i> Saving...
                            </span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>