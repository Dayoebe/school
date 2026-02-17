<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExamSlotRequest;
use App\Http\Requests\UpdateExamSlotRequest;
use App\Models\Exam;
use App\Models\ExamSlot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ExamSlotController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(ExamSlot::class, 'exam_slot');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Exam $exam): View
    {
        return view('pages.exam.exam-slot.index', compact('exam'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Exam $exam): View
    {
        return view('pages.exam.exam-slot.create', compact('exam'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreExamSlotRequest $request, Exam $exam): RedirectResponse
    {
        $data = $request->except('_token');
        DB::transaction(function () use ($exam, $data) {
            $exam->examSlots()->create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'total_marks' => $data['total_marks'],
            ]);
        });

        return back()->with('success', 'Exam Slot Created Successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Exam $exam, ExamSlot $examSlot): Response
    {
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Exam $exam, ExamSlot $examSlot): View
    {
        return view('pages.exam.exam-slot.edit', compact('examSlot', 'exam'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateExamSlotRequest $request, Exam $exam, ExamSlot $examSlot): RedirectResponse
    {
        $data = $request->except('_token', '_method');
        DB::transaction(function () use ($examSlot, $data) {
            $examSlot->update([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'total_marks' => $data['total_marks'],
            ]);
        });

        return back()->with('success', 'Exam Slot Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Exam $exam, ExamSlot $examSlot): RedirectResponse
    {
        $examSlot->delete();

        return back()->with('success', 'Exam Slot Deleted Successfully');
    }
}
