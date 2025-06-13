<?php

namespace App\Livewire;

use App\Services\MyClass\MyClassService;
use Livewire\Component;

class CreateStudentRecordFields extends Component
{
    public $myClasses;
    public $myClass;
    public $sections = [];
    public $section;

    public $initialMyClassId;
    public $initialSectionId;
    public $admissionNumber;
    public $admissionDate;

    protected MyClassService $myClassService;

    protected $rules = [
        'myClass' => 'required|string', // Class is required
        'section' => 'nullable|string', // Section is optional
        'admissionDate' => 'nullable|date', // Admission date is optional
    ];

    // Use Livewire's boot() instead of __construct()
    public function boot(MyClassService $myClassService)
    {
        $this->myClassService = $myClassService;
    }

    public function mount($initialMyClassId = null, $initialSectionId = null)
    {
        $this->myClasses = $this->myClassService->getAllClasses();
        $this->initialMyClassId = $initialMyClassId;
        $this->initialSectionId = $initialSectionId;

        if ($this->initialMyClassId) {
            $this->myClass = $this->initialMyClassId;
            $this->sections = collect($this->myClassService->getClassById($this->myClass)->sections);
            $this->section = $this->initialSectionId;
        } elseif ($this->myClasses->isNotEmpty()) {
            $this->myClass = $this->myClasses[0]['id'];
            $this->sections = collect($this->myClassService->getClassById($this->myClass)->sections);
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