<?php

namespace App\Livewire\Timetable;

use App\Models\Timetable;
use App\Models\TimetableTimeSlot;
use App\Models\CustomTimetableItem;
use App\Models\MyClass;
use App\Models\Weekday;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ManageTimetables extends Component
{
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

    // Custom item form
    public $customItemId;
    public $customItemName = '';
    
    // Collections
    public $classes;
    public $timetables;
    public $timeSlots;
    public $weekdays;
    public $subjects;
    public $customItems;
    public $currentTimetable;
    public $activeSemesterId = null;

    public bool $canReadTimetable = false;
    public bool $canCreateTimetable = false;
    public bool $canUpdateTimetable = false;
    public bool $canDeleteTimetable = false;
    public bool $canReadCustomItems = false;
    public bool $canCreateCustomItems = false;
    public bool $canUpdateCustomItems = false;
    public bool $canDeleteCustomItems = false;

    protected $queryString = [
        'mode' => ['except' => 'list'],
        'selectedClass' => ['except' => ''],
    ];

    public function mount()
    {
        $this->activeSemesterId = auth()->user()->school?->semester_id;
        $this->hydratePermissionFlags();
        $this->loadClasses();
        $this->loadWeekdays();
        $this->loadCustomItems();
        $this->hydrateModeFromRoute();

        $classFromQuery = request()->query('class');
        if ($classFromQuery && $this->classExistsInScope((int) $classFromQuery)) {
            $this->selectedClass = (int) $classFromQuery;
        }

        if (auth()->user()->hasRole('student') && auth()->user()->studentRecord?->myClass) {
            $this->selectedClass = auth()->user()->studentRecord->myClass->id;
        } elseif (!$this->selectedClass && $this->classes->isNotEmpty()) {
            $this->selectedClass = $this->classes->first()->id;
        } elseif ($this->selectedClass && !$this->classExistsInScope((int) $this->selectedClass)) {
            $this->selectedClass = '';
        }

        if ($this->selectedClass) {
            $this->loadTimetables();
        } else {
            $this->timetables = collect();
        }

        if (!$this->modeIsAllowed($this->mode)) {
            $this->mode = 'list';
        }

        if (in_array($this->mode, ['edit', 'build'], true)) {
            $this->mode = 'list';
            $this->timetableId = null;
        }
    }

    public function loadClasses()
    {
        if (auth()->user()->hasRole('student')) {
            $studentClass = auth()->user()->studentRecord?->myClass;
            if ($studentClass && MyClass::whereHas('classGroup', function ($q) {
                $q->where('school_id', auth()->user()->school_id);
            })->where('id', $studentClass->id)->exists()) {
                $this->classes = collect([$studentClass]);
            } else {
                $this->classes = collect();
            }
        } else {
            $this->classes = MyClass::whereHas('classGroup', function($q) {
                $q->where('school_id', auth()->user()->school_id);
            })->orderBy('name')->get();
        }
    }

    public function loadTimetables()
    {
        if (!$this->selectedClass || !$this->activeSemesterId) {
            $this->timetables = collect();
            return;
        }

        $this->timetables = $this->timetableQueryForSchool()
            ->where('my_class_id', $this->selectedClass)
            ->with(['timeSlots', 'myClass'])
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
        $this->customItems = CustomTimetableItem::query()
            ->orderBy('name')
            ->get();
    }

    public function loadTimetableForBuilding($timetableId)
    {
        $this->ensureCan(['read timetable', 'update timetable']);

        $this->currentTimetable = $this->timetableQueryForSchool()
            ->with(['timeSlots.weekdays', 'myClass.subjects'])
            ->findOrFail($timetableId);
        
        $this->timeSlots = $this->currentTimetable->timeSlots->sortBy('start_time');
        $this->subjects = $this->currentTimetable->myClass->subjects;
        
        if ($this->timeSlots->isNotEmpty()) {
            $this->selectedTimeSlot = $this->timeSlots->first()->id;
        }
    }

    public function updatedSelectedClass()
    {
        if (auth()->user()->hasRole('student')) {
            $this->selectedClass = auth()->user()->studentRecord?->myClass?->id ?? '';
        } elseif ($this->selectedClass && !$this->classExistsInScope((int) $this->selectedClass)) {
            $this->selectedClass = '';
        }

        $this->loadTimetables();
    }

    public function updatedRecordType()
    {
        $this->recordId = null;
    }

    // Mode switching
    public function switchMode($mode, $timetableId = null)
    {
        if (!$this->modeIsAllowed($mode)) {
            return;
        }

        if (in_array($mode, ['edit', 'build'], true) && !$timetableId) {
            $this->mode = 'list';
            $this->timetableId = null;
            $this->loadTimetables();
            return;
        }

        $this->mode = $mode;
        $this->timetableId = $timetableId;
        $this->resetValidation();
        
        if ($mode === 'edit' && $timetableId) {
            $this->loadTimetableForEdit($timetableId);
        } elseif ($mode === 'build' && $timetableId) {
            $this->loadTimetableForBuilding($timetableId);
        } elseif ($mode === 'create') {
            $this->resetTimetableForm();
            $this->my_class_id = $this->selectedClass ?: '';
        } elseif ($mode === 'custom-items') {
            $this->resetCustomItemForm();
            $this->loadCustomItems();
        } else {
            $this->loadTimetables();
        }
    }

    public function loadTimetableForEdit($timetableId)
    {
        $timetable = $this->timetableQueryForSchool()->findOrFail($timetableId);
        $this->ensureCan(['update timetable']);
        
        $this->timetableId = $timetable->id;
        $this->name = $timetable->name;
        $this->description = $timetable->description;
        $this->my_class_id = $timetable->my_class_id;
    }

    // Timetable CRUD
    public function createTimetable()
    {
        $this->ensureCan(['create timetable']);

        if (!$this->activeSemesterId) {
            session()->flash('error', 'Set an active semester before creating a timetable.');
            return;
        }
        
        $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('timetables', 'name')->where(function ($query) {
                    $query->where('my_class_id', $this->my_class_id)
                        ->where('semester_id', $this->activeSemesterId);
                }),
            ],
            'description' => 'nullable|string',
            'my_class_id' => 'required|exists:my_classes,id',
        ]);

        if (!$this->classExistsInScope((int) $this->my_class_id)) {
            session()->flash('error', 'Selected class is not valid for your school');
            return;
        }

        $timetable = Timetable::create([
            'name' => $this->name,
            'description' => $this->description,
            'my_class_id' => $this->my_class_id,
            'semester_id' => $this->activeSemesterId,
        ]);

        session()->flash('success', 'Timetable created successfully');
        $this->switchMode('build', $timetable->id);
    }

    public function updateTimetable()
    {
        $this->ensureCan(['update timetable']);

        $timetable = $this->timetableQueryForSchool()->findOrFail($this->timetableId);
        
        $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('timetables', 'name')
                    ->ignore($timetable->id)
                    ->where(function ($query) use ($timetable) {
                        $query->where('my_class_id', $timetable->my_class_id)
                            ->where('semester_id', $timetable->semester_id);
                    }),
            ],
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
        $this->ensureCan(['delete timetable']);

        $timetable = $this->timetableQueryForSchool()->findOrFail($timetableId);
        
        $timetable->delete();
        
        session()->flash('success', 'Timetable deleted successfully');
        $this->loadTimetables();
    }

    // Time slots
    public function createTimeSlot()
    {
        $this->ensureCan(['update timetable']);

        $this->validate([
            'startTime' => 'required|date_format:H:i',
            'stopTime' => 'required|date_format:H:i|after:startTime',
        ]);

        $timetable = $this->timetableQueryForSchool()->findOrFail($this->timetableId);

        $hasOverlap = TimetableTimeSlot::query()
            ->where('timetable_id', $timetable->id)
            ->where('start_time', '<', $this->stopTime)
            ->where('stop_time', '>', $this->startTime)
            ->exists();

        if ($hasOverlap) {
            session()->flash('error', 'Time slot overlaps with an existing slot.');
            return;
        }

        TimetableTimeSlot::create([
            'start_time' => $this->startTime,
            'stop_time' => $this->stopTime,
            'timetable_id' => $timetable->id,
        ]);

        $this->reset(['startTime', 'stopTime']);
        
        session()->flash('success', 'Time slot created successfully');
        $this->loadTimetableForBuilding($this->timetableId);
    }

    public function deleteTimeSlot($timeSlotId)
    {
        $this->ensureCan(['update timetable']);

        $timeSlot = TimetableTimeSlot::whereHas('timetable.myClass.classGroup', function ($query) {
            $query->where('school_id', auth()->user()->school_id);
        })->where('timetable_id', $this->timetableId)
            ->findOrFail($timeSlotId);

        $timeSlot->delete();
        
        session()->flash('success', 'Time slot deleted successfully');
        $this->loadTimetableForBuilding($this->timetableId);
    }

    // Timetable records
    public function attachRecord()
    {
        $this->ensureCan(['update timetable']);

        $this->validate([
            'selectedTimeSlot' => 'required|exists:timetable_time_slots,id',
            'selectedWeekday' => 'required|exists:weekdays,id',
            'recordType' => 'required|in:subject,customTimetableItem',
        ]);

        $timeSlot = TimetableTimeSlot::whereHas('timetable.myClass.classGroup', function ($query) {
            $query->where('school_id', auth()->user()->school_id);
        })->findOrFail($this->selectedTimeSlot);

        if ((int) $timeSlot->timetable_id !== (int) $this->timetableId) {
            session()->flash('error', 'Selected time slot does not belong to the current timetable.');
            return;
        }

        // Attach new record if ID provided
        if ($this->recordId) {
            $isValidRecord = false;
            $type = $this->recordType == 'subject' 
                ? 'App\Models\Subject' 
                : 'App\Models\CustomTimetableItem';

            if ($this->recordType === 'subject') {
                $isValidRecord = DB::table('subjects')
                    ->where('id', $this->recordId)
                    ->where('school_id', auth()->user()->school_id)
                    ->where(function ($query) use ($timeSlot) {
                        $query->where('my_class_id', $timeSlot->timetable->my_class_id)
                            ->orWhereExists(function ($subQuery) use ($timeSlot) {
                                $subQuery->select(DB::raw(1))
                                    ->from('class_subject')
                                    ->whereColumn('class_subject.subject_id', 'subjects.id')
                                    ->where('class_subject.my_class_id', $timeSlot->timetable->my_class_id)
                                    ->where('class_subject.school_id', auth()->user()->school_id);
                            });
                    })
                    ->exists();
            } else {
                $isValidRecord = CustomTimetableItem::query()
                    ->where('id', $this->recordId)
                    ->exists();
            }

            if (!$isValidRecord) {
                session()->flash('error', 'Selected record is not valid for your current school/class.');
                return;
            }

            // Remove existing record before attaching new one.
            $timeSlot->weekdays()->detach($this->selectedWeekday);

            $timeSlot->weekdays()->attach($this->selectedWeekday, [
                'timetable_time_slot_weekdayable_id' => $this->recordId,
                'timetable_time_slot_weekdayable_type' => $type,
            ]);
        } else {
            // Blank selection clears the cell.
            $timeSlot->weekdays()->detach($this->selectedWeekday);
        }

        session()->flash('success', 'Record updated successfully');
        $this->loadTimetableForBuilding($this->timetableId);
    }

    public function selectCell($timeSlotId, $weekdayId)
    {
        if (!$this->canUpdateTimetable) {
            return;
        }

        $exists = TimetableTimeSlot::query()
            ->where('id', $timeSlotId)
            ->where('timetable_id', $this->timetableId)
            ->exists();

        if (!$exists) {
            return;
        }

        $this->selectedTimeSlot = $timeSlotId;
        $this->selectedWeekday = $weekdayId;
        
        $this->dispatch('scroll-to-form');
    }

    // Custom timetable items
    public function saveCustomItem()
    {
        $isEditing = !empty($this->customItemId);

        $this->ensureCan($isEditing
            ? ['update custom timetable items', 'update custom timetable item']
            : ['create custom timetable items', 'create custom timetable item']
        );

        $validated = $this->validate([
            'customItemName' => [
                'required',
                'string',
                'max:255',
                Rule::unique('custom_timetable_items', 'name')
                    ->where(fn($query) => $query->where('school_id', auth()->user()->school_id))
                    ->ignore($this->customItemId),
            ],
        ]);

        if ($isEditing) {
            $item = CustomTimetableItem::query()->findOrFail($this->customItemId);
            $item->update(['name' => $validated['customItemName']]);
            session()->flash('success', 'Custom item updated successfully');
        } else {
            CustomTimetableItem::create([
                'name' => $validated['customItemName'],
                'school_id' => auth()->user()->school_id,
            ]);
            session()->flash('success', 'Custom item created successfully');
        }

        $this->resetCustomItemForm();
        $this->loadCustomItems();
    }

    public function editCustomItem($customItemId)
    {
        $this->ensureCan(['update custom timetable items', 'update custom timetable item']);

        $item = CustomTimetableItem::query()->findOrFail($customItemId);

        $this->customItemId = $item->id;
        $this->customItemName = $item->name;
        $this->mode = 'custom-items';
    }

    public function cancelCustomItemEdit()
    {
        $this->resetCustomItemForm();
    }

    public function deleteCustomItem($customItemId)
    {
        $this->ensureCan(['delete custom timetable items', 'delete custom timetable item']);

        $item = CustomTimetableItem::query()->findOrFail($customItemId);

        DB::table('timetable_time_slot_weekday')
            ->where('timetable_time_slot_weekdayable_type', CustomTimetableItem::class)
            ->where('timetable_time_slot_weekdayable_id', $item->id)
            ->delete();

        $item->delete();

        if ((int) $this->customItemId === (int) $item->id) {
            $this->resetCustomItemForm();
        }

        $this->loadCustomItems();
        session()->flash('success', 'Custom item deleted successfully');
    }

    // Helpers
    public function resetTimetableForm()
    {
        $this->reset(['timetableId', 'name', 'description', 'my_class_id']);
    }

    public function resetCustomItemForm()
    {
        $this->reset(['customItemId', 'customItemName']);
    }

    protected function classExistsInScope(int $classId): bool
    {
        return $this->classes?->contains('id', $classId) ?? false;
    }

    protected function timetableQueryForSchool()
    {
        if (!$this->activeSemesterId) {
            return Timetable::query()->whereRaw('1 = 0');
        }

        return Timetable::whereHas('myClass.classGroup', function ($query) {
            $query->where('school_id', auth()->user()->school_id);
        })->where('semester_id', $this->activeSemesterId);
    }

    protected function hydrateModeFromRoute(): void
    {
        $routeName = request()->route()?->getName();

        if ($routeName === 'timetables.create') {
            $this->mode = 'create';
            return;
        }

        if (in_array($routeName, ['custom-timetable-items.index', 'custom-timetable-items.create'], true)) {
            $this->mode = 'custom-items';
            return;
        }

        if (!in_array($this->mode, ['list', 'create', 'edit', 'build', 'custom-items'], true)) {
            $this->mode = 'list';
        }
    }

    protected function hydratePermissionFlags(): void
    {
        $this->canReadTimetable = $this->canAnyPermission(['read timetable']);
        $this->canCreateTimetable = $this->canAnyPermission(['create timetable']);
        $this->canUpdateTimetable = $this->canAnyPermission(['update timetable']);
        $this->canDeleteTimetable = $this->canAnyPermission(['delete timetable']);

        $this->canReadCustomItems = $this->canAnyPermission(['read custom timetable items', 'read custom timetable item']);
        $this->canCreateCustomItems = $this->canAnyPermission(['create custom timetable items', 'create custom timetable item']);
        $this->canUpdateCustomItems = $this->canAnyPermission(['update custom timetable items', 'update custom timetable item']);
        $this->canDeleteCustomItems = $this->canAnyPermission(['delete custom timetable items', 'delete custom timetable item']);
    }

    protected function canAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (auth()->user()->can($permission)) {
                return true;
            }
        }

        return false;
    }

    protected function modeIsAllowed(string $mode): bool
    {
        return match ($mode) {
            'list' => $this->canReadTimetable,
            'create' => $this->canCreateTimetable,
            'edit' => $this->canUpdateTimetable,
            'build' => $this->canReadTimetable || $this->canUpdateTimetable,
            'custom-items' => $this->canReadCustomItems || $this->canCreateCustomItems || $this->canUpdateCustomItems || $this->canDeleteCustomItems,
            default => false,
        };
    }

    protected function ensureCan(array $permissions): void
    {
        foreach ($permissions as $permission) {
            if (auth()->user()->can($permission)) {
                return;
            }
        }

        abort(403);
    }

    public function render()
    {
        return view('livewire.timetable.manage-timetables')
            ->layout('layouts.dashboard', [
                'breadcrumbs' => [
                    ['href' => route('dashboard'), 'text' => 'Dashboard'],
                    ['href' => route('timetables.index'), 'text' => 'Timetables', 'active' => true],
                ]
            ])
            ->title('Manage Timetables');
    }
}
