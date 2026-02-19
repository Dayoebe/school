<?php

namespace App\Livewire\Sections;

use App\Models\Section;
use App\Models\Subject;
use Livewire\Component;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class SectionDetail extends Component
{
    use AuthorizesRequests;

    public Section $section;
    public $students;
    public $availableSubjects;
    public $selectedSubjects = [];
    public $showSubjectModal = false;
    public $subjectSearch = '';

    protected $rules = [
        'selectedSubjects' => 'required|array|min:1',
        'selectedSubjects.*' => 'exists:subjects,id',
    ];

    protected $messages = [
        'selectedSubjects.required' => 'Please select at least one subject',
        'selectedSubjects.min' => 'Please select at least one subject',
    ];

    public function mount($sectionId)
    {
        $this->section = Section::whereHas('myClass.classGroup', function ($query) {
            $query->where('school_id', auth()->user()->school_id);
        })->with([
            'myClass',
            'subjects.teachers',
            'studentRecords.user'
        ])->findOrFail($sectionId);
        
        $this->authorize('view', $this->section);
        
        $this->loadStudents();
        $this->loadAvailableSubjects();
    }

    public function loadStudents()
    {
        $this->students = $this->section->studentRecords()
            ->with('user')
            ->get()
            ->pluck('user');
    }

    public function loadAvailableSubjects()
    {
        $this->availableSubjects = Subject::query()
            ->where('my_class_id', $this->section->my_class_id)
            ->whereDoesntHave('sections', function ($query) {
                $query->where('sections.id', $this->section->id);
            })
            ->with('teachers')
            ->get();
    }

    public function openSubjectModal()
    {
        $this->authorize('update', $this->section);
        $this->selectedSubjects = [];
        $this->subjectSearch = '';
        $this->showSubjectModal = true;
    }

    public function closeSubjectModal()
    {
        $this->showSubjectModal = false;
        $this->selectedSubjects = [];
        $this->subjectSearch = '';
        $this->resetValidation();
    }

    public function attachSubjects()
    {
        $this->authorize('update', $this->section);
        $this->validate();

        $validSubjectIds = Subject::query()
            ->whereIn('id', $this->selectedSubjects)
            ->pluck('id')
            ->toArray();

        if (count($validSubjectIds) !== count($this->selectedSubjects)) {
            session()->flash('error', 'One or more selected subjects are not in your current school.');
            return;
        }

        $this->section->subjects()->syncWithoutDetaching($validSubjectIds);

        // Update students' subject assignments
        foreach ($this->section->studentRecords as $record) {
            $record->assignSubjectsAutomatically();
        }

        session()->flash('success', 'Subjects added successfully');
        $this->closeSubjectModal();
        $this->section->refresh();
        $this->loadAvailableSubjects();
    }

    public function detachSubject($subjectId)
    {
        $this->authorize('update', $this->section);

        $subjectExists = Subject::query()
            ->where('id', $subjectId)
            ->exists();
        if (!$subjectExists) {
            session()->flash('error', 'Subject not found in your current school.');
            return;
        }
        
        $this->section->subjects()->detach($subjectId);

        // Update students' subject assignments
        foreach ($this->section->studentRecords as $record) {
            $record->assignSubjectsAutomatically();
        }

        session()->flash('success', 'Subject removed successfully');
        $this->section->refresh();
        $this->loadAvailableSubjects();
    }

    public function getFilteredSubjectsProperty()
    {
        if (empty($this->subjectSearch)) {
            return $this->availableSubjects;
        }

        return $this->availableSubjects->filter(function ($subject) {
            return stripos($subject->name, $this->subjectSearch) !== false;
        });
    }

    public function render()
    {
        return view('livewire.sections.section-detail')
            ->layout('layouts.dashboard', [
                'breadcrumbs' => [
                    ['href' => route('dashboard'), 'text' => 'Dashboard'],
                    ['href' => route('sections.index'), 'text' => 'Sections'],
                    ['href' => route('sections.show', $this->section->id), 'text' => "View {$this->section->name}", 'active' => true],
                ]
            ])
            ->title("View {$this->section->name}")
            ->with('page_heading', "{$this->section->name} Details");
    }
}
