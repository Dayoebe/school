<?php

namespace App\Livewire;

use App\Services\MyClass\MyClassService;
use Illuminate\Support\Facades\App;
use Livewire\Component;

class CreateStudentRecordFields extends Component
{
    public $myClasses;

    public $myClass;

    public $sections = [];

    public $section;

    protected $myClassService;



    public $initialMyClassId;
    public $initialSectionId;
    public $admissionNumber;
    public $admissionDate;
    
    protected $rules = [
        'myClass' => 'string|nullable',
        'section' => 'string|nullable',
    ];

    public function mount(MyClassService $myClassService, $initialMyClassId = null, $initialSectionId = null)
    {
        $this->myClassService = $myClassService;
        $this->myClasses = $myClassService->getAllClasses();

        $this->initialMyClassId = $initialMyClassId;
        $this->initialSectionId = $initialSectionId;

        // Handle edit or create
        if ($this->initialMyClassId) {
            $this->myClass = $this->initialMyClassId;
            $this->sections = collect($myClassService->getClassById($this->myClass)->sections);
            $this->section = $this->initialSectionId;
        } elseif ($this->myClasses->isNotEmpty()) {
            $this->myClass = $this->myClasses[0]['id'];
            $this->sections = collect($myClassService->getClassById($this->myClass)->sections);
        }
    }

    public function updatedMyClass()
    {
        $this->reset('section');
        $this->sections = collect($this->myClassService->getClassById($this->myClass)->sections);
    }

    public function render()
    {
        return view('livewire.create-student-record-fields');
    }
}
