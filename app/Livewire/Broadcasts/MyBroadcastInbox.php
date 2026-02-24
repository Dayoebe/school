<?php

namespace App\Livewire\Broadcasts;

use App\Models\BroadcastMessageRecipient;
use Livewire\Component;
use Livewire\WithPagination;

class MyBroadcastInbox extends Component
{
    use WithPagination;

    public string $search = '';

    public int $perPage = 12;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('view own broadcasts'), 403);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $messages = BroadcastMessageRecipient::query()
            ->where('user_id', auth()->id())
            ->whereNotNull('portal_delivered_at')
            ->with([
                'broadcastMessage.createdBy:id,name',
            ])
            ->whereHas('broadcastMessage', function ($query): void {
                if ($this->search === '') {
                    return;
                }

                $search = '%' . trim($this->search) . '%';
                $query->where(function ($inner) use ($search): void {
                    $inner->where('title', 'like', $search)
                        ->orWhere('body', 'like', $search);
                });
            })
            ->latest('portal_delivered_at')
            ->paginate($this->perPage);

        return view('livewire.broadcasts.my-broadcast-inbox', [
            'messages' => $messages,
        ])
            ->layout('layouts.dashboard', [
                'breadcrumbs' => [
                    ['href' => route('dashboard'), 'text' => 'Dashboard'],
                    ['href' => route('broadcasts.inbox'), 'text' => 'Portal Notices', 'active' => true],
                ],
            ])
            ->title('Portal Notices');
    }
}
