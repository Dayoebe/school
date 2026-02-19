<?php

namespace App\Livewire\Dashboard;

use App\Models\Exam;
use App\Models\Notice;
use App\Models\Result;
use App\Models\School;
use App\Models\Subject;
use App\Models\User;
use App\Models\MyClass;
use App\Models\Section;
use App\Models\ClassGroup;
use Livewire\Component;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;

class DashboardStats extends Component
{
    public $stats = [];
    public $snapshot = [];
    public $quickActions = [];
    public $studentPanel = [];
    public $parentPanel = [];
    public $academicContext = [];
    public $roleLabel = 'User';

    public $loading = true;
    public $isStaff = false;
    public $isStudent = false;
    public $isParent = false;
    public $isSuperAdmin = false;

    public function mount(): void
    {
        $user = auth()->user();
        if (!$user) {
            $this->loading = false;
            return;
        }

        $user->loadMissing([
            'school.academicYear',
            'school.semester',
            'studentRecord',
            'children.studentRecord',
        ]);

        $this->isSuperAdmin = $user->hasAnyRole(['super-admin', 'super_admin']);
        $this->isStudent = $user->hasRole('student');
        $this->isParent = $user->hasRole('parent');
        $this->isStaff = $user->hasAnyRole(['super-admin', 'super_admin', 'principal', 'admin', 'teacher']);

        $this->roleLabel = $this->resolveRoleLabel($user);
        $this->loadAcademicContext($user);
        $this->loadSnapshot($user);
        $this->loadQuickActions($user);

        if ($this->isStaff) {
            $this->loadStats($user);
        }

        if ($this->isStudent) {
            $this->loadStudentPanel($user);
        }

        if ($this->isParent) {
            $this->loadParentPanel($user);
        }

        $this->loading = false;
    }

    private function resolveRoleLabel(User $user): string
    {
        if ($this->isSuperAdmin) {
            return 'Super Admin';
        }

        $roleMap = [
            'principal' => 'Principal',
            'admin' => 'Admin',
            'teacher' => 'Teacher',
            'student' => 'Student',
            'parent' => 'Parent',
            'user' => 'User',
        ];

        foreach ($roleMap as $role => $label) {
            if ($user->hasRole($role)) {
                return $label;
            }
        }

        return 'User';
    }

    private function loadAcademicContext(User $user): void
    {
        $this->academicContext = [
            'school_name' => $user->school?->name ?? config('app.name'),
            'academic_year' => $user->school?->academicYear?->name ?? 'Not set',
            'semester' => $user->school?->semester?->name ?? 'Not set',
            'today' => Carbon::now()->format('D, M j, Y'),
        ];
    }

    private function loadSnapshot(User $user): void
    {
        $schoolId = $user->school_id;
        $today = Carbon::today();

        $activeNotices = 0;
        $ongoingExams = 0;
        $upcomingExams = 0;
        $publishedExams = 0;
        $termResults = 0;

        if ($schoolId) {
            $activeNotices = Notice::query()
                ->where('school_id', $schoolId)
                ->active()
                ->count();

            $examQuery = Exam::query()
                ->whereHas('semester', function ($query) use ($schoolId) {
                    $query->where('school_id', $schoolId);
                });

            $ongoingExams = (clone $examQuery)
                ->whereDate('start_date', '<=', $today)
                ->whereDate('stop_date', '>=', $today)
                ->count();

            $upcomingExams = (clone $examQuery)
                ->whereDate('start_date', '>', $today)
                ->count();

            $publishedExams = (clone $examQuery)
                ->where('publish_result', true)
                ->count();

            if ($user->school?->academic_year_id && $user->school?->semester_id) {
                $termResults = Result::query()
                    ->where('academic_year_id', $user->school->academic_year_id)
                    ->where('semester_id', $user->school->semester_id)
                    ->whereHas('studentRecord.user', function ($query) use ($schoolId) {
                        $query->where('school_id', $schoolId);
                    })
                    ->count();
            }
        }

        $this->snapshot = [
            'active_notices' => $activeNotices,
            'ongoing_exams' => $ongoingExams,
            'upcoming_exams' => $upcomingExams,
            'published_exams' => $publishedExams,
            'term_results' => $termResults,
        ];
    }

    private function loadQuickActions(User $user): void
    {
        $adminAndStaffRoles = ['super-admin', 'super_admin', 'principal', 'admin', 'teacher'];
        $adminRoles = ['super-admin', 'super_admin', 'principal', 'admin'];

        $actions = [
            [
                'title' => 'Students',
                'description' => 'Manage students, promotions, and graduations.',
                'icon' => 'fas fa-user-graduate',
                'route' => 'students.index',
                'roles' => $adminAndStaffRoles,
                'permissions' => ['read student', 'create student', 'promote student'],
            ],
            [
                'title' => 'Teachers',
                'description' => 'Manage teacher profiles and assignments.',
                'icon' => 'fas fa-chalkboard-teacher',
                'route' => 'teachers.index',
                'roles' => $adminAndStaffRoles,
                'permissions' => ['read teacher', 'create teacher'],
            ],
            [
                'title' => 'Subjects',
                'description' => 'Review curriculum subjects and teacher assignment.',
                'icon' => 'fas fa-book-open',
                'route' => 'subjects.index',
                'roles' => $adminAndStaffRoles,
                'permissions' => ['read subject', 'create subject', 'update subject'],
            ],
            [
                'title' => 'Exams',
                'description' => 'Configure exams and exam records.',
                'icon' => 'fas fa-file-signature',
                'route' => 'exams.index',
                'roles' => $adminAndStaffRoles,
                'permissions' => ['read exam', 'read exam record'],
            ],
            [
                'title' => 'Results',
                'description' => 'Upload and manage student results.',
                'icon' => 'fas fa-chart-line',
                'route' => 'result',
                'roles' => $adminAndStaffRoles,
                // Route "result" is protected by upload-result permission
                'permissions' => ['upload result'],
            ],
            [
                'title' => 'View Results',
                'description' => 'Review class-level published result records.',
                'icon' => 'fas fa-eye',
                'route' => 'result.view.class',
                'roles' => $adminAndStaffRoles,
                'permissions' => ['view result'],
            ],
            [
                'title' => 'CBT Exams',
                'description' => 'Take or review CBT exams and outcomes.',
                'icon' => 'fas fa-laptop-code',
                'route' => 'cbt.exams',
            ],
            [
                'title' => 'CBT Results',
                'description' => 'Open CBT result history and performance view.',
                'icon' => 'fas fa-list-alt',
                'route' => 'cbt.viewer',
            ],
            [
                'title' => 'Manage CBT',
                'description' => 'Create assessments and manage CBT questions.',
                'icon' => 'fas fa-cogs',
                'route' => 'cbt.manage',
                'roles' => $adminAndStaffRoles,
            ],
            [
                'title' => 'Notices',
                'description' => 'Create and publish school-wide announcements.',
                'icon' => 'fas fa-bullhorn',
                'route' => 'notices.index',
                'roles' => $adminAndStaffRoles,
                'permissions' => ['read notice', 'create notice', 'update notice'],
            ],
            [
                'title' => 'Fees',
                'description' => 'Manage fee categories and student invoices.',
                'icon' => 'fas fa-dollar-sign',
                'route' => 'fee-invoices.index',
                'roles' => $adminRoles,
                'permissions' => ['read fee', 'read fee invoice', 'read fee category'],
            ],
            [
                'title' => 'Timetables',
                'description' => 'Maintain class timetables and custom slots.',
                'icon' => 'fas fa-clock',
                'route' => 'timetables.index',
                'roles' => $adminAndStaffRoles,
                'permissions' => ['read timetable', 'create timetable'],
            ],
            [
                'title' => 'Profile',
                'description' => 'Update personal account information.',
                'icon' => 'fas fa-user',
                'route' => 'profile.edit',
            ],
            [
                'title' => 'Change Password',
                'description' => 'Secure your account credentials.',
                'icon' => 'fas fa-key',
                'route' => 'password.change',
            ],
        ];

        $this->quickActions = array_values(array_filter(
            $actions,
            function (array $action) use ($user): bool {
                if (!Route::has($action['route'])) {
                    return false;
                }

                $checks = [];

                if (!empty($action['roles']) && is_array($action['roles'])) {
                    $checks[] = $user->hasAnyRole($action['roles']);
                }

                if (!empty($action['permissions']) && is_array($action['permissions'])) {
                    $checks[] = $this->hasAnyPermission($user, $action['permissions']);
                }

                return $checks === [] || in_array(true, $checks, true);
            }
        ));
    }

    private function hasAnyPermission(User $user, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (is_string($permission) && $user->can($permission)) {
                return true;
            }
        }

        return false;
    }

    private function loadStats(User $user): void
    {
        $schoolId = $user->school_id;

        $this->stats = [
            'schools' => $this->isSuperAdmin ? School::count() : 0,
            'class_groups' => $schoolId ? ClassGroup::query()->count() : 0,
            'classes' => $schoolId ? MyClass::whereHas('classGroup', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            })->count() : 0,
            'sections' => $schoolId ? Section::whereHas('myClass.classGroup', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            })->count() : 0,
            'subjects' => $schoolId ? Subject::whereHas('myClass.classGroup', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            })->count() : 0,
            'active_students' => $this->getActiveStudentsCount($schoolId),
            'graduated_students' => $this->getGraduatedStudentsCount($schoolId),
            'teachers' => User::where('school_id', $schoolId)->role('teacher')->count(),
            'parents' => User::where('school_id', $schoolId)->role('parent')->count(),
            'total_notices' => $schoolId ? Notice::query()->count() : 0,
            'total_exams' => $schoolId ? Exam::whereHas('semester', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            })->count() : 0,
        ];
    }

    private function getActiveStudentsCount(?int $schoolId): int
    {
        if (!$schoolId) {
            return 0;
        }

        return User::where('school_id', $schoolId)
            ->role('student')
            ->whereHas('studentRecord', function ($q) {
                $q->where('is_graduated', false);
            })
            ->count();
    }

    private function getGraduatedStudentsCount(?int $schoolId): int
    {
        if (!$schoolId) {
            return 0;
        }

        return User::where('school_id', $schoolId)
            ->role('student')
            ->whereIn('id', function ($query) {
                $query->select('user_id')
                    ->from('student_records')
                    ->where('is_graduated', true)
                    ->whereNotNull('user_id');
            })
            ->count();
    }

    private function loadStudentPanel(User $user): void
    {
        $studentRecord = $user->studentRecord;
        if (!$studentRecord) {
            $this->studentPanel = [];
            return;
        }

        $currentAcademicYearId = $user->school?->academic_year_id;
        $currentSemesterId = $user->school?->semester_id;

        $resultQuery = Result::query()->where('student_record_id', $studentRecord->id);

        if ($currentAcademicYearId) {
            $resultQuery->where('academic_year_id', $currentAcademicYearId);
        }

        if ($currentSemesterId) {
            $resultQuery->where('semester_id', $currentSemesterId);
        }

        $className = $studentRecord->academicYearClass?->name ?? $studentRecord->myClass?->name ?? 'Not assigned';
        $sectionName = $studentRecord->academicYearSection?->name ?? $studentRecord->section?->name ?? 'Not assigned';

        $this->studentPanel = [
            'admission_number' => $studentRecord->admission_number ?: 'N/A',
            'class_name' => $className,
            'section_name' => $sectionName,
            'subject_count' => $studentRecord->studentSubjects()->count(),
            'result_count' => (clone $resultQuery)->count(),
            'approved_result_count' => (clone $resultQuery)->where('approved', true)->count(),
            'average_score' => number_format((float) ((clone $resultQuery)->avg('total_score') ?? 0), 1),
        ];
    }

    private function loadParentPanel(User $user): void
    {
        $children = $user->children()->with('studentRecord')->get();
        if ($children->isEmpty()) {
            $this->parentPanel = [
                'total_children' => 0,
                'hidden_count' => 0,
                'children' => [],
            ];
            return;
        }

        $classIds = $children
            ->pluck('studentRecord.my_class_id')
            ->filter()
            ->unique()
            ->values();

        $sectionIds = $children
            ->pluck('studentRecord.section_id')
            ->filter()
            ->unique()
            ->values();

        $classNames = $classIds->isEmpty()
            ? collect()
            : MyClass::whereIn('id', $classIds)->pluck('name', 'id');

        $sectionNames = $sectionIds->isEmpty()
            ? collect()
            : Section::whereIn('id', $sectionIds)->pluck('name', 'id');

        $limit = 6;
        $displayChildren = $children
            ->take($limit)
            ->map(function (User $child) use ($classNames, $sectionNames): array {
                $record = $child->studentRecord;

                $className = $record?->my_class_id ? ($classNames[$record->my_class_id] ?? 'Not assigned') : 'Not assigned';
                $sectionName = $record?->section_id ? ($sectionNames[$record->section_id] ?? 'Not assigned') : 'Not assigned';

                return [
                    'name' => $child->name,
                    'class_name' => $className,
                    'section_name' => $sectionName,
                    'admission_number' => $record?->admission_number ?: 'N/A',
                ];
            })
            ->values()
            ->all();

        $this->parentPanel = [
            'total_children' => $children->count(),
            'hidden_count' => max($children->count() - $limit, 0),
            'children' => $displayChildren,
        ];
    }

    public function render()
    {
        return view('livewire.dashboard.dashboard-stats');
    }
}
