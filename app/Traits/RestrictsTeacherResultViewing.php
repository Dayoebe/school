<?php

namespace App\Traits;

use App\Models\MyClass;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

trait RestrictsTeacherResultViewing
{
    use ResolvesRestrictedTeacherAssignments;

    protected function currentUserIsResultStaff(): bool
    {
        $user = auth()->user();

        return $user !== null
            && $user->hasAnyRole(['super-admin', 'super_admin', 'principal', 'admin', 'teacher']);
    }

    protected function isRestrictedTeacherResultViewer(): bool
    {
        return $this->isRestrictedTeacher();
    }

    protected function accessibleClassTeacherClassesQuery(): Builder
    {
        $query = MyClass::query()
            ->whereHas('classGroup', function ($query) {
                $query->where('school_id', auth()->user()->school_id);
            });

        if (!$this->currentUserIsResultStaff()) {
            return $query->whereRaw('1 = 0');
        }

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
        if (!$this->currentUserIsResultStaff()) {
            return collect();
        }

        if (!$this->isRestrictedTeacherResultViewer()) {
            return collect();
        }

        return $this->restrictedTeacherClassTeacherClassIds();
    }

    protected function accessibleSubjectTeacherClassesQuery(): Builder
    {
        $query = MyClass::query()
            ->whereHas('classGroup', function ($query) {
                $query->where('school_id', auth()->user()->school_id);
            });

        if (!$this->currentUserIsResultStaff()) {
            return $query->whereRaw('1 = 0');
        }

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
        if (!$this->currentUserIsResultStaff()) {
            return collect();
        }

        if (!$this->isRestrictedTeacherResultViewer()) {
            return collect();
        }

        return $this->restrictedTeacherClassTeacherClassIds();
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

        if (!$this->currentUserIsResultStaff()) {
            return $query->whereRaw('1 = 0')->orderBy('subjects.name')->distinct();
        }

        if (!$this->isRestrictedTeacherResultViewer()) {
            return $query->orderBy('subjects.name')->distinct();
        }

        return $this->restrictedTeacherSubjectsQuery($classId);
    }

    protected function currentUserCanAccessClassOnlyResultTools(): bool
    {
        if (!$this->currentUserIsResultStaff()) {
            return false;
        }

        return !$this->isRestrictedTeacherResultViewer()
            || $this->accessibleClassTeacherClassIds()->isNotEmpty();
    }

    protected function currentUserCanAccessSubjectResultTools(): bool
    {
        if (!$this->currentUserIsResultStaff()) {
            return false;
        }

        return !$this->isRestrictedTeacherResultViewer()
            || (
                $this->accessibleSubjectTeacherClassIds()->isNotEmpty()
                && $this->accessibleSubjectTeacherSubjectsQuery()->exists()
            );
    }

    protected function currentUserCanManageTermResultSettings(): bool
    {
        return $this->currentUserIsResultStaff() && !$this->isRestrictedTeacherResultViewer();
    }

    protected function currentUserCanViewClassTeacherClass(int|string|null $classId): bool
    {
        if (!$classId || !$this->currentUserIsResultStaff()) {
            return false;
        }

        if (!$this->isRestrictedTeacherResultViewer()) {
            return true;
        }

        return $this->accessibleClassTeacherClassIds()->contains((int) $classId);
    }

    protected function currentUserCanViewSubjectTeacherClass(int|string|null $classId): bool
    {
        if (!$classId || !$this->currentUserIsResultStaff()) {
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
        if (!$subjectId || !$this->currentUserIsResultStaff()) {
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
