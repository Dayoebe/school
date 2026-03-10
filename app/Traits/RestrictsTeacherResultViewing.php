<?php

namespace App\Traits;

use App\Models\MyClass;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

trait RestrictsTeacherResultViewing
{
    protected function isRestrictedTeacherResultViewer(): bool
    {
        $user = auth()->user();

        return $user !== null
            && $user->hasRole('teacher')
            && !$user->hasAnyRole(['super-admin', 'super_admin', 'principal', 'admin']);
    }

    protected function accessibleClassTeacherClassesQuery(): Builder
    {
        $query = MyClass::query()
            ->whereHas('classGroup', function ($query) {
                $query->where('school_id', auth()->user()->school_id);
            });

        if (!$this->isRestrictedTeacherResultViewer()) {
            return $query;
        }

        $classIds = $this->accessibleClassTeacherClassIds();

        if ($classIds->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn('my_classes.id', $classIds);
    }

    protected function accessibleClassTeacherClassIds(): Collection
    {
        if (!$this->isRestrictedTeacherResultViewer()) {
            return collect();
        }

        $user = auth()->user();

        return MyClass::query()
            ->whereHas('classGroup', function ($query) use ($user) {
                $query->where('school_id', $user->school_id);
            })
            ->whereHas('teachers', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })
            ->pluck('my_classes.id')
            ->map(fn ($id) => (int) $id);
    }

    protected function accessibleSubjectTeacherClassesQuery(): Builder
    {
        $query = MyClass::query()
            ->whereHas('classGroup', function ($query) {
                $query->where('school_id', auth()->user()->school_id);
            });

        if (!$this->isRestrictedTeacherResultViewer()) {
            return $query;
        }

        $classIds = $this->accessibleSubjectTeacherClassIds();

        if ($classIds->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn('my_classes.id', $classIds);
    }

    protected function accessibleSubjectTeacherClassIds(): Collection
    {
        if (!$this->isRestrictedTeacherResultViewer()) {
            return collect();
        }

        $user = auth()->user();

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
            ->whereHas('classGroup', function ($query) use ($user) {
                $query->where('school_id', $user->school_id);
            })
            ->whereIn('my_classes.id', $specificClassIds->merge($generalClassIds)->filter()->unique()->values())
            ->pluck('my_classes.id')
            ->map(fn ($id) => (int) $id);
    }

    protected function accessibleSubjectTeacherSubjectsQuery(?int $classId = null): Builder
    {
        $query = Subject::query()
            ->where('subjects.school_id', auth()->user()->school_id);

        if ($classId) {
            $query->where(function ($query) use ($classId) {
                $query->where('subjects.my_class_id', $classId)
                    ->orWhereHas('classes', function ($classQuery) use ($classId) {
                        $classQuery->where('my_classes.id', $classId);
                    })
                    ->orWhereIn('subjects.id', function ($subQuery) use ($classId) {
                        $subQuery->from('student_subject')
                            ->where('my_class_id', $classId)
                            ->select('subject_id');
                    });
            });
        }

        if (!$this->isRestrictedTeacherResultViewer()) {
            return $query->orderBy('subjects.name')->distinct();
        }

        $user = auth()->user();

        $query->whereExists(function ($subQuery) use ($user, $classId) {
            $subQuery->select(DB::raw(1))
                ->from('subject_teacher as st')
                ->whereColumn('st.subject_id', 'subjects.id')
                ->where('st.user_id', $user->id)
                ->where('st.school_id', $user->school_id)
                ->when($classId, function ($query) use ($classId) {
                    $query->where(function ($assignmentQuery) use ($classId) {
                        $assignmentQuery->where('st.is_general', true)
                            ->orWhere('st.my_class_id', $classId);
                    });
                });
        });

        return $query->orderBy('subjects.name')->distinct();
    }

    protected function currentUserCanAccessClassOnlyResultTools(): bool
    {
        return !$this->isRestrictedTeacherResultViewer()
            || $this->accessibleClassTeacherClassIds()->isNotEmpty();
    }

    protected function currentUserCanAccessSubjectResultTools(): bool
    {
        return !$this->isRestrictedTeacherResultViewer()
            || (
                $this->accessibleSubjectTeacherClassIds()->isNotEmpty()
                && $this->accessibleSubjectTeacherSubjectsQuery()->exists()
            );
    }

    protected function currentUserCanManageTermResultSettings(): bool
    {
        return !$this->isRestrictedTeacherResultViewer();
    }

    protected function currentUserCanViewClassTeacherClass(int|string|null $classId): bool
    {
        if (!$classId) {
            return false;
        }

        if (!$this->isRestrictedTeacherResultViewer()) {
            return true;
        }

        return $this->accessibleClassTeacherClassIds()->contains((int) $classId);
    }

    protected function currentUserCanViewSubjectTeacherClass(int|string|null $classId): bool
    {
        if (!$classId) {
            return false;
        }

        if (!$this->isRestrictedTeacherResultViewer()) {
            return true;
        }

        return $this->accessibleSubjectTeacherClassIds()->contains((int) $classId);
    }

    protected function currentUserCanViewSubjectTeacherSubject(
        int|string|null $subjectId,
        int|string|null $classId = null
    ): bool {
        if (!$subjectId) {
            return false;
        }

        if (!$this->isRestrictedTeacherResultViewer()) {
            return true;
        }

        if ($classId && !$this->currentUserCanViewSubjectTeacherClass($classId)) {
            return false;
        }

        return $this->accessibleSubjectTeacherSubjectsQuery($classId ? (int) $classId : null)
            ->where('subjects.id', (int) $subjectId)
            ->exists();
    }
}
