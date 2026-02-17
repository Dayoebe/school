<?php

return [
    'roles' => [
        'super_admin' => [
            'component' => \App\Livewire\Dashboard\Admin\AdminDashboard::class,
            'route' => 'dashboard.admin',
        ],
        'admin' => [
            'component' => \App\Livewire\Dashboard\Admin\AdminDashboard::class,
            'route' => 'dashboard.admin',
        ],
        'teacher' => [
            'component' => \App\Livewire\Dashboard\Teacher\TeacherDashboard::class,
            'route' => 'dashboard.teacher',
        ],
        'student' => [
            'component' => \App\Livewire\Dashboard\Student\StudentDashboard::class,
            'route' => 'dashboard.student',
        ],
        'parent' => [
            'component' => \App\Livewire\Dashboard\Parent\ParentDashboard::class,
            'route' => 'dashboard.parent',
        ],
    ],

    'permissions' => [
        'super_admin' => ['*'],
        'admin' => [
            'manage school settings',
            'read teacher',
            'create teacher',
            'update teacher',
            'delete teacher',
            'read student',
            'create student',
            'update student',
            'delete student',
            'read parent',
            'view result',
            'upload result',
        ],
        'teacher' => [
            'upload result',
            'view result',
            'read student',
        ],
        'student' => [
            'view own result',
        ],
        'parent' => [
            'view children result',
        ],
    ],
];
