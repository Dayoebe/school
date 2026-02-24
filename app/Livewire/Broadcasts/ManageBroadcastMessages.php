<?php

namespace App\Livewire\Broadcasts;

use App\Mail\BroadcastMessageMail;
use App\Models\BroadcastMessage;
use App\Models\MyClass;
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Livewire\WithPagination;

class ManageBroadcastMessages extends Component
{
    use WithPagination;

    public string $search = '';

    public int $perPage = 10;

    public string $title = '';

    public string $body = '';

    public string $targetType = 'school';

    public string $targetClassId = '';

    public string $targetSectionId = '';

    public string $targetRole = 'student';

    public bool $sendPortal = true;

    public bool $sendEmail = false;

    public bool $sendSms = false;

    public string $smsInfoMessage = '';

    /** @var array<int, array<string, mixed>> */
    public array $classes = [];

    /** @var array<int, array<string, mixed>> */
    public array $sections = [];

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('read broadcast message'), 403);

        $this->loadClasses();
        $this->loadSections();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedTargetType(): void
    {
        if ($this->targetType !== 'class') {
            $this->targetClassId = '';
            $this->targetSectionId = '';
            $this->sections = [];
        }

        if ($this->targetType !== 'role') {
            $this->targetRole = 'student';
        }
    }

    public function updatedTargetClassId(): void
    {
        $this->targetSectionId = '';
        $this->loadSections();
    }

    public function sendBroadcast(): void
    {
        abort_unless(auth()->user()?->can('create broadcast message'), 403);

        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'min:3', 'max:10000'],
            'targetType' => ['required', 'in:school,class,role'],
            'targetClassId' => ['nullable', 'integer', 'required_if:targetType,class'],
            'targetSectionId' => ['nullable', 'integer'],
            'targetRole' => ['nullable', 'string', 'required_if:targetType,role'],
            'sendPortal' => ['boolean'],
            'sendEmail' => ['boolean'],
            'sendSms' => ['boolean'],
        ]);

        if (!$this->sendPortal && !$this->sendEmail && !$this->sendSms) {
            $this->addError('sendPortal', 'Select at least one delivery channel.');
            return;
        }

        $recipients = $this->resolveRecipients();
        if ($recipients->isEmpty()) {
            $this->addError('targetType', 'No recipients found for the selected targeting rules.');
            return;
        }

        $targetMeta = $this->buildTargetMeta();
        $schoolId = (int) auth()->user()?->school_id;
        $now = now();

        $smsEnabled = (bool) config('services.sms.enabled', false);
        $smsStatus = $this->sendSms ? ($smsEnabled ? 'queued' : 'not_configured') : null;

        $broadcastMessage = BroadcastMessage::query()->create([
            'school_id' => $schoolId,
            'title' => trim($validated['title']),
            'body' => trim($validated['body']),
            'target_type' => $this->targetType,
            'target_meta' => $targetMeta,
            'send_portal' => $this->sendPortal,
            'send_email' => $this->sendEmail,
            'send_sms' => $this->sendSms,
            'sent_at' => $now,
            'sms_status' => $smsStatus,
            'created_by' => auth()->id(),
        ]);

        $channels = array_values(array_filter([
            $this->sendPortal ? 'portal' : null,
            $this->sendEmail ? 'email' : null,
            $this->sendSms ? 'sms' : null,
        ]));

        $recipientRows = [];

        foreach ($recipients as $recipient) {
            $recipientRows[] = [
                'broadcast_message_id' => $broadcastMessage->id,
                'school_id' => $schoolId,
                'user_id' => $recipient->id,
                'email' => $recipient->email,
                'phone' => $recipient->phone,
                'channels' => json_encode($channels),
                'portal_delivered_at' => $this->sendPortal ? $now : null,
                'email_delivered_at' => null,
                'sms_delivered_at' => null,
                'sms_status' => $smsStatus,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('broadcast_message_recipients')->insert($recipientRows);

        $emailDeliveredUserIds = [];

        if ($this->sendEmail) {
            foreach ($recipients as $recipient) {
                if (!$recipient->email) {
                    continue;
                }

                try {
                    Mail::to($recipient->email)->send(new BroadcastMessageMail($broadcastMessage, $recipient));
                    $emailDeliveredUserIds[] = $recipient->id;
                } catch (\Throwable $exception) {
                    report($exception);
                }
            }
        }

        if ($emailDeliveredUserIds !== []) {
            DB::table('broadcast_message_recipients')
                ->where('broadcast_message_id', $broadcastMessage->id)
                ->whereIn('user_id', $emailDeliveredUserIds)
                ->update([
                    'email_delivered_at' => now(),
                    'updated_at' => now(),
                ]);

            $broadcastMessage->update([
                'email_sent_at' => now(),
            ]);
        }

        if ($this->sendSms && !$smsEnabled) {
            $this->smsInfoMessage = 'SMS delivery is not configured. Message was saved for portal/email only.';
        } else {
            $this->smsInfoMessage = '';
        }

        $this->reset([
            'title',
            'body',
            'targetClassId',
            'targetSectionId',
            'targetRole',
            'sendEmail',
            'sendSms',
        ]);
        $this->targetType = 'school';
        $this->sendPortal = true;
        $this->targetRole = 'student';
        $this->loadSections();

        session()->flash('success', 'Broadcast sent to ' . count($recipientRows) . ' recipients.');
    }

    protected function resolveRecipients(): Collection
    {
        $schoolId = (int) auth()->user()?->school_id;

        return match ($this->targetType) {
            'class' => $this->resolveClassRecipients($schoolId),
            'role' => $this->resolveRoleRecipients($schoolId),
            default => User::query()
                ->where('school_id', $schoolId)
                ->whereNull('deleted_at')
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'phone']),
        };
    }

    protected function resolveClassRecipients(int $schoolId): Collection
    {
        $classId = (int) $this->targetClassId;

        if ($classId <= 0) {
            return new Collection();
        }

        $studentUserIds = User::query()
            ->role('student')
            ->where('school_id', $schoolId)
            ->whereHas('studentRecord', function ($query) use ($classId): void {
                $query->where('my_class_id', $classId)
                    ->when($this->targetSectionId !== '', function ($innerQuery): void {
                        $innerQuery->where('section_id', (int) $this->targetSectionId);
                    });
            })
            ->whereNull('deleted_at')
            ->pluck('id')
            ->all();

        $parentUserIds = [];
        if ($studentUserIds !== []) {
            $parentUserIds = DB::table('parent_records')
                ->whereIn('student_id', $studentUserIds)
                ->pluck('user_id')
                ->all();
        }

        $classTeacherIds = DB::table('class_teacher')
            ->where('class_id', $classId)
            ->pluck('teacher_id')
            ->all();

        $recipientIds = array_values(array_unique(array_filter(array_merge(
            $studentUserIds,
            $parentUserIds,
            $classTeacherIds
        ))));

        if ($recipientIds === []) {
            return new Collection();
        }

        return User::query()
            ->where('school_id', $schoolId)
            ->whereIn('id', $recipientIds)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone']);
    }

    protected function resolveRoleRecipients(int $schoolId): Collection
    {
        $targetRole = trim($this->targetRole);

        if ($targetRole === '') {
            return new Collection();
        }

        $roles = [$targetRole];
        if ($targetRole === 'super-admin') {
            $roles[] = 'super_admin';
        }

        if ($targetRole === 'super_admin') {
            $roles[] = 'super-admin';
        }

        return User::query()
            ->where('school_id', $schoolId)
            ->whereNull('deleted_at')
            ->whereHas('roles', function ($query) use ($roles): void {
                $query->whereIn('name', $roles);
            })
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone']);
    }

    protected function buildTargetMeta(): array
    {
        return match ($this->targetType) {
            'class' => [
                'class_id' => (int) $this->targetClassId,
                'section_id' => $this->targetSectionId !== '' ? (int) $this->targetSectionId : null,
            ],
            'role' => [
                'role' => $this->targetRole,
            ],
            default => [
                'scope' => 'school',
            ],
        };
    }

    protected function loadClasses(): void
    {
        $this->classes = MyClass::query()
            ->whereHas('classGroup', function ($query): void {
                $query->where('school_id', auth()->user()?->school_id);
            })
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (MyClass $class): array => [
                'id' => $class->id,
                'name' => $class->name,
            ])
            ->all();
    }

    protected function loadSections(): void
    {
        if ($this->targetClassId === '') {
            $this->sections = [];
            return;
        }

        $this->sections = Section::query()
            ->where('my_class_id', (int) $this->targetClassId)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Section $section): array => [
                'id' => $section->id,
                'name' => $section->name,
            ])
            ->all();

        if ($this->targetSectionId !== '' && !collect($this->sections)->contains('id', (int) $this->targetSectionId)) {
            $this->targetSectionId = '';
        }
    }

    public function render()
    {
        $messages = BroadcastMessage::query()
            ->with(['createdBy:id,name'])
            ->withCount([
                'recipients',
                'recipients as portal_recipient_count' => function ($query): void {
                    $query->whereNotNull('portal_delivered_at');
                },
                'recipients as email_recipient_count' => function ($query): void {
                    $query->whereNotNull('email_delivered_at');
                },
                'recipients as sms_recipient_count' => function ($query): void {
                    $query->whereNotNull('sms_delivered_at');
                },
            ])
            ->when($this->search !== '', function ($query): void {
                $search = '%' . trim($this->search) . '%';
                $query->where(function ($inner) use ($search): void {
                    $inner->where('title', 'like', $search)
                        ->orWhere('body', 'like', $search);
                });
            })
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.broadcasts.manage-broadcast-messages', [
            'messages' => $messages,
            'canCreateBroadcast' => auth()->user()?->can('create broadcast message'),
        ])
            ->layout('layouts.dashboard', [
                'breadcrumbs' => [
                    ['href' => route('dashboard'), 'text' => 'Dashboard'],
                    ['href' => route('broadcasts.manage'), 'text' => 'Broadcast Messaging', 'active' => true],
                ],
            ])
            ->title('Broadcast Messaging');
    }
}
