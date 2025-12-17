<div class="container mx-auto px-4 py-6">
    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-md mb-6 shadow-sm">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-md mb-6 shadow-sm">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                {{ session('error') }}
            </div>
        </div>
    @endif

    {{-- Main List View --}}
    @if ($view === 'list')
        <div class="grid lg:grid-cols-2 gap-6">
            {{-- Class Groups Section --}}
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-xl font-bold text-gray-800">Class Groups</h3>
                    @can('create', App\Models\ClassGroup::class)
                        <button wire:click="showCreateGroup" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition flex items-center text-sm">
                            <i class="fas fa-plus mr-2"></i> New Group
                        </button>
                    @endcan
                </div>
                <div class="p-6">
                    <input wire:model.live="search" type="text" placeholder="Search groups..." 
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 mb-4">
                    
                    <div class="space-y-3">
                        @forelse ($classGroups as $group)
                            <div class="p-4 bg-gray-50 rounded-md hover:bg-gray-100 transition flex justify-between items-center border border-gray-200">
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-800">{{ $group->name }}</h4>
                                    <p class="text-sm text-gray-500">{{ $group->classes_count }} classes</p>
                                </div>
                                <div class="flex space-x-3">
                                    @can('view', $group)
                                        <button wire:click="showViewGroup({{ $group->id }})" 
                                                class="text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    @endcan
                                    @can('update', $group)
                                        <button wire:click="showEditGroup({{ $group->id }})" 
                                                class="text-yellow-600 hover:text-yellow-800">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    @endcan
                                    @can('delete', $group)
                                        <button wire:click="deleteClassGroup({{ $group->id }})" 
                                                wire:confirm="Are you sure you want to delete this class group?"
                                                class="text-red-600 hover:text-red-800">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endcan
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-gray-500 py-4">No class groups found</p>
                        @endforelse
                    </div>
                    
                    <div class="mt-6">
                        {{ $classGroups->links('pagination::tailwind') }}
                    </div>
                </div>
            </div>

            {{-- Classes Section --}}
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-xl font-bold text-gray-800">Classes</h3>
                    @can('create', App\Models\MyClass::class)
                        <button wire:click="showCreateClass" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition flex items-center text-sm">
                            <i class="fas fa-plus mr-2"></i> New Class
                        </button>
                    @endcan
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        @forelse ($classes as $class)
                            <div class="p-4 bg-gray-50 rounded-md hover:bg-gray-100 transition flex justify-between items-center border border-gray-200">
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-800">{{ $class->name }}</h4>
                                    <p class="text-sm text-gray-500">{{ $class->classGroup->name }}</p>
                                </div>
                                <div class="flex space-x-3">
                                    @can('view', $class)
                                        <button wire:click="showViewClass({{ $class->id }})" 
                                                class="text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    @endcan
                                    @can('update', $class)
                                        <button wire:click="showEditClass({{ $class->id }})" 
                                                class="text-yellow-600 hover:text-yellow-800">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    @endcan
                                    @can('delete', $class)
                                        <button wire:click="deleteClass({{ $class->id }})" 
                                                wire:confirm="Are you sure you want to delete this class?"
                                                class="text-red-600 hover:text-red-800">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endcan
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-gray-500 py-4">No classes found</p>
                        @endforelse
                    </div>
                    
                    <div class="mt-6">
                        {{ $classes->links('pagination::tailwind') }}
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Create Class Group Form --}}
    @if ($view === 'create-group')
        <div class="max-w-2xl mx-auto bg-white rounded-xl shadow-md overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-xl font-bold text-gray-800">Create Class Group</h3>
            </div>
            <div class="p-6">
                <form wire:submit="createClassGroup">
                    <div class="mb-5">
                        <label class="block mb-2 text-sm font-medium text-gray-700">Class Group Name *</label>
                        <input wire:model="name" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                               placeholder="Enter class group name">
                        @error('name') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="flex space-x-3">
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition flex items-center">
                            <i class="fas fa-save mr-2"></i> Create
                        </button>
                        <button type="button" wire:click="showList" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 transition">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Edit Class Group Form --}}
    @if ($view === 'edit-group')
        <div class="max-w-2xl mx-auto bg-white rounded-xl shadow-md overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-xl font-bold text-gray-800">Edit Class Group</h3>
            </div>
            <div class="p-6">
                <form wire:submit="updateClassGroup">
                    <div class="mb-5">
                        <label class="block mb-2 text-sm font-medium text-gray-700">Class Group Name *</label>
                        <input wire:model="name" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('name') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="flex space-x-3">
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition flex items-center">
                            <i class="fas fa-save mr-2"></i> Update
                        </button>
                        <button type="button" wire:click="showList" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 transition">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- View Class Group --}}
    @if ($view === 'view-group')
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-2xl font-bold text-gray-800">{{ $selectedClassGroup->name }}</h2>
                <button wire:click="showList" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 transition flex items-center text-sm">
                    <i class="fas fa-arrow-left mr-2"></i> Back
                </button>
            </div>
            <div class="p-6">
                <h4 class="text-lg font-semibold mb-4 text-gray-700">
                    Contains {{ $selectedClassGroup->classes->count() }} 
                    {{ Str::plural('class', $selectedClassGroup->classes->count()) }}
                </h4>
                
                <div class="grid md:grid-cols-3 gap-4">
                    @foreach ($selectedClassGroup->classes as $class)
                        <div class="p-4 bg-gray-50 rounded-md border border-gray-200 hover:bg-gray-100 transition">
                            <button wire:click="showViewClass({{ $class->id }})" 
                                    class="text-blue-600 hover:underline font-semibold">
                                {{ $class->name }}
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- Create Class Form --}}
    @if ($view === 'create-class')
        <div class="max-w-2xl mx-auto bg-white rounded-xl shadow-md overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-xl font-bold text-gray-800">Create Class</h3>
            </div>
            <div class="p-6">
                <form wire:submit="createClass">
                    <div class="mb-5">
                        <label class="block mb-2 text-sm font-medium text-gray-700">Class Name *</label>
                        <input wire:model="name" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                               placeholder="Enter class name">
                        @error('name') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="mb-5">
                        <label class="block mb-2 text-sm font-medium text-gray-700">Class Group *</label>
                        <select wire:model="class_group_id" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select a class group</option>
                            @foreach ($allClassGroups as $group)
                                <option value="{{ $group->id }}">{{ $group->name }}</option>
                            @endforeach
                        </select>
                        @error('class_group_id') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="flex space-x-3">
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition flex items-center">
                            <i class="fas fa-save mr-2"></i> Create
                        </button>
                        <button type="button" wire:click="showList" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 transition">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Edit Class Form --}}
    @if ($view === 'edit-class')
        <div class="max-w-2xl mx-auto bg-white rounded-xl shadow-md overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-xl font-bold text-gray-800">Edit Class</h3>
            </div>
            <div class="p-6">
                <form wire:submit="updateClass">
                    <div class="mb-5">
                        <label class="block mb-2 text-sm font-medium text-gray-700">Class Name *</label>
                        <input wire:model="name" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('name') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="mb-5">
                        <label class="block mb-2 text-sm font-medium text-gray-700">Class Group *</label>
                        <select wire:model="class_group_id" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select a class group</option>
                            @foreach ($allClassGroups as $group)
                                <option value="{{ $group->id }}">{{ $group->name }}</option>
                            @endforeach
                        </select>
                        @error('class_group_id') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="flex space-x-3">
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition flex items-center">
                            <i class="fas fa-save mr-2"></i> Update
                        </button>
                        <button type="button" wire:click="showList" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 transition">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- View Class --}}
    @if ($view === 'view-class')
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">{{ $selectedClass->name }}</h2>
                    @if(auth()->user()->school->academicYear)
                        <p class="text-sm text-gray-500 mt-1">
                            Academic Year: {{ auth()->user()->school->academicYear->name }}
                        </p>
                    @endif
                </div>
                <button wire:click="showList" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 transition flex items-center text-sm">
                    <i class="fas fa-arrow-left mr-2"></i> Back
                </button>
            </div>
            <div class="p-6 space-y-8">
                {{-- Sections --}}
                <div>
                    <h3 class="text-xl font-bold mb-4 text-gray-800">Sections</h3>
                    <div class="grid md:grid-cols-4 gap-4">
                        @forelse ($selectedClass->sections as $section)
                            <div class="p-4 bg-gray-50 rounded-md border border-gray-200 text-center font-semibold text-gray-700">
                                {{ $section->name }}
                            </div>
                        @empty
                            <p class="text-gray-500 col-span-4">No sections found</p>
                        @endforelse
                    </div>
                </div>

                {{-- Students --}}
                <div>
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-bold text-gray-800">
                            Students 
                            @if($students)
                                ({{ $students->count() }})
                            @endif
                        </h3>
                        <div class="flex space-x-3">
                            <button wire:click="$toggle('showStudents')" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 transition flex items-center text-sm">
                                <i class="fas fa-{{ $showStudents ? 'eye-slash' : 'eye' }} mr-2"></i>
                                {{ $showStudents ? 'Hide' : 'Show' }} Students
                            </button>
                            @can('update', $selectedClass)
                                <button wire:click="assignSubjects" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition flex items-center text-sm">
                                    <i class="fas fa-link mr-2"></i> Assign Subjects
                                </button>
                            @endcan
                        </div>
                    </div>
                    
                    @if ($showStudents && $students)
                        <div class="overflow-x-auto border border-gray-200 rounded-md">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Section</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subjects</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse ($students as $student)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $student->user->name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $student->user->email }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $student->section->name ?? 'N/A' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $student->studentSubjects->count() }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                                No students found
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- Subjects --}}
                <div>
                    <h3 class="text-xl font-bold mb-4 text-gray-800">Subjects</h3>
                    <div class="overflow-x-auto border border-gray-200 rounded-md">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teachers</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($selectedClass->subjects as $subject)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $subject->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $subject->teachers->count() }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="px-6 py-4 text-center text-sm text-gray-500">
                                            No subjects found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>