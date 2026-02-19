<?php

namespace App\Traits;

use App\Models\School;
use App\Support\SchoolContext;
use Illuminate\Database\Eloquent\Builder;

trait InSchool
{
    protected static function bootInSchool(): void
    {
        static::addGlobalScope('school', function (Builder $query): void {
            if (SchoolContext::hasAuthenticatedUserWithoutSchool()) {
                $query->whereRaw('1 = 0');
                return;
            }

            $schoolId = SchoolContext::id();
            if ($schoolId !== null) {
                $query->where($query->qualifyColumn('school_id'), $schoolId);
            }
        });

        static::creating(function ($model): void {
            if (!empty($model->school_id)) {
                return;
            }

            $schoolId = SchoolContext::id();
            if ($schoolId !== null) {
                $model->school_id = $schoolId;
            }
        });
    }

    /**
     * Scope by provided school or current authenticated user's school.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeInSchool($query, $school = null): Builder
    {
        if ($school instanceof School) {
            $schoolId = $school->id;
        } elseif (is_numeric($school)) {
            $schoolId = (int) $school;
        } else {
            $schoolId = SchoolContext::id();
        }

        if ($schoolId === null) {
            if (SchoolContext::hasAuthenticatedUserWithoutSchool()) {
                return $query->whereRaw('1 = 0');
            }

            return $query;
        }

        return $query->where($query->qualifyColumn('school_id'), $schoolId);
    }

    public function scopeAllSchools(Builder $query): Builder
    {
        return $query->withoutGlobalScope('school');
    }
}
