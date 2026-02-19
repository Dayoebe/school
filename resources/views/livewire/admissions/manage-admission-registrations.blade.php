<div class="space-y-6">
    @if (session()->has('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-2 gap-3 md:grid-cols-5">
        <button wire:click="$set('statusFilter','all')" class="rounded-xl border p-4 text-left transition {{ $statusFilter === 'all' ? 'border-red-300 bg-red-50' : 'border-slate-200 bg-white' }}">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">All</p>
            <p class="mt-1 text-2xl font-black text-slate-900">{{ $statusCounts['all'] }}</p>
        </button>
        <button wire:click="$set('statusFilter','pending')" class="rounded-xl border p-4 text-left transition {{ $statusFilter === 'pending' ? 'border-amber-300 bg-amber-50' : 'border-slate-200 bg-white' }}">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Pending</p>
            <p class="mt-1 text-2xl font-black text-amber-700">{{ $statusCounts['pending'] }}</p>
        </button>
        <button wire:click="$set('statusFilter','contacted')" class="rounded-xl border p-4 text-left transition {{ $statusFilter === 'contacted' ? 'border-blue-300 bg-blue-50' : 'border-slate-200 bg-white' }}">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Contacted</p>
            <p class="mt-1 text-2xl font-black text-blue-700">{{ $statusCounts['contacted'] }}</p>
        </button>
        <button wire:click="$set('statusFilter','rejected')" class="rounded-xl border p-4 text-left transition {{ $statusFilter === 'rejected' ? 'border-rose-300 bg-rose-50' : 'border-slate-200 bg-white' }}">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Rejected</p>
            <p class="mt-1 text-2xl font-black text-rose-700">{{ $statusCounts['rejected'] }}</p>
        </button>
        <button wire:click="$set('statusFilter','enrolled')" class="rounded-xl border p-4 text-left transition {{ $statusFilter === 'enrolled' ? 'border-emerald-300 bg-emerald-50' : 'border-slate-200 bg-white' }}">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Enrolled</p>
            <p class="mt-1 text-2xl font-black text-emerald-700">{{ $statusCounts['enrolled'] }}</p>
        </button>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
            <div class="md:col-span-2">
                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Search</label>
                <input wire:model.live.debounce.300ms="search" type="text"
                    placeholder="Reference, student, guardian, phone..."
                    class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-200">
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Class</label>
                <select wire:model.live="classFilter"
                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-200">
                    <option value="">All Classes</option>
                    @foreach($classes as $myClass)
                        <option value="{{ $myClass->id }}">{{ $myClass->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Status</label>
                <select wire:model.live="statusFilter"
                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-200">
                    <option value="all">All</option>
                    <option value="pending">Pending</option>
                    <option value="contacted">Contacted</option>
                    <option value="rejected">Rejected</option>
                    <option value="enrolled">Enrolled</option>
                </select>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Reference</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Student</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Guardian</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Class</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Submitted</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($registrations as $registration)
                        <tr>
                            <td class="px-4 py-3 align-top">
                                <p class="text-sm font-semibold text-slate-900">{{ $registration->reference_no }}</p>
                                <p class="text-xs text-slate-500">{{ $registration->school?->name }}</p>
                            </td>
                            <td class="px-4 py-3 align-top">
                                <p class="text-sm font-semibold text-slate-900">{{ $registration->student_name }}</p>
                                <p class="text-xs text-slate-500">{{ $registration->student_email ?: 'No email' }}</p>
                                @if($registration->document_path)
                                    <p class="mt-1 text-xs font-semibold text-blue-700">
                                        <i class="fas fa-paperclip mr-1"></i>
                                        Document attached
                                    </p>
                                @endif
                            </td>
                            <td class="px-4 py-3 align-top">
                                <p class="text-sm font-semibold text-slate-900">{{ $registration->guardian_name }}</p>
                                <p class="text-xs text-slate-500">{{ $registration->guardian_phone }}</p>
                            </td>
                            <td class="px-4 py-3 align-top">
                                <p class="text-sm text-slate-800">{{ $registration->myClass?->name ?: 'N/A' }}</p>
                                <p class="text-xs text-slate-500">{{ $registration->section?->name ?: 'No section' }}</p>
                            </td>
                            <td class="px-4 py-3 align-top">
                                @php
                                    $statusClass = match($registration->status) {
                                        'pending' => 'bg-amber-100 text-amber-800',
                                        'contacted' => 'bg-blue-100 text-blue-800',
                                        'rejected' => 'bg-rose-100 text-rose-800',
                                        'enrolled' => 'bg-emerald-100 text-emerald-800',
                                        default => 'bg-slate-100 text-slate-800',
                                    };
                                @endphp
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $statusClass }}">{{ ucfirst($registration->status) }}</span>
                            </td>
                            <td class="px-4 py-3 align-top text-xs text-slate-600">
                                {{ $registration->created_at->format('M d, Y') }}<br>
                                {{ $registration->created_at->format('h:i A') }}
                            </td>
                            <td class="px-4 py-3 align-top">
                                <div class="flex flex-wrap gap-2">
                                    <button wire:click="viewAdmission({{ $registration->id }})" class="rounded-lg bg-slate-100 px-2.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-200">
                                        View
                                    </button>

                                    @if($registration->status !== 'enrolled')
                                        <button wire:click="markStatus({{ $registration->id }}, 'contacted')" class="rounded-lg bg-blue-100 px-2.5 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-200">
                                            Contacted
                                        </button>
                                        <button wire:click="markStatus({{ $registration->id }}, 'rejected')" class="rounded-lg bg-rose-100 px-2.5 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-200">
                                            Reject
                                        </button>
                                        <button wire:click="enrollStudent({{ $registration->id }})" wire:confirm="Enroll this applicant as a student now?"
                                            class="rounded-lg bg-emerald-100 px-2.5 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-200">
                                            Enroll
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-sm text-slate-500">
                                No admission registrations found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-200 bg-white px-4 py-3">
            {{ $registrations->links() }}
        </div>
    </div>

    @if($selectedAdmission)
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <h3 class="text-lg font-black text-slate-900">Admission Details: {{ $selectedAdmission->reference_no }}</h3>
                <button wire:click="clearSelected" class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-200">
                    Close
                </button>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Student</p>
                    <p class="mt-2 text-sm text-slate-800"><strong>Name:</strong> {{ $selectedAdmission->student_name }}</p>
                    <p class="mt-1 text-sm text-slate-800"><strong>Email:</strong> {{ $selectedAdmission->student_email ?: 'N/A' }}</p>
                    <p class="mt-1 text-sm text-slate-800"><strong>Gender:</strong> {{ ucfirst($selectedAdmission->gender ?: 'N/A') }}</p>
                    <p class="mt-1 text-sm text-slate-800"><strong>DOB:</strong> {{ $selectedAdmission->birthday?->format('M d, Y') ?: 'N/A' }}</p>
                    <p class="mt-1 text-sm text-slate-800"><strong>Class:</strong> {{ $selectedAdmission->myClass?->name ?: 'N/A' }}</p>
                    <p class="mt-1 text-sm text-slate-800"><strong>Section:</strong> {{ $selectedAdmission->section?->name ?: 'N/A' }}</p>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Guardian</p>
                    <p class="mt-2 text-sm text-slate-800"><strong>Name:</strong> {{ $selectedAdmission->guardian_name }}</p>
                    <p class="mt-1 text-sm text-slate-800"><strong>Phone:</strong> {{ $selectedAdmission->guardian_phone }}</p>
                    <p class="mt-1 text-sm text-slate-800"><strong>Email:</strong> {{ $selectedAdmission->guardian_email ?: 'N/A' }}</p>
                    <p class="mt-1 text-sm text-slate-800"><strong>Relationship:</strong> {{ $selectedAdmission->guardian_relationship ?: 'N/A' }}</p>
                    <p class="mt-1 text-sm text-slate-800"><strong>Address:</strong> {{ $selectedAdmission->address ?: 'N/A' }}</p>
                    <p class="mt-1 text-sm text-slate-800"><strong>Previous School:</strong> {{ $selectedAdmission->previous_school ?: 'N/A' }}</p>
                    <p class="mt-1 text-sm text-slate-800">
                        <strong>Document:</strong>
                        @if($selectedAdmission->document_path)
                            <a href="{{ asset('storage/' . $selectedAdmission->document_path) }}" target="_blank" class="font-semibold text-blue-700 hover:underline">
                                {{ $selectedAdmission->document_name ?: 'View Document' }}
                            </a>
                        @else
                            N/A
                        @endif
                    </p>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 md:col-span-2">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Processing</p>
                    <p class="mt-2 text-sm text-slate-800"><strong>Status:</strong> {{ ucfirst($selectedAdmission->status) }}</p>
                    <p class="mt-1 text-sm text-slate-800"><strong>Processed By:</strong> {{ $selectedAdmission->processedBy?->name ?: 'N/A' }}</p>
                    <p class="mt-1 text-sm text-slate-800"><strong>Processed At:</strong> {{ $selectedAdmission->processed_at?->format('M d, Y h:i A') ?: 'N/A' }}</p>
                    <p class="mt-1 text-sm text-slate-800"><strong>Enrolled Student:</strong> {{ $selectedAdmission->enrolledUser?->name ?: 'Not enrolled yet' }}</p>
                    <p class="mt-1 text-sm text-slate-800"><strong>Admission Number:</strong> {{ $selectedAdmission->enrolledStudentRecord?->admission_number ?: 'N/A' }}</p>
                    @if($selectedAdmission->notes)
                        <p class="mt-3 text-sm text-slate-800"><strong>Notes:</strong> {{ $selectedAdmission->notes }}</p>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
