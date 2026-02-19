<?php

namespace App\Livewire\Students;

use App\Models\User;
use Livewire\Component;

class StudentDetail extends Component
{
    public User $student;
    public $activeTab = 'profile';

    public function mount($studentId)
    {
        $this->student = User::with([
            'studentRecord.myClass',
            'studentRecord.section',
            'feeInvoices'
        ])
            ->role('student')
            ->where('school_id', auth()->user()->school_id)
            ->findOrFail($studentId);

        // Check if parent accessing their child
        if (auth()->user()->hasRole('parent')) {
            if ($this->student->parents()->where('parent_records.user_id', auth()->user()->id)->count() <= 0) {
                abort(404);
            }
        }
    }

    public function changeTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function printProfile()
    {
        // This will trigger a browser print
        $this->dispatch('print-profile');
    }

    public function render()
    {
        return view('livewire.students.student-detail')
            ->layout('layouts.dashboard', [
                'breadcrumbs' => [
                    ['href' => route('dashboard'), 'text' => 'Dashboard'],
                    ['href' => route('students.index'), 'text' => 'Students'],
                    ['href' => route('students.show', $this->student->id), 'text' => $this->student->name, 'active' => true],
                ]
            ])
            ->title($this->student->name . "'s Profile");
    }
}
