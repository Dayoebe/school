<div class="space-y-6">
    <!-- Header Card -->
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg shadow-lg overflow-hidden">
        <div class="px-6 py-8">
            <div class="flex flex-col md:flex-row items-center md:items-start gap-6">
                <img src="{{ $admin->profile_photo_url }}" alt="{{ $admin->name }}" 
                     class="w-32 h-32 rounded-full border-4 border-white shadow-lg object-cover">
                
                <div class="flex-1 text-white text-center md:text-left">
                    <h1 class="text-3xl font-bold mb-2">{{ $admin->name }}</h1>
                    <p class="text-indigo-100 mb-2">Administrator</p>
                    <div class="flex flex-wrap gap-4 justify-center md:justify-start text-sm">
                        <span class="flex items-center">
                            <i class="fas fa-envelope mr-2"></i>{{ $admin->email }}
                        </span>
                        @if($admin->phone)
                        <span class="flex items-center">
                            <i class="fas fa-phone mr-2"></i>{{ $admin->phone }}
                        </span>
                        @endif
                    </div>
                </div>
                
                <div class="flex gap-3">
                    <a href="{{ route('admins.index') }}" 
                       class="px-4 py-2 bg-white/20 backdrop-blur-sm hover:bg-white/30 text-white rounded-lg transition">
                        <i class="fas fa-arrow-left mr-2"></i>Back
                    </a>
                    @can('update', [$admin, 'admin'])
                    <a href="{{ route('admins.index', ['mode' => 'edit', 'adminId' => $admin->id]) }}" 
                       class="px-4 py-2 bg-white text-indigo-600 rounded-lg font-semibold shadow hover:shadow-lg transition">
                        <i class="fas fa-edit mr-2"></i>Edit
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <!-- Details Card -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="bg-gray-50 px-6 py-4 border-b">
            <h3 class="text-xl font-bold text-gray-900">Personal Information</h3>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div class="flex justify-between border-b pb-3">
                        <span class="font-semibold text-gray-600">Full Name:</span>
                        <span class="text-gray-900">{{ $admin->name }}</span>
                    </div>
                    
                    <div class="flex justify-between border-b pb-3">
                        <span class="font-semibold text-gray-600">Email:</span>
                        <span class="text-gray-900">{{ $admin->email }}</span>
                    </div>
                    
                    <div class="flex justify-between border-b pb-3">
                        <span class="font-semibold text-gray-600">Gender:</span>
                        <span class="text-gray-900">{{ ucfirst($admin->gender ?? 'N/A') }}</span>
                    </div>
                    
                    @if($admin->birthday)
                    <div class="flex justify-between border-b pb-3">
                        <span class="font-semibold text-gray-600">Birthday:</span>
                        <span class="text-gray-900">
                            @if($admin->birthday instanceof \Carbon\Carbon)
                                {{ $admin->birthday->format('M d, Y') }}
                            @else
                                {{ \Carbon\Carbon::parse($admin->birthday)->format('M d, Y') }}
                            @endif
                        </span>
                    </div>
                    @endif
                    
                    @if($admin->phone)
                    <div class="flex justify-between border-b pb-3">
                        <span class="font-semibold text-gray-600">Phone:</span>
                        <span class="text-gray-900">{{ $admin->phone }}</span>
                    </div>
                    @endif
                    
                    @if($admin->blood_group)
                    <div class="flex justify-between border-b pb-3">
                        <span class="font-semibold text-gray-600">Blood Group:</span>
                        <span class="text-gray-900">{{ $admin->blood_group }}</span>
                    </div>
                    @endif
                </div>

                <div class="space-y-4">
                    @if($admin->religion)
                    <div class="flex justify-between border-b pb-3">
                        <span class="font-semibold text-gray-600">Religion:</span>
                        <span class="text-gray-900">{{ $admin->religion }}</span>
                    </div>
                    @endif
                    
                    @if($admin->nationality)
                    <div class="flex justify-between border-b pb-3">
                        <span class="font-semibold text-gray-600">Nationality:</span>
                        <span class="text-gray-900">{{ $admin->nationality }}</span>
                    </div>
                    @endif
                    
                    @if($admin->state)
                    <div class="flex justify-between border-b pb-3">
                        <span class="font-semibold text-gray-600">State:</span>
                        <span class="text-gray-900">{{ $admin->state }}</span>
                    </div>
                    @endif
                    
                    @if($admin->city)
                    <div class="flex justify-between border-b pb-3">
                        <span class="font-semibold text-gray-600">City:</span>
                        <span class="text-gray-900">{{ $admin->city }}</span>
                    </div>
                    @endif
                    
                    @if($admin->address)
                    <div class="flex justify-between border-b pb-3">
                        <span class="font-semibold text-gray-600">Address:</span>
                        <span class="text-gray-900 text-right">{{ $admin->address }}</span>
                    </div>
                    @endif
                    
                    <div class="flex justify-between border-b pb-3">
                        <span class="font-semibold text-gray-600">Account Status:</span>
                        <span class="px-3 py-1 rounded-full text-sm font-semibold
                                     {{ $admin->locked ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                            {{ $admin->locked ? 'Locked' : 'Active' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>