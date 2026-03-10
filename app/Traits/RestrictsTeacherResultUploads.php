<?php

namespace App\Traits;

use App\Models\MyClass;
use App\Models\StudentRecord;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

trait RestrictsTeacherResultUploads
{
    protected function isRestrictedTeacherResultUploader(): bool
    {
        $user = auth()->user();

        return $user !== null
            && $user->hasRole('teacher')
            && !$user->hasAnyRole(['super-admin', 'super_admin', 'principal', 'admin']);
    }

    protected function accessibleResultUploadClassesQuery(?int $academicYearId = null): Builder
    {
        $query = MyClass::query()
            ->whereHas('classGroup', function ($query) {
                $query->where('school_id', auth()->user()->school_id);
            });

        if (!$this->isRestrictedTeacherResultUploader()) {
            return $query;
        }

        $classIds = $this->accessibleResultUploadClassIds($academicYearId);

        if ($classIds->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn('my_classes.id', $classIds);
    }

    protected function accessibleResultUploadClassIds(?int $academicYearId = null): Collection
    {
        if (!$this->isRestrictedTeacherResultUploader()) {
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
            ->pluck('my_classes.id');
    }

    protected function accessibleResultUploadSubjectsQuery(?int $classId = null, ?int $academicYearId = null): Builder
    {
        $query = Subject::query()
            ->where('subjects.school_id', auth()->user()->school_id);

        if ($classId) {
            $query->where(function ($query) use ($classId, $academicYearId) {
                $query->where('subjects.my_class_id', $classId)
                    ->orWhereHas('classes', function ($classQuery) use ($classId) {
                        $classQuery->where('my_classes.id', $classId);
                    })
                    ->orWhereIn('subjects.id', function ($subQuery) use ($classId) {
                        $subQuery->from('student_subject')
                            ->where('my_class_id', $classId)
                            ->select('subject_id');
                    })
                    ->orWhereIn('subjects.id', function ($subQuery) use ($classId, $academicYearId) {
                        $subQuery->from('student_subject as ss')
                            ->join('academic_year_student_record as aysr', 'aysr.student_record_id', '=', 'ss.student_record_id')
                            ->where('aysr.my_class_id', $classId)
                            ->when($academicYearId, function ($query) use ($academicYearId) {
                                $query->where('aysr.academic_year_id', $academicYearId);
                            })
                            ->select('ss.subject_id');
                    });
            });
        }

        if (!$this->isRestrictedTeacherResultUploader()) {
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

    protected function currentUserCanUploadResultClass(int|string|null $classId, ?int $academicYearId = null): bool
    {
        if (!$classId) {
            return false;
        }

        return $this->accessibleResultUploadClassesQuery($academicYearId)
            ->where('my_classes.id', (int) $classId)
            ->exists();
    }

    protected function currentUserCanUploadResultSubject(
        int|string|null $subjectId,
        int|string|null $classId,
        ?int $academicYearId = null
    ): bool {
        if (!$subjectId || !$classId) {
            return false;
        }

        return $this->accessibleResultUploadSubjectsQuery((int) $classId, $academicYearId)
            ->where('subjects.id', (int) $subjectId)
            ->exists();
    }

    protected function currentUserCanUploadResultStudent(
        int|string|null $studentRecordId,
        int|string|null $classId,
        ?int $academicYearId = null,
        int|string|null $sectionId = null
    ): bool {
        if (!$studentRecordId || !$classId) {
            return false;
        }

        if (!$this->currentUserCanUploadResultClass($classId, $academicYearId)) {
            return false;
        }

        $studentRecordId = (int) $studentRecordId;
        $classId = (int) $classId;
        $sectionId = $sectionId ? (int) $sectionId : null;

        $studentExists = StudentRecord::query()
            ->where('student_records.id', $studentRecordId)
            ->whereHas('user', function ($query) {
                $query->where('school_id', auth()->user()->school_id)
                    ->whereNull('deleted_at');
            })
            ->exists();

        if (!$studentExists) {
            return false;
        }

        if ($academicYearId) {
            $hasAcademicYearRecord = DB::table('academic_year_student_record')
                ->where('student_record_id', $studentRecordId)
                ->where('academic_year_id', $academicYearId)
                ->exists();

            if ($hasAcademicYearRecord) {
                return DB::table('academic_year_student_record')
                    ->where('student_record_id', $studentRecordId)
                    ->where('academic_year_id', $academicYearId)
                    ->where('my_class_id', $classId)
                    ->when($sectionId, function ($query) use ($sectionId) {
                        $query->where('section_id', $sectionId);
                    })
                    ->exists();
            }
        }

        return StudentRecord::query()
            ->where('id', $studentRecordId)
            ->where('my_class_id', $classId)
            ->when($sectionId, function ($query) use ($sectionId) {
                $query->where('section_id', $sectionId);
            })
            ->whereHas('user', function ($query) {
                $query->where('school_id', auth()->user()->school_id)
                    ->whereNull('deleted_at');
            })
            ->exists();
    }
}
