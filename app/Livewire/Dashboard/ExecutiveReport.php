<?php

namespace App\Livewire\Dashboard;

use App\Models\AdmissionRegistration;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\BroadcastMessage;
use App\Models\ContactMessage;
use App\Models\DisciplineIncident;
use App\Models\FeeInvoiceRecord;
use App\Models\MyClass;
use App\Models\Notice;
use App\Models\Result;
use App\Models\StudentRecord;
use App\Models\Subject;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ExecutiveReport extends Component
{
    public int $months = 6;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('read analytics dashboard'), 403);
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

    protected function emptyMetrics(): array
    {
        return [
            'total' => 0,
            'period_total' => 0,
        ];
    }

    protected function peopleMetrics(?int $schoolId, Collection $activeStudentRecordIds, Carbon $start, Carbon $end): array
    {
        if (!$schoolId) {
            return [
                'active_students' => 0,
                'total_students' => 0,
                'new_students' => 0,
                'teachers' => 0,
                'parents' => 0,
                'classes' => 0,
                'subjects' => 0,
            ];
        }

        $classQuery = MyClass::query()
            ->whereHas('classGroup', fn ($query) => $query->where('school_id', $schoolId));

        return [
            'active_students' => $activeStudentRecordIds->count(),
            'total_students' => User::query()->where('school_id', $schoolId)->role('student')->count(),
            'new_students' => $activeStudentRecordIds->isEmpty()
                ? 0
                : StudentRecord::query()
                    ->whereIn('id', $activeStudentRecordIds->all())
                    ->whereBetween('created_at', [$start, $end])
                    ->count(),
            'teachers' => User::query()->where('school_id', $schoolId)->role('teacher')->count(),
            'parents' => User::query()->where('school_id', $schoolId)->role('parent')->count(),
            'classes' => (clone $classQuery)->instructional()->count(),
            'subjects' => Subject::query()->where('school_id', $schoolId)->active()->count(),
        ];
    }

    protected function studentClassDistribution(?int $schoolId, ?int $academicYearId, Collection $activeStudentRecordIds): array
    {
        if (!$schoolId || $activeStudentRecordIds->isEmpty()) {
            return [];
        }

        $query = DB::table('student_records as sr')
            ->join('users as u', 'u.id', '=', 'sr.user_id')
            ->where('u.school_id', $schoolId)
            ->whereIn('sr.id', $activeStudentRecordIds->all());

        $classExpression = 'sr.my_class_id';
        if ($academicYearId) {
            $query->leftJoin('academic_year_student_record as aysr', function ($join) use ($academicYearId): void {
                $join->on('aysr.student_record_id', '=', 'sr.id')
                    ->where('aysr.academic_year_id', '=', $academicYearId);
            });
            $classExpression = 'COALESCE(aysr.my_class_id, sr.my_class_id)';
        }

        return $query
            ->join('my_classes as c', 'c.id', '=', DB::raw($classExpression))
            ->select('c.name', DB::raw('COUNT(DISTINCT sr.id) as total'))
            ->groupBy('c.id', 'c.name')
            ->orderByDesc('total')
            ->limit(6)
            ->get()
            ->map(fn ($row): array => [
                'name' => (string) $row->name,
                'total' => (int) $row->total,
            ])
            ->all();
    }

    protected function admissionMetrics(?int $schoolId, Carbon $start, Carbon $end): array
    {
        if (!$schoolId) {
            return array_merge($this->emptyMetrics(), [
                'pending' => 0,
                'reviewed' => 0,
                'approved' => 0,
                'rejected' => 0,
                'enrolled' => 0,
                'conversion_rate' => 0.0,
            ]);
        }

        $base = AdmissionRegistration::query()->where('school_id', $schoolId);
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

    protected function financeMetrics(?int $schoolId, Carbon $start, Carbon $end): array
    {
        if (!$schoolId) {
            return [
                'billable' => 0,
                'paid' => 0,
                'waiver' => 0,
                'outstanding' => 0,
                'period_collected' => 0,
                'due_items' => 0,
                'collection_rate' => 0.0,
            ];
        }

        $base = FeeInvoiceRecord::query()
            ->whereHas('feeInvoice.user', fn ($query) => $query->where('school_id', $schoolId));

        $allRows = (clone $base)->get(['amount', 'fine', 'paid', 'waiver']);
        $billable = (int) $allRows->sum(
            fn (FeeInvoiceRecord $row): int => (int) $row->getRawOriginal('amount') + (int) $row->getRawOriginal('fine')
        );
        $paid = (int) $allRows->sum(fn (FeeInvoiceRecord $row): int => (int) $row->getRawOriginal('paid'));
        $waiver = (int) $allRows->sum(fn (FeeInvoiceRecord $row): int => (int) $row->getRawOriginal('waiver'));
        $outstanding = max(0, $billable - $paid - $waiver);

        $periodRows = (clone $base)
            ->whereBetween('created_at', [$start, $end])
            ->get(['paid']);

        return [
            'billable' => $billable,
            'paid' => $paid,
            'waiver' => $waiver,
            'outstanding' => $outstanding,
            'period_collected' => (int) $periodRows->sum(fn (FeeInvoiceRecord $row): int => (int) $row->getRawOriginal('paid')),
            'due_items' => (clone $base)->isDue()->count(),
            'collection_rate' => $billable > 0 ? round((($paid + $waiver) / $billable) * 100, 1) : 0.0,
        ];
    }

    protected function academicMetrics(
        ?int $schoolId,
        ?int $academicYearId,
        ?int $semesterId,
        Collection $activeStudentRecordIds
    ): array {
        $fallback = [
            'has_context' => false,
            'result_entries' => 0,
            'approved_entries' => 0,
            'pending_entries' => 0,
            'students_with_results' => 0,
            'coverage_rate' => 0.0,
            'approval_rate' => 0.0,
            'average_score' => 0.0,
            'class_performance' => [],
        ];

        if (!$schoolId || !$academicYearId || !$semesterId || $activeStudentRecordIds->isEmpty()) {
            return $fallback;
        }

        $base = Result::query()
            ->where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->whereIn('student_record_id', $activeStudentRecordIds->all());

        $resultEntries = (clone $base)->count();
        $approvedEntries = (clone $base)->where('approved', true)->count();
        $studentsWithResults = (clone $base)->distinct('student_record_id')->count('student_record_id');

        return [
            'has_context' => true,
            'result_entries' => $resultEntries,
            'approved_entries' => $approvedEntries,
            'pending_entries' => max(0, $resultEntries - $approvedEntries),
            'students_with_results' => $studentsWithResults,
            'coverage_rate' => $activeStudentRecordIds->count() > 0
                ? round(($studentsWithResults / $activeStudentRecordIds->count()) * 100, 1)
                : 0.0,
            'approval_rate' => $resultEntries > 0 ? round(($approvedEntries / $resultEntries) * 100, 1) : 0.0,
            'average_score' => round((float) (clone $base)->avg('total_score'), 1),
            'class_performance' => $this->classPerformance($schoolId, $academicYearId, $semesterId, $activeStudentRecordIds),
        ];
    }

    protected function classPerformance(
        int $schoolId,
        int $academicYearId,
        int $semesterId,
        Collection $activeStudentRecordIds
    ): array {
        if ($activeStudentRecordIds->isEmpty()) {
            return [];
        }

        return DB::table('results as r')
            ->join('student_records as sr', 'sr.id', '=', 'r.student_record_id')
            ->join('users as u', 'u.id', '=', 'sr.user_id')
            ->leftJoin('academic_year_student_record as aysr', function ($join) use ($academicYearId): void {
                $join->on('aysr.student_record_id', '=', 'sr.id')
                    ->where('aysr.academic_year_id', '=', $academicYearId);
            })
            ->join('my_classes as c', 'c.id', '=', DB::raw('COALESCE(aysr.my_class_id, sr.my_class_id)'))
            ->where('u.school_id', $schoolId)
            ->where('r.academic_year_id', $academicYearId)
            ->where('r.semester_id', $semesterId)
            ->whereIn('r.student_record_id', $activeStudentRecordIds->all())
            ->select(
                'c.name',
                DB::raw('ROUND(AVG(r.total_score), 1) as average_score'),
                DB::raw('COUNT(DISTINCT r.student_record_id) as students')
            )
            ->groupBy('c.id', 'c.name')
            ->orderByDesc('average_score')
            ->limit(5)
            ->get()
            ->map(fn ($row): array => [
                'name' => (string) $row->name,
                'average_score' => (float) $row->average_score,
                'students' => (int) $row->students,
            ])
            ->all();
    }

    protected function attendanceMetrics(?int $schoolId, ?int $academicYearId, ?int $semesterId, Carbon $start, Carbon $end): array
    {
        if (!$schoolId) {
            return [
                'sessions' => 0,
                'records' => 0,
                'present' => 0,
                'absent' => 0,
                'late' => 0,
                'excused' => 0,
                'classes_marked' => 0,
                'attendance_rate' => 0.0,
            ];
        }

        $sessionQuery = AttendanceSession::query()
            ->where('school_id', $schoolId)
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
            ->when($academicYearId, fn ($query) => $query->where('academic_year_id', $academicYearId))
            ->when($semesterId, fn ($query) => $query->where('semester_id', $semesterId));

        $sessionIds = (clone $sessionQuery)->pluck('id');
        if ($sessionIds->isEmpty()) {
            return [
                'sessions' => 0,
                'records' => 0,
                'present' => 0,
                'absent' => 0,
                'late' => 0,
                'excused' => 0,
                'classes_marked' => 0,
                'attendance_rate' => 0.0,
            ];
        }

        $counts = AttendanceRecord::query()
            ->whereIn('attendance_session_id', $sessionIds->all())
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $records = (int) $counts->sum();
        $present = (int) ($counts['present'] ?? 0);
        $late = (int) ($counts['late'] ?? 0);

        return [
            'sessions' => $sessionIds->count(),
            'records' => $records,
            'present' => $present,
            'absent' => (int) ($counts['absent'] ?? 0),
            'late' => $late,
            'excused' => (int) ($counts['excused'] ?? 0),
            'classes_marked' => (clone $sessionQuery)->distinct('my_class_id')->count('my_class_id'),
            'attendance_rate' => $records > 0 ? round((($present + $late) / $records) * 100, 1) : 0.0,
        ];
    }

    protected function disciplineMetrics(?int $schoolId, Carbon $start, Carbon $end): array
    {
        if (!$schoolId) {
            return [
                'period_total' => 0,
                'unresolved' => 0,
                'high_priority' => 0,
                'parent_visible' => 0,
            ];
        }

        $base = DisciplineIncident::query()->where('school_id', $schoolId);
        $period = (clone $base)->whereBetween('incident_date', [$start->toDateString(), $end->toDateString()]);

        return [
            'period_total' => (clone $period)->count(),
            'unresolved' => (clone $base)->whereNull('resolved_at')->count(),
            'high_priority' => (clone $period)->whereIn('severity', ['high', 'critical'])->count(),
            'parent_visible' => (clone $period)->where('parent_visible', true)->count(),
        ];
    }

    protected function engagementMetrics(?int $schoolId, Carbon $start, Carbon $end): array
    {
        if (!$schoolId) {
            return [
                'active_notices' => 0,
                'period_notices' => 0,
                'period_broadcasts' => 0,
                'portal_reach' => 0,
                'inquiries_total' => 0,
                'inquiries_open' => 0,
                'inquiries_resolved' => 0,
            ];
        }

        $messageBase = ContactMessage::query()->where('school_id', $schoolId);

        return [
            'active_notices' => Notice::query()->where('school_id', $schoolId)->active()->count(),
            'period_notices' => Notice::query()->where('school_id', $schoolId)->whereBetween('created_at', [$start, $end])->count(),
            'period_broadcasts' => BroadcastMessage::query()->where('school_id', $schoolId)->whereBetween('created_at', [$start, $end])->count(),
            'portal_reach' => DB::table('broadcast_message_recipients')
                ->where('school_id', $schoolId)
                ->whereNotNull('portal_delivered_at')
                ->whereBetween('portal_delivered_at', [$start, $end])
                ->count(),
            'inquiries_total' => (clone $messageBase)->whereBetween('created_at', [$start, $end])->count(),
            'inquiries_open' => (clone $messageBase)->whereIn('status', ['new', 'in_progress'])->count(),
            'inquiries_resolved' => (clone $messageBase)->where('status', 'resolved')->count(),
        ];
    }

    protected function executiveSignals(
        array $people,
        array $admissions,
        array $finance,
        array $academics,
        array $attendance,
        array $discipline,
        array $engagement
    ): array {
        $signals = [];

        if (($people['active_students'] ?? 0) === 0) {
            $signals[] = ['tone' => 'amber', 'title' => 'No active student records', 'body' => 'Set the active academic year and promote/enrol students before leadership metrics can stabilise.'];
        }

        if (($admissions['pending'] ?? 0) > 0) {
            $signals[] = ['tone' => 'sky', 'title' => 'Admissions awaiting action', 'body' => number_format($admissions['pending']) . ' application(s) still need review.'];
        }

        if (($finance['billable'] ?? 0) > 0 && ($finance['collection_rate'] ?? 0) < 70) {
            $signals[] = ['tone' => 'rose', 'title' => 'Fee collection below target', 'body' => 'Current collection is ' . ($finance['collection_rate'] ?? 0) . '%. Review unpaid invoice items.'];
        }

        if (!($academics['has_context'] ?? false)) {
            $signals[] = ['tone' => 'amber', 'title' => 'Academic context incomplete', 'body' => 'Set the active academic year and semester to unlock result coverage metrics.'];
        } elseif (($academics['coverage_rate'] ?? 0) < 80) {
            $signals[] = ['tone' => 'sky', 'title' => 'Result coverage needs attention', 'body' => 'Only ' . ($academics['coverage_rate'] ?? 0) . '% of active students have result entries this term.'];
        }

        if (($attendance['records'] ?? 0) > 0 && ($attendance['attendance_rate'] ?? 0) < 90) {
            $signals[] = ['tone' => 'rose', 'title' => 'Attendance rate below 90%', 'body' => 'Attendance for the report window is ' . ($attendance['attendance_rate'] ?? 0) . '%.'];
        }

        if (($discipline['unresolved'] ?? 0) > 0) {
            $signals[] = ['tone' => 'amber', 'title' => 'Open discipline follow-up', 'body' => number_format($discipline['unresolved']) . ' incident(s) are unresolved.'];
        }

        if (($engagement['inquiries_open'] ?? 0) > 0) {
            $signals[] = ['tone' => 'sky', 'title' => 'Open public inquiries', 'body' => number_format($engagement['inquiries_open']) . ' inquiry thread(s) are still open.'];
        }

        if ($signals === []) {
            $signals[] = ['tone' => 'emerald', 'title' => 'No immediate leadership blockers', 'body' => 'Core admissions, finance, results, attendance, and welfare indicators are within available data thresholds.'];
        }

        return array_slice($signals, 0, 6);
    }

    public function render()
    {
        $user = auth()->user();
        $user?->loadMissing(['school.academicYear', 'school.semester']);

        $school = $user?->school;
        $schoolId = $school?->id;
        $academicYearId = $school?->academic_year_id;
        $semesterId = $school?->semester_id;
        $start = $this->reportingStart();
        $end = $this->reportingEnd();

        $activeStudentRecordIds = StudentRecord::activeStudentRecordIdsForSchoolAcademicYear($schoolId, $academicYearId);

        $people = $this->peopleMetrics($schoolId, $activeStudentRecordIds, $start, $end);
        $admissions = $this->admissionMetrics($schoolId, $start, $end);
        $finance = $this->financeMetrics($schoolId, $start, $end);
        $academics = $this->academicMetrics($schoolId, $academicYearId, $semesterId, $activeStudentRecordIds);
        $attendance = $this->attendanceMetrics($schoolId, $academicYearId, $semesterId, $start, $end);
        $discipline = $this->disciplineMetrics($schoolId, $start, $end);
        $engagement = $this->engagementMetrics($schoolId, $start, $end);

        return view('livewire.dashboard.executive-report', [
            'school' => $school,
            'start' => $start,
            'end' => $end,
            'people' => $people,
            'admissions' => $admissions,
            'finance' => $finance,
            'academics' => $academics,
            'attendance' => $attendance,
            'discipline' => $discipline,
            'engagement' => $engagement,
            'studentClassDistribution' => $this->studentClassDistribution($schoolId, $academicYearId, $activeStudentRecordIds),
            'signals' => $this->executiveSignals($people, $admissions, $finance, $academics, $attendance, $discipline, $engagement),
            'generatedAt' => now(),
        ])
            ->layout('layouts.dashboard', [
                'breadcrumbs' => [
                    ['href' => route('dashboard'), 'text' => 'Dashboard'],
                    ['href' => route('reports.executive'), 'text' => 'Executive Report', 'active' => true],
                ],
                'description' => 'Leadership snapshot for the selected school period.',
            ])
            ->title('Executive Report');
    }
}
