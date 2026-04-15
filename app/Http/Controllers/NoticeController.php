<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNoticeRequest;
use App\Http\Requests\UpdateNoticeRequest;
use App\Mail\NoticePublishedMail;
use App\Models\Notice;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class NoticeController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:read notice')->only(['index', 'show']);
        $this->middleware('permission:create notice')->only(['create', 'store']);
        $this->middleware('permission:update notice')->only(['edit', 'update']);
        $this->middleware('permission:delete notice')->only(['destroy']);
        $this->authorizeResource(Notice::class, 'notice');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('livewire.notices.pages.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('livewire.notices.pages.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreNoticeRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $user = $request->user();
        $attachmentPath = isset($data['attachment'])
            ? $data['attachment']->store('notices/', 'public')
            : null;
        $canSendNoticeEmail = $this->canSendNoticeEmail($user);
        $sendEmail = $canSendNoticeEmail && $request->boolean('send_email');
        $emailRecipientRoles = $sendEmail
            ? $this->normalizeEmailRecipientRoles($data['email_recipient_roles'] ?? [])
            : [];

        $notice = Notice::create([
            'title' => $data['title'],
            'content' => $data['content'],
            'start_date' => $data['start_date'],
            'stop_date' => $data['stop_date'],
            'attachment' => $attachmentPath,
            'school_id' => $user->school_id,
            'created_by' => $user->id,
            'send_email' => $sendEmail,
            'email_subject' => $sendEmail ? ($data['email_subject'] ?? null) : null,
            'email_body' => $sendEmail ? ($data['email_body'] ?? null) : null,
            'email_recipient_roles' => $sendEmail ? $emailRecipientRoles : null,
        ]);

        $deliveredEmailCount = $sendEmail
            ? $this->sendNoticeEmails($notice, $emailRecipientRoles)
            : 0;

        $message = 'Notice created successfully.';
        if ($sendEmail) {
            $message .= ' Email delivered to ' . $deliveredEmailCount . ' recipient' . ($deliveredEmailCount === 1 ? '' : 's') . '.';
        }

        return redirect()->route('notices.index')->with('success', $message);
    }

    private function canSendNoticeEmail(?User $user): bool
    {
        return $user?->hasAnyRole(['super-admin', 'super_admin', 'principal']) === true;
    }

    /**
     * @param array<int, string> $roles
     * @return array<int, string>
     */
    private function normalizeEmailRecipientRoles(array $roles): array
    {
        $roles = array_values(array_filter(array_unique($roles)));

        return $roles !== []
            ? $roles
            : ['student', 'teacher', 'parent', 'admin', 'principal'];
    }

    /**
     * @param array<int, string> $roles
     */
    private function sendNoticeEmails(Notice $notice, array $roles): int
    {
        $roleNames = $roles;

        if (in_array('super-admin', $roleNames, true) && !in_array('super_admin', $roleNames, true)) {
            $roleNames[] = 'super_admin';
        }

        if (in_array('super_admin', $roleNames, true) && !in_array('super-admin', $roleNames, true)) {
            $roleNames[] = 'super-admin';
        }

        $recipients = User::query()
            ->where('school_id', $notice->school_id)
            ->whereNull('deleted_at')
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->whereHas('roles', function ($query) use ($roleNames): void {
                $query->whereIn('name', $roleNames);
            })
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $deliveredCount = 0;

        foreach ($recipients as $recipient) {
            try {
                Mail::to($recipient->email)->send(new NoticePublishedMail($notice, $recipient));
                $deliveredCount++;
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        if ($deliveredCount > 0) {
            $notice->update([
                'email_sent_at' => now(),
                'email_recipient_count' => $deliveredCount,
            ]);
        }

        return $deliveredCount;
    }

    /**
     * Display the specified resource.
     */
    public function show(Notice $notice): View
    {
        return view('livewire.notices.pages.show', compact('notice'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Notice $notice): Response
    {
        abort(404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateNoticeRequest $request, Notice $notice): Response
    {
        abort(404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Notice $notice): RedirectResponse
    {
        $notice->delete();

        return back()->with('success', 'Notice deleted successfully');
    }
}
