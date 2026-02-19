<?php

namespace App\Livewire\Parents;

use App\Models\User;
use Livewire\Component;

class ParentDetail extends Component
{
    public User $parent;
    public $activeTab = 'profile';

    public function mount($parentId)
    {
        $this->parent = User::with(['children.studentRecord.myClass', 'children.studentRecord.section'])
            ->where('school_id', auth()->user()->school_id)
            ->findOrFail($parentId);

        // Check if user is actually a parent
        if (!$this->parent->hasRole('parent')) {
            abort(404);
        }

        // Check school access
        if ($this->parent->school_id !== auth()->user()->school_id) {
            abort(403);
        }
    }

    public function changeTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function printProfile()
    {
        $this->dispatch('print-profile');
    }

    public function render()
    {
        return view('livewire.parents.parent-detail')
            ->layout('layouts.dashboard', [
                'breadcrumbs' => [
                    ['href' => route('dashboard'), 'text' => 'Dashboard'],
                    ['href' => route('parents.index'), 'text' => 'Parents'],
                    ['href' => route('parents.show', $this->parent->id), 'text' => $this->parent->name, 'active' => true],
                ]
            ])
            ->title($this->parent->name . "'s Profile");
    }
}
