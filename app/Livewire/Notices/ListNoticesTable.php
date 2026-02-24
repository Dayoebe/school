<?php

namespace App\Livewire\Notices;

use App\Models\Notice;
use Livewire\Component;
use Livewire\WithPagination;

class ListNoticesTable extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = 'all';

    public int $perPage = 12;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'all'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    protected function canManageNotices(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        return $user->canAny(['update notice', 'delete notice', 'create notice']);
    }

    public function render()
    {
        $today = now()->toDateString();

        $query = Notice::query()
            ->when(trim($this->search) !== '', function ($builder): void {
                $term = '%' . trim($this->search) . '%';
                $builder->where(function ($inner) use ($term): void {
                    $inner->where('title', 'like', $term)
                        ->orWhere('content', 'like', $term);
                });
            });

        if (!$this->canManageNotices()) {
            $query->active();
        } else {
            match ($this->statusFilter) {
                'active' => $query
                    ->where('active', 1)
                    ->whereDate('start_date', '<=', $today)
                    ->whereDate('stop_date', '>=', $today),
                'upcoming' => $query->whereDate('start_date', '>', $today),
                'expired' => $query->whereDate('stop_date', '<', $today),
                'inactive' => $query->where('active', 0),
                default => null,
            };
        }

        $notices = $query
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->paginate($this->perPage);

        return view('livewire.notices.list-notices-table', [
            'notices' => $notices,
            'canManageNotices' => $this->canManageNotices(),
        ]);
    }
}
