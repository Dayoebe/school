<?php 
namespace App\Livewire;

use Livewire\Component;
use App\Models\MyClass;
use App\Models\Subject;
use App\Models\StudentRecord;

class ShowClass extends Component
{
    public $class;
    public $students;
    public $showButton = false;

    public function mount(MyClass $class)
    {
        $this->class = $class;
        $this->students = $class->studentRecords()->with('user')->get();
        
        // Check if any student is missing subjects
        $this->showButton = $this->students->isNotEmpty() && 
                            $this->students->some(fn($s) => $s->studentSubjects->isEmpty());
    }

    public function assignSubjects()
    {
        $subjects = Subject::where('my_class_id', $this->class->id)->get();
        
        foreach ($this->students as $student) {
            $syncData = [];
            foreach ($subjects as $subject) {
                $syncData[$subject->id] = [
                    'my_class_id' => $this->class->id,
                    'section_id' => $student->section_id,
                ];
            }
            $student->studentSubjects()->syncWithoutDetaching($syncData);
        }

        session()->flash('success', 'Subjects assigned to students successfully!');
        $this->showButton = false;
    }

    public function render()
    {
        return view('livewire.show-class');
    }
}