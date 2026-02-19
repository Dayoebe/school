<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;

class SchoolContext
{
    public static function id(): ?int
    {
        if (!Auth::hasUser()) {
            return null;
        }

        $schoolId = Auth::user()?->school_id;

        return $schoolId ? (int) $schoolId : null;
    }

    public static function hasAuthenticatedUserWithoutSchool(): bool
    {
        return Auth::hasUser() && Auth::user()?->school_id === null;
    }
}

