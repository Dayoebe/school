<div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <div class="bg-amber-600 px-6 py-4">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <h2 class="text-2xl font-bold text-white">
                <i class="fas fa-user-shield mr-2"></i>Administrators
            </h2>
            @can('create', [App\Models\User::class, 'admin'])
            <button wire:click="switchMode('create')" 
                    class="px-4 py-2 bg-white text-indigo-600 font-semibold rounded-lg hover:bg-indigo-50 transition">
                <i class="fas fa-plus mr-2"></i>Create Admin
            </button>
            @endcan
        </div>
    </div>

    <div class="p-6">
        <!-- Search and Sort -->
        <div class="mb-6 flex flex-col md:flex-row gap-4">
            <input type="text" 
                   wire:model.live.debounce.300ms="search" 
                   placeholder="Search administrators..."
                   class="flex-1 rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-indigo-500">
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase cursor-pointer hover:bg-gray-100"
                            wire:click="sortBy('name')">
                            Name
                            @if($sortField === 'name')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                            @endif
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase cursor-pointer hover:bg-gray-100"
                            wire:click="sortBy('email')">
                            Email
                            @if($sortField === 'email')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                            @endif
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Gender</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($admins as $admin)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <img src="{{ $admin->profile_photo_url }}" 
                                         alt="{{ $admin->name }}" 
                                         class="w-10 h-10 rounded-full mr-3">
                                    <span class="font-semibold text-gray-900">{{ $admin->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-700">{{ $admin->email }}</td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm">
                                    {{ ucfirst($admin->gender ?? 'N/A') }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @can('lock user')
                                <button wire:click="toggleLock({{ $admin->id }})" 
                                        class="px-3 py-1 rounded-full text-sm font-semibold transition
                                               {{ $admin->locked ? 'bg-red-100 text-red-700 hover:bg-red-200' : 'bg-green-100 text-green-700 hover:bg-green-200' }}">
                                    <i class="fas fa-{{ $admin->locked ? 'lock' : 'lock-open' }} mr-1"></i>
                                    {{ $admin->locked ? 'Locked' : 'Active' }}
                                </button>
                                @else
                                <span class="px-3 py-1 rounded-full text-sm font-semibold
                                             {{ $admin->locked ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                    {{ $admin->locked ? 'Locked' : 'Active' }}
                                </span>
                                @endcan
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    @can('view', [$admin, 'admin'])
                                    <a href="{{ route('admins.show', $admin->id) }}" 
                                       class="px-3 py-1.5 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition text-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @endcan
                                    
                                    @can('update', [$admin, 'admin'])
                                    <button wire:click="switchMode('edit', {{ $admin->id }})" 
                                            class="px-3 py-1.5 bg-yellow-100 text-yellow-700 rounded-lg hover:bg-yellow-200 transition text-sm">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    @endcan
                                    
                                    @can('delete', [$admin, 'admin'])
                                    <button wire:click="deleteAdmin({{ $admin->id }})" 
                                            wire:confirm="Are you sure you want to delete this admin?"
                                            class="px-3 py-1.5 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition text-sm">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-user-shield text-4xl text-gray-300 mb-4"></i>
                                <p class="text-lg">No administrators found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $admins->links() }}
        </div>
    </div>
</div>