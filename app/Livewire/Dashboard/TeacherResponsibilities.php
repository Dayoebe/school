<?php

namespace App\Livewire\Dashboard;

use App\Models\Result;
use App\Models\User;
use App\Support\TeacherResponsibilityBuilder;
use App\Traits\RestrictsTeacherResultViewing;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Livewire\Component;

class TeacherResponsibilities extends Component
{
    use RestrictsTeacherResultViewing;

    public bool $isTeacher = false;
    public bool $isStudent = false;
    public bool $isParent = false;
    public bool $isStaff = false;
    public bool $isSuperAdmin = false;

    public array $teacherPanel = [];
    public array $studentPanel = [];
    public array $parentPanel = [];
    public array $staffPanel = [];
    public array $pageActions = [];
    public array $academicContext = [];
    public string $roleLabel = 'User';

    public function mount(): void
    {
        /** @var User|null $user */
        $user = auth()->user();

        abort_unless($user && $user->can('view dashboard'), 403);

        $user->loadMissing([
            'school.academicYear',
            'school.semester',
            'studentRecord.studentSubjects',
            'children.studentRecord.myClass',
            'children.studentRecord.section',
        ]);

        $this->isSuperAdmin = $user->hasAnyRole(['super-admin', 'super_admin']);
        $this->isTeacher = $user->hasRole('teacher');
        $this->isStudent = $user->hasRole('student');
        $this->isParent = $user->hasRole('parent');
        $this->isStaff = $user->hasAnyRole(['super-admin', 'super_admin', 'principal', 'admin', 'teacher']);
        $this->roleLabel = $this->resolveRoleLabel($user);

        $this->academicContext = [
            'user_name' => $user->name,
            'school_name' => $user->school?->name ?? config('app.name'),
            'academic_year' => $user->school?->academicYear?->name ?? 'Not set',
            'semester' => $user->school?->semester?->name ?? 'Not set',
            'today' => Carbon::now()->format('D, M j, Y'),
        ];

        if ($this->isTeacher) {
            $this->teacherPanel = app(TeacherResponsibilityBuilder::class)->build($user);
        }

        if ($this->isStudent) {
            $this->loadStudentPanel($user);
        }

        if ($this->isParent) {
            $this->loadParentPanel($user);
        }

        $this->pageActions = $this->buildPageActions($user);

        if ($this->isStaff && !$this->isTeacher) {
            $this->staffPanel = [
                'action_count' => count($this->pageActions),
                'actions' => $this->pageActions,
            ];
        }
    }

    protected function resolveRoleLabel(User $user): string
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

    protected function loadStudentPanel(User $user): void
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

        $subjects = $studentRecord->studentSubjects()
            ->orderBy('subjects.name')
            ->get(['subjects.id', 'subjects.name', 'subjects.short_name'])
            ->map(fn ($subject): array => [
                'id' => (int) $subject->id,
                'name' => $subject->name,
                'short_name' => $subject->short_name,
            ])
            ->values()
            ->all();

        $this->studentPanel = [
            'admission_number' => $studentRecord->admission_number ?: 'N/A',
            'class_name' => $studentRecord->academicYearClass?->name ?? $studentRecord->myClass?->name ?? 'Not assigned',
            'section_name' => $studentRecord->academicYearSection?->name ?? $studentRecord->section?->name ?? 'Not assigned',
            'subject_count' => count($subjects),
            'result_count' => (clone $resultQuery)->count(),
            'approved_result_count' => (clone $resultQuery)->where('approved', true)->count(),
            'average_score' => number_format((float) ((clone $resultQuery)->avg('total_score') ?? 0), 1),
            'subjects' => $subjects,
        ];
    }

    protected function loadParentPanel(User $user): void
    {
        $children = $user->children()->with('studentRecord.myClass', 'studentRecord.section')->get();

        $this->parentPanel = [
            'total_children' => $children->count(),
            'children' => $children->map(function (User $child): array {
                $record = $child->studentRecord;

                return [
                    'name' => $child->name,
                    'admission_number' => $record?->admission_number ?: 'N/A',
                    'class_name' => $record?->myClass?->name ?? 'Not assigned',
                    'section_name' => $record?->section?->name ?? 'Not assigned',
                ];
            })->values()->all(),
        ];
    }

    protected function buildPageActions(User $user): array
    {
        $teacherResultReviewRoute = $this->currentUserCanAccessClassOnlyResultTools()
            ? 'result.view.class'
            : ($this->currentUserCanAccessSubjectResultTools() ? 'result.view.subject' : null);

        $items = [
            [
                'title' => 'Dashboard',
                'description' => 'Return to the main dashboard.',
                'icon' => 'fas fa-house',
                'route' => 'dashboard',
                'permissions' => ['view dashboard'],
                'tone' => 'bg-slate-900 text-white',
            ],
            [
                'title' => 'Attendance',
                'description' => 'Record attendance for the classes assigned to you.',
                'icon' => 'fas fa-user-check',
                'route' => 'attendance.index',
                'roles' => ['teacher'],
                'permissions' => ['read attendance'],
                'tone' => 'bg-blue-600 text-white',
                'requires_managed_classes' => true,
            ],
            [
                'title' => 'Result Upload',
                'description' => 'Upload results for the subjects assigned to you.',
                'icon' => 'fas fa-chart-line',
                'route' => 'result',
                'roles' => ['teacher'],
                'permissions' => ['upload result'],
                'tone' => 'bg-emerald-600 text-white',
                'requires_teaching_assignments' => true,
            ],
            [
                'title' => 'View Results',
                'description' => 'View results for the classes or subjects available to you.',
                'icon' => 'fas fa-eye',
                'route' => $teacherResultReviewRoute,
                'roles' => ['teacher', 'principal', 'admin', 'super-admin', 'super_admin'],
                'permissions' => ['view result'],
                'tone' => 'bg-cyan-500 text-slate-950',
            ],
            [
                'title' => 'Manage CBT',
                'description' => 'Manage CBT assessments and questions for your assigned subjects.',
                'icon' => 'fas fa-laptop-code',
                'route' => 'cbt.manage',
                'roles' => ['teacher'],
                'permissions' => ['manage cbt'],
                'tone' => 'bg-amber-500 text-slate-950',
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
                'requires_teaching_assignments' => true,
            ],
            [
                'title' => 'Timetable',
                'description' => 'View the timetable for your classes and teaching schedule.',
                'icon' => 'fas fa-clock',
                'route' => 'timetables.index',
                'roles' => ['teacher', 'principal', 'admin', 'super-admin', 'super_admin', 'student'],
                'permissions' => ['read timetable', 'create timetable'],
                'tone' => 'bg-violet-600 text-white',
            ],
            [
                'title' => 'My Results',
                'description' => 'View your current results.',
                'icon' => 'fas fa-file-lines',
                'route' => 'result.view.student',
                'roles' => ['student'],
                'permissions' => ['view result'],
                'tone' => 'bg-emerald-600 text-white',
            ],
            [
                'title' => 'Academic History',
                'description' => 'View your previous academic records.',
                'icon' => 'fas fa-clock-rotate-left',
                'route' => 'result.history',
                'roles' => ['student'],
                'permissions' => ['view result'],
                'tone' => 'bg-blue-600 text-white',
            ],
            [
                'title' => 'CBT Exams',
                'description' => 'Open available CBT exams.',
                'icon' => 'fas fa-laptop-code',
                'route' => 'cbt.exams',
                'roles' => ['student'],
                'permissions' => ['take cbt exam'],
                'tone' => 'bg-amber-500 text-slate-950',
            ],
            [
                'title' => 'CBT Results',
                'description' => 'View your CBT results.',
                'icon' => 'fas fa-list-alt',
                'route' => 'cbt.viewer',
                'roles' => ['student'],
                'permissions' => ['view cbt result'],
                'tone' => 'bg-cyan-500 text-slate-950',
            ],
            [
                'title' => 'Children Results',
                'description' => 'View results for your linked children.',
                'icon' => 'fas fa-children',
                'route' => 'result.view.student',
                'roles' => ['parent'],
                'permissions' => ['view result'],
                'tone' => 'bg-emerald-600 text-white',
            ],
            [
                'title' => 'Children History',
                'description' => 'View previous records for your linked children.',
                'icon' => 'fas fa-timeline',
                'route' => 'result.history',
                'roles' => ['parent'],
                'permissions' => ['view result'],
                'tone' => 'bg-blue-600 text-white',
            ],
            [
                'title' => 'Student Welfare',
                'description' => 'View attendance and discipline information for your child.',
                'icon' => 'fas fa-heart',
                'route' => 'parent.student-welfare',
                'roles' => ['parent'],
                'permissions' => ['read own child attendance', 'read own child discipline'],
                'tone' => 'bg-rose-600 text-white',
            ],
            [
                'title' => 'Students',
                'description' => 'Manage student records.',
                'icon' => 'fas fa-user-graduate',
                'route' => 'students.index',
                'roles' => ['principal', 'admin', 'super-admin', 'super_admin'],
                'permissions' => ['read student', 'create student', 'promote student'],
                'tone' => 'bg-emerald-600 text-white',
            ],
            [
                'title' => 'Teachers',
                'description' => 'Manage teacher records and assignments.',
                'icon' => 'fas fa-chalkboard-teacher',
                'route' => 'teachers.index',
                'roles' => ['principal', 'admin', 'super-admin', 'super_admin'],
                'permissions' => ['read teacher', 'create teacher'],
                'tone' => 'bg-blue-600 text-white',
            ],
            [
                'title' => 'Subjects',
                'description' => 'View and manage subjects.',
                'icon' => 'fas fa-book-open',
                'route' => 'subjects.index',
                'roles' => ['principal', 'admin', 'super-admin', 'super_admin'],
                'permissions' => ['read subject', 'create subject', 'update subject'],
                'tone' => 'bg-amber-500 text-slate-950',
            ],
            [
                'title' => 'Analytics',
                'description' => 'View school analytics.',
                'icon' => 'fas fa-chart-bar',
                'route' => 'analytics.index',
                'roles' => ['principal', 'admin', 'super-admin', 'super_admin'],
                'permissions' => ['read analytics dashboard'],
                'tone' => 'bg-slate-800 text-white',
            ],
        ];

        $managedClassCount = (int) ($this->teacherPanel['class_teacher_classes'] ?? 0);
        $teachingAssignmentCount = (int) ($this->teacherPanel['teaching_assignments'] ?? 0);

        return array_values(array_filter($items, function (array $item) use ($managedClassCount, $teachingAssignmentCount, $user): bool {
            if (empty($item['route']) || !Route::has($item['route'])) {
                return false;
            }

            if (!empty($item['roles']) && is_array($item['roles']) && !$user->hasAnyRole($item['roles'])) {
                return false;
            }

            if (($item['requires_managed_classes'] ?? false) && $managedClassCount === 0) {
                return false;
            }

            if (($item['requires_teaching_assignments'] ?? false) && $teachingAssignmentCount === 0) {
                return false;
            }

            if (!empty($item['permissions'])) {
                foreach ($item['permissions'] as $permission) {
                    if (is_string($permission) && $user->can($permission)) {
                        return true;
                    }
                }

                return false;
            }

            return true;
        }));
    }

    public function render()
    {
        return view('livewire.dashboard.teacher-responsibilities')
            ->layout('layouts.dashboard', [
                'breadcrumbs' => [
                    ['href' => route('dashboard'), 'text' => 'Dashboard'],
                    ['href' => route('dashboard.responsibilities'), 'text' => 'Responsibilities', 'active' => true],
                ],
            ])
            ->title('Responsibilities');
    }
}
