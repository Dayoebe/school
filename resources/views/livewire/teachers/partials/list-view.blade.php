<!-- Header with Actions -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
        <h2 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-chalkboard-teacher text-indigo-600 mr-2"></i>Teachers
        </h2>
        <button wire:click="switchMode('create')" 
                class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 text-white font-semibold rounded-lg hover:from-indigo-700 hover:to-blue-700 shadow-lg transition">
            <i class="fas fa-plus mr-2"></i>Add New Teacher
        </button>
    </div>
</div>

<!-- Filters -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Search</label>
            <input type="text" wire:model.live.debounce.300ms="search" 
                   placeholder="Search by name, email, phone..." 
                   class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
            <select wire:model="selectedStatus" class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-indigo-500">
                <option value="">All Status</option>
                <option value="0">Active</option>
                <option value="1">Locked</option>
            </select>
        </div>
        <div class="flex items-end gap-2">
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

<!-- Teachers Table -->
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gradient-to-r from-indigo-600 to-blue-600 text-white">
                <tr>
                    <th class="px-6 py-4 text-left cursor-pointer hover:bg-indigo-700" wire:click="sortBy('name')">
                        <div class="flex items-center gap-2">
                            Name
                            @if($sortField === 'name')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </div>
                    </th>
                    <th class="px-6 py-4 text-left cursor-pointer hover:bg-indigo-700" wire:click="sortBy('email')">
                        <div class="flex items-center gap-2">
                            Email
                            @if($sortField === 'email')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </div>
                    </th>
                    <th class="px-6 py-4 text-left">Phone</th>
                    <th class="px-6 py-4 text-left">Subjects</th>
                    <th class="px-6 py-4 text-left">Status</th>
                    <th class="px-6 py-4 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($teachers as $teacher)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <img src="{{ $teacher->profile_photo_url }}" 
                                     alt="{{ $teacher->name }}" 
                                     class="w-10 h-10 rounded-full object-cover">
                                <span class="font-semibold text-gray-900">{{ $teacher->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-600">{{ $teacher->email }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $teacher->phone ?? 'N/A' }}</td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 bg-indigo-100 text-indigo-800 rounded-full text-sm font-semibold">
                                {{ $teacher->subjects_count }} Subjects
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($teacher->locked)
                                <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs font-bold">
                                    <i class="fas fa-lock mr-1"></i>Locked
                                </span>
                            @else
                                <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-bold">
                                    <i class="fas fa-check mr-1"></i>Active
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('teachers.show', $teacher->id) }}" 
                                   class="px-3 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition text-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button wire:click="switchMode('edit', {{ $teacher->id }})" 
                                        class="px-3 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition text-sm">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button wire:click="deleteTeacher({{ $teacher->id }})" 
                                        wire:confirm="Are you sure you want to delete this teacher?"
                                        class="px-3 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition text-sm">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-user-slash text-gray-300 text-5xl mb-4"></i>
                                <p class="text-gray-500 text-lg">No teachers found</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($teachers->hasPages())
        <div class="px-6 py-4 border-t">
            {{ $teachers->links() }}
        </div>
    @endif
</div>