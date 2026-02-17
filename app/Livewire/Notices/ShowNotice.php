<?php

namespace App\Livewire\Notices;

use App\Models\Notice;
use Livewire\Component;

class ShowNotice extends Component
{
    public Notice $notice;

    public function render()
    {
        return view('livewire.notices.show-notice');
    }
}
