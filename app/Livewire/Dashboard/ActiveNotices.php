<?php

namespace App\Livewire\Dashboard;

use App\Models\Notice;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ActiveNotices extends Component
{
    public function markAsRead(int $noticeId): void
    {
        $user = auth()->user();

        if (!$user) {
            return;
        }

        $noticeExists = Notice::query()
            ->where('school_id', $user->school_id)
            ->active()
            ->whereKey($noticeId)
            ->exists();

        if (!$noticeExists) {
            return;
        }

        DB::table('notice_reads')->updateOrInsert(
            [
                'notice_id' => $noticeId,
                'user_id' => $user->id,
            ],
            [
                'read_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function markAsUnread(int $noticeId): void
    {
        $user = auth()->user();

        if (!$user) {
            return;
        }

        DB::table('notice_reads')
            ->where('notice_id', $noticeId)
            ->where('user_id', $user->id)
            ->delete();
    }

    public function markAllAsRead(): void
    {
        $user = auth()->user();

        if (!$user) {
            return;
        }

        $noticeIds = Notice::query()
            ->where('school_id', $user->school_id)
            ->active()
            ->pluck('id');

        foreach ($noticeIds as $noticeId) {
            DB::table('notice_reads')->updateOrInsert(
                [
                    'notice_id' => $noticeId,
                    'user_id' => $user->id,
                ],
                [
                    'read_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    protected function activeNotices(): Collection
    {
        $user = auth()->user();
        $schoolId = $user?->school_id;

        if (!$user || !$schoolId) {
            return collect();
        }

        $notices = Notice::query()
            ->with(['creator:id,name'])
            ->where('school_id', $schoolId)
            ->active()
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->limit(6)
            ->get(['id', 'title', 'content', 'attachment', 'start_date', 'stop_date', 'created_by', 'created_at']);

        $readNoticeIds = DB::table('notice_reads')
            ->where('user_id', $user->id)
            ->whereIn('notice_id', $notices->pluck('id'))
            ->whereNotNull('read_at')
            ->pluck('notice_id')
            ->all();

        return $notices->map(function (Notice $notice) use ($readNoticeIds): Notice {
            $notice->setAttribute('is_unread', !in_array($notice->id, $readNoticeIds, true));

            return $notice;
        });
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
        $unreadNoticeCount = $notices->filter(fn (Notice $notice): bool => (bool) $notice->getAttribute('is_unread'))->count();

        return view('livewire.dashboard.active-notices', [
            'notices' => $notices,
            'unreadNoticeCount' => $unreadNoticeCount,
            'canManageNotices' => $this->canManageNotices(),
        ]);
    }
}
