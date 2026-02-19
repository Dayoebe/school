<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExamRequest;
use App\Http\Requests\UpdateExamRequest;
use App\Http\Requests\UpdateExamStatusRequest;
use App\Models\Exam;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ExamController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Exam::class, 'exam');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('livewire.exams.pages.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('livewire.exams.pages.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreExamRequest $request): RedirectResponse
    {
        $data = $request->except('_token');
        $exam = Exam::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'semester_id' => $data['semester_id'],
            'start_date' => $data['start_date'],
            'stop_date' => $data['stop_date'],
        ]);

        return redirect()->route('exam-slots.create', $exam)->with('success', 'Exam created successfully, Now, create exam slots for the exam');
    }

    /**
     * Display the specified resource.
     */
    public function show(Exam $exam): Response
    {
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Exam $exam): View
    {
        return view('livewire.exams.pages.edit', compact('exam'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateExamRequest $request, Exam $exam): RedirectResponse
    {
        $data = $request->except(['_method', '_token']);
        $exam->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'semester_id' => $data['semester_id'],
            'start_date' => $data['start_date'],
            'stop_date' => $data['stop_date'],
        ]);

        return back()->with('success', 'Exam updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Exam $exam): RedirectResponse
    {
        $exam->delete();

        return back()->with('success', 'Exam deleted successfully');
    }

    /**
     * Tabulation for exams.
     */
    public function examTabulation(): View
    {
        $this->authorize('viewAny', Exam::class);

        return view('livewire.exams.pages.tabulation');
    }

    /**
     * Tabulation for semester results.
     */
    public function semesterResultTabulation(): View
    {
        $this->authorize('viewAny', Exam::class);

        return view('livewire.exams.pages.semester-result-tabulation');
    }

    /**
     * Tabulation for academic year results.
     */
    public function academicYearResultTabulation(): View
    {
        $this->authorize('viewAny', Exam::class);

        return view('livewire.exams.pages.academic-year-result-tabulation');
    }

    /**
     * Result checker.
     */
    public function resultChecker(): View
    {
        $this->authorize('checkResult', Exam::class);

        return view('livewire.exams.pages.result-checker');
    }

    /**
     * Set exam status.
     */
    public function setExamActiveStatus(Exam $exam, UpdateExamStatusRequest $request): RedirectResponse
    {
        $this->authorize('update', $exam);
        //get status from request
        $status = $request->status;
        $exam->update(['active' => $status]);

        return back()->with('success', 'Exam status updated successfully');
    }

    /**
     * Set publish result status.
     *
     * @param UpdateExamStatusRequest $request
     */
    public function setPublishResultStatus(Exam $exam, UpdateExamStatusRequest $request): RedirectResponse
    {
        $this->authorize('update', $exam);
        //get status from request
        $status = $request->status;

        if ($exam->examSlots()->count() <= 0 && (bool) $status === true) {
            return back()->with('danger', 'Cannot publish result for exam without exam slots');
        }

        $exam->update(['publish_result' => $status]);

        return back()->with('success', 'Result published status updated successfully');
    }
}
