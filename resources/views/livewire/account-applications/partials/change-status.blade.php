<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-edit mr-2 text-indigo-600"></i>Change Application Status
            </h2>
            <button wire:click="switchMode('view', {{ $selectedApplicant->id }})" 
                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </button>
        </div>
        <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg">
            <img src="{{ $selectedApplicant->profile_photo_url }}" 
                 alt="{{ $selectedApplicant->name }}"
                 class="w-16 h-16 rounded-full object-cover">
            <div>
                <p class="font-bold text-lg text-gray-900">{{ $selectedApplicant->name }}</p>
                <p class="text-sm text-gray-600">{{ $selectedApplicant->email }}</p>
                <p class="text-sm text-gray-600">
                    Role: <span class="font-semibold">{{ ucfirst($selectedApplicant->accountApplication->role->name) }}</span>
                </p>
            </div>
        </div>
    </div>

    <!-- Status History -->
    @if($selectedApplicant->accountApplication->statuses->isNotEmpty())
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="bg-gray-100 px-6 py-4 border-b">
                <h3 class="text-lg font-bold text-gray-900">
                    <i class="fas fa-history mr-2"></i>Status History
                </h3>
            </div>
            <div class="p-6">
                <div class="space-y-3">
                    @foreach($selectedApplicant->accountApplication->statuses->reverse()->take(3) as $statusItem)
                        <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                            <i class="fas fa-circle text-indigo-600 text-xs mt-1.5"></i>
                            <div class="flex-1">
                                <div class="flex justify-between items-start">
                                    <p class="font-semibold capitalize text-gray-900">{{ $statusItem->name }}</p>
                                    <span class="text-xs text-gray-500">
                                        {{ $statusItem->created_at->format('M d, Y') }}
                                    </span>
                                </div>
                                @if($statusItem->reason)
                                    <p class="text-sm text-gray-600 mt-1">{{ $statusItem->reason }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Change Status Form -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
            <h3 class="text-lg font-bold text-white">
                <i class="fas fa-clipboard-check mr-2"></i>Update Application Status
            </h3>
        </div>
        
        <form wire:submit.prevent="changeStatus" class="p-6 space-y-6">
            <!-- Status Selection -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">
                    Status <span class="text-red-500">*</span>
                </label>
                <select wire:model.live="status" 
                        class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-indigo-500 p-3">
                    <option value="">Select Status</option>
                    @foreach($statuses as $statusOption)
                        <option value="{{ $statusOption }}">{{ ucwords($statusOption) }}</option>
                    @endforeach
                </select>
                @error('status') 
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Reason/Message -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">
                    Reason/Message (Optional)
                </label>
                <textarea wire:model="reason" 
                          rows="3"
                          placeholder="Enter reason or message for the applicant..."
                          class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-indigo-500 p-3"></textarea>
                @error('reason') 
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Student Record Fields (only for approved students) -->
            @if($studentRecordFields)
                <div class="bg-blue-50 border-2 border-blue-200 rounded-lg p-6 space-y-4">
                    <h4 class="text-lg font-bold text-blue-900 mb-4">
                        <i class="fas fa-graduation-cap mr-2"></i>Student Record Information
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Class -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                Class <span class="text-red-500">*</span>
                            </label>
                            <select wire:model.live="my_class_id" 
                                    class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500 p-3">
                                <option value="">Select Class</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                            @error('my_class_id') 
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Section -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                Section (Optional)
                            </label>
                            <select wire:model="section_id" 
                                    class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500 p-3"
                                    {{ count($sections) === 0 ? 'disabled' : '' }}>
                                <option value="">{{ count($sections) === 0 ? 'No sections available' : 'Select Section' }}</option>
                                @foreach($sections as $section)
                                    <option value="{{ $section->id }}">{{ $section->name }}</option>
                                @endforeach
                            </select>
                            @error('section_id') 
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Admission Number -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                Admission Number (Optional)
                            </label>
                            <input type="text" 
                                   wire:model="admission_number"
                                   placeholder="Leave blank to auto-generate"
                                   class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500 p-3">
                            @error('admission_number') 
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Admission Date -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                Admission Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" 
                                   wire:model="admission_date"
                                   class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500 p-3">
                            @error('admission_date') 
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            @endif

            <!-- Submit Button -->
            <div class="flex gap-3 pt-4">
                <button type="button" 
                        wire:click="switchMode('view', {{ $selectedApplicant->id }})"
                        class="px-6 py-3 border-2 border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-100 transition">
                    Cancel
                </button>
                <button type="submit" 
                        class="flex-1 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold rounded-lg hover:from-indigo-700 hover:to-purple-700 shadow-lg transition">
                    <i class="fas fa-save mr-2"></i>Update Status
                </button>
            </div>
        </form>
    </div>
</div>