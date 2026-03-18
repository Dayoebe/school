<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

trait ResolvesAccessibleStudents
{
    protected function unrestrictedStudentAccessRoles(): array
    {
        return ['super-admin', 'super_admin', 'principal', 'admin', 'teacher'];
    }

    protected function canAccessAllStudentsInPortal(): bool
    {
        $user = auth()->user();

        return $user !== null
            && $user->hasAnyRole($this->unrestrictedStudentAccessRoles());
    }

    protected function isParentStudentPortalViewer(): bool
    {
        $user = auth()->user();

        return $user !== null
            && !$this->canAccessAllStudentsInPortal()
            && $user->hasRole('parent');
    }

    protected function isStudentStudentPortalViewer(): bool
    {
        $user = auth()->user();

        return $user !== null
            && !$this->canAccessAllStudentsInPortal()
            && $user->hasRole('student');
    }

    protected function isRestrictedStudentPortalViewer(): bool
    {
        return $this->isParentStudentPortalViewer() || $this->isStudentStudentPortalViewer();
    }

    protected function portalAccessibleStudentUserIds(): Collection
    {
        $user = auth()->user();

        if ($user === null || $this->canAccessAllStudentsInPortal()) {
            return collect();
        }

        if ($this->isStudentStudentPortalViewer()) {
            return collect([(int) $user->id]);
        }

        if ($this->isParentStudentPortalViewer()) {
            return $user->children()
                ->where('users.school_id', $user->school_id)
                ->whereNull('users.deleted_at')
                ->pluck('users.id')
                ->map(fn ($id) => (int) $id)
                ->values();
        }

        return collect();
    }

    protected function portalAccessibleStudentsQuery(): Builder
    {
        $schoolId = auth()->user()?->school_id;

        $query = User::query()
            ->role('student')
            ->where('school_id', $schoolId)
            ->whereNull('deleted_at');

        if ($this->canAccessAllStudentsInPortal()) {
            return $query;
        }

        $studentUserIds = $this->portalAccessibleStudentUserIds();

        if ($studentUserIds->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn('users.id', $studentUserIds);
    }
}
