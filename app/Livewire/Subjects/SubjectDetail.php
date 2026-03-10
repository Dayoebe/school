<?php

namespace App\Livewire\Subjects;

use App\Models\Subject;
use App\Models\User;
use Livewire\Component;

class SubjectDetail extends Component
{
    public Subject $subject;
    public $activeTab = 'details';
    public $teacherSearch = '';
    public $availableTeachers = [];
    public $subjectTeachers = [];
    public $canUpdateSubject = false;

    public function mount($subjectId)
    {
        $this->subject = Subject::query()
            ->with([
                'myClass.classGroup',
                'teachers',
                'studentRecords.user',
                'studentRecords.myClass',
                'studentRecords.section',
            ])
            ->findOrFail($subjectId);
        
        // Check authorization manually
        if (!auth()->user()->can('read subject')) {
            abort(403, 'Unauthorized action.');
        }

        $this->canUpdateSubject = auth()->user()->can('update subject');
        
        // Load initially assigned teachers
        $this->subjectTeachers = $this->subject->teachers()->pluck('users.id')->toArray();
    }

    public function changeTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function getAvailableTeachers()
    {
        if (!$this->canUpdateSubject || $this->teacherSearch === '') {
            return collect();
        }

        return User::role('teacher')
            ->where('school_id', auth()->user()->school_id)
            ->when($this->teacherSearch, function($q) {
                $q->where(function($query) {
                    $query->where('name', 'like', '%' . $this->teacherSearch . '%')
                          ->orWhere('email', 'like', '%' . $this->teacherSearch . '%');
                });
            })
            ->whereNotIn('id', $this->subjectTeachers)
            ->limit(10)
            ->get(['id', 'name', 'email']);
    }

    public function assignTeacher($teacherId)
    {
        if (!$this->canUpdateSubject) {
            abort(403, 'Unauthorized action.');
        }
        
        $teacher = User::role('teacher')
            ->where('school_id', auth()->user()->school_id)
            ->findOrFail($teacherId);
        
        if (!in_array($teacherId, $this->subjectTeachers)) {
            $this->subject->assignTeacher($teacherId, null, true);
            $this->subjectTeachers[] = $teacherId;
            $this->subject->load('teachers');
            
            session()->flash('success', 'Teacher assigned successfully');
        }
    }

    public function removeTeacher($teacherId)
    {
        if (!$this->canUpdateSubject) {
            abort(403, 'Unauthorized action.');
        }
        
        $teacherBelongsToCurrentSchool = User::role('teacher')
            ->where('school_id', auth()->user()->school_id)
            ->where('id', $teacherId)
            ->exists();

        if (!$teacherBelongsToCurrentSchool) {
            session()->flash('error', 'Teacher not found in your current school.');
            return;
        }

        $this->subject->teachers()->detach($teacherId);
        $this->subjectTeachers = array_filter($this->subjectTeachers, fn($id) => $id != $teacherId);
        $this->subject->load('teachers');
        
        session()->flash('success', 'Teacher removed successfully');
    }

    public function render()
    {
        $subject = $this->subject->load([
            'myClass.classGroup',
            'teachers',
            'studentRecords.user',
            'studentRecords.myClass',
            'studentRecords.section',
        ]);
        
        $availableTeachers = $this->getAvailableTeachers();

        return view('livewire.subjects.subject-detail', [
            'subject' => $subject,
            'availableTeachers' => $availableTeachers,
        ])
        ->layout('layouts.dashboard', [
            'breadcrumbs' => [
                ['href' => route('dashboard'), 'text' => 'Dashboard'],
                ['href' => route('subjects.index'), 'text' => 'Subjects'],
                ['href' => route('subjects.show', $this->subject->id), 'text' => $this->subject->name, 'active' => true],
            ]
        ])
        ->title($this->subject->name . ' - Subject Details');
    }
}
