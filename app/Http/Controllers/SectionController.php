<?php

namespace App\Http\Controllers;

use App\Http\Requests\SectionStoreRequest;
use App\Http\Requests\SectionUpdateRequest;
use App\Models\Section;
use App\Services\Section\SectionService;
use Illuminate\Http\RedirectResponse;
use App\Models\Subject;
use Illuminate\View\View;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    public $section;

    public function __construct(SectionService $section)
    {
        $this->section = $section;
        $this->authorizeResource(Section::class, 'section');
    }

    public function attachSubjects(Section $section, Request $request)
    {
        $request->validate([
            'subject_ids' => 'required|array',
            'subject_ids.*' => 'exists:subjects,id'
        ]);
        
        $section->subjects()->syncWithoutDetaching($request->subject_ids);
        
        // Update students in this section
        foreach ($section->studentRecords as $record) {
            $record->assignSubjectsAutomatically();
        }
        
        return back()->with('success', 'Subjects added to section successfully');
    }

public function detachSubject(Section $section, Subject $subject)
{
    // Fix: Use correct pivot table name
    $section->subjects()->detach($subject->id);
    
    // Update students in this section
    foreach ($section->studentRecords as $record) {
        $record->assignSubjectsAutomatically();
    }
    
    return back()->with('success', 'Subject removed from section');
}


    public function index(): View
    {
        return view('pages.section.index');
    }



    public function show(Section $section)
    {
        $section->load(['subjects.teachers', 'studentRecords.user']);
        
        $availableSubjects = Subject::where('my_class_id', $section->my_class_id)
            ->whereDoesntHave('sections', function ($query) use ($section) {
                $query->where('sections.id', $section->id);
            })
            ->with('teachers') // Eager-load teachers
            ->get();
    
        return view('pages.section.show', compact('section', 'availableSubjects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('pages.section.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SectionStoreRequest $request): RedirectResponse
    {
        $data = $request->except('_token');
        $this->section->createSection($data);

        return back()->with('success', 'Section created successfully');
    }

    
    public function edit(Section $section): View
    {
        $data['section'] = $section;

        return view('pages.section.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SectionUpdateRequest $request, Section $section): RedirectResponse
    {
        $data = $request->except('_token', '_method');

        $this->section->updateSection($section, $request);

        return back()->with('success', 'Section updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Section $section): RedirectResponse
    {
        $this->section->deleteSection($section);

        return back()->with('success', 'Section deleted successfully');
    }
}
