<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Teacher\TeacherService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\StudentRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;  
use App\Models\Result;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;

class TeacherController extends Controller
{

    public TeacherService $teacherService;

    /**
     * Get the list of students enrolled in a specific subject, excluding soft-deleted students.
     */
    public function getSubjectStudents(Subject $subject): JsonResponse
    {
        // Ensure 'user' relationship is eager loaded and filter out soft-deleted users
        $students = $subject->studentRecords()
            ->whereHas('user', function ($query) {
                $query->whereNull('deleted_at'); // Only include users that are not soft-deleted
            })
            ->with('user')
            ->get()
            ->map(function ($studentRecord) use ($subject) {
                // Use nullsafe operator (?->) to safely access user properties
                return [
                    'id' => $studentRecord->user?->id, // Safely access user ID
                    'name' => $studentRecord->user?->name, // Safely access user name
                    'admission_number' => $studentRecord->admission_number,
                    'profile_photo_url' => $studentRecord->user?->profile_photo_url, // Safely access profile photo URL
                    'subject_name' => $subject->name,
                ];
            });

        return response()->json($students);
    }

    /**
     * Get students and their existing results for bulk uploading, excluding soft-deleted students
     * and ensuring results are empty by default for new entries.
     */
    // public function getResultsForUpload(Request $request, Subject $subject): JsonResponse
    // {
    //     $request->validate([
    //         'academic_year_id' => 'required|exists:academic_years,id',
    //         'semester_id' => 'required|exists:semesters,id',
    //     ]);

    //     $academicYearId = $request->academic_year_id;
    //     $semesterId = $request->semester_id;

    //     // Get all student records for the subject, eager load their associated user,
    //     // and filter out soft-deleted users.
    //     $studentRecords = $subject->studentRecords()
    //         ->whereHas('user', function ($query) {
    //             $query->whereNull('deleted_at'); // Only include users that are not soft-deleted
    //         })
    //         ->with('user')
    //         ->get();

    //     // Get existing results for these students in the given context
    //     $existingResults = Result::where('subject_id', $subject->id)
    //         ->where('academic_year_id', $academicYearId)
    //         ->where('semester_id', $semesterId)
    //         ->whereIn('student_record_id', $studentRecords->pluck('id'))
    //         ->get()
    //         ->keyBy('student_record_id');

    //     $data = $studentRecords->map(function ($studentRecord) use ($existingResults) {
    //         $result = $existingResults->get($studentRecord->id);
    //         return [


    //             'name' => $studentRecord->user?->name ?? 'Deleted User',
    //             'profile_photo_url' => $studentRecord->user?->profile_photo_url ?? '',

    //             'student_record_id' => $studentRecord->id,
    //             // Use nullsafe operator (?->) to safely access user properties
                
    //             'admission_number' => $studentRecord->admission_number,
                
    //             // If a result exists, use its value; otherwise, default to null for empty input
    //             'ca1_score' => $result->ca1_score ?? null,
    //             'ca2_score' => $result->ca2_score ?? null,
    //             'ca3_score' => $result->ca3_score ?? null,
    //             'ca4_score' => $result->ca4_score ?? null,
    //             'exam_score' => $result->exam_score ?? null,
    //             'teacher_comment' => $result->teacher_comment ?? null,
    //         ];
    //     });

    //     return response()->json($data);
    // }

    /**
     * Bulk save or update student results for a subject.
     */
    public function bulkUploadResults(Request $request): JsonResponse
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id' => 'required|exists:semesters,id',
            'results' => 'required|array',
            'results.*.student_record_id' => 'required|exists:student_records,id',
            'results.*.ca1_score' => 'nullable|numeric|min:0|max:10',
            'results.*.ca2_score' => 'nullable|numeric|min:0|max:10',
            'results.*.ca3_score' => 'nullable|numeric|min:0|max:10',
            'results.*.ca4_score' => 'nullable|numeric|min:0|max:10',
            'results.*.exam_score' => 'nullable|numeric|min:0|max:60',
            'results.*.teacher_comment' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->results as $resultData) {
                // Calculate total score, handling nulls as 0 for calculation
                $totalScore =
                    ($resultData['ca1_score'] ?? 0) +
                    ($resultData['ca2_score'] ?? 0) +
                    ($resultData['ca3_score'] ?? 0) +
                    ($resultData['ca4_score'] ?? 0) +
                    ($resultData['exam_score'] ?? 0);

                // Update or create the result record
                Result::updateOrCreate(
                    [
                        'student_record_id' => $resultData['student_record_id'],
                        'subject_id' => $request->subject_id,
                        'academic_year_id' => $request->academic_year_id,
                        'semester_id' => $request->semester_id,
                    ],
                    [
                        'ca1_score' => $resultData['ca1_score'],
                        'ca2_score' => $resultData['ca2_score'],
                        'ca3_score' => $resultData['ca3_score'],
                        'ca4_score' => $resultData['ca4_score'],
                        'exam_score' => $resultData['exam_score'],
                        'teacher_comment' => $resultData['teacher_comment'],
                        'total_score' => $totalScore,
                        // Assuming the user associated with the student record is the student
                        // Use nullsafe operator here as well for robustness
                        'user_id' => DB::table('student_records')->find($resultData['student_record_id'])?->user_id,
                    ]
                );
            }
        });

        return response()->json(['message' => 'Results saved successfully!']);
    }


    public function __construct(TeacherService $teacherService)
    {
        $this->teacherService = $teacherService;
    }



    public function getStudentsInSubjects(Request $request): JsonResponse
    {
        $teacher = Auth::user();
        $academicYearId = $request->query('academic_year_id');
        $semesterId = $request->query('semester_id');
    
        if (!$academicYearId || !$semesterId) {
            return response()->json(['message' => 'Academic year and semester are required.'], 400);
        }
    
        // Get subjects taught by the current teacher
        $subjectIds = $teacher->subjects->pluck('id');
    
        // Get student records for these subjects in the specified academic year and semester
        $studentRecords = StudentRecord::whereIn('subject_id', $subjectIds)
            ->where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->with('user') // Eager load the user relationship
            ->get();
    
        // Collect unique students
        $students = $studentRecords->map(function ($record) {
            return [
                'id' => $record->user->id,
                'name' => $record->user->name,
                'admission_number' => $record->admission_number,
            ];
        })->unique('id')->values(); // Get unique students and re-index
    
        return response()->json($students);
    }

    
    // public function getStudentPerformance(Request $request, User $student): JsonResponse
    // {
    //     $teacher = Auth::user();
    //     $academicYearId = $request->query('academic_year_id');
    //     $semesterId = $request->query('semester_id');
    
    //     if (!$academicYearId || !$semesterId) {
    //         return response()->json(['message' => 'Academic year and semester are required.'], 400);
    //     }
    
    //     // Get subjects taught by the current teacher
    //     $teacherSubjectIds = $teacher->subjects->pluck('id');
    
    //     // Get the student's student_record_id for the current academic year and semester
    //     $studentRecord = $student->studentRecords()
    //                              ->where('academic_year_id', $academicYearId)
    //                              ->where('semester_id', $semesterId)
    //                              ->first();
    
    //     if (!$studentRecord) {
    //         return response()->json([]); // Student not enrolled in this period or no record
    //     }
    
    //     // Fetch results for this student record in subjects taught by the current teacher
    //     $results = Result::where('student_record_id', $studentRecord->id)
    //                      ->whereIn('subject_id', $teacherSubjectIds) // Only subjects taught by this teacher
    //                      ->with('subject.myClass') // Eager load subject and its class
    //                      ->get()
    //                      ->map(function ($result) {
    //                          return [
    //                              'subject_id' => $result->subject->id,
    //                              'subject_name' => $result->subject->name,
    //                              'class_name' => $result->subject->myClass->name ?? 'N/A',
    //                              'ca1_score' => $result->ca1_score,
    //                              'ca2_score' => $result->ca2_score,
    //                              'ca3_score' => $result->ca3_score,
    //                              'ca4_score' => $result->ca4_score,
    //                              'exam_score' => $result->exam_score,
    //                              // Add other fields if needed for calculation or display
    //                          ];
    //                      });
    
    //     return response()->json($results);
    // }
    public function getStudentPerformance(Request $request, User $student): JsonResponse
    {
        $teacher = Auth::user();
        $academicYearId = $request->query('academic_year_id');
        $semesterId = $request->query('semester_id');

        if (!$academicYearId || !$semesterId) {
            return response()->json(['message' => 'Academic year and semester are required.'], 400);
        }

        // Get subjects taught by the current teacher
        $teacherSubjectIds = $teacher->subjects->pluck('id');

        // Get the student's student_record_id for the current academic year and semester
        $studentRecord = $student->studentRecords()
                             ->where('academic_year_id', $academicYearId)
                             ->where('semester_id', $semesterId)
                             ->first();

        if (!$studentRecord) {
            return response()->json([]); // Student not enrolled in this period or no record
        }

        // Fetch results for this student record in subjects taught by the current teacher
        $results = Result::where('student_record_id', $studentRecord->id)
                     ->whereIn('subject_id', $teacherSubjectIds) // Only subjects taught by this teacher
                     ->with('subject.myClass') // Eager load subject and its class
                     ->get()
                     ->map(function ($result) {
                         return [
                             'subject_id' => $result->subject->id,
                             'subject_name' => $result->subject->name,
                             'class_name' => $result->subject->myClass->name ?? 'N/A',
                             'ca1_score' => $result->ca1_score,
                             'ca2_score' => $result->ca2_score,
                             'ca3_score' => $result->ca3_score,
                             'ca4_score' => $result->ca4_score,
                             'exam_score' => $result->exam_score,
                             'total_score' => $result->total_score, 
                             'teacher_comment' => $result->teacher_comment,
                                'student_record_id' => $result->student_record_id,
                                                             // Add other fields if needed for calculation or display
                         ];
                     });

        return response()->json($results);
    }





    public function getResultsForUpload(Request $request, Subject $subject): JsonResponse
{
    Log::info('getResultsForUpload called with request data:', $request->all());
    Log::info('Subject ID received:', ['subject_id' => $subject->id]);

    $request->validate([
        'academic_year_id' => 'required|exists:academic_years,id',
        'semester_id' => 'required|exists:semesters,id',
    ]);

    $academicYearId = $request->academic_year_id;
    $semesterId = $request->semester_id;

    Log::info('Validated Academic Year ID:', ['academic_year_id' => $academicYearId]);
    Log::info('Validated Semester ID:', ['semester_id' => $semesterId]);

    $studentRecords = $subject->studentRecords()
        ->whereHas('user', function ($query) {
            $query->whereNull('deleted_at');
        })
        ->with('user')
        ->get();

    Log::info('Student Records fetched count:', ['count' => $studentRecords->count()]);
    // Log a sample of student records if count > 0
    if ($studentRecords->count() > 0) {
        Log::info('Sample Student Records:', $studentRecords->take(5)->toArray());
    }

    $existingResults = Result::where('subject_id', $subject->id)
        ->where('academic_year_id', $academicYearId)
        ->where('semester_id', $semesterId)
        ->whereIn('student_record_id', $studentRecords->pluck('id'))
        ->get()
        ->keyBy('student_record_id');

    Log::info('Existing Results fetched count:', ['count' => $existingResults->count()]);
    // Log a sample of existing results if count > 0
    if ($existingResults->count() > 0) {
        Log::info('Sample Existing Results:', $existingResults->take(5)->toArray());
    }

    $data = $studentRecords->map(function ($studentRecord) use ($existingResults) {
        $result = $existingResults->get($studentRecord->id);
        return [
            'name' => $studentRecord->user?->name ?? 'Deleted User',
            'profile_photo_url' => $studentRecord->user?->profile_photo_url ?? '',
            'student_record_id' => $studentRecord->id,
            'admission_number' => $studentRecord->admission_number,
            'ca1_score' => $result->ca1_score ?? null,
            'ca2_score' => $result->ca2_score ?? null,
            'ca3_score' => $result->ca3_score ?? null,
            'ca4_score' => $result->ca4_score ?? null,
            'exam_score' => $result->exam_score ?? null,
            'teacher_comment' => $result->teacher_comment ?? null,
        ];
    });

    Log::info('Final mapped data count for response:', ['count' => $data->count()]);
    // Log a sample of the final data being sent
    if ($data->count() > 0) {
        Log::info('Sample Final mapped data:', $data->take(5)->toArray());
    }

    return response()->json($data);
}
    public function index(): View
    {
        $this->authorize('viewAny', [User::class, 'teacher']);

        return view('pages.teacher.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', [User::class, 'teacher']);

        return view('pages.teacher.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', [User::class, 'teacher']);
        $this->teacherService->createTeacher($request->except('_token'));

        return back()->with('success', 'Teacher Created Successfully');
    }

    /**
     * Display the specified resource.
     *
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(User $teacher): View
    {
        $this->authorize('view', [$teacher, 'teacher']);

        return view('pages.teacher.show', compact('teacher'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(User $teacher): View
    {
        $this->authorize('update', [$teacher, 'teacher']);

        return view('pages.teacher.edit', compact('teacher'));
    }

    /**
     * Update the specified resource in storage.
     *
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request, User $teacher): RedirectResponse
    {
        $this->authorize('update', [$teacher, 'teacher']);
        $this->teacherService->updateTeacher($teacher, $request->except('_token', '_method'));

        return back()->with('success', 'Teacher Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(User $teacher): RedirectResponse
    {
        $this->authorize('delete', [$teacher, 'teacher']);
        $this->teacherService->deleteTeacher($teacher);

        return back()->with('success', 'Teacher Deleted Successfully');
    }
}
