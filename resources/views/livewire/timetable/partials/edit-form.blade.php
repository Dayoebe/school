{{-- partials/edit-form.blade.php --}}
<div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <div class="bg-gradient-to-r from-yellow-600 to-orange-600 px-6 py-4">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-white">
                <i class="fas fa-edit mr-2"></i>Edit Timetable
            </h2>
            <button wire:click="switchMode('list')" 
                    class="px-4 py-2 bg-white/20 backdrop-blur-sm text-white font-semibold rounded-lg hover:bg-white/30 transition">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </button>
        </div>
    </div>

    <form wire:submit.prevent="updateTimetable" class="p-6 space-y-6">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                Timetable Name <span class="text-red-500">*</span>
            </label>
            <input type="text" wire:model="name" 
                   class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                   placeholder="Enter timetable name">
            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
            <textarea wire:model="description" rows="3"
                      class="w-full rounded-lg border-2 border-gray-300 p-3 focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                      placeholder="Enter description"></textarea>
            @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Class</label>
            <select wire:model="my_class_id" disabled
                    class="w-full rounded-lg border-2 border-gray-200 bg-gray-100 p-3 cursor-not-allowed">
                @foreach($classes as $class)
                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-sm text-gray-500">Class cannot be changed after creation</p>
        </div>

        <div class="flex justify-end gap-3">
            <button type="button" wire:click="switchMode('list')"
                    class="px-6 py-2.5 border-2 border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition">
                Cancel
            </button>
            <button type="submit" 
                    class="px-6 py-2.5 bg-gradient-to-r from-yellow-600 to-orange-600 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition">
                <i class="fas fa-save mr-2"></i>Update Timetable
            </button>
        </div>
    </form>
</div>

{{-- ========================================== --}}

{{-- partials/build-timetable.blade.php --}}
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold text-white">
                    <i class="fas fa-hammer mr-2"></i>Build: {{ $currentTimetable->name }}
                </h2>
                <button wire:click="switchMode('list')" 
                        class="px-4 py-2 bg-white/20 backdrop-blur-sm text-white font-semibold rounded-lg hover:bg-white/30 transition">
                    <i class="fas fa-arrow-left mr-2"></i>Back to List
                </button>
            </div>
        </div>

        <div class="p-6">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded">
                    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                </div>
            @endif

            <!-- Attach Record Form -->
            <div id="attach-form" class="mb-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border-2 border-indigo-200">
                <h5 class="text-lg font-bold text-gray-800 mb-4">
                    <i class="fas fa-link mr-2"></i>Attach Subject/Item to Timetable
                </h5>
                
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Time Slot</label>
                        <select wire:model.live="selectedTimeSlot" 
                                class="w-full rounded-lg border-2 border-gray-300 p-2.5 focus:ring-2 focus:ring-indigo-500">
                            @if($timeSlots->isEmpty())
                                <option>Create time slot first</option>
                            @else
                                @foreach($timeSlots as $slot)
                                    <option value="{{ $slot->id }}">{{ $slot->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Weekday</label>
                        <select wire:model.live="selectedWeekday" 
                                class="w-full rounded-lg border-2 border-gray-300 p-2.5 focus:ring-2 focus:ring-indigo-500">
                            @foreach($weekdays as $day)
                                <option value="{{ $day->id }}">{{ $day->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Type</label>
                        <select wire:model.live="recordType" 
                                class="w-full rounded-lg border-2 border-gray-300 p-2.5 focus:ring-2 focus:ring-indigo-500">
                            <option value="subject">Subject</option>
                            <option value="customTimetableItem">Custom Item</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Subject/Item</label>
                        <select wire:model="recordId" 
                                class="w-full rounded-lg border-2 border-gray-300 p-2.5 focus:ring-2 focus:ring-indigo-500">
                            <option value="">Make Blank</option>
                            @if($recordType === 'subject')
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                @endforeach
                            @else
                                @foreach($customItems as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>

                <button wire:click="attachRecord" 
                        class="mt-4 px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition">
                    <i class="fas fa-check mr-2"></i>Attach Record
                </button>
            </div>

            <!-- Timetable Grid -->
            @include('livewire.timetable.partials.timetable-grid')
        </div>
    </div>

    <!-- Time Slot Management -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="bg-gradient-to-r from-teal-600 to-green-600 px-6 py-4">
            <h3 class="text-xl font-bold text-white">
                <i class="fas fa-clock mr-2"></i>Time Slot Management
            </h3>
        </div>

        <div class="p-6">
            <!-- Create Time Slot Form -->
            <div class="mb-6 p-4 bg-green-50 rounded-lg border-2 border-green-200">
                <h5 class="text-lg font-bold text-gray-800 mb-4">Create New Time Slot</h5>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Start Time</label>
                        <input type="time" wire:model="startTime" 
                               class="w-full rounded-lg border-2 border-gray-300 p-2.5 focus:ring-2 focus:ring-green-500">
                        @error('startTime') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Stop Time</label>
                        <input type="time" wire:model="stopTime" 
                               class="w-full rounded-lg border-2 border-gray-300 p-2.5 focus:ring-2 focus:ring-green-500">
                        @error('stopTime') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    
                    <div class="flex items-end">
                        <button wire:click="createTimeSlot" 
                                class="w-full px-6 py-2.5 bg-gradient-to-r from-teal-600 to-green-600 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition">
                            <i class="fas fa-plus mr-2"></i>Create Slot
                        </button>
                    </div>
                </div>
            </div>

            <!-- Time Slots List -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b-2 border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Time Range</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Start Time</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Stop Time</th>
                            <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($timeSlots as $slot)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $slot->name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $slot->start_time }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $slot->stop_time }}</td>
                                <td class="px-4 py-3 text-sm text-right">
                                    <button wire:click="deleteTimeSlot({{ $slot->id }})"
                                            wire:confirm="Delete this time slot? All associated records will be removed."
                                            class="px-3 py-1.5 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                                    <i class="fas fa-clock text-4xl text-gray-300 mb-2"></i>
                                    <p>No time slots created yet</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('scroll-to-form', () => {
            document.getElementById('attach-form').scrollIntoView({ behavior: 'smooth' });
        });
    });
</script>
@endpush

{{-- ========================================== --}}

{{-- partials/timetable-grid.blade.php --}}
<div class="overflow-x-auto">
    <div class="min-w-full inline-block align-middle">
        <table class="min-w-full border-2 border-gray-300 rounded-lg">
            <thead class="bg-gradient-to-r from-gray-100 to-gray-200">
                <tr>
                    <th class="border-2 border-gray-300 px-4 py-3 text-center font-bold text-gray-700 whitespace-nowrap">
                        <div class="text-sm">
                            Time →<br>Day ↓
                        </div>
                    </th>
                    @foreach($timeSlots as $timeSlot)
                        <th class="border-2 border-gray-300 px-4 py-3 text-center font-semibold text-gray-700 whitespace-nowrap">
                            <div class="text-xs">
                                {{ $timeSlot->start_time }}<br>to<br>{{ $timeSlot->stop_time }}
                            </div>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($weekdays as $weekday)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="border-2 border-gray-300 px-4 py-3 bg-gray-50 font-bold text-gray-700">
                            {{ $weekday->name }}
                        </td>
                        @foreach($timeSlots as $timeSlot)
                            <td class="border-2 border-gray-300 px-4 py-3 text-center cursor-pointer hover:bg-blue-50 transition"
                                wire:click="selectCell({{ $timeSlot->id }}, {{ $weekday->id }})">
                                @php
                                    $pivot = $timeSlot->weekdays->find($weekday->id)?->timetableRecord;
                                @endphp
                                
                                @if($pivot)
                                    @if($pivot->timetable_time_slot_weekdayable_type == "App\Models\Subject")
                                        <span class="inline-block px-3 py-1.5 bg-blue-100 text-blue-800 rounded-lg font-semibold text-sm">
                                            {{ $subjects->find($pivot->timetable_time_slot_weekdayable_id)?->name }}
                                        </span>
                                    @elseif($pivot->timetable_time_slot_weekdayable_type == "App\Models\CustomTimetableItem")
                                        <span class="inline-block px-3 py-1.5 bg-gray-100 text-gray-800 rounded-lg font-semibold text-sm">
                                            {{ $customItems->find($pivot->timetable_time_slot_weekdayable_id)?->name }}
                                        </span>
                                    @endif
                                @else
                                    <span class="text-gray-400 text-xs">Click to assign</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4 flex items-center gap-4 text-sm text-gray-600">
    <div class="flex items-center gap-2">
        <div class="w-4 h-4 bg-blue-100 border-2 border-blue-300 rounded"></div>
        <span>Subject</span>
    </div>
    <div class="flex items-center gap-2">
        <div class="w-4 h-4 bg-gray-100 border-2 border-gray-300 rounded"></div>
        <span>Custom Item</span>
    </div>
</div>

{{-- ========================================== --}}

{{-- partials/custom-items.blade.php --}}
<div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <div class="bg-gradient-to-r from-pink-600 to-rose-600 px-6 py-4">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-white">
                <i class="fas fa-cog mr-2"></i>Custom Timetable Items
            </h2>
            <button wire:click="switchMode('list')" 
                    class="px-4 py-2 bg-white/20 backdrop-blur-sm text-white font-semibold rounded-lg hover:bg-white/30 transition">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </button>
        </div>
    </div>

    <div class="p-6">
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif

        <!-- Custom Items Content Here -->
        <p class="text-gray-600">Custom items management will be implemented separately</p>
    </div>
</div>