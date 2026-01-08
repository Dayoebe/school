<!-- Header with Actions -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
        <h2 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-lightbulb text-green-600 mr-2"></i>Subjects
        </h2>
        <div class="flex gap-3">
            @livewire('subjects.subject-integrity-checker')
            <button wire:click="switchMode('create')" 
                    class="px-6 py-2.5 bg-gradient-to-r from-green-600 to-teal-600 text-white font-semibold rounded-lg hover:from-green-700 hover:to-teal-700 shadow-lg transition">
                <i class="fas fa-plus mr-2"></i>Add New Subject
            </button>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Search</label>
            <input type="text" wire:model.live.debounce.300ms="search" 
                   placeholder="Search by subject name or code..." 
                   class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-green-500">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Filter by Class</label>
            <select wire:model="selectedClass" class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-green-500">
                <option value="">All Classes</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button wire:click="applyFilters" 
                    class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                <i class="fas fa-filter mr-2"></i>Apply
            </button>
            <button wire:click="clearFilters" 
                    class="px-4 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition">
                <i class="fas fa-redo mr-2"></i>Reset
            </button>
        </div>
    </div>
</div>

<!-- Flash Messages -->
@if (session()->has('success'))
    <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6 rounded">
        <div class="flex items-center">
            <i class="fas fa-check-circle text-green-400 text-xl mr-3"></i>
            <p class="text-green-700">{{ session('success') }}</p>
        </div>
    </div>
@endif

@if (session()->has('error'))
    <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6 rounded">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle text-red-400 text-xl mr-3"></i>
            <p class="text-red-700">{{ session('error') }}</p>
        </div>
    </div>
@endif

<!-- Subjects Table -->
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gradient-to-r from-green-600 to-teal-600 text-white">
                <tr>
                    <th class="px-6 py-4 text-left cursor-pointer hover:bg-green-700" wire:click="sortBy('name')">
                        <div class="flex items-center gap-2">
                            Subject Name
                            @if($sortField === 'name')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </div>
                    </th>
                    <th class="px-6 py-4 text-left">Code</th>
                    <th class="px-6 py-4 text-left">Classes</th>
                    <th class="px-6 py-4 text-left">Teachers</th>
                    <th class="px-6 py-4 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($subjects as $subject)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-r from-green-500 to-teal-500 flex items-center justify-center text-white">
                                    <i class="fas fa-book"></i>
                                </div>
                                <div>
                                    <span class="font-semibold text-gray-900">{{ $subject->name }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold">
                                {{ $subject->short_name }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($subject->classes->count() > 0)
                                <div class="flex flex-wrap gap-2">
                                    @foreach($subject->classes->take(3) as $class)
                                        <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                                            {{ $class->name }}
                                        </span>
                                    @endforeach
                                    @if($subject->classes->count() > 3)
                                        <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm font-medium">
                                            +{{ $subject->classes->count() - 3 }} more
                                        </span>
                                    @endif
                                </div>
                            @else
                                <span class="text-gray-400 text-sm">No classes assigned</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($subject->teachers_count > 0)
                                <div class="flex flex-wrap gap-1">
                                    @foreach($subject->teachers->take(2) as $teacher)
                                        <div class="group relative">
                                            <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded text-xs cursor-help">
                                                {{ $teacher->name }}
                                            </span>
                                            <!-- Tooltip showing assignment details -->
                                            <div class="hidden group-hover:block absolute z-10 bg-gray-900 text-white text-xs rounded py-1 px-2 -top-8 left-0 whitespace-nowrap">
                                                @if($teacher->pivot->is_general)
                                                    All Classes
                                                @else
                                                    @php
                                                        $assignedClass = $classes->firstWhere('id', $teacher->pivot->my_class_id);
                                                    @endphp
                                                    {{ $assignedClass?->name ?? 'Specific Class' }}
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                    @if($subject->teachers_count > 2)
                                        <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded text-xs">
                                            +{{ $subject->teachers_count - 2 }} more
                                        </span>
                                    @endif
                                </div>
                            @else
                                <span class="text-gray-400 text-sm">No teachers assigned</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('subjects.show', $subject->id) }}" 
                                   class="px-3 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition text-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button wire:click="switchMode('edit', {{ $subject->id }})" 
                                        class="px-3 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition text-sm">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button wire:click="deleteSubject({{ $subject->id }})" 
                                        wire:confirm="Are you sure you want to delete this subject? This will affect all assigned classes and students."
                                        class="px-3 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition text-sm">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-lightbulb text-gray-300 text-5xl mb-4"></i>
                                <p class="text-gray-500 text-lg">No subjects found</p>
                                @if($appliedClass || $search)
                                    <p class="text-gray-400 text-sm">Try changing your filters</p>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($subjects->hasPages())
        <div class="px-6 py-4 border-t">
            {{ $subjects->links() }}
        </div>
    @endif
</div>