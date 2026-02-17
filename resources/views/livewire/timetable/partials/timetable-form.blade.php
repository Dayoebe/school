{{-- partials/timetable-form.blade.php --}}
<div class="space-y-4">
    <x-input 
        id="name" 
        label="Timetable Name *" 
        placeholder="Enter timetable name" 
        wire:model="name"
    />
    
    <x-textarea 
        id="description" 
        label="Description" 
        placeholder="Enter description"
        wire:model="description"
    />
    
    <x-select 
        id="selectedClass" 
        label="Select Class *" 
        wire:model="selectedClass"
        @if(isset($isEdit)) disabled @endif
    >
        <option value="">Choose a class</option>
        @foreach($classes as $class)
            <option value="{{ $class->id }}">{{ $class->name }}</option>
        @endforeach
    </x-select>
</div>

{{-- ========================================== --}}

{{-- partials/list-actions.blade.php --}}
<div class="btn-group">
    @can('read timetable')
        <button wire:click="showBuildView({{ $timetable->id }})" class="btn btn-sm btn-info" title="View">
            <i class="fas fa-eye"></i>
        </button>
    @endcan
    
    @can('update timetable')
        <button wire:click="showEditForm({{ $timetable->id }})" class="btn btn-sm btn-warning" title="Edit">
            <i class="fas fa-pen"></i>
        </button>
        <button wire:click="showBuildView({{ $timetable->id }})" class="btn btn-sm btn-primary" title="Build">
            <i class="fas fa-hammer"></i>
        </button>
    @endcan
    
    @can('delete timetable')
        <button 
            wire:click="deleteTimetable({{ $timetable->id }})" 
            wire:confirm="Are you sure you want to delete this timetable?"
            class="btn btn-sm btn-danger" 
            title="Delete"
        >
            <i class="fas fa-trash"></i>
        </button>
    @endcan
</div>

{{-- ========================================== --}}

{{-- partials/build-timetable.blade.php --}}
<div>
    {{-- Attach Record Form --}}
    <div id="attach-form" class="mb-6 p-4 bg-gray-50 rounded-lg">
        <h5 class="text-lg font-semibold mb-4">Attach Subject/Item to Timetable</h5>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-select id="selectedTimeSlot" label="Time Slot" wire:model.live="selectedTimeSlot">
                @if($timeSlots->isEmpty())
                    <option>Create time slot first</option>
                @else
                    @foreach($timeSlots as $slot)
                        <option value="{{ $slot->id }}">{{ $slot->name }}</option>
                    @endforeach
                @endif
            </x-select>

            <x-select id="selectedWeekday" label="Weekday" wire:model.live="selectedWeekday">
                @foreach($weekdays as $day)
                    <option value="{{ $day->id }}">{{ $day->name }}</option>
                @endforeach
            </x-select>

            <x-select id="recordType" label="Type" wire:model.live="recordType">
                <option value="subject">Subject</option>
                <option value="customTimetableItem">Custom Item</option>
            </x-select>

            <x-select id="recordId" label="Subject/Item" wire:model="recordId">
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
            </x-select>
        </div>

        <x-button 
            label="Attach Record" 
            theme="primary" 
            wire:click="attachRecord" 
            class="mt-4"
        />
    </div>

    {{-- Timetable Grid --}}
    @include('livewire.timetable.partials.timetable-grid')

    {{-- Create Time Slot Form --}}
    <div class="mt-6 p-4 bg-gray-50 rounded-lg">
        <h5 class="text-lg font-semibold mb-4">Create Time Slot</h5>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-input 
                id="startTime" 
                type="time" 
                label="Start Time" 
                wire:model="startTime"
            />
            
            <x-input 
                id="stopTime" 
                type="time" 
                label="Stop Time" 
                wire:model="stopTime"
            />
            
            <div class="flex items-end">
                <x-button 
                    label="Create Slot" 
                    theme="primary" 
                    wire:click="createTimeSlot" 
                    class="w-full"
                />
            </div>
        </div>
    </div>

    {{-- Time Slots List --}}
    <div class="mt-6">
        <h5 class="text-lg font-semibold mb-4">Time Slots</h5>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Time Range</th>
                        <th>Start Time</th>
                        <th>Stop Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($timeSlots as $slot)
                        <tr>
                            <td>{{ $slot->name }}</td>
                            <td>{{ $slot->start_time }}</td>
                            <td>{{ $slot->stop_time }}</td>
                            <td>
                                <button 
                                    wire:click="deleteTimeSlot({{ $slot->id }})"
                                    wire:confirm="Delete this time slot?"
                                    class="btn btn-sm btn-danger"
                                >
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-gray-500">No time slots created yet</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
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
<div class="overflow-x-auto mb-6">
    <table class="table table-bordered w-full">
        <thead>
            <tr>
                <th class="text-center p-4 whitespace-nowrap">
                    <p>Time Slots <span style="font-family: Dejavu Sans, sans-serif;">→</span><br>
                    Weekdays <span style="font-family: Dejavu Sans, sans-serif;">↓</span></p>
                </th>
                @foreach($timeSlots as $timeSlot)
                    <th class="text-center p-4 whitespace-nowrap">
                        <p>{{ $timeSlot->start_time }}<br>-<br>{{ $timeSlot->stop_time }}</p>
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($weekdays as $weekday)
                <tr>
                    <td class="p-4 font-semibold">{{ $weekday->name }}</td>
                    @foreach($timeSlots as $timeSlot)
                        <td 
                            class="p-4 cursor-pointer hover:bg-gray-100 transition"
                            wire:click="selectCell({{ $timeSlot->id }}, {{ $weekday->id }})"
                        >
                            @php
                                $pivot = $timeSlot->weekdays->find($weekday->id)?->timetableRecord;
                            @endphp
                            
                            @if($pivot)
                                @if($pivot->timetable_time_slot_weekdayable_type == "App\Models\Subject")
                                    <span class="badge badge-primary">
                                        {{ $subjects->find($pivot->timetable_time_slot_weekdayable_id)?->name }}
                                    </span>
                                @elseif($pivot->timetable_time_slot_weekdayable_type == "App\Models\CustomTimetableItem")
                                    <span class="badge badge-secondary">
                                        {{ $customItems->find($pivot->timetable_time_slot_weekdayable_id)?->name }}
                                    </span>
                                @endif
                            @else
                                <span class="text-gray-400 text-sm">Click to assign</span>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- ========================================== --}}

{{-- partials/custom-items.blade.php --}}
<div>
    {{-- Create/Edit Form --}}
    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
        <h5 class="text-lg font-semibold mb-4">
            {{ $customItemId ? 'Edit' : 'Create' }} Custom Item
        </h5>
        
        <div class="flex gap-4">
            <div class="flex-1">
                <x-input 
                    id="customItemName" 
                    label="Item Name" 
                    placeholder="e.g., Break, Lunch, Assembly"
                    wire:model="customItemName"
                />
            </div>
            
            <div class="flex items-end gap-2">
                @if($customItemId)
                    <x-button 
                        label="Update" 
                        theme="primary" 
                        wire:click="updateCustomItem"
                    />
                    <x-button 
                        label="Cancel" 
                        theme="secondary" 
                        wire:click="cancelCustomItemEdit"
                    />
                @else
                    <x-button 
                        label="Create" 
                        theme="primary" 
                        wire:click="createCustomItem"
                    />
                @endif
            </div>
        </div>
    </div>

    {{-- Items List --}}
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customItems as $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td>
                            <button 
                                wire:click="editCustomItem({{ $item->id }})" 
                                class="btn btn-sm btn-warning"
                            >
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button 
                                wire:click="deleteCustomItem({{ $item->id }})"
                                wire:confirm="Delete this custom item?"
                                class="btn btn-sm btn-danger"
                            >
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="text-center text-gray-500">
                            No custom items created yet
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>