<?php

namespace App\Traits;

use App\Models\Assessment\Assessment;
use App\Models\MyClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

trait RestrictsTeacherCbtManagement
{
    use ResolvesRestrictedTeacherAssignments;

    protected function resolveCbtAccessUser(?User $user = null): ?User
    {
        return $user ?: auth()->user();
    }

    protected function isRestrictedTeacherCbtManager(?User $user = null): bool
    {
        return $this->isRestrictedTeacher($this->resolveCbtAccessUser($user));
    }

    protected function accessibleCbtClassesQuery(?User $user = null): Builder
    {
        $user = $this->resolveCbtAccessUser($user);
        $query = MyClass::query();

        if (!$user?->school_id) {
            return $query->whereRaw('1 = 0');
        }

        $query->whereHas('classGroup', function (Builder $classGroupQuery) use ($user) {
            $classGroupQuery->where('school_id', $user->school_id);
        });

        if (!$this->isRestrictedTeacherCbtManager($user)) {
            return $query;
        }

        $classIds = $this->accessibleCbtClassIds($user);

        if ($classIds->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn('my_classes.id', $classIds);
    }

    protected function accessibleCbtClassIds(?User $user = null): Collection
    {
        $user = $this->resolveCbtAccessUser($user);

        if (!$user?->school_id || !$this->isRestrictedTeacherCbtManager($user)) {
            return collect();
        }

        return $this->restrictedTeacherSubjectTeacherClassIds($user);
    }

    protected function accessibleCbtSubjectsQuery(?int $classId = null, ?User $user = null): Builder
    {
        $user = $this->resolveCbtAccessUser($user);
        $query = Subject::query();

        if (!$user?->school_id) {
            return $query->whereRaw('1 = 0');
        }

        $query->where('subjects.school_id', $user->school_id);

        if ($classId) {
            $query->where(function (Builder $classScope) use ($classId) {
                $classScope->where('subjects.my_class_id', $classId)
                    ->orWhereHas('classes', function (Builder $classQuery) use ($classId) {
                        $classQuery->where('my_classes.id', $classId);
                    })
                    ->orWhereIn('subjects.id', function ($subQuery) use ($classId) {
                        $subQuery->from('student_subject')
                            ->where('my_class_id', $classId)
                            ->select('subject_id');
                    });
            });
        }

        if (!$this->isRestrictedTeacherCbtManager($user)) {
            return $query->orderBy('subjects.name')->distinct();
        }

        $classIds = $this->accessibleCbtClassIds($user);

        if ($classIds->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        if ($classId !== null && !$classIds->contains((int) $classId)) {
            return $query->whereRaw('1 = 0');
        }

        $query->whereExists(function ($subQuery) use ($user, $classId, $classIds) {
            $subQuery->select(DB::raw(1))
                ->from('subject_teacher as st')
                ->whereColumn('st.subject_id', 'subjects.id')
                ->where('st.user_id', $user->id)
                ->where('st.school_id', $user->school_id)
                ->where(function ($assignmentQuery) use ($classId, $classIds) {
                    $assignmentQuery->where('st.is_general', true);

                    if ($classId !== null) {
                        $assignmentQuery->orWhere('st.my_class_id', (int) $classId);

                        return;
                    }

                    $assignmentQuery->orWhereIn('st.my_class_id', $classIds);
                });
        });

        return $query->orderBy('subjects.name')->distinct();
    }

    protected function accessibleCbtAssessmentsQuery(?User $user = null): Builder
    {
        $user = $this->resolveCbtAccessUser($user);
        $query = Assessment::query()->standaloneCBT();

        if (!$user?->school_id) {
            return $query->whereRaw('1 = 0');
        }

        $query->forSchool($user->school_id);
        $query->forCurrentSchoolAcademicPeriod($user);

        if (!$this->isRestrictedTeacherCbtManager($user)) {
            return $query;
        }

        $classIds = $this->accessibleCbtClassIds($user);

        if ($classIds->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return $query
            ->whereIn('assessments.course_id', $classIds)
            ->whereNotNull('assessments.lesson_id')
            ->whereExists(function ($subQuery) use ($user) {
                $subQuery->select(DB::raw(1))
                    ->from('subject_teacher as st')
                    ->whereColumn('st.subject_id', 'assessments.lesson_id')
                    ->where('st.user_id', $user->id)
                    ->where('st.school_id', $user->school_id)
                    ->where(function ($assignmentQuery) {
                        $assignmentQuery->where('st.is_general', true)
                            ->orWhereColumn('st.my_class_id', 'assessments.course_id');
                    });
            });
    }

    protected function currentUserCanManageCbtClass(int|string|null $classId, ?User $user = null): bool
    {
        if (!$classId) {
            return false;
        }

        return $this->accessibleCbtClassesQuery($user)
            ->where('my_classes.id', (int) $classId)
            ->exists();
    }

    protected function currentUserCanManageCbtSubject(
        int|string|null $subjectId,
        int|string|null $classId,
        ?User $user = null
    ): bool {
        if (!$subjectId || !$classId) {
            return false;
        }

        return $this->accessibleCbtSubjectsQuery((int) $classId, $user)
            ->where('subjects.id', (int) $subjectId)
            ->exists();
    }

    protected function currentUserCanManageCbtAssessment(int|string|null $assessmentId, ?User $user = null): bool
    {
        if (!$assessmentId) {
            return false;
        }

        return $this->accessibleCbtAssessmentsQuery($user)
            ->where('assessments.id', (int) $assessmentId)
            ->exists();
    }
}
