<div class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-teal-600 to-cyan-600 rounded-lg shadow-lg overflow-hidden">
        <div class="px-6 py-8">
            <div class="flex flex-col md:flex-row items-center md:items-start gap-6">
                <img src="{{ $selectedApplicant->profile_photo_url }}" 
                     alt="{{ $selectedApplicant->name }}" 
                     class="w-32 h-32 rounded-full border-4 border-white shadow-lg object-cover">
                
                <div class="flex-1 text-white text-center md:text-left">
                    <h1 class="text-3xl font-bold mb-2">{{ $selectedApplicant->name }}</h1>
                    <div class="flex flex-wrap gap-4 justify-center md:justify-start text-sm">
                        <span class="flex items-center">
                            <i class="fas fa-envelope mr-2"></i>{{ $selectedApplicant->email }}
                        </span>
                        <span class="flex items-center">
                            <i class="fas fa-user-tag mr-2"></i>{{ ucfirst($selectedApplicant->accountApplication->role->name ?? 'N/A') }}
                        </span>
                        <span class="flex items-center">
                            <i class="fas fa-calendar mr-2"></i>Applied: {{ $selectedApplicant->created_at->format('M d, Y') }}
                        </span>
                    </div>
                </div>
                
                <div class="flex gap-3">
                    <button wire:click="switchMode('list')" 
                            class="px-4 py-2 bg-white/20 backdrop-blur-sm hover:bg-white/30 text-white rounded-lg transition">
                        <i class="fas fa-arrow-left mr-2"></i>Back
                    </button>
                    @can('change account application status')
                        <button wire:click="switchMode('change-status', {{ $selectedApplicant->id }})" 
                                class="px-4 py-2 bg-white text-teal-600 rounded-lg font-semibold shadow hover:shadow-lg transition">
                            <i class="fas fa-edit mr-2"></i>Change Status
                        </button>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <!-- Personal Information -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="bg-gray-100 px-6 py-4 border-b">
            <h3 class="text-xl font-bold text-gray-900">
                <i class="fas fa-user mr-2 text-indigo-600"></i>Personal Information
            </h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="flex justify-between border-b pb-3">
                    <span class="font-semibold text-gray-600">Full Name:</span>
                    <span class="text-gray-900">{{ $selectedApplicant->name }}</span>
                </div>
                <div class="flex justify-between border-b pb-3">
                    <span class="font-semibold text-gray-600">Email:</span>
                    <span class="text-gray-900">{{ $selectedApplicant->email }}</span>
                </div>
                <div class="flex justify-between border-b pb-3">
                    <span class="font-semibold text-gray-600">Gender:</span>
                    <span class="text-gray-900">{{ ucfirst($selectedApplicant->gender ?? 'N/A') }}</span>
                </div>
                <div class="flex justify-between border-b pb-3">
                    <span class="font-semibold text-gray-600">Phone:</span>
                    <span class="text-gray-900">{{ $selectedApplicant->phone ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between border-b pb-3">
                    <span class="font-semibold text-gray-600">Birthday:</span>
                    <span class="text-gray-900">
                        {{ $selectedApplicant->birthday ? $selectedApplicant->birthday->format('M d, Y') : 'N/A' }}
                    </span>
                </div>
                <div class="flex justify-between border-b pb-3">
                    <span class="font-semibold text-gray-600">Address:</span>
                    <span class="text-gray-900 text-right">{{ $selectedApplicant->address ?? 'N/A' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Application Details -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="bg-gray-100 px-6 py-4 border-b">
            <h3 class="text-xl font-bold text-gray-900">
                <i class="fas fa-file-alt mr-2 text-blue-600"></i>Application Details
            </h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="flex justify-between border-b pb-3">
                    <span class="font-semibold text-gray-600">Applying for Role:</span>
                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-semibold">
                        {{ ucfirst($selectedApplicant->accountApplication->role->name ?? 'N/A') }}
                    </span>
                </div>
                <div class="flex justify-between border-b pb-3">
                    <span class="font-semibold text-gray-600">Current Status:</span>
                    @php
                        $status = $selectedApplicant->accountApplication->status ?? 'pending';
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
                </div>
            </div>
        </div>
    </div>

    <!-- Status History -->
    @if($selectedApplicant->accountApplication->statuses->isNotEmpty())
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="bg-gray-100 px-6 py-4 border-b">
                <h3 class="text-xl font-bold text-gray-900">
                    <i class="fas fa-history mr-2 text-purple-600"></i>Status History
                </h3>
            </div>
            <div class="p-6 space-y-4">
                @foreach($selectedApplicant->accountApplication->statuses->reverse() as $statusItem)
                    <div class="relative pl-8 pb-8 border-l-2 border-gray-300 last:border-l-0 last:pb-0">
                        <div class="absolute -left-2 top-0 w-4 h-4 rounded-full bg-indigo-600"></div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="font-bold text-lg capitalize text-gray-900">{{ $statusItem->name }}</h4>
                                <span class="text-sm text-gray-500">
                                    {{ $statusItem->created_at->format('M d, Y h:i A') }}
                                </span>
                            </div>
                            @if($statusItem->reason)
                                <p class="text-gray-700 mt-2">{{ $statusItem->reason }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>