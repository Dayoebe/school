<div class="space-y-6">
    <!-- Success Message -->
    @if($showSuccess)
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-600 text-xl mr-3"></i>
                <p class="text-green-800 font-medium">{{ $successMessage }}</p>
            </div>
            <button wire:click="$set('showSuccess', false)" class="text-green-600 hover:text-green-800">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    <!-- Header -->
    <div class="bg-gradient-to-r from-purple-600 to-indigo-700 rounded-2xl shadow-xl p-6">
        <h2 class="text-2xl font-bold text-white mb-2">Term Settings</h2>
        <p class="text-purple-100">Manage announcements and resumption dates for the selected term</p>
    </div>

    <!-- Settings Form -->
    <div class="bg-white rounded-2xl shadow-lg p-6">
        <div class="space-y-6">

            @if($academicYearId && $semesterId)
                <!-- Scope Selection -->
                <div class="border-t border-gray-200 pt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Settings Scope</label>
                    <div class="flex space-x-4">
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" wire:model.live="isGlobal" value="1" class="mr-2">
                            <span class="text-sm font-medium text-gray-700">
                                <i class="fas fa-globe text-blue-600 mr-1"></i> Global (All Classes)
                            </span>
                        </label>
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" wire:model.live="isGlobal" value="0" class="mr-2">
                            <span class="text-sm font-medium text-gray-700">
                                <i class="fas fa-school text-purple-600 mr-1"></i> Specific Class
                            </span>
                        </label>
                    </div>
                </div>

                <!-- Class Selection (if not global) -->
                @if(!$isGlobal)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Class</label>
                        <select wire:model.live="selectedClassId"
                            class="w-full border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-purple-500">
                            <option value="">Select Class</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                        @error('selectedClassId')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <!-- Resumption Date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar-alt text-indigo-600 mr-1"></i> Resumption Date
                    </label>
                    <input type="date" wire:model="resumptionDate"
                        class="w-full border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500">
                    @error('resumptionDate')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- General Announcement -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-bullhorn text-indigo-600 mr-1"></i> General Announcement
                    </label>
                    <textarea wire:model="generalAnnouncement" rows="4"
                        class="w-full border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500"
                        placeholder="Enter announcement for this term..."></textarea>
                    <p class="text-sm text-gray-500 mt-1">
                        This announcement will appear on all student reports for this term
                        @if(!$isGlobal && $selectedClassId)
                            in {{ $classes->firstWhere('id', $selectedClassId)->name ?? 'the selected class' }}
                        @endif
                    </p>
                    @error('generalAnnouncement')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Preview -->
                @if($generalAnnouncement || $resumptionDate)
                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                        <h4 class="font-semibold text-blue-900 mb-2">
                            <i class="fas fa-eye mr-2"></i>Preview
                        </h4>
                        <div class="text-sm text-gray-700 space-y-2">
                            @if($generalAnnouncement)
                                <p><strong>Announcement:</strong> {{ $generalAnnouncement }}</p>
                            @endif
                            @if($resumptionDate)
                                <p><strong>Resumption Date:</strong> {{ \Carbon\Carbon::parse($resumptionDate)->format('d/m/Y') }}</p>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Save Button -->
                <div class="flex justify-end">
                    <button wire:click="save"
                        class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg transition-all duration-300 flex items-center">
                        <i class="fas fa-save mr-2"></i> Save Settings
                    </button>
                </div>
            @else
                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 text-center">
                    <i class="fas fa-info-circle text-yellow-600 text-3xl mb-3"></i>
                    <p class="text-yellow-800 font-medium">Please select an academic year and term to manage settings</p>
                </div>
            @endif
        </div>
    </div>
</div>