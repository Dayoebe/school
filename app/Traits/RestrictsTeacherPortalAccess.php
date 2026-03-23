<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Support\Str;

trait RestrictsTeacherPortalAccess
{
    use ResolvesRestrictedTeacherAssignments;

    protected function resolveTeacherPortalUser(?User $user = null): ?User
    {
        return $user ?: auth()->user();
    }

    protected function isRestrictedTeacherPortalUser(?User $user = null): bool
    {
        return $this->isRestrictedTeacher($this->resolveTeacherPortalUser($user));
    }

    protected function restrictedTeacherAllowedRoutes(): array
    {
        return [
            'dashboard',
            'dashboard.responsibilities',
            'exams.index',
            'exams.create',
            'exams.store',
            'exam-slots.*',
            'exam-records.*',
            'exam-papers.*',
            'exams.result-checker',
            'results.index',
            'result',
            'result.upload.individual',
            'result.upload.bulk',
            'cbt.manage',
            'attendance.index',
            'syllabi.index',
            'syllabi.create',
            'syllabi.show',
            'timetables.index',
            'profile.edit',
            'profile.update',
            'password.change',
            'password.change.update',
            'logout',
        ];
    }

    protected function restrictedTeacherCanAccessRoute(?string $routeName, ?User $user = null): bool
    {
        $user = $this->resolveTeacherPortalUser($user);

        if (!$this->isRestrictedTeacherPortalUser($user)) {
            return true;
        }

        if ($routeName === null || $routeName === '') {
            return false;
        }

        return Str::is($this->restrictedTeacherAllowedRoutes(), $routeName);
    }
}
