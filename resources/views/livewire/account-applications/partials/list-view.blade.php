<div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <!-- Header -->
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <h2 class="text-2xl font-bold text-white">
                <i class="fas fa-user-clock mr-2"></i>
                {{ $filter === 'pending' ? 'Pending Applications' : 'Rejected Applications' }}
            </h2>
            
            <div class="flex flex-wrap gap-3">
                <button wire:click="$set('filter', 'pending')" 
                        class="px-4 py-2 rounded-lg font-semibold transition {{ $filter === 'pending' ? 'bg-white text-indigo-600' : 'bg-white/20 text-white hover:bg-white/30' }}">
                    <i class="fas fa-clock mr-2"></i>Pending
                </button>
                <button wire:click="$set('filter', 'rejected')" 
                        class="px-4 py-2 rounded-lg font-semibold transition {{ $filter === 'rejected' ? 'bg-white text-indigo-600' : 'bg-white/20 text-white hover:bg-white/30' }}">
                    <i class="fas fa-times-circle mr-2"></i>Rejected
                </button>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="p-6 border-b bg-gray-50">
        <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <input type="text" 
                       wire:model.live.debounce.300ms="search" 
                       placeholder="Search by name or email..."
                       class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-100 border-b-2 border-gray-200">
                <tr>
                    <th class="px-6 py-4 text-left text-sm font-bold text-gray-700">Applicant</th>
                    <th class="px-6 py-4 text-left text-sm font-bold text-gray-700">Role</th>
                    <th class="px-6 py-4 text-left text-sm font-bold text-gray-700">Status</th>
                    <th class="px-6 py-4 text-left text-sm font-bold text-gray-700">Applied Date</th>
                    <th class="px-6 py-4 text-left text-sm font-bold text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($applicants as $applicant)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <img src="{{ $applicant->profile_photo_url }}" 
                                     alt="{{ $applicant->name }}"
                                     class="w-10 h-10 rounded-full object-cover mr-3">
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $applicant->name }}</p>
                                    <p class="text-sm text-gray-500">{{ $applicant->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-semibold">
                                {{ ucfirst($applicant->accountApplication->role->name ?? 'N/A') }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $status = $applicant->accountApplication->status ?? 'pending';
                                $statusColors = [
                                    'approved' => 'bg-green-100 text-green-800',
                                    'rejected' => 'bg-red-100 text-red-800',
                                    'under review' => 'bg-yellow-100 text-yellow-800',
                                    'user action required' => 'bg-orange-100 text-orange-800',
                                ];
                                $colorClass = $statusColors[$status] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="px-3 py-1 rounded-full text-sm font-semibold capitalize {{ $colorClass }}">
                                {{ $status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-600">
                            {{ $applicant->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex gap-2">
                                <button wire:click="switchMode('view', {{ $applicant->id }})"
                                        class="px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-sm transition">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @can('change account application status')
                                    <button wire:click="switchMode('change-status', {{ $applicant->id }})"
                                            class="px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white rounded-lg text-sm transition">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                @endcan
                                @can('delete', [$applicant, 'applicant'])
                                    <button wire:click="deleteApplicant({{ $applicant->id }})"
                                            wire:confirm="Are you sure you want to delete this application?"
                                            class="px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white rounded-lg text-sm transition">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-inbox text-gray-300 text-5xl mb-4"></i>
                                <p class="text-lg text-gray-500">No applications found</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($applicants->hasPages())
        <div class="px-6 py-4 border-t bg-gray-50">
            {{ $applicants->links() }}
        </div>
    @endif
</div>