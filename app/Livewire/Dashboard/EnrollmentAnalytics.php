<?php

namespace App\Livewire\Dashboard;

use App\Models\AcademicYear;
use App\Models\AdmissionRegistration;
use App\Models\MyClass;
use App\Models\StudentRecord;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class EnrollmentAnalytics extends Component
{
    public int $months = 6;

    public string $selectedAcademicYearId = '';

    public string $selectedClassId = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('read analytics dashboard'), 403);

        $school = auth()->user()?->school;

        $this->selectedAcademicYearId = (string) (
            $school?->academic_year_id
            ?? AcademicYear::query()
                ->where('school_id', $school?->id)
                ->orderByDesc('start_year')
                ->value('id')
            ?? ''
        );
    }

    public function updatedMonths($value): void
    {
        $value = (int) $value;
        $this->months = in_array($value, [3, 6, 12], true) ? $value : 6;
    }

    protected function reportingStart(): Carbon
    {
        return now()->startOfMonth()->subMonths(max(1, $this->months) - 1);
    }

    protected function reportingEnd(): Carbon
    {
        return now()->endOfMonth();
    }

    protected function academicYears(?int $schoolId): Collection
    {
        if (!$schoolId) {
            return collect();
        }

        return AcademicYear::query()
            ->where('school_id', $schoolId)
            ->orderByDesc('start_year')
            ->get(['id', 'start_year', 'stop_year']);
    }

    protected function classes(?int $schoolId): Collection
    {
        if (!$schoolId) {
            return collect();
        }

        return MyClass::query()
            ->whereHas('classGroup', fn ($query) => $query->where('school_id', $schoolId))
            ->instructional()
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    protected function monthKeys(Carbon $start, Carbon $end): array
    {
        return collect(CarbonPeriod::create($start->copy()->startOfMonth(), '1 month', $end->copy()->startOfMonth()))
            ->map(fn (Carbon $month): string => $month->format('Y-m'))
            ->values()
            ->all();
    }

    protected function peopleMetrics(
        ?int $schoolId,
        Collection $activeStudentRecordIds,
        Carbon $start,
        Carbon $end
    ): array {
        if (!$schoolId) {
            return [
                'active_students' => 0,
                'total_students' => 0,
                'inactive_students' => 0,
                'graduated_students' => 0,
                'new_students' => 0,
                'linked_to_parent' => 0,
                'parent_link_rate' => 0.0,
            ];
        }

        $totalStudents = User::query()->where('school_id', $schoolId)->role('student')->count();
        $activeStudents = $activeStudentRecordIds->count();
        $graduatedStudents = User::query()
            ->where('school_id', $schoolId)
            ->role('student')
            ->whereHas('studentRecord', fn ($query) => $query->where('is_graduated', true))
            ->count();

        $newStudents = $activeStudentRecordIds->isEmpty()
            ? 0
            : StudentRecord::query()
                ->whereIn('id', $activeStudentRecordIds->all())
                ->where(function ($query) use ($start, $end): void {
                    $query->whereBetween('admission_date', [$start->toDateString(), $end->toDateString()])
                        ->orWhereBetween('created_at', [$start, $end]);
                })
                ->count();

        $activeStudentUserIds = $activeStudentRecordIds->isEmpty()
            ? collect()
            : StudentRecord::query()
                ->whereIn('id', $activeStudentRecordIds->all())
                ->whereNotNull('user_id')
                ->pluck('user_id');

        $linkedToParent = $activeStudentUserIds->isEmpty()
            ? 0
            : DB::table('parent_records')
                ->whereIn('student_id', $activeStudentUserIds->all())
                ->distinct()
                ->count('student_id');

        return [
            'active_students' => $activeStudents,
            'total_students' => $totalStudents,
            'inactive_students' => max(0, $totalStudents - $activeStudents),
            'graduated_students' => $graduatedStudents,
            'new_students' => $newStudents,
            'linked_to_parent' => $linkedToParent,
            'parent_link_rate' => $activeStudents > 0 ? round(($linkedToParent / $activeStudents) * 100, 1) : 0.0,
        ];
    }

    protected function admissionMetrics(?int $schoolId, ?int $classId, Carbon $start, Carbon $end): array
    {
        if (!$schoolId) {
            return [
                'total' => 0,
                'period_total' => 0,
                'pending' => 0,
                'reviewed' => 0,
                'approved' => 0,
                'rejected' => 0,
                'enrolled' => 0,
                'conversion_rate' => 0.0,
            ];
        }

        $base = AdmissionRegistration::query()
            ->where('school_id', $schoolId)
            ->when($classId, fn ($query) => $query->where('my_class_id', $classId));
        $total = (clone $base)->count();
        $enrolled = (clone $base)->whereNotNull('enrolled_at')->count();

        return [
            'total' => $total,
            'period_total' => (clone $base)->whereBetween('created_at', [$start, $end])->count(),
            'pending' => (clone $base)->where('status', 'pending')->count(),
            'reviewed' => (clone $base)->where('status', 'reviewed')->count(),
            'approved' => (clone $base)->where('status', 'approved')->count(),
            'rejected' => (clone $base)->where('status', 'rejected')->count(),
            'enrolled' => $enrolled,
            'conversion_rate' => $total > 0 ? round(($enrolled / $total) * 100, 1) : 0.0,
        ];
    }

    protected function classDistribution(?int $schoolId, ?int $academicYearId, Collection $activeStudentRecordIds): array
    {
        if (!$schoolId || !$academicYearId || $activeStudentRecordIds->isEmpty()) {
            return [];
        }

        return DB::table('student_records as sr')
            ->join('users as u', 'u.id', '=', 'sr.user_id')
            ->leftJoin('academic_year_student_record as aysr', function ($join) use ($academicYearId): void {
                $join->on('aysr.student_record_id', '=', 'sr.id')
                    ->where('aysr.academic_year_id', '=', $academicYearId);
            })
            ->join('my_classes as c', 'c.id', '=', DB::raw('COALESCE(aysr.my_class_id, sr.my_class_id)'))
            ->where('u.school_id', $schoolId)
            ->whereNull('u.deleted_at')
            ->whereIn('sr.id', $activeStudentRecordIds->all())
            ->select('c.name', DB::raw('COUNT(DISTINCT sr.id) as total'))
            ->groupBy('c.id', 'c.name')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row): array => [
                'name' => (string) $row->name,
                'total' => (int) $row->total,
            ])
            ->all();
    }

    protected function sectionDistribution(?int $schoolId, ?int $academicYearId, Collection $activeStudentRecordIds): array
    {
        if (!$schoolId || !$academicYearId || $activeStudentRecordIds->isEmpty()) {
            return [];
        }

        return DB::table('student_records as sr')
            ->join('users as u', 'u.id', '=', 'sr.user_id')
            ->leftJoin('academic_year_student_record as aysr', function ($join) use ($academicYearId): void {
                $join->on('aysr.student_record_id', '=', 'sr.id')
                    ->where('aysr.academic_year_id', '=', $academicYearId);
            })
            ->leftJoin('sections as s', 's.id', '=', DB::raw('COALESCE(aysr.section_id, sr.section_id)'))
            ->where('u.school_id', $schoolId)
            ->whereNull('u.deleted_at')
            ->whereIn('sr.id', $activeStudentRecordIds->all())
            ->select(DB::raw("COALESCE(s.name, 'No Section') as name"), DB::raw('COUNT(DISTINCT sr.id) as total'))
            ->groupBy(DB::raw("COALESCE(s.name, 'No Section')"))
            ->orderByDesc('total')
            ->limit(8)
            ->get()
            ->map(fn ($row): array => [
                'name' => (string) $row->name,
                'total' => (int) $row->total,
            ])
            ->all();
    }

    protected function genderDistribution(?int $schoolId, Collection $activeStudentRecordIds): array
    {
        if (!$schoolId || $activeStudentRecordIds->isEmpty()) {
            return [];
        }

        return DB::table('student_records as sr')
            ->join('users as u', 'u.id', '=', 'sr.user_id')
            ->where('u.school_id', $schoolId)
            ->whereNull('u.deleted_at')
            ->whereIn('sr.id', $activeStudentRecordIds->all())
            ->select(DB::raw("COALESCE(NULLIF(LOWER(TRIM(u.gender)), ''), 'unspecified') as gender"), DB::raw('COUNT(DISTINCT sr.id) as total'))
            ->groupBy(DB::raw("COALESCE(NULLIF(LOWER(TRIM(u.gender)), ''), 'unspecified')"))
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row): array => [
                'name' => ucfirst((string) $row->gender),
                'total' => (int) $row->total,
            ])
            ->all();
    }

    protected function monthlyTrend(
        ?int $schoolId,
        ?int $classId,
        Collection $activeStudentRecordIds,
        array $monthKeys,
        Carbon $start,
        Carbon $end
    ): array {
        $students = array_fill_keys($monthKeys, 0);
        $applications = array_fill_keys($monthKeys, 0);

        if (!$schoolId) {
            return [
                'students' => $students,
                'applications' => $applications,
            ];
        }

        if ($activeStudentRecordIds->isNotEmpty()) {
            $studentRows = DB::table('student_records as sr')
                ->join('users as u', 'u.id', '=', 'sr.user_id')
                ->where('u.school_id', $schoolId)
                ->whereIn('sr.id', $activeStudentRecordIds->all())
                ->where(function ($query) use ($start, $end): void {
                    $query->whereBetween('sr.admission_date', [$start->toDateString(), $end->toDateString()])
                        ->orWhereBetween('sr.created_at', [$start, $end]);
                })
                ->get(['sr.admission_date', 'sr.created_at']);

            foreach ($studentRows as $row) {
                $date = $row->admission_date ?: $row->created_at;
                if (!$date) {
                    continue;
                }

                $key = Carbon::parse($date)->format('Y-m');
                if (array_key_exists($key, $students)) {
                    $students[$key]++;
                }
            }
        }

        $applicationRows = AdmissionRegistration::query()
            ->where('school_id', $schoolId)
            ->when($classId, fn ($query) => $query->where('my_class_id', $classId))
            ->whereBetween('created_at', [$start, $end])
            ->get(['created_at']);

        foreach ($applicationRows as $row) {
            $key = Carbon::parse($row->created_at)->format('Y-m');
            if (array_key_exists($key, $applications)) {
                $applications[$key]++;
            }
        }

        return [
            'students' => $students,
            'applications' => $applications,
        ];
    }

    protected function signals(array $people, array $admissions): array
    {
        $signals = [];

        if (($people['active_students'] ?? 0) === 0) {
            $signals[] = ['tone' => 'amber', 'title' => 'No active enrollment', 'body' => 'Set academic year records or enroll students before enrollment analytics can stabilise.'];
        }

        if (($people['new_students'] ?? 0) === 0 && ($admissions['period_total'] ?? 0) === 0) {
            $signals[] = ['tone' => 'sky', 'title' => 'No recent intake activity', 'body' => 'No new students or admission applications were recorded in this window.'];
        }

        if (($admissions['pending'] ?? 0) > 0) {
            $signals[] = ['tone' => 'amber', 'title' => 'Admissions pending', 'body' => number_format((int) $admissions['pending']) . ' application(s) still need review.'];
        }

        if (($admissions['total'] ?? 0) > 0 && ($admissions['conversion_rate'] ?? 0) < 30) {
            $signals[] = ['tone' => 'rose', 'title' => 'Low application conversion', 'body' => 'Current conversion is ' . number_format((float) $admissions['conversion_rate'], 1) . '%.'];
        }

        if (($people['active_students'] ?? 0) > 0 && ($people['parent_link_rate'] ?? 0) < 80) {
            $signals[] = ['tone' => 'sky', 'title' => 'Parent links incomplete', 'body' => 'Only ' . number_format((float) $people['parent_link_rate'], 1) . '% of active students are linked to a parent account.'];
        }

        if (($people['inactive_students'] ?? 0) > 0) {
            $signals[] = ['tone' => 'amber', 'title' => 'Inactive student accounts', 'body' => number_format((int) $people['inactive_students']) . ' student account(s) are outside the active enrollment count.'];
        }

        if ($signals === []) {
            $signals[] = ['tone' => 'emerald', 'title' => 'Enrollment data is healthy', 'body' => 'No major intake, parent-link, or student-status risks were detected.'];
        }

        return array_slice($signals, 0, 6);
    }

    public function render()
    {
        $user = auth()->user();
        $school = $user?->school;
        $schoolId = $school?->id;
        $academicYearId = $this->selectedAcademicYearId !== '' ? (int) $this->selectedAcademicYearId : null;
        $classId = $this->selectedClassId !== '' ? (int) $this->selectedClassId : null;
        $start = $this->reportingStart();
        $end = $this->reportingEnd();
        $monthKeys = $this->monthKeys($start, $end);

        $activeStudentRecordIds = StudentRecord::activeStudentRecordIdsForSchoolAcademicYear($schoolId, $academicYearId, $classId);
        $people = $this->peopleMetrics($schoolId, $activeStudentRecordIds, $start, $end);
        $admissions = $this->admissionMetrics($schoolId, $classId, $start, $end);
        $monthlyTrend = $this->monthlyTrend($schoolId, $classId, $activeStudentRecordIds, $monthKeys, $start, $end);

        return view('livewire.dashboard.enrollment-analytics', [
            'academicYears' => $this->academicYears($schoolId),
            'classes' => $this->classes($schoolId),
            'school' => $school,
            'start' => $start,
            'end' => $end,
            'monthKeys' => $monthKeys,
            'monthLabels' => collect($monthKeys)->map(fn (string $month): string => Carbon::createFromFormat('Y-m', $month)->format('M Y'))->all(),
            'people' => $people,
            'admissions' => $admissions,
            'monthlyTrend' => $monthlyTrend,
            'classDistribution' => $this->classDistribution($schoolId, $academicYearId, $activeStudentRecordIds),
            'sectionDistribution' => $this->sectionDistribution($schoolId, $academicYearId, $activeStudentRecordIds),
            'genderDistribution' => $this->genderDistribution($schoolId, $activeStudentRecordIds),
            'signals' => $this->signals($people, $admissions),
            'generatedAt' => now(),
        ])
            ->layout('layouts.dashboard', [
                'breadcrumbs' => [
                    ['href' => route('dashboard'), 'text' => 'Dashboard'],
                    ['href' => route('reports.enrollment-analytics'), 'text' => 'Enrollment Analytics', 'active' => true],
                ],
                'description' => 'Enrollment, intake, and admission movement.',
            ])
            ->title('Enrollment Analytics');
    }
}
