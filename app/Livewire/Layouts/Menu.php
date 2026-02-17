<?php

namespace App\Livewire\Layouts;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Livewire\Component;

class Menu extends Component
{
    public array $menu = [];

    protected array $roleAliases = [
        'super admin' => ['super-admin', 'super_admin', 'super admin', 'Super Admin'],
        'principal' => ['principal', 'Principal'],
        'teacher' => ['teacher', 'Teacher'],
        'student' => ['student', 'Student'],
        'parent' => ['parent', 'Parent'],
        'user' => ['user', 'users', 'User', 'Users'],
        'admin' => ['admin', 'Admin'],
    ];

    public function mount(): void
    {
        $adminRoles = ['super admin', 'principal', 'admin'];
        $staffRoles = ['super admin', 'principal', 'admin', 'teacher'];
        $allRoles = ['super admin', 'principal', 'admin', 'teacher', 'student', 'parent', 'user'];

        $this->menu = [
            ['header' => 'Overview'],
            [
                'type' => 'menu-item',
                'icon' => 'fas fa-tachometer-alt',
                'text' => 'Dashboard',
                'route' => 'dashboard',
                'roles' => $allRoles,
            ],
            [
                'type' => 'menu-item',
                'icon' => 'fas fa-bullhorn',
                'text' => 'Notices',
                'route' => 'notices.index',
                'roles' => $staffRoles,
                'permissions' => ['read notice', 'create notice', 'update notice'],
            ],
            [
                'type' => 'menu-item',
                'text' => 'CBT',
                'icon' => 'fas fa-laptop-code',
                'submenu' => [
                    [
                        'type' => 'menu-item',
                        'text' => 'Take CBT Exams',
                        'route' => 'cbt.exams',
                        'roles' => $allRoles,
                    ],
                    [
                        'type' => 'menu-item',
                        'text' => 'CBT Results',
                        'route' => 'cbt.viewer',
                        'roles' => $allRoles,
                    ],
                    [
                        'type' => 'menu-item',
                        'text' => 'Manage CBT',
                        'route' => 'cbt.manage',
                        'roles' => $staffRoles,
                        'permissions' => ['upload result', 'view result'],
                    ],
                ],
            ],

            ['header' => 'School Management'],
            [
                'type' => 'menu-item',
                'text' => 'Schools',
                'icon' => 'fas fa-school',
                'roles' => $adminRoles,
                'permissions' => ['read school', 'create school', 'manage school settings'],
                'submenu' => [
                    [
                        'type'  => 'menu-item',
                        'text'  => 'View Schools',
                        'route' => 'schools.index',
                    ],
                    [
                        'type'  => 'menu-item',
                        'text'  => 'Create School',
                        'route' => 'schools.index',
                        'params' => ['mode' => 'create'],
                        'permissions' => ['create school'],
                    ],
                    [
                        'type'  => 'menu-item',
                        'icon'  => 'fas fa-cog',
                        'text'  => 'School Settings',
                        'route' => 'schools.settings',
                        'permissions' => ['manage school settings'],
                    ],
                ],
            ],
            [
                'type'    => 'menu-item',
                'text'    => 'Admins',
                'icon'    => 'fas fa-user-shield',
                'roles' => $adminRoles,
                'permissions' => ['read admin', 'create admin'],
                'submenu' => [
                    [
                        'type'  => 'menu-item',
                        'text'  => 'View Admins',
                        'route' => 'admins.index',
                        'permissions' => ['read admin'],
                    ],
                    [
                        'type'  => 'menu-item',
                        'text'  => 'Create Admin',
                        'route' => 'admins.index',
                        'params' => ['mode' => 'create'],
                        'permissions' => ['create admin'],
                    ],
                ],
            ],
            [
                'type' => 'menu-item',
                'text' => 'Teachers',
                'icon' => 'fas fa-chalkboard-teacher',
                'roles' => $staffRoles,
                'permissions' => ['read teacher', 'create teacher'],
                'submenu' => [
                    [
                        'type' => 'menu-item',
                        'text' => 'View Teachers',
                        'route' => 'teachers.index',
                        'permissions' => ['read teacher'],
                    ],
                    [
                        'type' => 'menu-item',
                        'text' => 'Create Teacher',
                        'route' => 'teachers.create',
                        'permissions' => ['create teacher'],
                    ],
                ],
            ],
            [
                'type' => 'menu-item',
                'text' => 'Students',
                'icon' => 'fas fa-user-graduate',
                'roles' => $staffRoles,
                'permissions' => ['read student', 'create student', 'promote student', 'read promotion'],
                'submenu' => [
                    [
                        'type' => 'menu-item',
                        'text' => 'View Students',
                        'route' => 'students.index',
                        'permissions' => ['read student'],
                    ],
                    [
                        'type' => 'menu-item',
                        'text' => 'Create Student',
                        'route' => 'students.create',
                        'permissions' => ['create student'],
                    ],
                    [
                        'type' => 'menu-item',
                        'text' => 'Promote Students',
                        'route' => 'students.promote',
                        'permissions' => ['promote student', 'read promotion'],
                    ],
                    [
                        'type' => 'menu-item',
                        'text' => 'Graduate Students',
                        'route' => 'students.graduate',
                        'permissions' => ['read student'],
                    ],
                    [
                        'type' => 'menu-item',
                        'text' => 'Graduation History',
                        'route' => 'students.graduations',
                        'permissions' => ['read student'],
                    ],
                ],
            ],
            [
                'type' => 'menu-item',
                'text' => 'Parents',
                'icon' => 'fas fa-users',
                'roles' => $staffRoles,
                'permissions' => ['read parent', 'create parent'],
                'submenu' => [
                    [
                        'type' => 'menu-item',
                        'text' => 'View Parents',
                        'route' => 'parents.index',
                        'permissions' => ['read parent'],
                    ],
                    [
                        'type' => 'menu-item',
                        'text' => 'Add Parent',
                        'route' => 'parents.index',
                        'params' => ['mode' => 'create'],
                        'permissions' => ['create parent'],
                    ],
                ],
            ],
            [
                'type' => 'menu-item',
                'text' => 'Account Applications',
                'icon' => 'fas fa-user-plus',
                'roles' => $adminRoles,
                'permissions' => ['read applicant'],
                'submenu' => [
                    [
                        'type' => 'menu-item',
                        'text' => 'Pending Applications',
                        'route' => 'account-applications.index',
                    ],
                    [
                        'type' => 'menu-item',
                        'text' => 'Rejected Applications',
                        'route' => 'account-applications.rejected-applications',
                    ],
                ],
            ],

            ['header' => 'Academics'],
            [
                'type' => 'menu-item',
                'text' => 'Academic Calendar',
                'icon' => 'fas fa-calendar-alt',
                'roles' => $staffRoles,
                'permissions' => ['read academic year', 'read semester'],
                'submenu' => [
                    [
                        'type' => 'menu-item',
                        'text' => 'Academic Years',
                        'route' => 'academic-years.index',
                        'permissions' => ['read academic year'],
                    ],
                    [
                        'type' => 'menu-item',
                        'text' => 'Terms',
                        'route' => 'semesters.index',
                        'permissions' => ['read semester'],
                    ],
                ],
            ],
            [
                'type' => 'menu-item',
                'text' => 'Classes',
                'icon' => 'fas fa-chalkboard',
                'roles' => $staffRoles,
                'permissions' => ['read class', 'read class group'],
                'submenu' => [
                    [
                        'type' => 'menu-item',
                        'text' => 'All Classes',
                        'route' => 'classes.index',
                        'permissions' => ['read class'],
                    ],
                    [
                        'type' => 'menu-item',
                        'text' => 'Class Groups',
                        'route' => 'class-groups.index',
                        'permissions' => ['read class group'],
                    ],
                    [
                        'type' => 'menu-item',
                        'text' => 'Sections',
                        'route' => 'sections.index',
                        'permissions' => ['read section', 'create section'],
                    ],
                ],
            ],
            [
                'type' => 'menu-item',
                'text' => 'Subjects',
                'icon' => 'fas fa-book-open',
                'roles' => $staffRoles,
                'permissions' => ['read subject', 'create subject', 'update subject'],
                'submenu' => [
                    [
                        'type' => 'menu-item',
                        'text' => 'View Subjects',
                        'route' => 'subjects.index',
                        'permissions' => ['read subject'],
                    ],
                    [
                        'type' => 'menu-item',
                        'text' => 'Create Subject',
                        'route' => 'subjects.create',
                        'permissions' => ['create subject'],
                    ],
                    [
                        'type' => 'menu-item',
                        'text' => 'Assign Teacher',
                        'route' => 'subjects.assign-teacher',
                        'permissions' => ['update subject'],
                    ],
                ],
            ],
            [
                'type' => 'menu-item',
                'text' => 'Syllabi',
                'icon' => 'fas fa-list-check',
                'route' => 'syllabi.index',
                'roles' => $staffRoles,
                'permissions' => ['read syllabus', 'create syllabus'],
            ],
            [
                'type' => 'menu-item',
                'text' => 'Timetables',
                'icon' => 'fas fa-clock',
                'roles' => $staffRoles,
                'permissions' => ['read timetable', 'read custom timetable items'],
                'submenu' => [
                    [
                        'type' => 'menu-item',
                        'text' => 'View Timetables',
                        'route' => 'timetables.index',
                        'permissions' => ['read timetable'],
                    ],
                    [
                        'type' => 'menu-item',
                        'text' => 'Custom Items',
                        'route' => 'custom-timetable-items.index',
                        'permissions' => ['read custom timetable items'],
                    ],
                ],
            ],

            ['header' => 'Assessment & Results'],
            [
                'type' => 'menu-item',
                'text' => 'Exams',
                'icon' => 'fas fa-file-signature',
                'roles' => $staffRoles,
                'permissions' => ['read exam', 'read exam record'],
                'submenu' => [
                    [
                        'type' => 'menu-item',
                        'text' => 'Exam Setup',
                        'route' => 'exams.index',
                        'permissions' => ['read exam'],
                    ],
                    [
                        'type' => 'menu-item',
                        'text' => 'Exam Records',
                        'route' => 'exam-records.index',
                        'permissions' => ['read exam record', 'read exam'],
                    ],
                    [
                        'type' => 'menu-item',
                        'text' => 'Result Checker',
                        'route' => 'exams.result-checker',
                        'permissions' => ['view result', 'read exam'],
                    ],
                    [
                        'type' => 'menu-item',
                        'text' => 'Grade Systems',
                        'route' => 'grade-systems.index',
                        'permissions' => ['read grade system', 'create grade system'],
                    ],
                ],
            ],
            [
                'type' => 'menu-item',
                'icon' => 'fas fa-chart-line',
                'text' => 'Results',
                'roles' => $staffRoles,
                'permissions' => ['upload result', 'view result'],
                'submenu' => [
                    [
                        'type' => 'menu-item',
                        'icon' => 'fas fa-tachometer-alt',
                        'text' => 'Results Dashboard',
                        'route' => 'result',
                    ],
                    [
                        'type' => 'menu-item',
                        'icon' => 'fas fa-user-edit',
                        'text' => 'Individual Upload',
                        'route' => 'result.upload.individual',
                        'permissions' => ['upload result'],
                    ],
                    [
                        'type' => 'menu-item',
                        'icon' => 'fas fa-users-cog',
                        'text' => 'Bulk Upload',
                        'route' => 'result.upload.bulk',
                        'permissions' => ['upload result'],
                    ],
                    [
                        'type' => 'menu-item',
                        'icon' => 'fas fa-users',
                        'text' => 'Class Results',
                        'route' => 'result.view.class',
                        'permissions' => ['view result'],
                    ],
                    [
                        'type' => 'menu-item',
                        'icon' => 'fas fa-book',
                        'text' => 'Subject Results',
                        'route' => 'result.view.subject',
                        'permissions' => ['view result'],
                    ],
                    [
                        'type' => 'menu-item',
                        'icon' => 'fas fa-user-graduate',
                        'text' => 'Student Results',
                        'route' => 'result.view.student',
                        'permissions' => ['view result'],
                    ],
                    [
                        'type' => 'menu-item',
                        'icon' => 'fas fa-history',
                        'text' => 'Student History',
                        'route' => 'result.history',
                        'permissions' => ['view result'],
                    ],
                    [
                        'type' => 'menu-item',
                        'icon' => 'fas fa-calendar-alt',
                        'text' => 'Annual Class Results',
                        'route' => 'result.annual',
                        'permissions' => ['view result'],
                    ],
                ],
            ],

            ['header' => 'Finance'],
            [
                'type' => 'menu-item',
                'text' => 'Fees',
                'icon' => 'fas fa-dollar-sign',
                'roles' => $adminRoles,
                'permissions' => ['read fee', 'read fee invoice', 'read fee category'],
                'submenu' => [
                    [
                        'type' => 'menu-item',
                        'text' => 'Fee Invoices',
                        'route' => 'fee-invoices.index',
                        'permissions' => ['read fee invoice'],
                    ],
                    [
                        'type' => 'menu-item',
                        'text' => 'Create Fee Invoice',
                        'route' => 'fee-invoices.create',
                        'permissions' => ['create fee invoice'],
                    ],
                    [
                        'type' => 'menu-item',
                        'text' => 'Fees',
                        'route' => 'fees.index',
                        'permissions' => ['read fee'],
                    ],
                    [
                        'type' => 'menu-item',
                        'text' => 'Fee Categories',
                        'route' => 'fee-categories.index',
                        'permissions' => ['read fee category'],
                    ],
                ],
            ],

            ['header' => 'Account'],
            [
                'type' => 'menu-item',
                'icon' => 'fas fa-user',
                'text' => 'Profile',
                'route' => 'profile.edit',
                'roles' => $allRoles,
            ],
            [
                'type' => 'menu-item',
                'icon' => 'fas fa-key',
                'text' => 'Change Password',
                'route' => 'password.change',
                'roles' => $allRoles,
            ],
        ];
    }

    public function isVisible(array $item): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        if (!empty($item['route']) && !Route::has($item['route'])) {
            return false;
        }

        $authChecks = [];

        if (!empty($item['roles']) && is_array($item['roles'])) {
            $authChecks[] = $this->hasAnyRole($item['roles']);
        }

        if (!empty($item['permissions']) && is_array($item['permissions'])) {
            $authChecks[] = $this->hasAnyPermission($item['permissions']);
        }

        if (!empty($item['can']) && is_string($item['can'])) {
            $authChecks[] = $user->can($item['can']);
        }

        if ($authChecks !== [] && !in_array(true, $authChecks, true)) {
            return false;
        }

        if (!empty($item['submenu']) && is_array($item['submenu']) && $this->visibleSubmenu($item['submenu']) === []) {
            return false;
        }

        return true;
    }

    public function visibleSubmenu(array $submenu): array
    {
        return array_values(array_filter($submenu, fn (array $item): bool => $this->isVisible($item)));
    }

    protected function hasAnyRole(array $roles): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        $expandedRoles = [];
        foreach ($roles as $role) {
            if (!is_string($role)) {
                continue;
            }
            $expandedRoles = array_merge($expandedRoles, $this->expandRoleAliases($role));
        }

        $expandedRoles = array_values(array_unique($expandedRoles));
        if ($expandedRoles === []) {
            return false;
        }

        return $user->hasAnyRole($expandedRoles);
    }

    protected function hasAnyPermission(array $permissions): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        foreach ($permissions as $permission) {
            if (is_string($permission) && $user->can($permission)) {
                return true;
            }
        }

        return false;
    }

    protected function expandRoleAliases(string $role): array
    {
        $role = trim($role);
        if ($role === '') {
            return [];
        }

        $normalizedRole = strtolower(str_replace(['-', '_'], ' ', $role));
        $aliases = [$role, str_replace(' ', '-', $normalizedRole), str_replace(' ', '_', $normalizedRole)];
        $normalizedAliases = [$normalizedRole];

        foreach ($this->roleAliases as $normalizedAlias => $variations) {
            $normalizedVariations = array_map(
                fn (string $value): string => strtolower(str_replace(['-', '_'], ' ', $value)),
                $variations
            );

            if ($normalizedAlias === $normalizedRole || in_array($normalizedRole, $normalizedVariations, true)) {
                $aliases = array_merge($aliases, $variations);
                $normalizedAliases[] = $normalizedAlias;
            }
        }

        foreach ($normalizedAliases as $normalizedAlias) {
            $aliases[] = $normalizedAlias;
            $aliases[] = str_replace(' ', '-', $normalizedAlias);
            $aliases[] = str_replace(' ', '_', $normalizedAlias);
        }

        return array_values(array_unique($aliases));
    }

    public function render()
    {
        return view('livewire.layouts.menu');
    }
}
