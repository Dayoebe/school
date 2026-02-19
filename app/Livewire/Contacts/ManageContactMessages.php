<?php

namespace App\Livewire\Contacts;

use App\Mail\ContactMessageReply;
use App\Models\ContactMessage;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Throwable;
use Livewire\Component;
use Livewire\WithPagination;

class ManageContactMessages extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = 'all';
    public int $perPage = 15;

    public ?int $selectedMessageId = null;
    public bool $contactTableReady = true;
    public bool $replyColumnsReady = false;
    public string $responseStatus = 'resolved';
    public string $responseNote = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'all'],
    ];

    public function mount(): void
    {
        if (!auth()->check() || !auth()->user()->hasAnyRole(['super-admin', 'super_admin', 'admin', 'principal', 'teacher'])) {
            abort(403);
        }

        $this->contactTableReady = $this->contactTableExists();
        $this->replyColumnsReady = $this->replyColumnsExist();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function viewMessage(int $messageId): void
    {
        if (!$this->contactTableReady) {
            return;
        }

        $message = $this->baseQuery()->findOrFail($messageId);

        if (!$message->read_at) {
            $message->update([
                'read_at' => now(),
                'handled_by' => auth()->id(),
                'status' => $message->status === 'new' ? 'read' : $message->status,
            ]);

            $message = $message->fresh();
        }

        $this->selectedMessageId = $message->id;
        $this->responseStatus = in_array($message->status, ['read', 'in_progress', 'resolved'], true)
            ? $message->status
            : 'resolved';
        $this->responseNote = (string) ($message->response_note ?? '');
    }

    public function clearSelected(): void
    {
        $this->selectedMessageId = null;
        $this->responseStatus = 'resolved';
        $this->responseNote = '';
        $this->resetValidation();
    }

    public function markStatus(int $messageId, string $status): void
    {
        if (!$this->contactTableReady) {
            return;
        }

        if (!in_array($status, ['read', 'in_progress', 'resolved'], true)) {
            return;
        }

        $message = $this->baseQuery()->findOrFail($messageId);

        $payload = [
            'status' => $status,
            'handled_by' => auth()->id(),
            'read_at' => $message->read_at ?: now(),
        ];

        if ($status === 'resolved') {
            $payload['resolved_at'] = now();
        } else {
            $payload['resolved_at'] = null;
        }

        $message->update($payload);

        if ($this->selectedMessageId === $message->id) {
            $this->responseStatus = $message->status;
        }

        session()->flash('success', 'Message status updated.');
    }

    public function sendReply(int $messageId): void
    {
        if (!$this->contactTableReady || !$this->replyColumnsReady) {
            session()->flash('error', 'Reply setup is incomplete. Please run latest migrations.');
            return;
        }

        $validated = $this->validate([
            'responseStatus' => ['required', 'in:read,in_progress,resolved'],
            'responseNote' => ['required', 'string', 'min:3', 'max:5000'],
        ]);

        $message = $this->baseQuery()->findOrFail($messageId);

        DB::transaction(function () use ($message, $validated) {
            $payload = [
                'status' => $validated['responseStatus'],
                'response_note' => trim($validated['responseNote']),
                'response_sent_at' => now(),
                'response_sent_by' => auth()->id(),
                'handled_by' => auth()->id(),
                'read_at' => $message->read_at ?: now(),
            ];

            $payload['resolved_at'] = $validated['responseStatus'] === 'resolved' ? now() : null;

            $message->update($payload);
        });

        $message = $message->fresh(['school:id,name']);

        $mailSent = true;
        try {
            Mail::to($message->email)->send(new ContactMessageReply($message));
        } catch (Throwable $e) {
            report($e);
            $mailSent = false;
            session()->flash('error', 'Reply was saved, but email could not be sent.');
        }

        $this->selectedMessageId = $message->id;
        $this->responseStatus = $message->status;
        $this->responseNote = (string) $message->response_note;

        if ($mailSent) {
            session()->flash('success', 'Reply sent successfully.');
        }
    }

    protected function baseQuery()
    {
        $query = ContactMessage::query()->with(['school:id,name']);

        $schoolId = auth()->user()?->school_id;

        if ($schoolId) {
            $query->where('school_id', $schoolId);
        } else {
            $query->whereRaw('1 = 0');
        }

        return $query;
    }

    protected function detailedBaseQuery()
    {
        return $this->baseQuery()->with([
            'handledBy:id,name',
            'responseBy:id,name',
        ]);
    }

    public function render()
    {
        if (!$this->contactTableReady) {
            return view('livewire.contacts.manage-contact-messages', [
                'messages' => $this->emptyPaginator(),
                'statusCounts' => [
                    'all' => 0,
                    'new' => 0,
                    'read' => 0,
                    'in_progress' => 0,
                    'resolved' => 0,
                ],
                'selectedMessage' => null,
            ])->layout('layouts.dashboard', [
                'breadcrumbs' => [
                    ['href' => route('dashboard'), 'text' => 'Dashboard'],
                    ['href' => route('contacts.messages.index'), 'text' => 'Contact Messages', 'active' => true],
                ],
            ])->title('Contact Messages');
        }

        $query = $this->baseQuery()
            ->when($this->statusFilter !== 'all', function ($q) {
                $q->where('status', $this->statusFilter);
            })
            ->when($this->search !== '', function ($q) {
                $search = '%' . trim($this->search) . '%';
                $q->where(function ($inner) use ($search) {
                    $inner->where('full_name', 'like', $search)
                        ->orWhere('email', 'like', $search)
                        ->orWhere('phone', 'like', $search)
                        ->orWhere('subject', 'like', $search)
                        ->orWhere('message', 'like', $search);
                });
            })
            ->latest();

        $messages = $query->paginate($this->perPage);

        $countBase = $this->baseQuery();
        $statusCounts = [
            'all' => (clone $countBase)->count(),
            'new' => (clone $countBase)->where('status', 'new')->count(),
            'read' => (clone $countBase)->where('status', 'read')->count(),
            'in_progress' => (clone $countBase)->where('status', 'in_progress')->count(),
            'resolved' => (clone $countBase)->where('status', 'resolved')->count(),
        ];

        $selectedMessage = null;
        if ($this->selectedMessageId) {
            $selectedMessage = $this->detailedBaseQuery()->find($this->selectedMessageId);

            if (!$selectedMessage) {
                $this->selectedMessageId = null;
                $this->responseStatus = 'resolved';
                $this->responseNote = '';
            }
        }

        return view('livewire.contacts.manage-contact-messages', [
            'messages' => $messages,
            'statusCounts' => $statusCounts,
            'selectedMessage' => $selectedMessage,
        ])->layout('layouts.dashboard', [
            'breadcrumbs' => [
                ['href' => route('dashboard'), 'text' => 'Dashboard'],
                ['href' => route('contacts.messages.index'), 'text' => 'Contact Messages', 'active' => true],
            ],
        ])->title('Contact Messages');
    }

    protected function emptyPaginator(): LengthAwarePaginator
    {
        return new LengthAwarePaginator([], 0, $this->perPage, 1, [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);
    }

    protected function contactTableExists(): bool
    {
        try {
            return Schema::hasTable('contact_messages');
        } catch (Throwable $e) {
            report($e);
            return false;
        }
    }

    protected function replyColumnsExist(): bool
    {
        if (!$this->contactTableReady) {
            return false;
        }

        try {
            return Schema::hasColumns('contact_messages', [
                'response_note',
                'response_sent_at',
                'response_sent_by',
            ]);
        } catch (Throwable $e) {
            report($e);
            return false;
        }
    }

}
