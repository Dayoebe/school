<?php

namespace App\Livewire\Schools;

use App\Models\School;
use Livewire\Component;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class SchoolDetail extends Component
{
    use AuthorizesRequests;

    public School $school;

    public function mount($schoolId)
    {
        $this->school = School::with(['academicYear', 'semester'])->findOrFail($schoolId);
        $this->authorize('view', $this->school);
    }

    public function render()
    {
        return view('livewire.schools.school-detail')
            ->layout('layouts.dashboard', [
                'breadcrumbs' => [
                    ['href' => route('dashboard'), 'text' => 'Dashboard'],
                    ['href' => route('schools.index'), 'text' => 'Schools'],
                    ['href' => route('schools.show', $this->school->id), 'text' => $this->school->name, 'active' => true],
                ]
            ])
            ->title($this->school->name);
    }
}