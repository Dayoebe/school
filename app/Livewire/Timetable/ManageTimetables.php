<?php

namespace App\Livewire\Timetable;

use App\Models\Timetable;
use App\Models\TimetableTimeSlot;
use App\Models\CustomTimetableItem;
use App\Models\MyClass;
use App\Models\Weekday;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ManageTimetables extends Component
{
    use AuthorizesRequests;

    public $mode = 'list';
    
    // Filters
    public $selectedClass = '';
    public $search = '';
    
    // Timetable form
    public $timetableId;
    public $name = '';
    public $description = '';
    public $my_class_id = '';
    
    // Building timetable
    public $selectedTimeSlot;
    public $selectedWeekday;
    public $recordType = 'subject';
    public $recordId;
    public $startTime = '';
    public $stopTime = '';
    
    // Collections
    public $classes;
    public $timetables;
    public $timeSlots;
    public $weekdays;
    public $subjects;
    public $customItems;
    public $currentTimetable;

    protected $queryString = [
        'mode' => ['except' => 'list'],
        'selectedClass' => ['except' => ''],
    ];

    public function mount()
    {
        $this->loadClasses();
        $this->loadWeekdays();
        $this->loadCustomItems();
        
        if (auth()->user()->hasRole('student')) {
            $this->selectedClass = auth()->user()->studentRecord->myClass->id;
        } elseif ($this->selectedClass && $this->classes->isNotEmpty()) {
            // Keep selected class
        } elseif ($this->classes->isNotEmpty()) {
            $this->selectedClass = $this->classes->first()->id;
        }
        
        if ($this->selectedClass) {
            $this->loadTimetables();
        }
    }

    public function loadClasses()
    {
        if (auth()->user()->hasRole('student')) {
            $this->classes = collect([auth()->user()->studentRecord->myClass]);
        } else {
            $this->classes = MyClass::whereHas('classGroup', function($q) {
                $q->where('school_id', auth()->user()->school_id);
            })->orderBy('name')->get();
        }
    }

    public function loadTimetables()
    {
        if (!$this->selectedClass) return;
        
        $this->timetables = Timetable::where('my_class_id', $this->selectedClass)
            ->where('semester_id', auth()->user()->school->semester_id)
            ->with('timeSlots')
            ->latest()
            ->get();
    }

    public function loadWeekdays()
    {
        $this->weekdays = Weekday::all();
        if ($this->weekdays->isNotEmpty()) {
            $this->selectedWeekday = $this->weekdays->first()->id;
        }
    }

    public function loadCustomItems()
    {
        $this->customItems = CustomTimetableItem::where('school_id', auth()->user()->school_id)->get();
    }

    public function loadTimetableForBuilding($timetableId)
    {
        $this->currentTimetable = Timetable::with(['timeSlots.weekdays', 'myClass.subjects'])
            ->findOrFail($timetableId);
        
        $this->timeSlots = $this->currentTimetable->timeSlots->sortBy('start_time');
        $this->subjects = $this->currentTimetable->myClass->subjects;
        
        if ($this->timeSlots->isNotEmpty()) {
            $this->selectedTimeSlot = $this->timeSlots->first()->id;
        }
    }

    public function updatedSelectedClass()
    {
        $this->loadTimetables();
    }

    public function updatedRecordType()
    {
        $this->recordId = null;
    }

    // Mode switching
    public function switchMode($mode, $timetableId = null)
    {
        $this->mode = $mode;
        $this->timetableId = $timetableId;
        $this->resetValidation();
        
        if ($mode === 'edit' && $timetableId) {
            $this->loadTimetableForEdit($timetableId);
        } elseif ($mode === 'build' && $timetableId) {
            $this->loadTimetableForBuilding($timetableId);
        } elseif ($mode === 'create') {
            $this->resetTimetableForm();
        }
    }

    public function loadTimetableForEdit($timetableId)
    {
        $timetable = Timetable::findOrFail($timetableId);
        $this->authorize('update', $timetable);
        
        $this->timetableId = $timetable->id;
        $this->name = $timetable->name;
        $this->description = $timetable->description;
        $this->my_class_id = $timetable->my_class_id;
    }

    // Timetable CRUD
    public function createTimetable()
    {
        $this->authorize('create', Timetable::class);
        
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'my_class_id' => 'required|exists:my_classes,id',
        ]);

        $timetable = Timetable::create([
            'name' => $this->name,
            'description' => $this->description,
            'my_class_id' => $this->my_class_id,
            'semester_id' => auth()->user()->school->semester_id,
        ]);

        session()->flash('success', 'Timetable created successfully');
        $this->switchMode('build', $timetable->id);
    }

    public function updateTimetable()
    {
        $timetable = Timetable::findOrFail($this->timetableId);
        $this->authorize('update', $timetable);
        
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $timetable->update([
            'name' => $this->name,
            'description' => $this->description,
        ]);

        session()->flash('success', 'Timetable updated successfully');
        $this->switchMode('list');
    }

    public function deleteTimetable($timetableId)
    {
        $timetable = Timetable::findOrFail($timetableId);
        $this->authorize('delete', $timetable);
        
        $timetable->delete();
        
        session()->flash('success', 'Timetable deleted successfully');
        $this->loadTimetables();
    }

    // Time slots
    public function createTimeSlot()
    {
        $this->validate([
            'startTime' => 'required',
            'stopTime' => 'required|after:startTime',
        ]);

        TimetableTimeSlot::create([
            'start_time' => $this->startTime,
            'stop_time' => $this->stopTime,
            'timetable_id' => $this->timetableId,
        ]);

        $this->reset(['startTime', 'stopTime']);
        
        session()->flash('success', 'Time slot created successfully');
        $this->loadTimetableForBuilding($this->timetableId);
    }

    public function deleteTimeSlot($timeSlotId)
    {
        $timeSlot = TimetableTimeSlot::findOrFail($timeSlotId);
        $timeSlot->delete();
        
        session()->flash('success', 'Time slot deleted successfully');
        $this->loadTimetableForBuilding($this->timetableId);
    }

    // Timetable records
    public function attachRecord()
    {
        $this->validate([
            'selectedTimeSlot' => 'required|exists:timetable_time_slots,id',
            'selectedWeekday' => 'required|exists:weekdays,id',
            'recordType' => 'required|in:subject,customTimetableItem',
        ]);

        $timeSlot = TimetableTimeSlot::findOrFail($this->selectedTimeSlot);
        
        // Remove existing record
        $timeSlot->weekdays()->detach($this->selectedWeekday);

        // Attach new record if ID provided
        if ($this->recordId) {
            $type = $this->recordType == 'subject' 
                ? 'App\Models\Subject' 
                : 'App\Models\CustomTimetableItem';
            
            $timeSlot->weekdays()->attach($this->selectedWeekday, [
                'timetable_time_slot_weekdayable_id' => $this->recordId,
                'timetable_time_slot_weekdayable_type' => $type,
            ]);
        }

        session()->flash('success', 'Record updated successfully');
        $this->loadTimetableForBuilding($this->timetableId);
    }

    public function selectCell($timeSlotId, $weekdayId)
    {
        $this->selectedTimeSlot = $timeSlotId;
        $this->selectedWeekday = $weekdayId;
        
        $this->dispatch('scroll-to-form');
    }

    // Helpers
    public function resetTimetableForm()
    {
        $this->reset(['timetableId', 'name', 'description', 'my_class_id']);
    }

    public function render()
    {
        return view('livewire.timetable.manage-timetables')
            ->layout('layouts.new', [
                'breadcrumbs' => [
                    ['href' => route('dashboard'), 'text' => 'Dashboard'],
                    ['href' => route('timetables.index'), 'text' => 'Timetables', 'active' => true],
                ]
            ])
            ->title('Manage Timetables');
    }
}