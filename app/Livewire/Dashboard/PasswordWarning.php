<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;

class PasswordWarning extends Component
{
    public $show = true;

    public function dismiss()
    {
        $this->show = false;
    }

    public function render()
    {
        return view('livewire.dashboard.password-warning');
    }
}