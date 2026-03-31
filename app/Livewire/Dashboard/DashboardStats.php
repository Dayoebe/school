<?php

namespace App\Livewire\Dashboard;

use App\Models\Exam;
use App\Models\Notice;
use App\Models\Result;
use App\Models\School;
use App\Models\StudentRecord;
use App\Models\Subject;
use App\Models\User;
use App\Models\MyClass;
use App\Models\Section;
use App\Models\ClassGroup;
use App\Support\TeacherResponsibilityBuilder;
use App\Traits\RestrictsTeacherPortalAccess;
use App\Traits\RestrictsTeacherResultViewing;
use Livewire\Component;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;

class DashboardStats extends Component
{
    use RestrictsTeacherPortalAccess;
    use RestrictsTeacherResultViewing;

    public $stats = [];
    public $snapshot = [];
    public $quickActions = [];
    public $teacherPanel = [];
    public $studentPanel = [];
    public $parentPanel = [];
    public $academicContext = [];
    public $roleLabel = 'User';

    public $loading = true;
    public $isStaff = false;
    public $isTeacher = false;
    public $isRestrictedTeacher = false;
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
        $this->isTeacher = $user->hasRole('teacher');
        $this->isRestrictedTeacher = $this->isRestrictedTeacherPortalUser($user);
        $this->isStudent = $user->hasRole('student');
        $this->isParent = $user->hasRole('parent');
        $this->isStaff = $user->hasAnyRole(['super-admin', 'super_admin', 'principal', 'admin', 'teacher']);

        $this->roleLabel = $this->resolveRoleLabel($user);
        $this->loadAcademicContext($user);
        $this->loadSnapshot($user);
        $this->loadQuickActions($user);

        if ($this->isStaff && !$this->isRestrictedTeacher) {
            $this->loadStats($user);
        }

        if ($this->isTeacher) {
            $this->loadTeacherPanel($user);
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
        $activeStudentRecordIds = StudentRecord::activeStudentRecordIdsForSchoolAcademicYear(
            $schoolId,
            $user->school?->academic_year_id
        );

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
                $termResults = $activeStudentRecordIds->isEmpty()
                    ? 0
                    : Result::query()
                        ->where('academic_year_id', $user->school->academic_year_id)
                        ->where('semester_id', $user->school->semester_id)
                        ->whereIn('student_record_id', $activeStudentRecordIds)
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
        $superAdminRoles = ['super-admin', 'super_admin'];
        $viewResultsRoute = $this->currentUserCanAccessClassOnlyResultTools()
            ? 'result.view.class'
            : ($this->currentUserCanAccessSubjectResultTools() ? 'result.view.subject' : 'result');
        $viewResultsDescription = $this->currentUserCanAccessClassOnlyResultTools()
            ? 'Review class-level published result records.'
            : 'Review only the subjects and classes assigned to you.';

        $actions = [
            [
                'title' => 'Schools',
                'description' => 'Switch schools and manage school settings.',
                'icon' => 'fas fa-school',
                'route' => 'schools.index',
                'group' => 'Operations',
                'roles' => $superAdminRoles,
                'permissions' => ['read school', 'create school', 'manage school settings'],
            ],
            [
                'title' => 'Students',
                'description' => 'Manage students, promotions, and graduations.',
                'icon' => 'fas fa-user-graduate',
                'route' => 'students.index',
                'group' => 'People',
                'roles' => $adminAndStaffRoles,
                'permissions' => ['read student', 'create student', 'promote student'],
            ],
            [
                'title' => 'Teachers',
                'description' => 'Manage teacher profiles and assignments.',
                'icon' => 'fas fa-chalkboard-teacher',
                'route' => 'teachers.index',
                'group' => 'People',
                'roles' => $adminAndStaffRoles,
                'permissions' => ['read teacher', 'create teacher'],
            ],
            [
                'title' => 'Subjects',
                'description' => 'Review curriculum subjects and teacher assignment.',
                'icon' => 'fas fa-book-open',
                'route' => 'subjects.index',
                'group' => 'Academic',
                'roles' => $adminAndStaffRoles,
                'permissions' => ['read subject', 'create subject', 'update subject'],
            ],
            [
                'title' => 'Responsibilities',
                'description' => 'View the information and tools relevant to your role.',
                'icon' => 'fas fa-briefcase',
                'route' => 'dashboard.responsibilities',
                'group' => 'Academic',
                'permissions' => ['view dashboard'],
            ],
            [
                'title' => 'Exams',
                'description' => 'Configure exams and exam records.',
                'icon' => 'fas fa-file-signature',
                'route' => 'exams.index',
                'group' => 'Assessment',
                'roles' => $adminAndStaffRoles,
                'permissions' => ['read exam', 'read exam record'],
            ],
            [
                'title' => 'Results',
                'description' => 'Upload and manage student results.',
                'icon' => 'fas fa-chart-line',
                'route' => 'result',
                'group' => 'Assessment',
                'roles' => $adminAndStaffRoles,
                // Route "result" is protected by upload-result permission
                'permissions' => ['upload result'],
            ],
            [
                'title' => 'Review Results',
                'description' => $viewResultsDescription,
                'icon' => 'fas fa-eye',
                'route' => $viewResultsRoute,
                'group' => 'Assessment',
                'roles' => $adminAndStaffRoles,
                'permissions' => ['view result'],
            ],
            [
                'title' => 'My Results',
                'description' => 'Open your current result sheet for the selected period.',
                'icon' => 'fas fa-file-lines',
                'route' => 'result.view.student',
                'group' => 'Academic',
                'roles' => ['student'],
                'permissions' => ['view result'],
            ],
            [
                'title' => 'Academic History',
                'description' => 'Browse your results across previous terms and years.',
                'icon' => 'fas fa-clock-rotate-left',
                'route' => 'result.history',
                'group' => 'Academic',
                'roles' => ['student'],
                'permissions' => ['view result'],
            ],
            [
                'title' => 'CBT Exams',
                'description' => 'Start an authorised CBT for your class and subject.',
                'icon' => 'fas fa-laptop-code',
                'route' => 'cbt.exams',
                'group' => 'Assessment',
                'roles' => ['student'],
                'permissions' => ['take cbt exam'],
            ],
            [
                'title' => 'CBT Results',
                'description' => 'Open your CBT result history.',
                'icon' => 'fas fa-list-alt',
                'route' => 'cbt.viewer',
                'group' => 'Assessment',
                'roles' => ['student'],
                'permissions' => ['view cbt result'],
            ],
            [
                'title' => 'Manage CBT',
                'description' => 'Create CBT papers only for your assigned subjects and classes.',
                'icon' => 'fas fa-cogs',
                'route' => 'cbt.manage',
                'group' => 'Assessment',
                'roles' => $adminAndStaffRoles,
                'permissions' => ['manage cbt'],
            ],
            [
                'title' => 'Children Results',
                'description' => 'Open result sheets for children linked to your account.',
                'icon' => 'fas fa-children',
                'route' => 'result.view.student',
                'group' => 'Academic',
                'roles' => ['parent'],
                'permissions' => ['view result'],
            ],
            [
                'title' => 'Children History',
                'description' => 'Review previous result history for your children.',
                'icon' => 'fas fa-timeline',
                'route' => 'result.history',
                'group' => 'Academic',
                'roles' => ['parent'],
                'permissions' => ['view result'],
            ],
            [
                'title' => 'Student Welfare',
                'description' => 'Check attendance and discipline information for your child.',
                'icon' => 'fas fa-heart',
                'route' => 'parent.student-welfare',
                'group' => 'Academic',
                'roles' => ['parent'],
                'permissions' => ['read own child attendance', 'read own child discipline'],
            ],
            [
                'title' => 'Attendance',
                'description' => 'Record attendance only for classes where you are the class teacher.',
                'icon' => 'fas fa-user-check',
                'route' => 'attendance.index',
                'group' => 'Academic',
                'roles' => $adminAndStaffRoles,
                'permissions' => ['read attendance'],
            ],
            [
                'title' => 'Syllabi',
                'description' => 'Work only on syllabi for the classes and subjects assigned to you.',
                'icon' => 'fas fa-list-check',
                'route' => 'syllabi.index',
                'group' => 'Academic',
                'roles' => $adminAndStaffRoles,
                'permissions' => ['read syllabus', 'create syllabus'],
            ],
            [
                'title' => 'Notices',
                'description' => 'Create and publish school-wide announcements.',
                'icon' => 'fas fa-bullhorn',
                'route' => 'notices.index',
                'group' => 'Operations',
                'roles' => $adminAndStaffRoles,
                'permissions' => ['read notice', 'create notice', 'update notice'],
            ],
            [
                'title' => 'Fees',
                'description' => 'Manage fee categories and student invoices.',
                'icon' => 'fas fa-dollar-sign',
                'route' => 'fee-invoices.index',
                'group' => 'Operations',
                'roles' => $adminRoles,
                'permissions' => ['read fee', 'read fee invoice', 'read fee category'],
            ],
            [
                'title' => 'Timetables',
                'description' => 'Maintain class timetables and custom slots.',
                'icon' => 'fas fa-clock',
                'route' => 'timetables.index',
                'group' => 'Operations',
                'roles' => $adminAndStaffRoles,
                'permissions' => ['read timetable', 'create timetable'],
            ],
            [
                'title' => 'Analytics',
                'description' => 'Track admissions, engagement, and finance performance.',
                'icon' => 'fas fa-chart-pie',
                'route' => 'analytics.index',
                'group' => 'Operations',
                'roles' => $adminRoles,
                'permissions' => ['read analytics dashboard'],
            ],
            [
                'title' => 'Profile',
                'description' => 'Update personal account information.',
                'icon' => 'fas fa-user',
                'route' => 'profile.edit',
                'group' => 'Account',
            ],
            [
                'title' => 'Change Password',
                'description' => 'Secure your account credentials.',
                'icon' => 'fas fa-key',
                'route' => 'password.change',
                'group' => 'Account',
            ],
        ];

        $this->quickActions = array_values(array_filter(
            $actions,
            fn (array $action): bool => $this->canAccessAction($user, $action)
        ));
    }

    private function canAccessAction(User $user, array $action): bool
    {
        if (empty($action['route']) || !Route::has($action['route'])) {
            return false;
        }

        if ($this->isRestrictedTeacherPortalUser($user) && !$this->restrictedTeacherCanAccessRoute($action['route'], $user)) {
            return false;
        }

        if (!empty($action['roles']) && is_array($action['roles']) && !$user->hasAnyRole($action['roles'])) {
            return false;
        }

        if (!empty($action['permissions']) && is_array($action['permissions']) && !$this->hasAnyPermission($user, $action['permissions'])) {
            return false;
        }

        return true;
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
        $activeStudentsCount = $this->getActiveStudentsCount($schoolId);

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
            'active_students' => $activeStudentsCount,
            'inactive_students' => $this->getInactiveStudentsCount($schoolId, $activeStudentsCount),
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

        return StudentRecord::activeStudentRecordIdsForSchoolAcademicYear(
            $schoolId,
            auth()->user()?->school?->academic_year_id
        )->count();
    }

    private function getInactiveStudentsCount(?int $schoolId, ?int $activeStudentsCount = null): int
    {
        if (!$schoolId) {
            return 0;
        }

        $activeStudentsCount ??= $this->getActiveStudentsCount($schoolId);

        $totalStudents = User::query()
            ->where('school_id', $schoolId)
            ->role('student')
            ->count();

        return max($totalStudents - $activeStudentsCount, 0);
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
        $children = $user->children()
            ->where('users.school_id', $user->school_id)
            ->whereNull('users.deleted_at')
            ->with('studentRecord')
            ->get();
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

    private function loadTeacherPanel(User $user): void
    {
        $teacherPanel = app(TeacherResponsibilityBuilder::class)->build($user);

        $teacherToolCount = collect($this->quickActions)
            ->reject(fn (array $action) => in_array($action['route'] ?? '', ['profile.edit', 'password.change'], true))
            ->count();

        $teacherPanel['teacher_tools'] = $teacherToolCount;
        $teacherPanel['focus_items'] = $this->loadTeacherFocusItems(
            $user,
            (int) ($teacherPanel['class_teacher_classes'] ?? 0),
            (int) ($teacherPanel['teaching_assignments'] ?? 0)
        );

        $this->teacherPanel = $teacherPanel;
    }

    private function loadTeacherFocusItems(User $user, int $managedClassCount, int $teachingAssignmentCount): array
    {
        $items = [
            [
                'title' => 'Responsibilities',
                'description' => 'Open the page that shows the information relevant to your role.',
                'icon' => 'fas fa-briefcase',
                'route' => 'dashboard.responsibilities',
                'permissions' => ['view dashboard'],
                'tone' => 'bg-violet-600 text-white',
                'cta' => 'Open page',
            ],
            [
                'title' => 'Attendance',
                'description' => 'Record attendance for the classes assigned to you.',
                'icon' => 'fas fa-user-check',
                'route' => 'attendance.index',
                'roles' => ['teacher'],
                'permissions' => ['read attendance'],
                'tone' => 'bg-blue-600 text-white',
                'cta' => 'Open page',
                'requires_managed_classes' => true,
            ],
            [
                'title' => 'Results',
                'description' => 'Upload or review results for your assigned subjects.',
                'icon' => 'fas fa-chart-line',
                'route' => 'result',
                'roles' => ['teacher'],
                'permissions' => ['upload result'],
                'tone' => 'bg-emerald-600 text-white',
                'cta' => 'Open page',
                'requires_teaching_assignments' => true,
            ],
            [
                'title' => 'CBT Management',
                'description' => 'Manage CBT assessments and questions for your assigned subjects.',
                'icon' => 'fas fa-laptop-code',
                'route' => 'cbt.manage',
                'roles' => ['teacher'],
                'permissions' => ['manage cbt'],
                'tone' => 'bg-amber-500 text-slate-950',
                'cta' => 'Open page',
                'requires_teaching_assignments' => true,
            ],
            [
                'title' => 'Syllabi',
                'description' => 'Manage syllabi for the classes and subjects assigned to you.',
                'icon' => 'fas fa-list-check',
                'route' => 'syllabi.index',
                'roles' => ['teacher'],
                'permissions' => ['read syllabus', 'create syllabus'],
                'tone' => 'bg-rose-600 text-white',
                'cta' => 'Open page',
                'requires_teaching_assignments' => true,
            ],
            [
                'title' => 'Timetable',
                'description' => 'View the timetable for your classes and teaching schedule.',
                'icon' => 'fas fa-clock',
                'route' => 'timetables.index',
                'roles' => ['teacher'],
                'permissions' => ['read timetable', 'create timetable'],
                'tone' => 'bg-slate-800 text-white',
                'cta' => 'Open page',
            ],
        ];

        return array_values(array_filter($items, function (array $item) use ($managedClassCount, $teachingAssignmentCount, $user): bool {
            if (($item['requires_managed_classes'] ?? false) && $managedClassCount === 0) {
                return false;
            }

            if (($item['requires_teaching_assignments'] ?? false) && $teachingAssignmentCount === 0) {
                return false;
            }

            return $this->canAccessAction($user, $item);
        }));
    }

    public function render()
    {
        return view('livewire.dashboard.dashboard-stats');
    }
}
