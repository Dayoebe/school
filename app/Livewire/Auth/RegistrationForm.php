<?php

namespace App\Livewire\Auth;

use App\Models\School;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class RegistrationForm extends Component
{
    public $roles;

    public $schools;

    public function mount()
    {
        $this->schools = School::query()->get();
        $this->roles = Role::whereIn('name', ['teacher', 'student', 'parent'])->get();
    }

    public function render()
    {
        return view('livewire.auth.registration-form');
    }
}
