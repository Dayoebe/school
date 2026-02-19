<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGradeSystemRequest;
use App\Http\Requests\UpdateGradeSystemRequest;
use App\Models\GradeSystem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class GradeSystemController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(GradeSystem::class, 'grade_system');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('livewire.grade-systems.pages.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('livewire.grade-systems.pages.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGradeSystemRequest $request): RedirectResponse
    {
        $data = $request->except('_token');

        $gradesInDb = GradeSystem::where('class_group_id', $data['class_group_id'])->get();
        if ($this->gradeRangeExists(
            ['grade_from' => $data['grade_from'], 'grade_till' => $data['grade_till']],
            $gradesInDb
        )) {
            return back()
                ->withErrors(['grade_from' => 'Grade range is in another range in class group'])
                ->withInput();
        }

        GradeSystem::create([
            'class_group_id' => $data['class_group_id'],
            'grade_from' => $data['grade_from'],
            'grade_till' => $data['grade_till'],
            'name' => $data['name'],
            'remark' => $data['remark'],
        ]);

        return back()->with('success', 'Grade range created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(GradeSystem $gradeSystem): Response
    {
        abort(404);

        // return view('livewire.grade-systems.pages.show', compact('gradeSystem'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GradeSystem $gradeSystem): View
    {
        return view('livewire.grade-systems.pages.edit', compact('gradeSystem'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGradeSystemRequest $request, GradeSystem $gradeSystem): RedirectResponse
    {
        $data = $request->except('_token');
        $gradesInDb = GradeSystem::where('class_group_id', $data['class_group_id'])
            ->where('id', '!=', $gradeSystem->id)
            ->get();

        if ($this->gradeRangeExists(
            ['grade_from' => $data['grade_from'], 'grade_till' => $data['grade_till']],
            $gradesInDb
        )) {
            return back()
                ->withErrors(['grade_from' => 'Grade range is in another range in class group'])
                ->withInput();
        }

        $gradeSystem->update([
            'class_group_id' => $data['class_group_id'],
            'grade_from' => $data['grade_from'],
            'grade_till' => $data['grade_till'],
            'name' => $data['name'],
            'remark' => $data['remark'],
        ]);

        return back()->with('success', 'Grade range updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GradeSystem $gradeSystem): RedirectResponse
    {
        $gradeSystem->delete();

        return back()->with('success', 'successfully deleted grade');
    }

    private function gradeRangeExists(array $grade, $grades): bool
    {
        foreach ($grades as $existingGrade) {
            if ($grade['grade_from'] >= $existingGrade['grade_from'] && $grade['grade_till'] <= $existingGrade['grade_till']) {
                return true;
            }

            if ($existingGrade['grade_from'] >= $grade['grade_from'] && $existingGrade['grade_till'] <= $grade['grade_till']) {
                return true;
            }

            if (in_array($grade['grade_from'], range($existingGrade['grade_from'], $existingGrade['grade_till']))) {
                return true;
            }

            if (in_array($grade['grade_till'], range($existingGrade['grade_from'], $existingGrade['grade_till']))) {
                return true;
            }
        }

        return false;
    }
}
