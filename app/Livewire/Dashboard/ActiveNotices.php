<?php

namespace App\Livewire\Dashboard;

use App\Models\Notice;
use Illuminate\Support\Collection;
use Livewire\Component;

class ActiveNotices extends Component
{
    protected function activeNotices(): Collection
    {
        $schoolId = auth()->user()?->school_id;

        if (!$schoolId) {
            return collect();
        }

        return Notice::query()
            ->where('school_id', $schoolId)
            ->active()
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->limit(4)
            ->get(['id', 'title', 'content', 'attachment', 'start_date', 'stop_date']);
    }

    protected function canManageNotices(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        return $user->canAny(['read notice', 'create notice', 'update notice', 'delete notice']);
    }

    public function render()
    {
        $notices = $this->activeNotices();

        return view('livewire.dashboard.active-notices', [
            'notices' => $notices,
            'canManageNotices' => $this->canManageNotices(),
        ]);
    }
}
