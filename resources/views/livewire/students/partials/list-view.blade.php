<!-- Header -->
<div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white p-6 rounded-lg shadow-lg">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-3xl font-bold flex items-center">
                <i class="fas fa-user-graduate mr-3"></i>Student Management
            </h2>
            <p class="text-blue-100 mt-1">{{ $students->total() ?? 0 }} total students</p>
        </div>
        
        <div class="flex flex-wrap gap-3">
            <button wire:click="switchMode('create')" 
                    class="bg-white text-blue-600 px-6 py-3 rounded-lg font-semibold shadow-lg hover:shadow-xl transition-all">
                <i class="fas fa-plus-circle mr-2"></i>Create Student
            </button>
        </div>
    </div>
</div>

<!-- Flash Messages -->
@if (session()->has('success'))
    <div x-data="{ show: true }" x-show="show" x-transition 
         class="bg-green-50 border-l-4 border-green-500 p-4 rounded-r-lg">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-500 text-xl mr-3"></i>
                <p class="text-green-800 font-medium">{{ session('success') }}</p>
            </div>
            <button @click="show = false" class="text-green-500 hover:text-green-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
@endif

@if (session()->has('error'))
    <div x-data="{ show: true }" x-show="show" x-transition 
         class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-500 text-xl mr-3"></i>
                <p class="text-red-800 font-medium">{{ session('error') }}</p>
            </div>
            <button @click="show = false" class="text-red-500 hover:text-red-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
@endif

<!-- Filters -->
<div class="bg-white rounded-lg shadow p-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div class="md:col-span-2">
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-search mr-2 text-indigo-500"></i>Search
            </label>
            <input type="search" wire:model.live.debounce.500ms="search" 
                   placeholder="Name, email, or admission number..." 
                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
        </div>
        
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-school mr-2 text-purple-500"></i>Class
            </label>
            <select wire:model.live="selectedClass" 
                    class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                <option value="">All Classes</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-layer-group mr-2 text-blue-500"></i>Section
            </label>
            <select wire:model.live="selectedSection" 
                    class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                    {{ empty($sections) ? 'disabled' : '' }}>
                <option value="">All Sections</option>
                @foreach($sections as $section)
                    <option value="{{ $section->id }}">{{ $section->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-toggle-on mr-2 text-green-500"></i>Status
            </label>
            <select wire:model.live="selectedStatus" 
                    class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                <option value="">All Statuses</option>
                <option value="0">Active</option>
                <option value="1">Locked</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Per Page</label>
            <select wire:model.live="perPage" 
                    class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                <option value="10">10</option>
                <option value="15">15</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
        </div>

        <div class="md:col-span-2 flex items-end gap-3">
            <button wire:click="applyFilters" 
                    class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition">
                <i class="fas fa-filter mr-2"></i>Apply
            </button>
            <button wire:click="clearFilters" 
                    class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition">
                <i class="fas fa-redo mr-2"></i>Clear
            </button>
        </div>
    </div>
</div>

<!-- Bulk Actions Bar -->
@if(count($selectedStudents) > 0)
    <div class="bg-indigo-50 border-2 border-indigo-200 rounded-lg p-4">
        <div class="flex justify-between items-center">
            <span class="font-semibold text-indigo-900">
                <i class="fas fa-check-circle mr-2"></i>{{ count($selectedStudents) }} student(s) selected
            </span>
            <div class="flex gap-3">
                @if($appliedClass)
                    <button wire:click="openBulkModal('assign_section')" 
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                        <i class="fas fa-layer-group mr-2"></i>Assign Section
                    </button>
                @endif
                <button wire:click="openBulkModal('move_class')" 
                        class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition">
                    <i class="fas fa-exchange-alt mr-2"></i>Move to Class
                </button>
            </div>
        </div>
    </div>
@endif

<!-- Students Table -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    @if($appliedClass)
                        <th class="px-6 py-4">
                            <input type="checkbox" wire:model.live="selectAll" 
                                   class="rounded border-gray-300 text-indigo-600">
                        </th>
                    @endif
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase">Photo</th>
                    <th wire:click="sortBy('name')" class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase cursor-pointer hover:bg-gray-100">
                        Name
                        @if($sortField === 'name')
                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                        @endif
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase">Email</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase">Admission No</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase">Class</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase">Section</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase">Status</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($students as $student)
                <tr class="hover:bg-indigo-50 transition-colors">
                    @if($appliedClass)
                        <td class="px-6 py-4">
                            <input type="checkbox" wire:model.live="selectedStudents" 
                                   value="{{ $student->id }}" 
                                   class="rounded border-gray-300 text-indigo-600">
                        </td>
                    @endif
                    <td class="px-6 py-4">
                        <img src="{{ $student->profile_photo_url }}" 
                             alt="{{ $student->name }}" 
                             class="h-10 w-10 rounded-full object-cover border-2 border-blue-200">
                    </td>
                    <td class="px-6 py-4 font-semibold text-gray-900">{{ $student->name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $student->email }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        {{ $student->studentRecord->admission_number ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4">
                        @if($student->studentRecord->current_year_class ?? false)
                            <span class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-xs font-semibold">
                                {{ $student->studentRecord->current_year_class->name }}
                            </span>
                        @else
                            <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-xs font-semibold">
                                Not Assigned
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($student->studentRecord->current_year_section ?? false)
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold">
                                {{ $student->studentRecord->current_year_section->name }}
                            </span>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-3 py-1 {{ $student->locked ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }} rounded-full text-xs font-semibold">
                            {{ $student->locked ? 'Locked' : 'Active' }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex gap-2">
                            <a href="{{ route('students.show', $student->id) }}" 
                               class="p-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition">
                                <i class="fas fa-eye"></i>
                            </a>
                            <button wire:click="switchMode('edit', {{ $student->id }})" 
                                    class="p-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg transition">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button wire:click="deleteStudent({{ $student->id }})" 
                                    onclick="return confirm('Delete this student?')"
                                    class="p-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <i class="fas fa-inbox text-gray-300 text-5xl mb-4"></i>
                            <p class="text-lg text-gray-500">No students found</p>
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="bg-gray-50 px-6 py-4">
        {{ $students->links() }}
    </div>
</div>