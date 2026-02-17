<?php

namespace App\Livewire\Timetable;

use App\Models\Timetable;
use App\Models\Weekday;
use Livewire\Component;

class ViewTimetable extends Component
{
    public $timetableId;
    public $timetable;
    public $timeSlots;
    public $weekdays;
    public $subjects;
    public $customItems;
    public $showDescription = true;

    public function mount($timetableId = null)
    {
        // If no timetable ID provided, try to get student's class timetable
        if (!$timetableId && auth()->user()->hasRole('student')) {
            $classId = auth()->user()->studentRecord->myClass->id;
            $semesterId = auth()->user()->school->semester_id;
            
            $this->timetable = Timetable::where('my_class_id', $classId)
                ->where('semester_id', $semesterId)
                ->with(['timeSlots.weekdays', 'myClass.subjects'])
                ->first();
        } else {
            $this->timetable = Timetable::with(['timeSlots.weekdays', 'myClass.subjects'])
                ->findOrFail($timetableId);
        }

        if ($this->timetable) {
            $this->loadTimetableData();
        }
    }

    private function loadTimetableData()
    {
        $this->timeSlots = $this->timetable->timeSlots->sortBy('start_time')->load('weekdays');
        $this->weekdays = Weekday::all();
        $this->subjects = $this->timetable->myClass->subjects;
        
        // Load custom items from school
        $this->customItems = \App\Models\CustomTimetableItem::where(
            'school_id', 
            auth()->user()->school_id
        )->get();
    }

    public function print()
    {
        return redirect()->route('timetables.print', $this->timetable->id);
    }

    public function render()
    {
        return view('livewire.timetable.view-timetable');
    }
}