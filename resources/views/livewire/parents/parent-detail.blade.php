<div x-data="{ activeTab: @entangle('activeTab') }" class="space-y-6">
    
    <!-- Header Card -->
    <div class="bg-purple-600 rounded-lg shadow-lg overflow-hidden">
        <div class="px-6 py-8">
            <div class="flex flex-col md:flex-row items-center md:items-start gap-6">
                <img src="{{ $parent->profile_photo_url }}" alt="{{ $parent->name }}" 
                     class="w-32 h-32 rounded-full border-4 border-white shadow-lg object-cover">
                
                <div class="flex-1 text-white text-center md:text-left">
                    <h1 class="text-3xl font-bold mb-2">{{ $parent->name }}</h1>
                    <div class="flex flex-wrap gap-4 justify-center md:justify-start text-sm">
                        <span class="flex items-center">
                            <i class="fas fa-envelope mr-2"></i>{{ $parent->email }}
                        </span>
                        <span class="flex items-center">
                            <i class="fas fa-phone mr-2"></i>{{ $parent->phone ?? 'N/A' }}
                        </span>
                        <span class="flex items-center">
                            <i class="fas fa-users mr-2"></i>{{ $parent->children->count() }} {{ Str::plural('Child', $parent->children->count()) }}
                        </span>
                    </div>
                </div>
                
                <div class="flex gap-3">
                    <a href="{{ route('parents.index') }}" 
                       class="px-4 py-2 bg-white/20 backdrop-blur-sm hover:bg-white/30 text-white rounded-lg transition">
                        <i class="fas fa-arrow-left mr-2"></i>Back
                    </a>
                    <button wire:click="printProfile" 
                            class="px-4 py-2 bg-white text-purple-600 rounded-lg font-semibold shadow hover:shadow-lg transition">
                        <i class="fas fa-print mr-2"></i>Print Profile
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="flex border-b overflow-x-auto">
            <button @click="activeTab = 'profile'" 
                    :class="activeTab === 'profile' ? 'border-b-2 border-purple-600 text-purple-600' : 'text-gray-600 hover:text-purple-600'"
                    class="px-6 py-4 font-semibold transition whitespace-nowrap">
                <i class="fas fa-user mr-2"></i>Profile
            </button>
            <button @click="activeTab = 'children'" 
                    :class="activeTab === 'children' ? 'border-b-2 border-purple-600 text-purple-600' : 'text-gray-600 hover:text-purple-600'"
                    class="px-6 py-4 font-semibold transition whitespace-nowrap">
                <i class="fas fa-users mr-2"></i>Children
            </button>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
            <!-- Profile Tab -->
            <div x-show="activeTab === 'profile'" x-transition>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Personal Information</h3>
                        
                        <div class="flex justify-between border-b pb-3">
                            <span class="font-semibold text-gray-600">Full Name:</span>
                            <span class="text-gray-900">{{ $parent->name }}</span>
                        </div>
                        <div class="flex justify-between border-b pb-3">
                            <span class="font-semibold text-gray-600">Email:</span>
                            <span class="text-gray-900">{{ $parent->email }}</span>
                        </div>
                        <div class="flex justify-between border-b pb-3">
                            <span class="font-semibold text-gray-600">Gender:</span>
                            <span class="text-gray-900">{{ ucfirst($parent->gender ?? 'N/A') }}</span>
                        </div>
                        <div class="flex justify-between border-b pb-3">
                            <span class="font-semibold text-gray-600">Birthday:</span>
                            <span class="text-gray-900">
                                @if($parent->birthday)
                                    @if($parent->birthday instanceof \Carbon\Carbon)
                                        {{ $parent->birthday->format('M d, Y') }}
                                    @else
                                        {{ \Carbon\Carbon::parse($parent->birthday)->format('M d, Y') }}
                                    @endif
                                @else
                                    N/A
                                @endif
                            </span>
                        </div>
                        <div class="flex justify-between border-b pb-3">
                            <span class="font-semibold text-gray-600">Phone:</span>
                            <span class="text-gray-900">{{ $parent->phone ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between border-b pb-3">
                            <span class="font-semibold text-gray-600">Blood Group:</span>
                            <span class="text-gray-900">{{ $parent->blood_group ?? 'N/A' }}</span>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Other Information</h3>
                        
                        <div class="flex justify-between border-b pb-3">
                            <span class="font-semibold text-gray-600">Religion:</span>
                            <span class="text-gray-900">{{ $parent->religion ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between border-b pb-3">
                            <span class="font-semibold text-gray-600">Nationality:</span>
                            <span class="text-gray-900">{{ $parent->nationality ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between border-b pb-3">
                            <span class="font-semibold text-gray-600">State:</span>
                            <span class="text-gray-900">{{ $parent->state ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between border-b pb-3">
                            <span class="font-semibold text-gray-600">City:</span>
                            <span class="text-gray-900">{{ $parent->city ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between border-b pb-3">
                            <span class="font-semibold text-gray-600">Address:</span>
                            <span class="text-gray-900 text-right">{{ $parent->address ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Children Tab -->
            <div x-show="activeTab === 'children'" x-transition>
                @if($parent->children->isNotEmpty())
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($parent->children as $child)
                            <a href="{{ route('students.show', $child->id) }}" 
                               class="block bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg p-6 border-2 border-blue-200 hover:border-indigo-400 hover:shadow-lg transition-all">
                                <div class="flex items-center gap-4 mb-4">
                                    <img src="{{ $child->profile_photo_url }}" 
                                         alt="{{ $child->name }}" 
                                         class="w-16 h-16 rounded-full object-cover border-2 border-blue-300">
                                    <div>
                                        <h4 class="text-lg font-bold text-gray-900">{{ $child->name }}</h4>
                                        <p class="text-sm text-gray-600">{{ $child->email }}</p>
                                    </div>
                                </div>
                                
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Class:</span>
                                        <span class="font-semibold text-gray-900">
                                            {{ $child->studentRecord->myClass->name ?? 'N/A' }}
                                        </span>
                                    </div>
                                    @if($child->studentRecord->section)
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Section:</span>
                                            <span class="font-semibold text-gray-900">
                                                {{ $child->studentRecord->section->name }}
                                            </span>
                                        </div>
                                    @endif
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Admission #:</span>
                                        <span class="font-semibold text-gray-900">
                                            {{ $child->studentRecord->admission_number ?? 'N/A' }}
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="mt-4 pt-4 border-t border-blue-200">
                                    <span class="text-blue-600 font-semibold hover:text-blue-700">
                                        View Full Profile <i class="fas fa-arrow-right ml-1"></i>
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12 bg-gray-50 rounded-lg">
                        <i class="fas fa-users text-gray-300 text-5xl mb-4"></i>
                        <p class="text-lg text-gray-500">No children assigned to this parent</p>
                        <a href="{{ route('parents.assign-student', $parent->id) }}" 
                           class="inline-block mt-4 px-6 py-2.5 bg-purple-600 text-white font-semibold rounded-lg hover:bg-purple-700">
                            <i class="fas fa-user-plus mr-2"></i>Assign Students
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
Livewire.on('print-profile', () => {
    window.print();
});
</script>
@endpush