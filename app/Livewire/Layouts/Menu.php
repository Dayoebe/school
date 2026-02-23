<?php

namespace App\Livewire\Layouts;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Livewire\Component;

class Menu extends Component
{
    public array $menu = [];

    public function mount(): void
    {
        $this->menu = array_merge(
            $this->overviewMenu(),
            $this->learningMenu(),
            $this->schoolManagementMenu(),
            $this->academicsMenu(),
            $this->assessmentMenu(),
            $this->financeMenu(),
            $this->engagementMenu(),
            $this->plannedFeaturesMenu(),
            $this->accountMenu(),
        );
    }

    protected function overviewMenu(): array
    {
        return [
            ['header' => 'Overview'],
            [
                'type' => 'menu-item',
                'icon' => 'fas fa-tachometer-alt',
                'text' => 'Dashboard',
                'route' => 'dashboard',
                'permissions' => ['view dashboard'],
            ],
        ];
    }

    protected function learningMenu(): array
    {
        return [
            ['header' => 'Learning'],
            [
                'type' => 'menu-item',
                'text' => 'My Learning',
                'icon' => 'fas fa-graduation-cap',
                'submenu' => [
                    [
                        'type' => 'menu-item',
                        'text' => 'Take CBT Exams',
                        'route' => 'cbt.exams',
                        'permissions' => ['take cbt exam'],
                    ],
                    [
                        'type' => 'menu-item',
                        'text' => 'CBT Results',
                        'route' => 'cbt.viewer',
                        'permissions' => ['view cbt result'],
                    ],
                    [
                        'type' => 'menu-item',
                        'text' => 'View Student Results',
                        'route' => 'result.view.student',
                        'permissions' => ['view result'],
                    ],
                    [
                        'type' => 'menu-item',
                        'text' => 'Academic History',
                        'route' => 'result.history',
                        'permissions' => ['view result'],
                    ],
                    [
                        'type' => 'menu-item',
                        'text' => 'Children Overview',
                        'route' => 'dashboard',
                        'permissions' => ['view dashboard'],
                    ],
                ],
            ],
        ];
    }

    protected function schoolManagementMenu(): array
    {
        return [
            ['header' => 'School Management'],
            [
                'type' => 'menu-item',
                'text' => 'Schools',
                'icon' => 'fas fa-school',
                'permissions' => ['read school', 'create school', 'manage school settings'],
                'submenu' => [
                    [
                        'type' => 'menu-item',
                        'text' => 'View Schools',
                        'route' => 'schools.index',
                        'permissions' => ['read school'],
                    ],
                    [
                        'type' => 'menu-item',
                        'text' => 'Create School',
                        'route' => 'schools.index',
                        'params' => ['mode' => 'create'],
                        'permissions' => ['create school'],
                    ],
                ],
            ],
            [
                'type' => 'menu-item',
                'text' => 'Website Settings',
                'icon' => 'fas fa-sliders-h',
                'route' => 'schools.settings',
                'permissions' => ['manage school settings'],
            ],
            [
                'type' => 'menu-item',
                'text' => 'Admins',
                'icon' => 'fas fa-user-shield',
                'permissions' => ['read admin', 'create admin'],
                'submenu' => [
                    [
                        'type' => 'menu-item',
                        'text' => 'View Admins',
                        'route' => 'admins.index',
                        'permissions' => ['read admin'],
                    ],
                    [
                        'type' => 'menu-item',
                        'text' => 'Create Admin',
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
                'permissions' => ['read student', 'create student', 'promote student', 'read promotion'],
                'submenu' => [
                    [
                        'type' => 'menu-item',
                        'text' => 'Registered Students',
                        'route' => 'students.index',
                        'permissions' => ['read student'],
                    ],
                    [
                        'type' => 'menu-item',
                        'text' => 'Admission Registrations',
                        'route' => 'admissions.registrations.index',
                        'permissions' => ['read admission registration'],
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
                        'permissions' => ['graduate student'],
                    ],
                    [
                        'type' => 'menu-item',
                        'text' => 'Graduation History',
                        'route' => 'students.graduations',
                        'permissions' => ['view graduations'],
                    ],
                ],
            ],
            [
                'type' => 'menu-item',
                'text' => 'Parents',
                'icon' => 'fas fa-users',
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
                'permissions' => ['read applicant'],
                'can' => ['viewAny', [\App\Models\User::class, 'applicant']],
                'submenu' => [
                    [
                        'type' => 'menu-item',
                        'text' => 'Pending Applications',
                        'route' => 'account-applications.index',
                        'permissions' => ['read applicant'],
                        'can' => ['viewAny', [\App\Models\User::class, 'applicant']],
                    ],
                    [
                        'type' => 'menu-item',
                        'text' => 'Rejected Applications',
                        'route' => 'account-applications.rejected-applications',
                        'permissions' => ['read applicant'],
                        'can' => ['viewAny', [\App\Models\User::class, 'applicant']],
                    ],
                ],
            ],
        ];
    }

    protected function academicsMenu(): array
    {
        return [
            ['header' => 'Academics'],
            [
                'type' => 'menu-item',
                'text' => 'Academic Calendar',
                'icon' => 'fas fa-calendar-alt',
                'permissions' => ['read academic year', 'read semester'],
                'submenu' => [
                    [
                        'type' => 'menu-item',
                        'text' => 'Academic Years',
                        'route' => 'academic-years.index',
                        'permissions' => ['read academic year'],
                        'can' => ['viewAny', \App\Models\AcademicYear::class],
                    ],
                    [
                        'type' => 'menu-item',
                        'text' => 'Terms',
                        'route' => 'semesters.index',
                        'permissions' => ['read semester'],
                        'can' => ['viewAny', \App\Models\Semester::class],
                    ],
                ],
            ],
            [
                'type' => 'menu-item',
                'text' => 'Classes',
                'icon' => 'fas fa-chalkboard',
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
                'permissions' => ['read syllabus', 'create syllabus'],
            ],
            [
                'type' => 'menu-item',
                'text' => 'Timetables',
                'icon' => 'fas fa-clock',
                'permissions' => ['read timetable', 'read custom timetable item'],
                'submenu' => [
                    [
                        'type' => 'menu-item',
                        'text' => 'View Timetables',
                        'route' => 'timetables.index',
                        'permissions' => ['read timetable'],
                    ],
                    [
                        'type' => 'menu-item',
                        'text' => 'Create Timetable',
                        'route' => 'timetables.create',
                        'permissions' => ['create timetable'],
                    ],
                    [
                        'type' => 'menu-item',
                        'text' => 'Custom Items',
                        'route' => 'custom-timetable-items.index',
                        'permissions' => ['read custom timetable item'],
                    ],
                    [
                        'type' => 'menu-item',
                        'text' => 'Create Custom Item',
                        'route' => 'custom-timetable-items.create',
                        'permissions' => ['create custom timetable item'],
                    ],
                ],
            ],
        ];
    }

    protected function assessmentMenu(): array
    {
        return [
            ['header' => 'Assessment & Results'],
            [
                'type' => 'menu-item',
                'text' => 'CBT',
                'icon' => 'fas fa-laptop-code',
                'permissions' => ['take cbt exam', 'view cbt result', 'manage cbt'],
                'submenu' => [
                    [
                        'type' => 'menu-item',
                        'text' => 'Take CBT Exams',
                        'route' => 'cbt.exams',
                        'permissions' => ['take cbt exam'],
                    ],
                    [
                        'type' => 'menu-item',
                        'text' => 'CBT Results',
                        'route' => 'cbt.viewer',
                        'permissions' => ['view cbt result'],
                    ],
                    [
                        'type' => 'menu-item',
                        'text' => 'Manage CBT',
                        'route' => 'cbt.manage',
                        'permissions' => ['manage cbt'],
                    ],
                ],
            ],
            [
                'type' => 'menu-item',
                'text' => 'Exams',
                'icon' => 'fas fa-file-signature',
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
                        'permissions' => ['check result'],
                    ],
                    [
                        'type' => 'menu-item',
                        'text' => 'Tabulation Sheet',
                        'route' => 'exams.tabulation',
                        'permissions' => ['read exam'],
                    ],
                    [
                        'type' => 'menu-item',
                        'text' => 'Semester Tabulation',
                        'route' => 'exams.semester-result-tabulation',
                        'permissions' => ['read exam'],
                    ],
                    [
                        'type' => 'menu-item',
                        'text' => 'Annual Tabulation',
                        'route' => 'exams.academic-year-result-tabulation',
                        'permissions' => ['read exam'],
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
                'permissions' => ['upload result', 'view result'],
                'submenu' => [
                    [
                        'type' => 'menu-item',
                        'icon' => 'fas fa-tachometer-alt',
                        'text' => 'Results Dashboard',
                        'route' => 'result',
                        'permissions' => ['upload result'],
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
                    [
                        'type' => 'menu-item',
                        'icon' => 'fas fa-file-excel',
                        'text' => 'Annual Export (Excel)',
                        'route' => 'result.annual.export',
                        'permissions' => ['view result'],
                    ],
                    [
                        'type' => 'menu-item',
                        'icon' => 'fas fa-file-pdf',
                        'text' => 'Annual Export (PDF)',
                        'route' => 'result.annual.export.pdf',
                        'permissions' => ['view result'],
                    ],
                ],
            ],
            [
                'type' => 'menu-item',
                'text' => 'Teacher Result Entry',
                'icon' => 'fas fa-pen',
                'route' => 'results.index',
                'permissions' => ['upload result'],
            ],
        ];
    }

    protected function financeMenu(): array
    {
        return [
            ['header' => 'Finance'],
            [
                'type' => 'menu-item',
                'text' => 'Fees',
                'icon' => 'fas fa-dollar-sign',
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
                        'text' => 'Create Fee',
                        'route' => 'fees.create',
                        'permissions' => ['create fee'],
                    ],
                    [
                        'type' => 'menu-item',
                        'text' => 'Fee Categories',
                        'route' => 'fee-categories.index',
                        'permissions' => ['read fee category'],
                    ],
                    [
                        'type' => 'menu-item',
                        'text' => 'Create Fee Category',
                        'route' => 'fee-categories.create',
                        'permissions' => ['create fee category'],
                    ],
                ],
            ],
        ];
    }

    protected function engagementMenu(): array
    {
        return [
            ['header' => 'Communication & Media'],
            [
                'type' => 'menu-item',
                'icon' => 'fas fa-bullhorn',
                'text' => 'Notices',
                'route' => 'notices.index',
                'permissions' => ['read notice', 'create notice', 'update notice'],
            ],
            [
                'type' => 'menu-item',
                'icon' => 'fas fa-envelope',
                'text' => 'Contact Messages',
                'route' => 'contacts.messages.index',
                'permissions' => ['read contact message'],
            ],
            [
                'type' => 'menu-item',
                'icon' => 'fas fa-images',
                'text' => 'Gallery Manager',
                'route' => 'gallery.manage',
                'permissions' => ['manage gallery'],
            ],
        ];
    }

    protected function plannedFeaturesMenu(): array
    {
        return [
            ['header' => 'Planned Features'],
            [
                'type' => 'menu-item',
                'text' => 'Attendance & Discipline',
                'icon' => 'fas fa-user-check',
                'permissions' => ['view dashboard'],
                'submenu' => [
                    ['type' => 'menu-item', 'text' => 'Daily Attendance', 'coming_soon' => true],
                    ['type' => 'menu-item', 'text' => 'Behavior Log', 'coming_soon' => true],
                    ['type' => 'menu-item', 'text' => 'Parent Alerts', 'coming_soon' => true],
                ],
            ],
            [
                'type' => 'menu-item',
                'text' => 'Teaching & Learning',
                'icon' => 'fas fa-book-reader',
                'permissions' => ['view dashboard'],
                'submenu' => [
                    ['type' => 'menu-item', 'text' => 'Assignments', 'coming_soon' => true],
                    ['type' => 'menu-item', 'text' => 'Lesson Plans', 'coming_soon' => true],
                    ['type' => 'menu-item', 'text' => 'Digital Library', 'coming_soon' => true],
                ],
            ],
            [
                'type' => 'menu-item',
                'text' => 'Operations',
                'icon' => 'fas fa-sitemap',
                'permissions' => ['view dashboard'],
                'submenu' => [
                    ['type' => 'menu-item', 'text' => 'Transport', 'coming_soon' => true],
                    ['type' => 'menu-item', 'text' => 'Hostel', 'coming_soon' => true],
                    ['type' => 'menu-item', 'text' => 'Clinic', 'coming_soon' => true],
                    ['type' => 'menu-item', 'text' => 'Inventory', 'coming_soon' => true],
                ],
            ],
            [
                'type' => 'menu-item',
                'text' => 'Reports & Insights',
                'icon' => 'fas fa-chart-pie',
                'permissions' => ['view dashboard'],
                'submenu' => [
                    ['type' => 'menu-item', 'text' => 'Executive Reports', 'coming_soon' => true],
                    ['type' => 'menu-item', 'text' => 'Enrollment Analytics', 'coming_soon' => true],
                    ['type' => 'menu-item', 'text' => 'Performance Trends', 'coming_soon' => true],
                ],
            ],
        ];
    }

    protected function accountMenu(): array
    {
        return [
            ['header' => 'Account'],
            [
                'type' => 'menu-item',
                'icon' => 'fas fa-user',
                'text' => 'Profile',
                'route' => 'profile.edit',
                'permissions' => ['manage own profile'],
            ],
            [
                'type' => 'menu-item',
                'icon' => 'fas fa-key',
                'text' => 'Change Password',
                'route' => 'password.change',
                'permissions' => ['change own password'],
            ],
        ];
    }

    public function isVisible(array $item): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        if (empty($item['coming_soon']) && !empty($item['route']) && !Route::has($item['route'])) {
            return false;
        }

        if (!empty($item['permissions']) && is_array($item['permissions']) && !$this->hasAnyPermission($item['permissions'])) {
            return false;
        }

        if (array_key_exists('can', $item) && !$this->passesCanCheck($item['can'])) {
            return false;
        }

        if (!empty($item['can_any']) && is_array($item['can_any']) && !$this->passesAnyCanChecks($item['can_any'])) {
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

    protected function passesCanCheck(mixed $can): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        if (is_string($can)) {
            return $user->can($can);
        }

        if (is_array($can) && array_is_list($can) && isset($can[0]) && is_string($can[0])) {
            $ability = $can[0];
            $arguments = $can[1] ?? [];

            return $user->can($ability, $arguments);
        }

        return false;
    }

    protected function passesAnyCanChecks(array $canChecks): bool
    {
        foreach ($canChecks as $canCheck) {
            if ($this->passesCanCheck($canCheck)) {
                return true;
            }
        }

        return false;
    }

    public function render()
    {
        return view('livewire.layouts.menu');
    }
}
