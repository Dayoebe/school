<?php

namespace App\Livewire\Admins;

use App\Models\User;
use Livewire\Component;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AdminDetail extends Component
{
    use AuthorizesRequests;

    public User $admin;

    public function mount($adminId)
    {
        $this->admin = User::role('admin')
            ->where('school_id', auth()->user()->school_id)
            ->findOrFail($adminId);
        $this->authorize('view', [$this->admin, 'admin']);
    }

    public function render()
    {
        return view('livewire.admins.admin-detail')
            ->layout('layouts.dashboard', [
                'breadcrumbs' => [
                    ['href' => route('dashboard'), 'text' => 'Dashboard'],
                    ['href' => route('admins.index'), 'text' => 'Admins'],
                    ['href' => route('admins.show', $this->admin->id), 'text' => $this->admin->name, 'active' => true],
                ]
            ])
            ->title($this->admin->name . "'s Profile");
    }
}
