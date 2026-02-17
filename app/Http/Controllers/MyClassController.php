<?php

namespace App\Http\Controllers;

use App\Http\Requests\MyClassStoreRequest;
use App\Http\Requests\MyClassUpdateRequest;
use App\Models\ClassGroup;
use App\Models\MyClass;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MyClassController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(MyClass::class, 'class');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('pages.class.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('pages.class.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MyClassStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $classGroup = ClassGroup::where('school_id', auth()->user()->school_id)
            ->findOrFail($data['class_group_id']);

        MyClass::create([
            'name' => $data['name'],
            'class_group_id' => $classGroup->id,
        ]);

        return back()->with('success', __('Class created successfully'));
    }

    /**
     * Display the specified resource.
     */
    public function show(MyClass $class): View
    {
        $data['class'] = $class;

        return view('pages.class.show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MyClass $class): View
    {
        $data['myClass'] = $class;

        return view('pages.class.edit', $data);
    }

    public function assignSubjects(MyClass $class)
{
    $this->authorize('update', $class);
    
    $subjects = $class->subjects;
    $students = $class->studentRecords()->with('studentSubjects')->get();

    foreach ($students as $student) {
        $syncData = [];
        foreach ($subjects as $subject) {
            $syncData[$subject->id] = [
                'my_class_id' => $class->id,
                'section_id' => $student->section_id,
            ];
        }
        $student->studentSubjects()->syncWithoutDetaching($syncData);
    }

    return back()->with('success', 'Subjects assigned to students successfully!');
}


    /**
     * Update the specified resource in storage.
     *
     * @param MyClassUpdateRequest $request
     */
    public function update(MyClassUpdateRequest $request, MyClass $class): RedirectResponse
    {
        $data = $request->validated();
        $classGroup = ClassGroup::where('school_id', auth()->user()->school_id)
            ->findOrFail($data['class_group_id']);

        $class->update([
            'name' => $data['name'],
            'class_group_id' => $classGroup->id,
        ]);

        return back()->with('success', __('Class updated successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MyClass $class): RedirectResponse
    {
        if ($class->studentRecords()->count() > 0) {
            return back()->with('danger', 'Class contains students');
        }

        $class->delete();

        return back()->with('success', __('Class deleted successfully'));
    }
}
