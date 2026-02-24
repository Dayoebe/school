<?php

namespace App\Livewire\Attendance;

use App\Models\AttendanceRecord;
use App\Models\DisciplineIncident;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Component;

class ParentStudentWelfare extends Component
{
    public string $selectedStudentRecordId = '';

    public string $month = '';

    /** @var array<int, array<string, mixed>> */
    public array $children = [];

    public function mount(): void
    {
        abort_unless(
            auth()->user()?->can('read own child attendance') || auth()->user()?->can('read own child discipline'),
            403
        );

        $this->month = now()->format('Y-m');
        $this->loadChildren();
    }

    protected function loadChildren(): void
    {
        $children = auth()->user()?->children()
            ->where('users.school_id', auth()->user()?->school_id)
            ->with(['studentRecord:id,user_id,admission_number'])
            ->orderBy('users.name')
            ->get();

        $this->children = $children
            ?->filter(fn ($child) => $child->studentRecord !== null)
            ->map(static fn ($child): array => [
                'user_id' => (int) $child->id,
                'name' => (string) $child->name,
                'student_record_id' => (int) $child->studentRecord->id,
                'admission_number' => (string) ($child->studentRecord->admission_number ?? ''),
            ])
            ->values()
            ->all() ?? [];

        if ($this->children !== [] && $this->selectedStudentRecordId === '') {
            $this->selectedStudentRecordId = (string) $this->children[0]['student_record_id'];
        }

        if ($this->selectedStudentRecordId !== '' && !collect($this->children)->contains('student_record_id', (int) $this->selectedStudentRecordId)) {
            $this->selectedStudentRecordId = '';
        }
    }

    protected function attendanceRecords(): Collection
    {
        if ($this->selectedStudentRecordId === '') {
            return collect();
        }

        [$start, $end] = $this->monthRange();

        return AttendanceRecord::query()
            ->select('attendance_records.*')
            ->join('attendance_sessions as attendance_session_filter', 'attendance_session_filter.id', '=', 'attendance_records.attendance_session_id')
            ->where('attendance_records.student_record_id', (int) $this->selectedStudentRecordId)
            ->where('attendance_session_filter.school_id', auth()->user()?->school_id)
            ->whereBetween('attendance_session_filter.attendance_date', [$start, $end])
            ->with([
                'attendanceSession.myClass:id,name',
                'attendanceSession.section:id,name',
            ])
            ->orderByDesc('attendance_session_filter.attendance_date')
            ->get();
    }

    protected function disciplineIncidents(): Collection
    {
        if ($this->selectedStudentRecordId === '') {
            return collect();
        }

        return DisciplineIncident::query()
            ->where('student_record_id', (int) $this->selectedStudentRecordId)
            ->where('parent_visible', true)
            ->orderByDesc('incident_date')
            ->orderByDesc('id')
            ->get();
    }

    protected function monthRange(): array
    {
        $month = trim($this->month);
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = now()->format('Y-m');
            $this->month = $month;
        }

        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth()->toDateString();
        $end = Carbon::createFromFormat('Y-m', $month)->endOfMonth()->toDateString();

        return [$start, $end];
    }

    protected function attendanceSummary(Collection $records): array
    {
        $summary = [
            'present' => 0,
            'absent' => 0,
            'late' => 0,
            'excused' => 0,
        ];

        foreach ($records as $record) {
            $status = (string) $record->status;
            if (isset($summary[$status])) {
                $summary[$status]++;
            }
        }

        return $summary;
    }

    public function render()
    {
        $attendance = auth()->user()?->can('read own child attendance') ? $this->attendanceRecords() : collect();
        $discipline = auth()->user()?->can('read own child discipline') ? $this->disciplineIncidents() : collect();

        return view('livewire.attendance.parent-student-welfare', [
            'attendance' => $attendance,
            'discipline' => $discipline,
            'attendanceSummary' => $this->attendanceSummary($attendance),
            'selectedChild' => collect($this->children)->firstWhere('student_record_id', (int) $this->selectedStudentRecordId),
        ])
            ->layout('layouts.dashboard', [
                'breadcrumbs' => [
                    ['href' => route('dashboard'), 'text' => 'Dashboard'],
                    ['href' => route('parent.student-welfare'), 'text' => 'Child Attendance & Discipline', 'active' => true],
                ],
            ])
            ->title('Child Attendance & Discipline');
    }
}
