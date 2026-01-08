<?php

namespace App\Livewire\Teachers;

use App\Models\User;
use App\Models\Subject;
use Livewire\Component;

class TeacherDetail extends Component
{
    public User $teacher;
    public $activeTab = 'profile';
    public $availableSubjects = [];
    public $subjectSearch = '';
    public $assignedSubjects = [];
    public $teacherSubjects = [];

    public function mount($teacherId)
    {
        $this->teacher = User::with(['subjects', 'subjects.myClass'])
            ->findOrFail($teacherId);
        
        // Load initially assigned subjects
        $this->teacherSubjects = $this->teacher->subjects()->pluck('subjects.id')->toArray();
    }

    public function changeTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function getAvailableSubjects()
    {
        return Subject::where('school_id', auth()->user()->school_id)
            ->when($this->subjectSearch, function($q) {
                $q->where(function($query) {
                    $query->where('name', 'like', '%' . $this->subjectSearch . '%')
                          ->orWhere('short_name', 'like', '%' . $this->subjectSearch . '%');
                });
            })
            ->whereNotIn('id', $this->teacherSubjects)
            ->with('myClass')
            ->limit(10)
            ->get();
    }

    public function assignSubject($subjectId)
    {
        $subject = Subject::findOrFail($subjectId);
        
        if (!in_array($subjectId, $this->teacherSubjects)) {
            $this->teacher->subjects()->attach($subjectId);
            $this->teacherSubjects[] = $subjectId;
            $this->teacher->load('subjects');
            
            session()->flash('success', 'Subject assigned successfully');
        }
    }

    public function removeSubject($subjectId)
    {
        $this->teacher->subjects()->detach($subjectId);
        $this->teacherSubjects = array_filter($this->teacherSubjects, fn($id) => $id != $subjectId);
        $this->teacher->load('subjects');
        
        session()->flash('success', 'Subject removed successfully');
    }

    public function render()
    {
        $teacher = $this->teacher->load(['subjects.myClass']);
        
        $availableSubjects = $this->getAvailableSubjects();

        return view('livewire.teachers.teacher-detail', [
            'teacher' => $teacher,
            'availableSubjects' => $availableSubjects,
        ])
        ->layout('layouts.new', [
            'breadcrumbs' => [
                ['href' => route('dashboard'), 'text' => 'Dashboard'],
                ['href' => route('teachers.index'), 'text' => 'Teachers'],
                ['href' => route('teachers.show', $this->teacher->id), 'text' => $this->teacher->name, 'active' => true],
            ]
        ])
        ->title($this->teacher->name . "'s Profile");
    }
}