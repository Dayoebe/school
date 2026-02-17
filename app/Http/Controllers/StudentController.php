<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentStoreRequest;
use App\Models\MyClass;
use App\Models\StudentRecord;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class StudentController extends Controller
{
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

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'other_names' => 'nullable|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'nullable|string|min:8|confirmed',
            'birthday' => 'nullable|date',
            'address' => 'nullable|string',
            'blood_group' => 'nullable|string|max:50',
            'religion' => 'nullable|string|max:255',
            'nationality' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'gender' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:50',
            'my_class_id' => 'nullable|exists:my_classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'admission_number' => 'nullable|unique:student_records,admission_number',
            'admission_date' => 'nullable|date',
        ]);

        if (!empty($validated['section_id']) && !empty($validated['my_class_id'])) {
            $class = MyClass::with('sections')->find($validated['my_class_id']);
            if (!$class || !$class->sections->contains('id', $validated['section_id'])) {
                return back()->withErrors([
                    'section_id' => 'Section is not in selected class',
                ])->withInput();
            }
        }

        $name = $validated['name'] ?? trim(sprintf(
            '%s %s %s',
            $validated['first_name'] ?? '',
            $validated['last_name'] ?? '',
            $validated['other_names'] ?? ''
        ));
        $name = trim($name) !== '' ? trim($name) : $validated['email'];

        DB::transaction(function () use ($validated, $name) {
            $user = User::create([
                'name' => $name,
                'email' => $validated['email'],
                'password' => Hash::make($validated['password'] ?? '12345678'),
                'school_id' => auth()->user()->school_id,
                'birthday' => $validated['birthday'] ?? null,
                'address' => $validated['address'] ?? null,
                'blood_group' => $validated['blood_group'] ?? null,
                'religion' => $validated['religion'] ?? null,
                'nationality' => $validated['nationality'] ?? null,
                'state' => $validated['state'] ?? null,
                'city' => $validated['city'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'phone' => $validated['phone'] ?? null,
            ]);

            $user->assignRole('student');

            $studentRecord = $user->studentRecord()->create([
                'my_class_id' => $validated['my_class_id'] ?? null,
                'section_id' => $validated['section_id'] ?? null,
                'admission_number' => $validated['admission_number'] ?? $this->generateAdmissionNumber(),
                'admission_date' => $validated['admission_date'] ?? null,
            ]);

            $currentAcademicYear = auth()->user()->school->academicYear;
            if ($currentAcademicYear) {
                $studentRecord->academicYears()->syncWithoutDetaching([
                    $currentAcademicYear->id => [
                        'my_class_id' => $validated['my_class_id'] ?? null,
                        'section_id' => $validated['section_id'] ?? null,
                    ],
                ]);
            }
        });

        return back()->with('success', 'Student Created Successfully');
    }

    public function show(User $student): View|Response
    {
        $this->ensureStudent($student);
        $this->authorize('view', [$student, 'student']);

        if (auth()->user()->hasRole('parent') && $student->parents()->where('parent_records.user_id', auth()->user()->id)->count() <= 0) {
            abort(404);
        }

        return view('pages.student.show', compact('student'));
    }

    public function printProfile(User $student): Response
    {
        $this->ensureStudent($student);
        $this->authorize('view', [$student, 'student']);
        $data['student'] = $student;

        if (auth()->user()->hasRole('parent') && $student->parents()->where('parent_records.user_id', auth()->user()->id)->count() <= 0) {
            abort(404);
        }

        $pdf = Pdf::loadView('pages.student.print-student-profile', $data);
        $pdf->getDomPDF()->setHttpContext(
            stream_context_create([
                'ssl' => [
                    'allow_self_signed' => true,
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
            ])
        );

        return $pdf->download($data['student']->name . '.pdf');
    }

    public function edit(User $student): View
    {
        $this->ensureStudent($student);
        $this->authorize('update', [$student, 'student']);
        $data['student'] = $student;

        return view('pages.student.edit', $data);
    }

    public function update(Request $request, User $student): RedirectResponse
    {
        $this->ensureStudent($student);
        $this->authorize('update', [$student, 'student']);

        $request->validate([
            'name' => 'nullable|string|max:255',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'other_names' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255|unique:users,email,' . $student->id,
            'password' => 'nullable|string|min:8|confirmed',
            'admission_number' => 'nullable|string|max:255',
            'admission_date' => 'nullable|date',
            'my_class_id' => 'nullable|exists:my_classes,id',
            'section_id' => 'nullable|exists:sections,id',
        ]);

        if ($request->filled('section_id') && $request->filled('my_class_id')) {
            $class = MyClass::with('sections')->find($request->my_class_id);
            if (!$class || !$class->sections->contains('id', $request->section_id)) {
                return back()->withErrors([
                    'section_id' => 'Section is not in selected class',
                ])->withInput();
            }
        }

        $derivedName = trim(sprintf(
            '%s %s %s',
            $request->first_name ?? '',
            $request->last_name ?? '',
            $request->other_names ?? ''
        ));

        $student->update(array_filter([
            'name' => $request->name ?: ($derivedName !== '' ? $derivedName : null),
            'email' => $request->email,
            'birthday' => $request->birthday,
            'address' => $request->address,
            'blood_group' => $request->blood_group,
            'religion' => $request->religion,
            'nationality' => $request->nationality,
            'state' => $request->state,
            'city' => $request->city,
            'gender' => $request->gender,
            'phone' => $request->phone,
        ], fn ($value) => $value !== null));

        if ($request->filled('password')) {
            $student->update(['password' => Hash::make($request->password)]);
        }

        if ($student->studentRecord) {
            $student->studentRecord->update([
                'admission_number' => $request->admission_number,
                'admission_date' => $request->admission_date,
                'my_class_id' => $request->my_class_id,
                'section_id' => $request->section_id,
            ]);

            $currentAcademicYear = auth()->user()->school->academicYear;
            if ($currentAcademicYear) {
                $student->studentRecord->academicYears()->syncWithoutDetaching([
                    $currentAcademicYear->id => [
                        'my_class_id' => $request->my_class_id,
                        'section_id' => $request->section_id,
                    ],
                ]);
            }
        }

        return back()->with('success', 'Student Updated Successfully');
    }

    public function destroy(User $student): RedirectResponse
    {
        $this->ensureStudent($student);
        $this->authorize('delete', [$student, 'student']);
        $student->delete();

        return back()->with('success', 'Student Deleted Successfully');
    }

    private function ensureStudent(User $user): void
    {
        if (!$user->hasRole('student')) {
            abort(404);
        }
    }

    private function generateAdmissionNumber(?int $schoolId = null): string
    {
        $school = $schoolId ? \App\Models\School::find($schoolId) : auth()->user()->school;
        $schoolInitials = $school?->initials;
        $prefix = $schoolInitials ? $schoolInitials . '/' : '';
        $currentYear = date('y');

        do {
            $candidate = $prefix . $currentYear . '/' . mt_rand(100000, 999999);
            $exists = StudentRecord::where('admission_number', $candidate)->exists();
        } while ($exists);

        return $candidate;
    }
}
