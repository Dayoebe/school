<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExamRecordRequest;
use App\Http\Requests\UpdateExamRecordRequest;
use App\Models\ExamRecord;
use App\Models\ExamSlot;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ExamRecordController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(ExamRecord::class, 'exam_record');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('pages.exam.exam-record.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('pages.exam.exam-record.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreExamRecordRequest $request): RedirectResponse
    {
        $data = $request->except('_token');
        $subject = Subject::findOrFail($data['subject_id']);

        if (
            auth()->user()->hasRole('teacher')
            && $subject->teachers()->where('users.id', auth()->id())->doesntExist()
        ) {
            abort(403, 'Creating exam record for this subject is unauthorised.');
        }

        DB::transaction(function () use ($data) {
            foreach ($data['exam_records'] as $record) {
                $examSlot = ExamSlot::findOrFail($record['exam_slot_id']);
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
}
