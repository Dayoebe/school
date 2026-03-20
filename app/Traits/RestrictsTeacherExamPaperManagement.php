<?php

namespace App\Traits;

use App\Models\MyClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

trait RestrictsTeacherExamPaperManagement
{
    use ResolvesRestrictedTeacherAssignments;

    protected function resolveExamPaperAccessUser(?User $user = null): ?User
    {
        return $user ?: auth()->user();
    }

    protected function isRestrictedTeacherExamPaperManager(?User $user = null): bool
    {
        return $this->isRestrictedTeacher($this->resolveExamPaperAccessUser($user));
    }

    protected function accessibleExamPaperClassIds(?User $user = null): Collection
    {
        $user = $this->resolveExamPaperAccessUser($user);

        if (!$user?->school_id || !$this->isRestrictedTeacherExamPaperManager($user)) {
            return collect();
        }

        return $this->restrictedTeacherSubjectTeacherClassIds($user);
    }

    protected function accessibleExamPaperClassesQuery(?User $user = null): Builder
    {
        $user = $this->resolveExamPaperAccessUser($user);
        $query = MyClass::query();

        if (!$user?->school_id) {
            return $query->whereRaw('1 = 0');
        }

        $query->whereHas('classGroup', function (Builder $classGroupQuery) use ($user) {
            $classGroupQuery->where('school_id', $user->school_id);
        });

        if (!$this->isRestrictedTeacherExamPaperManager($user)) {
            return $query;
        }

        $classIds = $this->accessibleExamPaperClassIds($user);

        if ($classIds->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn('my_classes.id', $classIds);
    }

    protected function accessibleExamPaperSubjectsQuery(?int $classId = null, ?User $user = null): Builder
    {
        $user = $this->resolveExamPaperAccessUser($user);
        $query = Subject::query()->where('subjects.school_id', $user?->school_id);

        if (!$user?->school_id) {
            return $query->whereRaw('1 = 0');
        }

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

        if (!$this->isRestrictedTeacherExamPaperManager($user)) {
            return $query->orderBy('subjects.name')->distinct();
        }

        $classIds = $this->accessibleExamPaperClassIds($user);

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

    protected function currentUserCanManageExamPaperClass(int|string|null $classId, ?User $user = null): bool
    {
        if (!$classId) {
            return false;
        }

        return $this->accessibleExamPaperClassesQuery($user)
            ->where('my_classes.id', (int) $classId)
            ->exists();
    }

    protected function currentUserCanManageExamPaperSubject(
        int|string|null $subjectId,
        int|string|null $classId,
        ?User $user = null
    ): bool {
        if (!$subjectId || !$classId) {
            return false;
        }

        return $this->accessibleExamPaperSubjectsQuery((int) $classId, $user)
            ->where('subjects.id', (int) $subjectId)
            ->exists();
    }
}
