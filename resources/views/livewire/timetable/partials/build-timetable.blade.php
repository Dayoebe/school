<div class="space-y-6">
    <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-6 py-4">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold text-gray-900">
                    <i class="fas fa-calendar-week mr-2"></i>Timetable: {{ $currentTimetable->name }}
                </h2>
                <button wire:click="switchMode('list')"
                    class="rounded-lg bg-gray-100 px-4 py-2 font-semibold text-gray-700 hover:bg-gray-200">
                    <i class="fas fa-arrow-left mr-2"></i>Back to List
                </button>
            </div>
        </div>

        <div class="space-y-6 p-6">
            @if(session('success'))
                <div class="rounded border border-green-200 bg-green-50 px-4 py-3 text-green-700">
                    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="rounded border border-red-200 bg-red-50 px-4 py-3 text-red-700">
                    <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
                </div>
            @endif

            @if($canUpdateTimetable)
                <div id="attach-form" class="rounded-lg border border-indigo-200 bg-indigo-50 p-4">
                    <h5 class="mb-4 text-lg font-bold text-gray-800">
                        <i class="fas fa-link mr-2"></i>Attach Subject/Item
                    </h5>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-gray-700">Time Slot</label>
                            <select wire:model.live="selectedTimeSlot"
                                class="w-full rounded-lg border border-gray-300 p-2.5 focus:border-indigo-500 focus:ring-indigo-500">
                                @if($timeSlots->isEmpty())
                                    <option value="">Create time slot first</option>
                                @else
                                    @foreach($timeSlots as $slot)
                                        <option value="{{ $slot->id }}">{{ $slot->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-gray-700">Weekday</label>
                            <select wire:model.live="selectedWeekday"
                                class="w-full rounded-lg border border-gray-300 p-2.5 focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach($weekdays as $day)
                                    <option value="{{ $day->id }}">{{ $day->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-gray-700">Type</label>
                            <select wire:model.live="recordType"
                                class="w-full rounded-lg border border-gray-300 p-2.5 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="subject">Subject</option>
                                <option value="customTimetableItem">Custom Item</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-gray-700">Subject/Item</label>
                            <select wire:model="recordId"
                                class="w-full rounded-lg border border-gray-300 p-2.5 focus:border-indigo-500 focus:ring-indigo-500">
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
                        class="mt-4 rounded-lg bg-indigo-600 px-6 py-2.5 font-semibold text-white hover:bg-indigo-700">
                        <i class="fas fa-check mr-2"></i>Save Cell
                    </button>
                </div>
            @else
                <div class="rounded border border-blue-200 bg-blue-50 px-4 py-3 text-blue-800">
                    <i class="fas fa-info-circle mr-2"></i>Read-only mode. You can view timetable cells but cannot edit them.
                </div>
            @endif

            @include('livewire.timetable.partials.timetable-grid')
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-6 py-4">
            <h3 class="text-xl font-bold text-gray-900">
                <i class="fas fa-clock mr-2"></i>Time Slots
            </h3>
        </div>

        <div class="space-y-6 p-6">
            @if($canUpdateTimetable)
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                    <h5 class="mb-4 text-lg font-bold text-gray-800">Create New Time Slot</h5>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-gray-700">Start Time</label>
                            <input type="time" wire:model="startTime"
                                class="w-full rounded-lg border border-gray-300 p-2.5 focus:border-indigo-500 focus:ring-indigo-500">
                            @error('startTime') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-gray-700">Stop Time</label>
                            <input type="time" wire:model="stopTime"
                                class="w-full rounded-lg border border-gray-300 p-2.5 focus:border-indigo-500 focus:ring-indigo-500">
                            @error('stopTime') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex items-end">
                            <button wire:click="createTimeSlot"
                                class="w-full rounded-lg bg-indigo-600 px-6 py-2.5 font-semibold text-white hover:bg-indigo-700">
                                <i class="fas fa-plus mr-2"></i>Create Slot
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="border-b border-gray-200 bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Time Range</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Start Time</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Stop Time</th>
                            <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($timeSlots as $slot)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $slot->name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $slot->start_time }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $slot->stop_time }}</td>
                                <td class="px-4 py-3 text-right text-sm">
                                    @if($canUpdateTimetable)
                                        <button wire:click="deleteTimeSlot({{ $slot->id }})"
                                            wire:confirm="Delete this time slot? All associated records will be removed."
                                            class="rounded-lg bg-red-100 px-3 py-1.5 text-red-700 hover:bg-red-200">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                                    <i class="fas fa-clock mb-2 text-4xl text-gray-300"></i>
                                    <p>No time slots created yet.</p>
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
