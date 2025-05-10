<?php

namespace App\Livewire;

use Livewire\Component;

class NationalityAndStateInputFields extends Component
{
    public $nationality;
    public $state;

    protected $rules = [
        'nationality' => 'string',
        'state'       => 'string',
    ];

    public function updatedNationality()
    {
        $this->dispatch('nationality-updated', ['nationality' => $this->nationality]);
    }

    public function updatedState()
    {
        $this->dispatch('state-updated', ['state' => $this->state]);
    }

    public function render()
    {
        return view('livewire.nationality-and-state-input-fields');
    }
}
