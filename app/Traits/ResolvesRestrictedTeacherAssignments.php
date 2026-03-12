<?php

namespace App\Traits;

use App\Models\MyClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

trait ResolvesRestrictedTeacherAssignments
{
    protected function resolveRestrictedTeacherUser(?User $user = null): ?User
    {
        return $user ?: auth()->user();
    }

    protected function isRestrictedTeacher(?User $user = null): bool
    {
        $user = $this->resolveRestrictedTeacherUser($user);

        return $user !== null
            && $user->hasRole('teacher')
            && !$user->hasAnyRole(['super-admin', 'super_admin', 'principal', 'admin']);
    }

    protected function restrictedTeacherClassTeacherClassIds(?User $user = null): Collection
    {
        $user = $this->resolveRestrictedTeacherUser($user);

        if (!$user?->school_id || !$this->isRestrictedTeacher($user)) {
            return collect();
        }

        return MyClass::query()
            ->whereHas('classGroup', function (Builder $query) use ($user) {
                $query->where('school_id', $user->school_id);
            })
            ->whereHas('teachers', function (Builder $query) use ($user) {
                $query->where('users.id', $user->id);
            })
            ->pluck('my_classes.id')
            ->map(fn ($id) => (int) $id);
    }

    protected function restrictedTeacherSubjectTeacherClassIds(?User $user = null): Collection
    {
        $user = $this->resolveRestrictedTeacherUser($user);

        if (!$user?->school_id || !$this->isRestrictedTeacher($user)) {
            return collect();
        }

        $specificClassIds = DB::table('subject_teacher')
            ->where('user_id', $user->id)
            ->where('school_id', $user->school_id)
            ->where('is_general', false)
            ->whereNotNull('my_class_id')
            ->pluck('my_class_id');

        $generalSubjectIds = DB::table('subject_teacher')
            ->where('user_id', $user->id)
            ->where('school_id', $user->school_id)
            ->where('is_general', true)
            ->pluck('subject_id');

        $generalClassIds = collect();

        if ($generalSubjectIds->isNotEmpty()) {
            $generalClassIds = $generalClassIds
                ->merge(
                    DB::table('class_subject')
                        ->whereIn('subject_id', $generalSubjectIds)
                        ->pluck('my_class_id')
                )
                ->merge(
                    Subject::query()
                        ->whereIn('id', $generalSubjectIds)
                        ->where('school_id', $user->school_id)
                        ->whereNotNull('my_class_id')
                        ->pluck('my_class_id')
                )
                ->merge(
                    DB::table('student_subject')
                        ->whereIn('subject_id', $generalSubjectIds)
                        ->whereNotNull('my_class_id')
                        ->pluck('my_class_id')
                );
        }

        return MyClass::query()
            ->whereHas('classGroup', function (Builder $query) use ($user) {
                $query->where('school_id', $user->school_id);
            })
            ->whereIn(
                'my_classes.id',
                $specificClassIds->merge($generalClassIds)->filter()->unique()->values()
            )
            ->pluck('my_classes.id')
            ->map(fn ($id) => (int) $id);
    }

    protected function restrictedTeacherAllClassIds(?User $user = null): Collection
    {
        $user = $this->resolveRestrictedTeacherUser($user);

        if (!$this->isRestrictedTeacher($user)) {
            return collect();
        }

        return $this->restrictedTeacherClassTeacherClassIds($user)
            ->filter()
            ->unique()
            ->values();
    }

    protected function restrictedTeacherSubjectsQuery(?int $classId = null, ?User $user = null): Builder
    {
        $user = $this->resolveRestrictedTeacherUser($user);
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

        if (!$this->isRestrictedTeacher($user)) {
            return $query->orderBy('subjects.name')->distinct();
        }

        $classTeacherClassIds = $this->restrictedTeacherClassTeacherClassIds($user);

        if ($classTeacherClassIds->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        if ($classId !== null && !$classTeacherClassIds->contains((int) $classId)) {
            return $query->whereRaw('1 = 0');
        }

        $specificSubjectIds = DB::table('subject_teacher')
            ->where('user_id', $user->id)
            ->where('school_id', $user->school_id)
            ->where('is_general', false)
            ->when($classId !== null, function ($assignmentQuery) use ($classId) {
                $assignmentQuery->where('my_class_id', (int) $classId);
            }, function ($assignmentQuery) use ($classTeacherClassIds) {
                $assignmentQuery->whereIn('my_class_id', $classTeacherClassIds);
            })
            ->pluck('subject_id');

        $generalSubjectIds = DB::table('subject_teacher')
            ->where('user_id', $user->id)
            ->where('school_id', $user->school_id)
            ->where('is_general', true)
            ->pluck('subject_id');

        $allowedGeneralSubjectIds = collect();

        if ($generalSubjectIds->isNotEmpty()) {
            $relevantClassIds = $classId !== null
                ? collect([(int) $classId])
                : $classTeacherClassIds;

            $allowedGeneralSubjectIds = Subject::query()
                ->where('subjects.school_id', $user->school_id)
                ->whereIn('subjects.id', $generalSubjectIds)
                ->where(function (Builder $subjectScope) use ($relevantClassIds) {
                    $subjectScope->whereIn('subjects.my_class_id', $relevantClassIds)
                        ->orWhereHas('classes', function (Builder $classQuery) use ($relevantClassIds) {
                            $classQuery->whereIn('my_classes.id', $relevantClassIds);
                        })
                        ->orWhereIn('subjects.id', function ($subQuery) use ($relevantClassIds) {
                            $subQuery->from('student_subject')
                                ->whereIn('my_class_id', $relevantClassIds)
                                ->select('subject_id');
                        });
                })
                ->pluck('subjects.id');
        }

        $allowedSubjectIds = $specificSubjectIds
            ->merge($allowedGeneralSubjectIds)
            ->filter()
            ->unique()
            ->values();

        if ($allowedSubjectIds->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        $query->whereIn('subjects.id', $allowedSubjectIds);

        return $query->orderBy('subjects.name')->distinct();
    }

    protected function restrictedTeacherCanAccessClassTeacherClass(
        int|string|null $classId,
        ?User $user = null
    ): bool {
        if (!$classId) {
            return false;
        }

        $user = $this->resolveRestrictedTeacherUser($user);

        if (!$user) {
            return false;
        }

        if (!$this->isRestrictedTeacher($user)) {
            return true;
        }

        return $this->restrictedTeacherClassTeacherClassIds($user)->contains((int) $classId);
    }

    protected function restrictedTeacherCanAccessSubjectClass(
        int|string|null $classId,
        ?User $user = null
    ): bool {
        return $this->restrictedTeacherCanAccessClassTeacherClass($classId, $user);
    }

    protected function restrictedTeacherCanAccessSubjectInClass(
        int|string|null $subjectId,
        int|string|null $classId = null,
        ?User $user = null
    ): bool {
        if (!$subjectId) {
            return false;
        }

        $user = $this->resolveRestrictedTeacherUser($user);

        if (!$user) {
            return false;
        }

        if (!$this->isRestrictedTeacher($user)) {
            return true;
        }

        if ($classId && !$this->restrictedTeacherCanAccessSubjectClass($classId, $user)) {
            return false;
        }

        return $this->restrictedTeacherSubjectsQuery($classId ? (int) $classId : null, $user)
            ->where('subjects.id', (int) $subjectId)
            ->exists();
    }
}
