<?php

namespace App\Livewire\Attendance;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\MyClass;
use App\Models\Section;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ManageAttendance extends Component
{
    public string $attendanceDate = '';

    public string $selectedClassId = '';

    public string $selectedSectionId = '';

    public string $sessionNotes = '';

    /** @var array<int, string> */
    public array $statuses = [];

    /** @var array<int, string> */
    public array $remarks = [];

    /** @var array<int, array<string, mixed>> */
    public array $students = [];

    /** @var array<int, array<string, mixed>> */
    public array $classes = [];

    /** @var array<int, array<string, mixed>> */
    public array $sections = [];

    public ?int $loadedSessionId = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('read attendance'), 403);

        $this->attendanceDate = now()->toDateString();
        $this->loadClasses();
        $this->loadSections();
        $this->loadAttendanceSheet();
    }

    public function updatedSelectedClassId(): void
    {
        $this->selectedSectionId = '';
        $this->loadSections();
        $this->loadAttendanceSheet();
    }

    public function updatedSelectedSectionId(): void
    {
        $this->loadAttendanceSheet();
    }

    public function updatedAttendanceDate(): void
    {
        $this->loadAttendanceSheet();
    }

    public function saveAttendance(): void
    {
        $this->validate([
            'attendanceDate' => ['required', 'date'],
            'selectedClassId' => ['required', 'integer'],
            'selectedSectionId' => ['nullable', 'integer'],
            'statuses.*' => ['required', 'in:present,absent,late,excused'],
            'remarks.*' => ['nullable', 'string', 'max:1000'],
            'sessionNotes' => ['nullable', 'string', 'max:3000'],
        ]);

        $existingSession = $this->resolveAttendanceSession();
        if ($existingSession) {
            abort_unless(auth()->user()?->can('update attendance'), 403);
        } else {
            abort_unless(auth()->user()?->can('create attendance'), 403);
        }

        if ($this->students === []) {
            $this->addError('selectedClassId', 'No students found for this class/section in the active academic year.');
            return;
        }

        $school = auth()->user()?->school;
        $academicYearId = (int) ($school?->academic_year_id ?? 0);

        if ($academicYearId <= 0) {
            $this->addError('attendanceDate', 'Set an active academic year before recording attendance.');
            return;
        }

        $sectionId = $this->selectedSectionId !== '' ? (int) $this->selectedSectionId : null;
        $classId = (int) $this->selectedClassId;

        DB::transaction(function () use ($existingSession, $academicYearId, $classId, $sectionId): void {
            $session = $existingSession ?: new AttendanceSession();

            $session->fill([
                'attendance_date' => $this->attendanceDate,
                'academic_year_id' => $academicYearId,
                'semester_id' => auth()->user()?->school?->semester_id,
                'my_class_id' => $classId,
                'section_id' => $sectionId,
                'notes' => trim($this->sessionNotes) ?: null,
                'taken_by' => auth()->id(),
            ]);
            $session->save();

            $studentRecordIds = array_map(
                static fn (array $student): int => (int) $student['student_record_id'],
                $this->students
            );

            $session->records()->whereNotIn('student_record_id', $studentRecordIds)->delete();

            foreach ($studentRecordIds as $studentRecordId) {
                AttendanceRecord::query()->updateOrCreate(
                    [
                        'attendance_session_id' => $session->id,
                        'student_record_id' => $studentRecordId,
                    ],
                    [
                        'status' => $this->statuses[$studentRecordId] ?? 'present',
                        'remark' => trim((string) ($this->remarks[$studentRecordId] ?? '')) ?: null,
                        'marked_by' => auth()->id(),
                    ]
                );
            }
        });

        $this->loadAttendanceSheet();

        session()->flash('success', 'Attendance saved successfully.');
    }

    protected function loadClasses(): void
    {
        $this->classes = MyClass::query()
            ->whereHas('classGroup', function ($query): void {
                $query->where('school_id', auth()->user()?->school_id);
            })
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (MyClass $class): array => [
                'id' => $class->id,
                'name' => $class->name,
            ])
            ->all();

        if ($this->selectedClassId === '' && $this->classes !== []) {
            $this->selectedClassId = (string) $this->classes[0]['id'];
        }
    }

    protected function loadSections(): void
    {
        if ($this->selectedClassId === '') {
            $this->sections = [];
            return;
        }

        $this->sections = Section::query()
            ->where('my_class_id', (int) $this->selectedClassId)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Section $section): array => [
                'id' => $section->id,
                'name' => $section->name,
            ])
            ->all();

        if ($this->selectedSectionId !== '' && !collect($this->sections)->contains('id', (int) $this->selectedSectionId)) {
            $this->selectedSectionId = '';
        }
    }

    protected function loadAttendanceSheet(): void
    {
        $this->statuses = [];
        $this->remarks = [];
        $this->sessionNotes = '';
        $this->loadedSessionId = null;

        $this->students = $this->fetchStudentsForSelection();

        if ($this->students === []) {
            return;
        }

        $session = $this->resolveAttendanceSession();

        $recordsByStudent = [];
        if ($session) {
            $this->loadedSessionId = $session->id;
            $this->sessionNotes = (string) ($session->notes ?? '');
            $recordsByStudent = $session->records
                ->keyBy('student_record_id')
                ->all();
        }

        foreach ($this->students as $student) {
            $studentRecordId = (int) $student['student_record_id'];
            $record = $recordsByStudent[$studentRecordId] ?? null;

            $this->statuses[$studentRecordId] = $record?->status ?? 'present';
            $this->remarks[$studentRecordId] = (string) ($record?->remark ?? '');
        }
    }

    /** @return array<int, array<string, mixed>> */
    protected function fetchStudentsForSelection(): array
    {
        $school = auth()->user()?->school;
        $schoolId = (int) ($school?->id ?? 0);
        $academicYearId = (int) ($school?->academic_year_id ?? 0);
        $classId = (int) $this->selectedClassId;

        if ($schoolId <= 0 || $academicYearId <= 0 || $classId <= 0) {
            return [];
        }

        $query = DB::table('academic_year_student_record as aysr')
            ->join('student_records as sr', 'sr.id', '=', 'aysr.student_record_id')
            ->join('users as u', 'u.id', '=', 'sr.user_id')
            ->where('aysr.academic_year_id', $academicYearId)
            ->where('aysr.my_class_id', $classId)
            ->where('u.school_id', $schoolId)
            ->where('sr.is_graduated', 0)
            ->whereNull('u.deleted_at');

        if ($this->selectedSectionId !== '') {
            $query->where('aysr.section_id', (int) $this->selectedSectionId);
        }

        return $query
            ->orderBy('u.name')
            ->get([
                'sr.id as student_record_id',
                'u.id as user_id',
                'u.name',
                'sr.admission_number',
            ])
            ->map(static fn ($row): array => [
                'student_record_id' => (int) $row->student_record_id,
                'user_id' => (int) $row->user_id,
                'name' => (string) $row->name,
                'admission_number' => (string) ($row->admission_number ?? ''),
            ])
            ->all();
    }

    protected function resolveAttendanceSession(): ?AttendanceSession
    {
        $school = auth()->user()?->school;
        $academicYearId = (int) ($school?->academic_year_id ?? 0);
        $classId = (int) $this->selectedClassId;

        if ($academicYearId <= 0 || $classId <= 0 || $this->attendanceDate === '') {
            return null;
        }

        $query = AttendanceSession::query()
            ->with('records')
            ->where('attendance_date', $this->attendanceDate)
            ->where('academic_year_id', $academicYearId)
            ->where('my_class_id', $classId);

        if ($this->selectedSectionId !== '') {
            $query->where('section_id', (int) $this->selectedSectionId);
        } else {
            $query->whereNull('section_id');
        }

        return $query->first();
    }

    protected function attendanceSummary(): array
    {
        $summary = [
            'present' => 0,
            'absent' => 0,
            'late' => 0,
            'excused' => 0,
        ];

        foreach ($this->statuses as $status) {
            if (isset($summary[$status])) {
                $summary[$status]++;
            }
        }

        return $summary;
    }

    public function render()
    {
        return view('livewire.attendance.manage-attendance', [
            'summary' => $this->attendanceSummary(),
            'canEditAttendance' => auth()->user()?->can('create attendance') || auth()->user()?->can('update attendance'),
            'hasAcademicYear' => (bool) auth()->user()?->school?->academic_year_id,
        ])
            ->layout('layouts.dashboard', [
                'breadcrumbs' => [
                    ['href' => route('dashboard'), 'text' => 'Dashboard'],
                    ['href' => route('attendance.index'), 'text' => 'Daily Attendance', 'active' => true],
                ],
            ])
            ->title('Daily Attendance');
    }
}
