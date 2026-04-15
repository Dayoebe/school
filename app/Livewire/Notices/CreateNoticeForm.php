<?php

namespace App\Livewire\Notices;

use Livewire\Component;

class CreateNoticeForm extends Component
{
    public function render()
    {
        $user = auth()->user();

        return view('livewire.notices.create-notice-form', [
            'canSendNoticeEmail' => $user?->hasAnyRole(['super-admin', 'super_admin', 'principal']) === true,
            'noticeEmailRoleOptions' => [
                'student' => 'Students',
                'teacher' => 'Teachers',
                'parent' => 'Parents',
                'admin' => 'Admins',
                'principal' => 'Principals',
            ],
        ]);
    }
}
