<div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <!-- Header -->
    <div class="bg-rose-600 px-6 py-4">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <h2 class="text-2xl font-bold text-white">
                <i class="fas fa-users mr-2"></i>Parents Management
            </h2>
            <button wire:click="switchMode('create')" 
                    class="px-6 py-2.5 bg-white text-purple-600 font-semibold rounded-lg hover:bg-gray-100 shadow-lg transition">
                <i class="fas fa-plus mr-2"></i>Add New Parent
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="p-6 bg-gray-50 border-b">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-2">
                <input wire:model.live.debounce.300ms="search" 
                       type="text" 
                       placeholder="Search by name, email, or phone..." 
                       class="w-full rounded-lg border-2 border-gray-300 px-4 py-2.5 focus:ring-2 focus:ring-purple-500">
            </div>
            
            <select wire:model="selectedStatus" class="rounded-lg border-2 border-gray-300 px-4 py-2.5">
                <option value="">All Status</option>
                <option value="0">Active</option>
                <option value="1">Locked</option>
            </select>

            <div class="flex gap-2">
                <button wire:click="applyFilters" 
                        class="flex-1 px-4 py-2.5 bg-purple-600 text-white font-semibold rounded-lg hover:bg-purple-700">
                    <i class="fas fa-filter mr-2"></i>Filter
                </button>
                <button wire:click="clearFilters" 
                        class="flex-1 px-4 py-2.5 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300">
                    <i class="fas fa-times mr-2"></i>Clear
                </button>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-100 border-b-2 border-gray-200">
                <tr>
                    <th class="px-6 py-4 text-left">
                        <input type="checkbox" 
                               wire:model.live="selectAll" 
                               class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                    </th>
                    <th wire:click="sortBy('name')" 
                        class="px-6 py-4 text-left font-semibold text-gray-700 cursor-pointer hover:bg-gray-200">
                        Name
                        @if($sortField === 'name')
                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                        @endif
                    </th>
                    <th class="px-6 py-4 text-left font-semibold text-gray-700">Email</th>
                    <th class="px-6 py-4 text-left font-semibold text-gray-700">Phone</th>
                    <th class="px-6 py-4 text-left font-semibold text-gray-700">Gender</th>
                    <th class="px-6 py-4 text-left font-semibold text-gray-700">Children</th>
                    <th class="px-6 py-4 text-left font-semibold text-gray-700">Status</th>
                    <th class="px-6 py-4 text-center font-semibold text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($parents as $parent)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            <input type="checkbox" 
                                   wire:model.live="selectedParents" 
                                   value="{{ $parent->id }}" 
                                   class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <img src="{{ $parent->profile_photo_url }}" 
                                     alt="{{ $parent->name }}" 
                                     class="w-10 h-10 rounded-full object-cover border-2 border-gray-200">
                                <span class="font-medium text-gray-900">{{ $parent->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-700">{{ $parent->email }}</td>
                        <td class="px-6 py-4 text-gray-700">{{ $parent->phone ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-gray-700">{{ ucfirst($parent->gender ?? 'N/A') }}</td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-semibold">
                                {{ $parent->children_count }} {{ Str::plural('Child', $parent->children_count) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <button wire:click="toggleLock({{ $parent->id }})" 
                                    class="px-3 py-1 rounded-full text-xs font-bold {{ $parent->locked ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                {{ $parent->locked ? 'Locked' : 'Active' }}
                            </button>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('parents.show', $parent->id) }}" 
                                   class="px-3 py-1.5 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button wire:click="switchMode('edit', {{ $parent->id }})" 
                                        class="px-3 py-1.5 bg-yellow-100 text-yellow-700 rounded-lg hover:bg-yellow-200 transition">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="{{ route('parents.assign-student', $parent->id) }}" 
                                   class="px-3 py-1.5 bg-purple-100 text-purple-700 rounded-lg hover:bg-purple-200 transition">
                                    <i class="fas fa-user-plus"></i>
                                </a>
                                <button wire:click="deleteParent({{ $parent->id }})" 
                                        wire:confirm="Are you sure you want to delete this parent?"
                                        class="px-3 py-1.5 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-users text-5xl mb-4 text-gray-300"></i>
                            <p class="text-lg">No parents found</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($parents->hasPages())
        <div class="px-6 py-4 bg-gray-50 border-t">
            {{ $parents->links() }}
        </div>
    @endif
</div>