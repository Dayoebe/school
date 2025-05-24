<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentStoreRequest;
use App\Models\User;
use App\Services\Student\StudentService;
use App\Services\User\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use App\Models\Subject;

class StudentController extends Controller
{
    public $student;
    public $userService;
    public $my_class_id;
    public $section_id;

    public function __construct(StudentService $student, UserService $userService)
    {
        $this->student = $student;
        $this->userService = $userService;
    }

    public function index(): View
    {
        $this->authorize('viewAny', [User::class, 'student']);
        return view('pages.student.index');
    }

    public function create(): View
    {
        $this->authorize('create', [User::class, 'student']);
        return view('pages.student.create');
    }
    public function store(StudentStoreRequest $request): RedirectResponse
    {
        $this->authorize('create', [User::class, 'student']);
        $this->student->createStudent($request);

        return back()->with('success', 'Student Created Successfully');
    }

    public function show(User $student): View|Response
    {
        $this->userService->verifyUserIsOfRoleElseNotFound($student, 'student');
        $this->authorize('view', [$student, 'student']);

        if (auth()->user()->hasRole('parent') && $student->parents()->where('parent_records.user_id', auth()->user()->id)->count() <= 0) {
            abort(404);
        }

        return view('pages.student.show', compact('student'));
    }

    public function printProfile(User $student): Response
    {
        $this->userService->verifyUserIsOfRoleElseNotFound($student, 'student');
        $this->authorize('view', [$student, 'student']);
        $data['student'] = $student;

        if (auth()->user()->hasRole('parent') && $student->parents()->where('parent_records.user_id', auth()->user()->id)->count() <= 0) {
            abort(404);
        }

        return $this->student->printProfile($data['student']->name, 'pages.student.print-student-profile', $data);
    }

    public function edit(User $student): View
    {
        $this->userService->verifyUserIsOfRoleElseNotFound($student, 'student');
        $this->authorize('update', [$student, 'student']);
        $data['student'] = $student;

        return view('pages.student.edit', $data);
    }

    public function update(Request $request, User $student): RedirectResponse
    {
        $this->userService->verifyUserIsOfRoleElseNotFound($student, 'student');
        $this->authorize('update', [$student, 'student']);
        $data = $request->except('_token', '_method');
        $this->student->updateStudent($student, $data);

        $student = User::findOrFail($student->id);

        $request->validate([
            'admission_number' => 'nullable|string|max:255',
            'admission_date' => 'nullable|date',
        ]);

        if ($student->studentRecord) {
            $student->studentRecord->update([
                'admission_number' => $request->admission_number,
                'admission_date' => $request->admission_date,
                'my_class_id' => $request->my_class_id,
                'section_id' => $request->section_id,
            ]);
        }

        return back()->with('success', 'Student Updated Successfully');
    }

    public function destroy(User $student): RedirectResponse
    {
        $this->userService->verifyUserIsOfRoleElseNotFound($student, 'student');
        $this->authorize('delete', [$student, 'student']);
        $this->student->deleteStudent($student);

        return back()->with('success', 'Student Deleted Successfully');
    }
}
