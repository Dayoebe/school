<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExamRecordRequest;
use App\Http\Requests\UpdateExamRecordRequest;
use App\Models\ExamRecord;
use App\Models\ExamSlot;
use App\Models\Section;
use App\Models\Subject;
use App\Models\User;
use App\Traits\RestrictsTeacherExamPaperManagement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ExamRecordController extends Controller
{
    use RestrictsTeacherExamPaperManagement;

    public function __construct()
    {
        $this->authorizeResource(ExamRecord::class, 'exam_record');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('livewire.exams.pages.exam-record.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): RedirectResponse
    {
        return redirect()->route('exam-records.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreExamRecordRequest $request): RedirectResponse
    {
        $data = $request->except('_token');
        $schoolId = $this->currentSchoolId();

        if (!$schoolId) {
            abort(403, 'No school context found for this account.');
        }

        $subject = Subject::query()
            ->where('school_id', $schoolId)
            ->findOrFail($data['subject_id']);

        $section = Section::query()
            ->whereHas('myClass.classGroup', function ($query) use ($schoolId) {
                $query->where('school_id', $schoolId);
            })
            ->with('myClass')
            ->findOrFail($data['section_id']);

        $student = User::query()
            ->where('school_id', $schoolId)
            ->with('studentRecord')
            ->findOrFail($data['user_id']);

        $classId = (int) $section->my_class_id;

        $this->ensureCurrentUserCanManageExamRecordScope($classId, (int) $subject->id);
        $this->ensureStudentBelongsToSection($student, $section);

        if (!$this->subjectAllowedInSectionClass($subject, $section, $schoolId)) {
            throw ValidationException::withMessages([
                'subject_id' => 'Selected subject is not assigned to the selected section class.',
            ]);
        }

        DB::transaction(function () use ($data) {
            foreach ($data['exam_records'] as $record) {
                $examSlot = ExamSlot::whereHas('exam.semester', function ($query) {
                    $query->where('school_id', $this->currentSchoolId());
                })->findOrFail($record['exam_slot_id']);
                $studentMarks = $record['student_marks'] ?? null;

                if ($studentMarks !== null && $studentMarks !== '' && (float) $studentMarks > (float) $examSlot->total_marks) {
                    throw ValidationException::withMessages([
                        'exam_records' => 'Student marks cannot be greater than total marks',
                    ]);
                }

                ExamRecord::updateOrCreate(
                    [
                        'user_id' => $data['user_id'],
                        'section_id' => $data['section_id'],
                        'subject_id' => $data['subject_id'],
                        'exam_slot_id' => $record['exam_slot_id'],
                    ],
                    [
                        'student_marks' => ($studentMarks === '' ? null : $studentMarks),
                    ]
                );
            }
        });

        return back()->with('success', 'Exam Records Created/updated Successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(ExamRecord $examRecord): Response
    {
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ExamRecord $examRecord): Response
    {
        abort(404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateExamRecordRequest $request, ExamRecord $examRecord): RedirectResponse
    {
        abort(404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ExamRecord $examRecord): Response
    {
        abort(404);
    }

    protected function currentSchoolId(): ?int
    {
        return auth()->user()?->school_id;
    }

    protected function subjectAllowedInSectionClass(Subject $subject, Section $section, int $schoolId): bool
    {
        if ($subject->is_general) {
            return true;
        }

        if ((int) $subject->my_class_id === (int) $section->my_class_id) {
            return true;
        }

        return DB::table('class_subject')
            ->where('school_id', $schoolId)
            ->where('subject_id', $subject->id)
            ->where('my_class_id', $section->my_class_id)
            ->exists();
    }

    protected function ensureCurrentUserCanManageExamRecordScope(int $classId, int $subjectId): void
    {
        abort_unless(
            $this->currentUserCanManageExamPaperClass($classId),
            403,
            'Creating exam record for this class is unauthorised.'
        );

        abort_unless(
            $this->currentUserCanManageExamPaperSubject($subjectId, $classId),
            403,
            'Creating exam record for this subject is unauthorised.'
        );
    }

    protected function ensureStudentBelongsToSection(User $student, Section $section): void
    {
        $studentRecordId = $student->studentRecord?->id;
        $academicYearId = auth()->user()?->school?->academic_year_id;

        if ($studentRecordId && $academicYearId) {
            $assignedToSection = DB::table('academic_year_student_record')
                ->where('student_record_id', $studentRecordId)
                ->where('academic_year_id', $academicYearId)
                ->where('my_class_id', $section->my_class_id)
                ->where('section_id', $section->id)
                ->exists();

            if ($assignedToSection) {
                return;
            }
        }

        if ((int) $student->studentRecord?->section_id === (int) $section->id) {
            return;
        }

        throw ValidationException::withMessages([
            'user_id' => 'Selected student does not belong to the selected section.',
        ]);
    }
}
