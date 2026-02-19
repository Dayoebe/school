{{-- partials/build-timetable.blade.php --}}
<div class="space-y-6">
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

            @include('livewire.timetable.partials.timetable-grid')
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="bg-gradient-to-r from-teal-600 to-green-600 px-6 py-4">
            <h3 class="text-xl font-bold text-white">
                <i class="fas fa-clock mr-2"></i>Time Slot Management
            </h3>
        </div>

        <div class="p-6">
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
            const formElement = document.getElementById('attach-form');
            if (formElement) {
                formElement.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });
</script>
@endpush
