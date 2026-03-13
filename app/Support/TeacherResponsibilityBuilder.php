<?php

namespace App\Support;

use App\Models\MyClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TeacherResponsibilityBuilder
{
    public function build(User $user): array
    {
        $payload = $this->emptyPayload();
        $schoolId = $user->school_id;
        $academicYearId = $user->school?->academic_year_id;

        if (!$schoolId || !$user->hasRole('teacher')) {
            return $payload;
        }

        $managedClasses = MyClass::query()
            ->whereHas('classGroup', function ($query) use ($schoolId) {
                $query->where('school_id', $schoolId);
            })
            ->whereHas('teachers', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })
            ->with('classGroup:id,name')
            ->withCount(['sections', 'subjects'])
            ->orderBy('name')
            ->get(['my_classes.id', 'my_classes.name', 'my_classes.class_group_id']);

        $managedClassIds = $managedClasses->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        $subjectAssignmentRows = DB::table('subject_teacher as st')
            ->join('subjects', 'subjects.id', '=', 'st.subject_id')
            ->where('st.user_id', $user->id)
            ->where('st.school_id', $schoolId)
            ->where('subjects.school_id', $schoolId)
            ->where('subjects.is_legacy', false)
            ->whereNull('subjects.deleted_at')
            ->select('st.subject_id', 'st.my_class_id', 'st.is_general')
            ->orderBy('subjects.name')
            ->get();

        $subjectIds = $subjectAssignmentRows->pluck('subject_id')
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        $subjects = $subjectIds->isEmpty()
            ? collect()
            : Subject::query()
                ->whereIn('subjects.id', $subjectIds)
                ->with([
                    'classes:id,name,class_group_id',
                    'classes.classGroup:id,name',
                    'myClass:id,name,class_group_id',
                    'myClass.classGroup:id,name',
                ])
                ->get()
                ->keyBy('id');

        $studentSubjectClassIds = $subjectIds->isEmpty()
            ? collect()
            : DB::table('student_subject')
                ->whereIn('subject_id', $subjectIds)
                ->whereNotNull('my_class_id')
                ->select('subject_id', 'my_class_id')
                ->distinct()
                ->get()
                ->groupBy(fn ($row) => (int) $row->subject_id)
                ->map(function (Collection $rows): Collection {
                    return $rows->pluck('my_class_id')
                        ->map(fn ($id) => (int) $id)
                        ->filter()
                        ->unique()
                        ->values();
                });

        $rawTeachingAssignments = collect();

        foreach ($subjectAssignmentRows as $assignment) {
            $subject = $subjects->get((int) $assignment->subject_id);

            if (!$subject) {
                continue;
            }

            $classIds = collect();

            if ((bool) $assignment->is_general) {
                if ($subject->my_class_id) {
                    $classIds->push((int) $subject->my_class_id);
                }

                $classIds = $classIds
                    ->merge($subject->classes->pluck('id')->map(fn ($id) => (int) $id))
                    ->merge($studentSubjectClassIds->get((int) $assignment->subject_id, collect()));
            } elseif ($assignment->my_class_id) {
                $classIds->push((int) $assignment->my_class_id);
            }

            foreach ($classIds->filter()->unique()->values() as $classId) {
                $rawTeachingAssignments->push([
                    'subject_id' => (int) $subject->id,
                    'subject_name' => $subject->name,
                    'subject_short_name' => $subject->short_name,
                    'class_id' => (int) $classId,
                    'assignment_scope' => (bool) $assignment->is_general
                        ? 'General subject assignment'
                        : 'Class-specific assignment',
                ]);
            }
        }

        $teachingClassIds = $rawTeachingAssignments->pluck('class_id')
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        $allClassIds = $managedClassIds
            ->merge($teachingClassIds)
            ->filter()
            ->unique()
            ->values();

        $responsibilityClasses = $allClassIds->isEmpty()
            ? collect()
            : MyClass::query()
                ->whereIn('my_classes.id', $allClassIds)
                ->with('classGroup:id,name')
                ->withCount(['sections', 'subjects'])
                ->get(['my_classes.id', 'my_classes.name', 'my_classes.class_group_id'])
                ->keyBy('id');

        $classStudentCounts = $this->countStudentsByClassIds($allClassIds, $academicYearId, $schoolId);
        $managedClassLookup = $managedClassIds->flip();

        $managedClassesData = $managedClasses->map(function (MyClass $class) use ($classStudentCounts): array {
            return [
                'id' => (int) $class->id,
                'name' => $class->name,
                'class_group' => $class->classGroup?->name ?? 'No class group',
                'student_count' => (int) ($classStudentCounts->get((int) $class->id, 0)),
                'section_count' => (int) ($class->sections_count ?? 0),
                'subject_count' => (int) ($class->subjects_count ?? 0),
            ];
        })->values();

        $teachingAssignments = $rawTeachingAssignments
            ->map(function (array $assignment) use ($classStudentCounts, $managedClassLookup, $responsibilityClasses): ?array {
                $class = $responsibilityClasses->get($assignment['class_id']);

                if (!$class) {
                    return null;
                }

                return array_merge($assignment, [
                    'class_name' => $class->name,
                    'class_group' => $class->classGroup?->name ?? 'No class group',
                    'student_count' => (int) ($classStudentCounts->get($assignment['class_id'], 0)),
                    'section_count' => (int) ($class->sections_count ?? 0),
                    'class_subject_count' => (int) ($class->subjects_count ?? 0),
                    'is_managed_class' => $managedClassLookup->has($assignment['class_id']),
                ]);
            })
            ->filter()
            ->unique(fn (array $assignment): string => $assignment['subject_id'] . ':' . $assignment['class_id'])
            ->sortBy(fn (array $assignment): string => strtolower($assignment['class_name'] . ' ' . $assignment['subject_name']))
            ->values();

        $teachingSubjectsByManagedClass = $teachingAssignments
            ->groupBy('class_id')
            ->map(fn (Collection $assignments): int => $assignments->pluck('subject_id')->unique()->count());

        $managedClassesData = $managedClassesData
            ->map(function (array $class) use ($teachingSubjectsByManagedClass): array {
                $class['teaching_subject_count'] = (int) ($teachingSubjectsByManagedClass->get($class['id'], 0));

                return $class;
            })
            ->values();

        $responsibilityMap = $responsibilityClasses
            ->map(function (MyClass $class) use ($classStudentCounts, $managedClassLookup, $teachingAssignments): array {
                $classAssignments = $teachingAssignments
                    ->where('class_id', (int) $class->id)
                    ->values();

                return [
                    'class_id' => (int) $class->id,
                    'class_name' => $class->name,
                    'class_group' => $class->classGroup?->name ?? 'No class group',
                    'student_count' => (int) ($classStudentCounts->get((int) $class->id, 0)),
                    'section_count' => (int) ($class->sections_count ?? 0),
                    'class_subject_count' => (int) ($class->subjects_count ?? 0),
                    'is_class_teacher' => $managedClassLookup->has((int) $class->id),
                    'teaching_subject_count' => $classAssignments->pluck('subject_id')->unique()->count(),
                    'subjects' => $classAssignments->values()->all(),
                ];
            })
            ->sortBy(fn (array $class): string => strtolower($class['class_name']))
            ->values()
            ->all();

        return array_merge($payload, [
            'class_teacher_classes' => $managedClassesData->count(),
            'teaching_classes' => $teachingAssignments->pluck('class_id')->unique()->count(),
            'assigned_subjects' => $teachingAssignments->pluck('subject_id')->unique()->count(),
            'teaching_assignments' => $teachingAssignments->count(),
            'managed_students' => (int) $managedClassesData->sum('student_count'),
            'managed_classes' => $managedClassesData->all(),
            'subject_assignments' => $teachingAssignments->all(),
            'responsibility_map' => $responsibilityMap,
        ]);
    }

    protected function countStudentsByClassIds(Collection $classIds, ?int $academicYearId, ?int $schoolId): Collection
    {
        $classIds = $classIds
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($classIds->isEmpty()) {
            return collect();
        }

        if ($academicYearId) {
            return DB::table('academic_year_student_record')
                ->select('my_class_id', DB::raw('COUNT(*) as student_count'))
                ->where('academic_year_id', $academicYearId)
                ->whereIn('my_class_id', $classIds)
                ->groupBy('my_class_id')
                ->get()
                ->mapWithKeys(fn ($row): array => [(int) $row->my_class_id => (int) $row->student_count]);
        }

        if (!$schoolId) {
            return collect();
        }

        return DB::table('student_records')
            ->join('users', 'users.id', '=', 'student_records.user_id')
            ->select('student_records.my_class_id', DB::raw('COUNT(*) as student_count'))
            ->where('users.school_id', $schoolId)
            ->whereNull('users.deleted_at')
            ->whereIn('student_records.my_class_id', $classIds)
            ->groupBy('student_records.my_class_id')
            ->get()
            ->mapWithKeys(fn ($row): array => [(int) $row->my_class_id => (int) $row->student_count]);
    }

    protected function emptyPayload(): array
    {
        return [
            'class_teacher_classes' => 0,
            'teaching_classes' => 0,
            'assigned_subjects' => 0,
            'teaching_assignments' => 0,
            'managed_students' => 0,
            'managed_classes' => [],
            'subject_assignments' => [],
            'responsibility_map' => [],
        ];
    }
}
