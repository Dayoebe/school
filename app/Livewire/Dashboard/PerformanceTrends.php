<?php

namespace App\Livewire\Dashboard;

use App\Models\AcademicYear;
use App\Models\MyClass;
use App\Models\Result;
use App\Models\Semester;
use App\Models\StudentRecord;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class PerformanceTrends extends Component
{
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

    protected function semesters(?int $schoolId, ?int $academicYearId): Collection
    {
        if (!$schoolId || !$academicYearId) {
            return collect();
        }

        return Semester::query()
            ->where('school_id', $schoolId)
            ->where('academic_year_id', $academicYearId)
            ->orderBy('id')
            ->get(['id', 'name', 'academic_year_id']);
    }

    protected function results(?int $academicYearId, Collection $studentRecordIds): Collection
    {
        if (!$academicYearId || $studentRecordIds->isEmpty()) {
            return collect();
        }

        return Result::query()
            ->where('academic_year_id', $academicYearId)
            ->whereIn('student_record_id', $studentRecordIds->all())
            ->with(['semester:id,name,academic_year_id', 'subject:id,name,short_name'])
            ->get();
    }

    protected function termTrends(Collection $results, Collection $semesters, int $activeStudentCount): array
    {
        return $semesters
            ->map(function (Semester $semester) use ($results, $activeStudentCount): array {
                $semesterResults = $results->where('semester_id', $semester->id);
                $entries = $semesterResults->count();
                $approved = $semesterResults->where('approved', true)->count();
                $studentAverages = $semesterResults
                    ->groupBy('student_record_id')
                    ->map(fn (Collection $rows): float => (float) $rows->avg('total_score'));
                $studentsWithResults = $studentAverages->count();
                $passers = $studentAverages->filter(fn (float $average): bool => $average >= 50)->count();

                return [
                    'semester_id' => $semester->id,
                    'label' => $semester->name,
                    'average_score' => $entries > 0 ? round((float) $semesterResults->avg('total_score'), 1) : 0.0,
                    'entries' => $entries,
                    'students_with_results' => $studentsWithResults,
                    'pass_rate' => $studentsWithResults > 0 ? round(($passers / $studentsWithResults) * 100, 1) : 0.0,
                    'coverage_rate' => $activeStudentCount > 0 ? round(($studentsWithResults / $activeStudentCount) * 100, 1) : 0.0,
                    'approval_rate' => $entries > 0 ? round(($approved / $entries) * 100, 1) : 0.0,
                    'at_risk_students' => $studentAverages->filter(fn (float $average): bool => $average < 50)->count(),
                ];
            })
            ->values()
            ->all();
    }

    protected function nonEmptyTerms(array $termTrends): Collection
    {
        return collect($termTrends)
            ->filter(fn (array $term): bool => (int) $term['entries'] > 0)
            ->values();
    }

    protected function studentClassMap(?int $schoolId, ?int $academicYearId, Collection $studentRecordIds): Collection
    {
        if (!$schoolId || !$academicYearId || $studentRecordIds->isEmpty()) {
            return collect();
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
            ->whereIn('sr.id', $studentRecordIds->all())
            ->select('sr.id as student_record_id', 'c.name as class_name')
            ->get()
            ->pluck('class_name', 'student_record_id');
    }

    protected function classMovement(
        Collection $results,
        Collection $classMap,
        ?int $firstSemesterId,
        ?int $latestSemesterId
    ): array {
        if ($results->isEmpty() || $classMap->isEmpty()) {
            return [];
        }

        return $results
            ->groupBy(fn (Result $result): string => (string) ($classMap[$result->student_record_id] ?? 'Unassigned'))
            ->map(function (Collection $rows, string $className) use ($firstSemesterId, $latestSemesterId): array {
                $firstAverage = $firstSemesterId ? $rows->where('semester_id', $firstSemesterId)->avg('total_score') : null;
                $latestAverage = $latestSemesterId ? $rows->where('semester_id', $latestSemesterId)->avg('total_score') : null;

                return [
                    'class_name' => $className,
                    'average_score' => round((float) $rows->avg('total_score'), 1),
                    'latest_average' => $latestAverage !== null ? round((float) $latestAverage, 1) : null,
                    'change' => ($firstAverage !== null && $latestAverage !== null)
                        ? round((float) $latestAverage - (float) $firstAverage, 1)
                        : null,
                    'students' => $rows->pluck('student_record_id')->unique()->count(),
                    'entries' => $rows->count(),
                ];
            })
            ->sortByDesc('average_score')
            ->values()
            ->all();
    }

    protected function subjectMovement(
        Collection $results,
        ?int $firstSemesterId,
        ?int $latestSemesterId
    ): array {
        if ($results->isEmpty()) {
            return [];
        }

        return $results
            ->filter(fn (Result $result): bool => $result->subject !== null)
            ->groupBy('subject_id')
            ->map(function (Collection $rows) use ($firstSemesterId, $latestSemesterId): array {
                $subject = $rows->first()->subject;
                $firstAverage = $firstSemesterId ? $rows->where('semester_id', $firstSemesterId)->avg('total_score') : null;
                $latestAverage = $latestSemesterId ? $rows->where('semester_id', $latestSemesterId)->avg('total_score') : null;
                $entries = $rows->count();
                $passers = $rows->filter(fn (Result $result): bool => (float) $result->total_score >= 50)->count();

                return [
                    'subject_name' => $subject->short_name ?: $subject->name,
                    'average_score' => round((float) $rows->avg('total_score'), 1),
                    'latest_average' => $latestAverage !== null ? round((float) $latestAverage, 1) : null,
                    'change' => ($firstAverage !== null && $latestAverage !== null)
                        ? round((float) $latestAverage - (float) $firstAverage, 1)
                        : null,
                    'pass_rate' => $entries > 0 ? round(($passers / $entries) * 100, 1) : 0.0,
                    'entries' => $entries,
                ];
            })
            ->sortByDesc('average_score')
            ->values()
            ->all();
    }

    protected function summary(array $termTrends, int $activeStudentCount, Collection $results): array
    {
        $nonEmptyTerms = $this->nonEmptyTerms($termTrends);
        $firstTerm = $nonEmptyTerms->first();
        $latestTerm = $nonEmptyTerms->last();

        return [
            'active_students' => $activeStudentCount,
            'result_entries' => $results->count(),
            'terms_with_results' => $nonEmptyTerms->count(),
            'latest_average' => (float) data_get($latestTerm, 'average_score', 0),
            'latest_pass_rate' => (float) data_get($latestTerm, 'pass_rate', 0),
            'latest_coverage_rate' => (float) data_get($latestTerm, 'coverage_rate', 0),
            'latest_approval_rate' => (float) data_get($latestTerm, 'approval_rate', 0),
            'latest_at_risk_students' => (int) data_get($latestTerm, 'at_risk_students', 0),
            'change' => ($firstTerm && $latestTerm)
                ? round((float) $latestTerm['average_score'] - (float) $firstTerm['average_score'], 1)
                : 0.0,
            'best_term' => $nonEmptyTerms->sortByDesc('average_score')->first(),
            'weakest_term' => $nonEmptyTerms->sortBy('average_score')->first(),
        ];
    }

    protected function signals(array $summary, array $subjectMovement): array
    {
        $signals = [];

        if (($summary['active_students'] ?? 0) === 0) {
            $signals[] = ['tone' => 'amber', 'title' => 'No active students', 'body' => 'Set the academic year records before trend data can be calculated.'];
        }

        if (($summary['result_entries'] ?? 0) === 0) {
            $signals[] = ['tone' => 'amber', 'title' => 'No result entries', 'body' => 'Upload or approve term results to unlock performance trend analysis.'];
        }

        if (($summary['change'] ?? 0) >= 5) {
            $signals[] = ['tone' => 'emerald', 'title' => 'Performance is improving', 'body' => 'Average score is up by ' . number_format((float) $summary['change'], 1) . ' points across recorded terms.'];
        } elseif (($summary['change'] ?? 0) <= -5) {
            $signals[] = ['tone' => 'rose', 'title' => 'Performance is declining', 'body' => 'Average score is down by ' . number_format(abs((float) $summary['change']), 1) . ' points across recorded terms.'];
        }

        if (($summary['latest_coverage_rate'] ?? 0) > 0 && ($summary['latest_coverage_rate'] ?? 0) < 85) {
            $signals[] = ['tone' => 'sky', 'title' => 'Result coverage is incomplete', 'body' => 'Latest coverage is ' . number_format((float) $summary['latest_coverage_rate'], 1) . '%.'];
        }

        if (($summary['latest_approval_rate'] ?? 0) > 0 && ($summary['latest_approval_rate'] ?? 0) < 90) {
            $signals[] = ['tone' => 'sky', 'title' => 'Approvals need follow-up', 'body' => 'Latest approval rate is ' . number_format((float) $summary['latest_approval_rate'], 1) . '%.'];
        }

        if (($summary['latest_at_risk_students'] ?? 0) > 0) {
            $signals[] = ['tone' => 'rose', 'title' => 'At-risk students present', 'body' => number_format((int) $summary['latest_at_risk_students']) . ' student(s) averaged below 50 in the latest recorded term.'];
        }

        $decliningSubject = collect($subjectMovement)
            ->filter(fn (array $subject): bool => ($subject['change'] ?? 0) <= -5)
            ->sortBy('change')
            ->first();

        if ($decliningSubject) {
            $signals[] = ['tone' => 'amber', 'title' => 'Subject decline detected', 'body' => $decliningSubject['subject_name'] . ' is down by ' . number_format(abs((float) $decliningSubject['change']), 1) . ' points.'];
        }

        if ($signals === []) {
            $signals[] = ['tone' => 'emerald', 'title' => 'Trend is stable', 'body' => 'No major performance, coverage, or approval risks were detected from available records.'];
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

        $academicYears = $this->academicYears($schoolId);
        $classes = $this->classes($schoolId);
        $semesters = $this->semesters($schoolId, $academicYearId);
        $studentRecordIds = StudentRecord::activeStudentRecordIdsForSchoolAcademicYear($schoolId, $academicYearId, $classId);
        $results = $this->results($academicYearId, $studentRecordIds);

        $termTrends = $this->termTrends($results, $semesters, $studentRecordIds->count());
        $nonEmptyTerms = $this->nonEmptyTerms($termTrends);
        $firstSemesterId = data_get($nonEmptyTerms->first(), 'semester_id');
        $latestSemesterId = data_get($nonEmptyTerms->last(), 'semester_id');
        $classMap = $this->studentClassMap($schoolId, $academicYearId, $studentRecordIds);
        $classMovement = $this->classMovement($results, $classMap, $firstSemesterId, $latestSemesterId);
        $subjectMovement = $this->subjectMovement($results, $firstSemesterId, $latestSemesterId);
        $summary = $this->summary($termTrends, $studentRecordIds->count(), $results);

        return view('livewire.dashboard.performance-trends', [
            'academicYears' => $academicYears,
            'classes' => $classes,
            'school' => $school,
            'termTrends' => $termTrends,
            'classMovement' => $classMovement,
            'subjectMovement' => $subjectMovement,
            'summary' => $summary,
            'signals' => $this->signals($summary, $subjectMovement),
            'generatedAt' => now(),
        ])
            ->layout('layouts.dashboard', [
                'breadcrumbs' => [
                    ['href' => route('dashboard'), 'text' => 'Dashboard'],
                    ['href' => route('reports.performance-trends'), 'text' => 'Performance Trends', 'active' => true],
                ],
                'description' => 'Term-by-term academic performance movement.',
            ])
            ->title('Performance Trends');
    }
}
