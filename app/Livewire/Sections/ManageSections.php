<?php

namespace App\Livewire\Sections;

use App\Models\MyClass;
use App\Models\Section;
use Livewire\Component;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ManageSections extends Component
{
    use AuthorizesRequests;

    public $classes;
    public $selectedClass;
    public $sections = [];
    
    // Modal states
    public $showCreateModal = false;
    public $showEditModal = false;
    
    // Form fields
    public $name = '';
    public $my_class_id = '';
    public $editingSectionId = null;

    protected $queryString = ['selectedClass'];

    public function mount()
    {
        $this->authorize('viewAny', Section::class);
        
        // Multiple ways to get classes - try all approaches
        $schoolId = auth()->user()->school_id;
        
        // Approach 1: Direct via class groups
        $this->classes = MyClass::whereHas('classGroup', function($query) use ($schoolId) {
                $query->where('school_id', $schoolId);
            })
            ->with('sections')
            ->get();
        
        // Approach 2: If approach 1 fails, try direct relationship
        if ($this->classes->isEmpty()) {
            $classGroupIds = auth()->user()->school->classGroups->pluck('id');
            $this->classes = MyClass::whereIn('class_group_id', $classGroupIds)
                ->with('sections')
                ->get();
        }
        
        // Approach 3: If still empty, get all classes for school (fallback)
        if ($this->classes->isEmpty()) {
            // This assumes you have a way to get classes by school
            // Adjust based on your actual database structure
            $this->classes = MyClass::whereHas('classGroup', function($query) use ($schoolId) {
                $query->where('school_id', $schoolId);
            })->with('sections')->get();
        }
        
        // Debug: Uncomment to see what's happening
        // dd([
        //     'school_id' => $schoolId,
        //     'school' => auth()->user()->school,
        //     'classGroups' => auth()->user()->school->classGroups,
        //     'classes_count' => $this->classes->count(),
        //     'classes' => $this->classes,
        // ]);
        
        if ($this->classes->isNotEmpty()) {
            $this->selectedClass = $this->selectedClass ?? $this->classes->first()->id;
            $this->loadSections();
        }
    }

    public function updatedSelectedClass()
    {
        $this->loadSections();
    }

    public function loadSections()
    {
        if ($this->selectedClass) {
            $this->sections = Section::where('my_class_id', $this->selectedClass)
                ->withCount('studentRecords')
                ->with('myClass')
                ->get();
        }
    }

    public function openCreateModal()
    {
        $this->authorize('create', Section::class);
        $this->resetForm();
        $this->my_class_id = $this->selectedClass;
        $this->showCreateModal = true;
    }

    public function openEditModal($sectionId)
    {
        $section = Section::findOrFail($sectionId);
        $this->authorize('update', $section);
        
        $this->editingSectionId = $section->id;
        $this->name = $section->name;
        $this->my_class_id = $section->my_class_id;
        $this->showEditModal = true;
    }

    public function closeModals()
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->name = '';
        $this->my_class_id = '';
        $this->editingSectionId = null;
        $this->resetValidation();
    }

    public function createSection()
    {
        $this->authorize('create', Section::class);
        
        $this->validate([
            'name' => [
                'required',
                'max:255',
                Rule::unique('sections', 'name')->where('my_class_id', $this->my_class_id),
            ],
            'my_class_id' => 'required|exists:my_classes,id',
        ], [
            'my_class_id.required' => 'Please select a class',
        ]);

        Section::create([
            'name' => $this->name,
            'my_class_id' => $this->my_class_id,
        ]);

        session()->flash('success', 'Section created successfully');
        $this->closeModals();
        $this->loadSections();
    }

    public function updateSection()
    {
        $section = Section::findOrFail($this->editingSectionId);
        $this->authorize('update', $section);

        $this->validate([
            'name' => [
                'required',
                'max:255',
                Rule::unique('sections', 'name')
                    ->ignore($this->editingSectionId)
                    ->where('my_class_id', $section->my_class_id),
            ],
        ]);

        $section->update(['name' => $this->name]);

        session()->flash('success', 'Section updated successfully');
        $this->closeModals();
        $this->loadSections();
    }

    public function deleteSection($sectionId)
    {
        $section = Section::findOrFail($sectionId);
        $this->authorize('delete', $section);
        
        if ($section->studentRecords()->count() > 0) {
            session()->flash('error', 'Cannot delete section with students');
            return;
        }

        $section->delete();
        session()->flash('success', 'Section deleted successfully');
        $this->loadSections();
    }

    public function render()
    {
        return view('livewire.sections.manage-sections')
            ->layout('layouts.new', [
                'breadcrumbs' => [
                    ['href' => route('dashboard'), 'text' => 'Dashboard'],
                    ['href' => route('sections.index'), 'text' => 'Sections', 'active' => true]
                ]
            ])
            ->title('Class Sections')
            ->with('page_heading', 'Class Sections');
    }
}