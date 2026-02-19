<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSyllabusRequest;
use App\Http\Requests\UpdateSyllabusRequest;
use App\Models\Syllabus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SyllabusController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Syllabus::class, 'syllabus');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('livewire.syllabi.pages.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('livewire.syllabi.pages.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSyllabusRequest $request)
    {
        $data = $request->except(['_token']);

        $filePath = $data['file']->store('syllabus/', 'public');

        Syllabus::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'file' => $filePath,
            'subject_id' => $data['subject_id'],
            'semester_id' => auth()->user()->school->semester_id,
        ]);

        return back()->with('success', 'Successfully created Syllabus');
    }

    /**
     * Display the specified resource.
     */
    public function show(Syllabus $syllabus): View
    {
        return view('livewire.syllabi.pages.show', compact('syllabus'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Syllabus $syllabus): Response
    {
        abort(404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSyllabusRequest $request, Syllabus $syllabus): Response
    {
        abort(404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Syllabus $syllabus): RedirectResponse
    {
        Storage::disk('public')->delete($syllabus->file);
        $syllabus->delete();

        return back()->with('success', 'Successfully deleted Syllabus');
    }
}
