<div x-data="{ activeTab: @entangle('activeTab') }" class="space-y-6">
    
    <!-- Header Card -->
    <div class="bg-teal-600 rounded-lg shadow-lg overflow-hidden">
        <div class="px-6 py-8">
            <div class="flex flex-col md:flex-row items-center md:items-start gap-6">
                <img src="{{ $student->profile_photo_url }}" alt="{{ $student->name }}" 
                     class="w-32 h-32 rounded-full border-4 border-white shadow-lg object-cover">
                
                <div class="flex-1 text-white text-center md:text-left">
                    <h1 class="text-3xl font-bold mb-2">{{ $student->name }}</h1>
                    <div class="flex flex-wrap gap-4 justify-center md:justify-start text-sm">
                        <span class="flex items-center">
                            <i class="fas fa-envelope mr-2"></i>{{ $student->email }}
                        </span>
                        <span class="flex items-center">
                            <i class="fas fa-id-card mr-2"></i>{{ $student->studentRecord->admission_number ?? 'N/A' }}
                        </span>
                        <span class="flex items-center">
                            <i class="fas fa-school mr-2"></i>{{ $student->studentRecord->myClass->name ?? 'N/A' }}
                        </span>
                        @if($student->studentRecord->section)
                            <span class="flex items-center">
                                <i class="fas fa-layer-group mr-2"></i>{{ $student->studentRecord->section->name }}
                            </span>
                        @endif
                    </div>
                </div>
                
                <div class="flex gap-3">
                    <a href="{{ route('students.index') }}" 
                       class="px-4 py-2 bg-white/20 backdrop-blur-sm hover:bg-white/30 text-white rounded-lg transition">
                        <i class="fas fa-arrow-left mr-2"></i>Back
                    </a>
                    <button wire:click="printProfile" 
                            class="px-4 py-2 bg-white text-black rounded-lg font-semibold shadow hover:shadow-lg transition">
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
                    :class="activeTab === 'profile' ? 'border-b-2 border-indigo-600 text-indigo-600' : 'text-gray-600 hover:text-indigo-600'"
                    class="px-6 py-4 font-semibold transition whitespace-nowrap">
                <i class="fas fa-user mr-2"></i>Profile
            </button>
            <button @click="activeTab = 'academic'" 
                    :class="activeTab === 'academic' ? 'border-b-2 border-indigo-600 text-indigo-600' : 'text-gray-600 hover:text-indigo-600'"
                    class="px-6 py-4 font-semibold transition whitespace-nowrap">
                <i class="fas fa-graduation-cap mr-2"></i>Academic Info
            </button>
            <button @click="activeTab = 'fees'" 
                    :class="activeTab === 'fees' ? 'border-b-2 border-indigo-600 text-indigo-600' : 'text-gray-600 hover:text-indigo-600'"
                    class="px-6 py-4 font-semibold transition whitespace-nowrap">
                <i class="fas fa-money-bill-wave mr-2"></i>Fee Invoices
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
                            <span class="text-gray-900">{{ $student->name }}</span>
                        </div>
                        <div class="flex justify-between border-b pb-3">
                            <span class="font-semibold text-gray-600">Email:</span>
                            <span class="text-gray-900">{{ $student->email }}</span>
                        </div>
                        <div class="flex justify-between border-b pb-3">
                            <span class="font-semibold text-gray-600">Gender:</span>
                            <span class="text-gray-900">{{ ucfirst($student->gender ?? 'N/A') }}</span>
                        </div>
                        <div class="flex justify-between border-b pb-3">
                            <span class="font-semibold text-gray-600">Birthday:</span>
                            <span class="text-gray-900">
                                @if($student->birthday)
                                    @if($student->birthday instanceof \Carbon\Carbon)
                                        {{ $student->birthday->format('M d, Y') }}
                                    @else
                                        {{ \Carbon\Carbon::parse($student->birthday)->format('M d, Y') }}
                                    @endif
                                @else
                                    N/A
                                @endif
                            </span>
                        </div>
                        <div class="flex justify-between border-b pb-3">
                            <span class="font-semibold text-gray-600">Phone:</span>
                            <span class="text-gray-900">{{ $student->phone ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between border-b pb-3">
                            <span class="font-semibold text-gray-600">Blood Group:</span>
                            <span class="text-gray-900">{{ $student->blood_group ?? 'N/A' }}</span>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Other Information</h3>
                        
                        <div class="flex justify-between border-b pb-3">
                            <span class="font-semibold text-gray-600">Religion:</span>
                            <span class="text-gray-900">{{ $student->religion ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between border-b pb-3">
                            <span class="font-semibold text-gray-600">Nationality:</span>
                            <span class="text-gray-900">{{ $student->nationality ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between border-b pb-3">
                            <span class="font-semibold text-gray-600">State:</span>
                            <span class="text-gray-900">{{ $student->state ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between border-b pb-3">
                            <span class="font-semibold text-gray-600">City:</span>
                            <span class="text-gray-900">{{ $student->city ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between border-b pb-3">
                            <span class="font-semibold text-gray-600">Address:</span>
                            <span class="text-gray-900 text-right">{{ $student->address ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Academic Tab -->
            <div x-show="activeTab === 'academic'" x-transition>
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-6">Academic Information</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-white p-6 rounded-lg shadow">
                            <div class="flex items-center mb-4">
                                <i class="fas fa-school text-3xl text-magenta-600 mr-4"></i>
                                <div>
                                    <p class="text-sm text-gray-600">Current Class</p>
                                    <p class="text-xl font-bold text-gray-900">
                                        {{ $student->studentRecord->myClass->name ?? 'N/A' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-lg shadow">
                            <div class="flex items-center mb-4">
                                <i class="fas fa-layer-group text-3xl text-blue-600 mr-4"></i>
                                <div>
                                    <p class="text-sm text-gray-600">Section</p>
                                    <p class="text-xl font-bold text-gray-900">
                                        {{ $student->studentRecord->section->name ?? 'Not Assigned' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-lg shadow">
                            <div class="flex items-center mb-4">
                                <i class="fas fa-id-card text-3xl text-green-600 mr-4"></i>
                                <div>
                                    <p class="text-sm text-gray-600">Admission Number</p>
                                    <p class="text-xl font-bold text-gray-900">
                                        {{ $student->studentRecord->admission_number ?? 'N/A' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-lg shadow">
                            <div class="flex items-center mb-4">
                                <i class="fas fa-calendar-alt text-3xl text-orange-600 mr-4"></i>
                                <div>
                                    <p class="text-sm text-gray-600">Admission Date</p>
                                    <p class="text-xl font-bold text-gray-900">
                                        @if($student->studentRecord->admission_date)
                                            @if($student->studentRecord->admission_date instanceof \Carbon\Carbon)
                                                {{ $student->studentRecord->admission_date->format('M d, Y') }}
                                            @else
                                                {{ \Carbon\Carbon::parse($student->studentRecord->admission_date)->format('M d, Y') }}
                                            @endif
                                        @else
                                            N/A
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($student->studentRecord->is_graduated)
                        <div class="mt-6 bg-green-100 border-l-4 border-green-500 p-4 rounded">
                            <div class="flex items-center">
                                <i class="fas fa-graduation-cap text-green-600 text-2xl mr-3"></i>
                                <span class="font-bold text-green-800">This student has graduated</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Fees Tab -->
            <div x-show="activeTab === 'fees'" x-transition>
                @if($student->feeInvoices->isNotEmpty())
                    <div class="space-y-4">
                        @foreach($student->feeInvoices as $invoice)
                            <a href="{{ route('fee-invoices.show', $invoice->id) }}" 
                               class="block p-6 bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg border-2 border-gray-200 hover:border-indigo-400 hover:shadow-lg transition-all">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <h4 class="text-lg font-bold text-gray-900">{{ $invoice->name }}</h4>
                                        <p class="text-sm text-gray-600 mt-1">
                                            Due: 
                                            @if($invoice->due_date instanceof \Carbon\Carbon)
                                                {{ $invoice->due_date->format('M d, Y') }}
                                            @else
                                                {{ \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-2xl font-bold text-gray-900">{{ number_format($invoice->amount, 2) }}</p>
                                        <p class="text-sm text-gray-600">Paid: {{ number_format($invoice->paid, 2) }}</p>
                                        @if($invoice->balance <= 0)
                                            <span class="inline-block mt-2 px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-bold">
                                                <i class="fas fa-check mr-1"></i>Paid
                                            </span>
                                        @elseif($invoice->paid > 0)
                                            <span class="inline-block mt-2 px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-bold">
                                                <i class="fas fa-exclamation mr-1"></i>Partial
                                            </span>
                                        @else
                                            <span class="inline-block mt-2 px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs font-bold">
                                                <i class="fas fa-times mr-1"></i>Unpaid
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12 bg-gray-50 rounded-lg">
                        <i class="fas fa-file-invoice text-gray-300 text-5xl mb-4"></i>
                        <p class="text-lg text-gray-500">No fee invoices found for this student</p>
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