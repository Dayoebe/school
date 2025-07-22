<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\MyClass;
use App\Models\User;
use App\Models\Result;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http; // For making HTTP requests to internal APIs
use Illuminate\Validation\ValidationException;

class TeacherDashboard extends Component
{
    // Public properties that will be reactive and accessible in the Blade view
    public $subjects;
    public $academicYearId;
    public $semesterId;
    public $teacherClasses;
    public $upcomingEvents;
    public $allAcademicYears;
    public $allSemesters;

    public $activeTab = 'subjects'; // Controls which tab is active
    public $showStudentsModal = false; // Controls visibility of the students modal
    public $showUploadModal = false; // Controls visibility of the upload results modal
    public $loading = false; // General loading indicator
    public $studentList = []; // Stores students for the students modal
    public $studentResults = []; // Stores student results for the upload modal
    public $currentSubjectName = ''; // Displays the name of the currently selected subject in modals
    public $uploadSubjectId; // Stores the ID of the subject selected for result upload

    // Properties for 'View Results' tab
    public $selectedAcademicYearForView; // Selected academic year for viewing historical results
    public $selectedSemesterForView; // Selected semester for viewing historical results
    public $selectedSubjectForView = ''; // Selected subject for viewing historical results (optional)
    public $semestersForSelectedYear = []; // Filtered semesters based on selected academic year
    public $viewedResults = []; // Stores historical results for display
    public $viewResultsLoading = false; // Loading indicator for historical results

    // Custom modal state for alerts/messages
    public $modal = [
        'show' => false,
        'title' => '',
        'message' => '',
        'type' => 'success' // Can be 'success' or 'error'
    ];

    // Listeners for events emitted by other Livewire components (e.g., set-academic-year, set-semester)
    protected $listeners = [
        'academicYearUpdated' => 'updateAcademicYear',
        'semesterUpdated' => 'updateSemester',
        'renderCharts' => 'renderChartsData' // Listener for chart rendering
    ];

    /**
     * The mount method is called once when the component is initialized.
     * It's used to fetch initial data for the dashboard.
     */
    public function mount()
    {
        $user = Auth::user();

        // Ensure user and school exist before trying to access relationships
        $academicYear = null;
        $semester = null;

        if ($user && $user->school) {
            // Eager load 'semesters' relationship to prevent N+1 query
            $academicYear = $user->school->academicYear()->with('semesters')->first() ?? AcademicYear::latest()->with('semesters')->first();
            $semester = $academicYear ? $academicYear->semesters->sortByDesc('id')->first() : null; // Access as collection property
        } else {
            // Fallback if user or school is not set, eager load semesters here too
            $academicYear = AcademicYear::latest()->with('semesters')->first();
            $semester = $academicYear ? $academicYear->semesters->sortByDesc('id')->first() : null; // Access as collection property
        }

        // Set initial academic year and semester IDs for the component, defaulting to null if not found
        $this->academicYearId = $academicYear->id ?? null;
        $this->semesterId = $semester->id ?? null;
        $this->selectedAcademicYearForView = $this->academicYearId;
        $this->selectedSemesterForView = $this->semesterId;

        // Load teacher's subjects and their associated classes, and count student records
        // Ensure $user->subjects is always a collection before calling toArray() or pluck()
        $userSubjects = collect();
        if ($user) {
            $user->load([
                'subjects' => function ($query) {
                    $query->with('myClass')->withCount('studentRecords');
                },
            ]);
            $userSubjects = $user->subjects;
        }
        $this->subjects = $userSubjects->toArray();

        // Get unique classes taught by the teacher and their student counts
        $teacherClassesCollection = $userSubjects->pluck('myClass')
            ->unique('id')
            ->filter()
            ->values();

        $classIds = $teacherClassesCollection->pluck('id')->toArray();
        $teacherClassesWithCounts = collect(); // Default to empty collection
        if (!empty($classIds)) {
            $teacherClassesWithCounts = MyClass::whereIn('id', $classIds)
                ->withCount('studentRecords')
                ->get()
                ->keyBy('id');
        }

        $this->teacherClasses = $teacherClassesCollection->map(function ($classItem) use ($teacherClassesWithCounts) {
            if ($teacherClassesWithCounts->has($classItem->id)) {
                $classItem->student_records_count = $teacherClassesWithCounts[$classItem->id]->student_records_count;
            } else {
                $classItem->student_records_count = 0;
            }
            return $classItem;
        })->toArray();

        // Fetch upcoming events (dummy data or from a real source)
        $this->upcomingEvents = $this->getUpcomingEvents();

        // Fetch all academic years and semesters for dropdowns
        // Eager load semesters here as well for the dropdowns to prevent N+1 when accessing semesters later
        $this->allAcademicYears = AcademicYear::with('semesters')->orderBy('start_year', 'desc')->get()->toArray();
        $this->allSemesters = Semester::all()->toArray();

        // Initialize semesters for the view results tab based on the current academic year
        $this->filterSemestersForView();
        // Fetch initial historical results if current academic year and semester are set
        $this->fetchHistoricalResults();
    }

    /**
     * Updates the academic year when an event is dispatched.
     * @param int $academicYearId
     */
    public function updateAcademicYear($academicYearId)
    {
        $this->academicYearId = $academicYearId;
        $this->selectedAcademicYearForView = $academicYearId;
        $this->filterSemestersForView();
        $this->fetchHistoricalResults();
        // Re-render charts if analytics tab is active
        if ($this->activeTab === 'analytics') {
            $this->renderChartsData();
        }
    }

    /**
     * Updates the semester when an event is dispatched.
     * @param int $semesterId
     */
    public function updateSemester($semesterId)
    {
        $this->semesterId = $semesterId;
        $this->selectedSemesterForView = $semesterId;
        $this->fetchHistoricalResults();
        // Re-render charts if analytics tab is active
        if ($this->activeTab === 'analytics') {
            $this->renderChartsData();
        }
    }

    /**
     * Displays a custom modal with a given title, message, and type.
     * @param string $title
     * @param string $message
     * @param string $type ('success' or 'error')
     */
    public function showModal($title, $message, $type = 'success')
    {
        $this->modal = [
            'show' => true,
            'title' => $title,
            'message' => $message,
            'type' => $type
        ];
    }

    /**
     * Closes the custom modal.
     */
    public function closeCustomModal()
    {
        $this->modal['show'] = false;
    }

    /**
     * Retrieves a list of upcoming events.
     * (This can be replaced with a real query to an events table.)
     * @return array
     */
    protected function getUpcomingEvents()
    {
        return [
            [
                'title' => 'Term Examination',
                'date' => now()->addDays(7)->toDateTimeString(),
                'type' => 'exam'
            ],
            [
                'title' => 'Staff Meeting',
                'date' => now()->addDays(2)->toDateTimeString(),
                'type' => 'meeting'
            ]
        ];
    }

    /**
     * Filters the list of semesters based on the selected academic year for viewing results.
     */
    public function filterSemestersForView()
    {
        if ($this->selectedAcademicYearForView) {
            $this->semestersForSelectedYear = collect($this->allSemesters)->filter(function ($semester) {
                return $semester['academic_year_id'] == $this->selectedAcademicYearForView;
            })->values()->toArray();

            // If the currently selected semester is not in the filtered list, reset it
            if (!collect($this->semestersForSelectedYear)->pluck('id')->contains($this->selectedSemesterForView)) {
                $this->selectedSemesterForView = null;
            }
        } else {
            $this->semestersForSelectedYear = [];
            $this->selectedSemesterForView = null;
        }
    }

    /**
     * Opens the students modal and fetches the list of students for a given subject.
     * @param int $subjectId
     */
    public function openStudentsModal($subjectId)
    {
        $subject = collect($this->subjects)->firstWhere('id', $subjectId);
        $this->currentSubjectName = $subject ? $subject['name'] : 'Subject';
        $this->loading = true;
        $this->showStudentsModal = true;
        $this->studentList = [];

        try {
            // Make an HTTP request to your existing API endpoint
            $response = Http::get(route('api.teacher.subject.students', ['subject' => $subjectId]));
            if ($response->successful()) {
                $this->studentList = collect($response->json())->sortBy('name')->values()->toArray();
            } else {
                $this->showModal('Error', $response->json('message', 'Failed to fetch students'), 'error');
            }
        } catch (\Exception $e) {
            $this->showModal('Error', 'An error occurred while fetching students: ' . $e->getMessage(), 'error');
        } finally {
            $this->loading = false;
        }
    }

    /**
     * Opens the upload results modal and fetches existing results for students in a subject.
     */
    public function openUploadModal()
    {
        if (!$this->uploadSubjectId) {
            $this->showModal('Warning', 'Please select a subject first.', 'error');
            return;
        }
        if (!$this->academicYearId || !$this->semesterId) {
            $this->showModal('Warning', 'Academic year or semester is not set. Please contact an administrator.', 'error');
            return;
        }

        $subject = collect($this->subjects)->firstWhere('id', $this->uploadSubjectId);
        $this->currentSubjectName = $subject ? $subject['name'] : 'Subject';
        $this->loading = true;
        $this->showUploadModal = true;
        $this->studentResults = [];

        try {
            // Make an HTTP request to your existing API endpoint
            $response = Http::get(route('api.teacher.results.for-upload', [
                'subject' => $this->uploadSubjectId,
                'academic_year_id' => $this->academicYearId,
                'semester_id' => $this->semesterId
            ]));

            if ($response->successful()) {
                $fetchedResults = $response->json();
                // Map results to ensure empty scores are represented as empty strings for input fields
                $this->studentResults = collect($fetchedResults)->map(function ($student) {
                    return [
                        ...$student,
                        'ca1_score' => ($student['ca1_score'] === null || $student['ca1_score'] === 0) ? '' : $student['ca1_score'],
                        'ca2_score' => ($student['ca2_score'] === null || $student['ca2_score'] === 0) ? '' : $student['ca2_score'],
                        'ca3_score' => ($student['ca3_score'] === null || $student['ca3_score'] === 0) ? '' : $student['ca3_score'],
                        'ca4_score' => ($student['ca4_score'] === null || $student['ca4_score'] === 0) ? '' : $student['ca4_score'],
                        'exam_score' => ($student['exam_score'] === null || $student['exam_score'] === 0) ? '' : $student['exam_score'],
                        'teacher_comment' => $student['teacher_comment'] ?? '',
                    ];
                })->sortBy('name')->values()->toArray();
            } else {
                $this->showModal('Error', $response->json('message', 'Failed to fetch student results for upload'), 'error');
            }
        } catch (\Exception $e) {
            $this->showModal('Error', 'An error occurred while fetching student results: ' . $e->getMessage(), 'error');
        } finally {
            $this->loading = false;
        }
    }

    /**
     * Clears all score inputs and comment for a specific student in the upload modal.
     * @param int $index
     */
    public function clearStudentScores($index)
    {
        $this->studentResults[$index]['ca1_score'] = '';
        $this->studentResults[$index]['ca2_score'] = '';
        $this->studentResults[$index]['ca3_score'] = '';
        $this->studentResults[$index]['ca4_score'] = '';
        $this->studentResults[$index]['exam_score'] = '';
        $this->studentResults[$index]['teacher_comment'] = '';
    }

    /**
     * Saves or updates student results in bulk.
     */
    public function saveResults()
    {
        $this->loading = true;
        try {
            // Prepare payload for the bulk upload API
            $payload = [
                'subject_id' => $this->uploadSubjectId,
                'academic_year_id' => $this->academicYearId,
                'semester_id' => $this->semesterId,
                'results' => collect($this->studentResults)->map(function ($student) {
                    return [
                        'student_record_id' => $student['student_record_id'],
                        'ca1_score' => $student['ca1_score'] === '' ? null : (float)$student['ca1_score'],
                        'ca2_score' => $student['ca2_score'] === '' ? null : (float)$student['ca2_score'],
                        'ca3_score' => $student['ca3_score'] === '' ? null : (float)$student['ca3_score'],
                        'ca4_score' => $student['ca4_score'] === '' ? null : (float)$student['ca4_score'],
                        'exam_score' => $student['exam_score'] === '' ? null : (float)$student['exam_score'],
                        'teacher_comment' => $student['teacher_comment'] ?: null,
                    ];
                })->toArray()
            ];

            // Make an HTTP POST request to your existing API endpoint
            $response = Http::post(route('api.teacher.results.bulk-upload'), $payload);

            if ($response->successful()) {
                $this->showModal('Success', $response->json('message', 'Results saved successfully!'));
                $this->showUploadModal = false;
                $this->fetchHistoricalResults(); // Refresh view results after saving
                if ($this->activeTab === 'analytics') {
                    $this->renderChartsData(); // Re-render charts after saving
                }
            } else {
                $errors = $response->json('errors');
                if ($errors) {
                    $errorMessage = 'Validation Errors:\n';
                    foreach ($errors as $fieldErrors) {
                        $errorMessage .= implode(', ', $fieldErrors) . '\n';
                    }
                    $this->showModal('Validation Error', $errorMessage, 'error');
                } else {
                    $this->showModal('Error', $response->json('message', 'Failed to save results'), 'error');
                }
            }
        } catch (\Exception $e) {
            $this->showModal('Error', 'An error occurred while saving results: ' . $e->getMessage(), 'error');
        } finally {
            $this->loading = false;
        }
    }

    /**
     * Fetches and displays historical results based on selected academic year, semester, and subject.
     */
    public function fetchHistoricalResults()
    {
        if (!$this->selectedAcademicYearForView || !$this->selectedSemesterForView) {
            $this->viewedResults = [];
            return;
        }

        $this->viewResultsLoading = true;
        $this->viewedResults = [];
        $allFetchedResults = [];

        $subjectsToFetch = $this->subjects;
        if ($this->selectedSubjectForView) {
            $subjectsToFetch = collect($this->subjects)->filter(fn($s) => $s['id'] == $this->selectedSubjectForView)->toArray();
        }

        foreach ($subjectsToFetch as $subject) {
            try {
                // Corrected route name here
                $response = Http::get(route('api.teacher.results.for-upload', [
                    'subject' => $subject['id'],
                    'academic_year_id' => $this->selectedAcademicYearForView,
                    'semester_id' => $this->selectedSemesterForView
                ]));

                if ($response->successful()) {
                    $subjectResults = $response->json();
                    // Filter out results that have no scores entered
                    $filteredSubjectResults = collect($subjectResults)->filter(function ($res) {
                        return ($res['ca1_score'] !== null && $res['ca1_score'] !== 0) ||
                            ($res['ca2_score'] !== null && $res['ca2_score'] !== 0) ||
                            ($res['ca3_score'] !== null && $res['ca3_score'] !== 0) ||
                            ($res['ca4_score'] !== null && $res['ca4_score'] !== 0) ||
                            ($res['exam_score'] !== null && $res['exam_score'] !== 0);
                    })->map(function ($res) use ($subject) {
                        return [
                            ...$res,
                            'subject_name' => $subject['name'],
                            'class_name' => $subject['my_class']['name'] ?? 'N/A'
                        ];
                    })->toArray();
                    $allFetchedResults = array_merge($allFetchedResults, $filteredSubjectResults);
                } else {
                    // Log error but continue fetching for other subjects
                    error_log('Error fetching historical results for subject ' . $subject['name'] . ': ' . $response->body());
                }
            } catch (\Exception $e) {
                error_log('Network error fetching historical results for subject ' . $subject['name'] . ': ' . $e->getMessage());
            }
        }

        // Calculate total scores and grades, then sort and assign positions
        $processedResults = collect($allFetchedResults)->map(function ($result) {
            $total = $this->calculateTotal($result);
            $grade = $this->calculateGrade($total);
            return [
                ...$result,
                'total_score' => $total,
                'grade' => $grade,
            ];
        })->sortByDesc('total_score')->values()->toArray(); // Sort by total score descending

        // Assign positions based on sorted total scores
        $currentPosition = 1;
        $previousTotal = -1;
        foreach ($processedResults as $index => &$result) {
            if ($result['total_score'] !== $previousTotal) {
                $currentPosition = $index + 1;
            }
            $result['position'] = $currentPosition;
            $previousTotal = $result['total_score'];
        }
        unset($result); // Break the reference to the last element

        $this->viewedResults = $processedResults;
        $this->viewResultsLoading = false;
    }

    /**
     * Prepares data for chart rendering and dispatches an event to the Blade view.
     */
    public function renderChartsData()
    {
        $yearId = $this->academicYearId;
        $semesterId = $this->semesterId;
        // Ensure academicYearId and semesterId are not null before querying
        if (is_null($yearId) || is_null($semesterId)) {
            $this->dispatch('renderCharts', [
                'averageScores' => ['labels' => [], 'data' => []],
                'gradeDistribution' => ['labels' => [], 'data' => []],
            ]);
            return;
        }

        $allResults = Result::where('academic_year_id', $yearId)
                            ->where('semester_id', $semesterId)
                            ->get();

        // Prepare Average Score by Subject data
        $subjectAverages = [];
        $subjectNames = [];
        // Ensure $this->subjects is an array before iterating
        foreach ($this->subjects ?? [] as $subject) {
            $subjectResults = $allResults->filter(fn($r) => $r->subject_id === $subject['id']);
            if ($subjectResults->count() > 0) {
                $totalScore = $subjectResults->sum(fn($r) => ($r->ca1_score ?? 0) + ($r->ca2_score ?? 0) + ($r->ca3_score ?? 0) + ($r->ca4_score ?? 0) + ($r->exam_score ?? 0));
                $subjectAverages[] = $totalScore / $subjectResults->count();
            } else {
                $subjectAverages[] = 0;
            }
            $subjectNames[] = $subject['name'];
        }

        // Prepare Grade Distribution data
        $gradeCounts = ['A1' => 0, 'B2' => 0, 'B3' => 0, 'C4' => 0, 'C5' => 0, 'C6' => 0, 'D7' => 0, 'E8' => 0, 'F9' => 0];
        $allResults->each(function ($result) use (&$gradeCounts) {
            $total = $this->calculateTotal($result->toArray()); // Pass as array for consistency
            $grade = $this->calculateGrade($total);
            if (isset($gradeCounts[$grade])) {
                $gradeCounts[$grade]++;
            }
        });

        $this->dispatch('renderCharts', [
            'averageScores' => [
                'labels' => $subjectNames,
                'data' => $subjectAverages,
            ],
            'gradeDistribution' => [
                'labels' => array_keys($gradeCounts),
                'data' => array_values($gradeCounts),
            ],
        ]);
    }


    /**
     * Calculates the total score for a student.
     * @param array $student
     * @return float
     */
    public function calculateTotal($student)
    {
        $ca1 = (float)($student['ca1_score'] ?? 0);
        $ca2 = (float)($student['ca2_score'] ?? 0);
        $ca3 = (float)($student['ca3_score'] ?? 0);
        $ca4 = (float)($student['ca4_score'] ?? 0);
        $exam = (float)($student['exam_score'] ?? 0);

        // Ensure scores are within valid ranges
        $validCa1 = min(max($ca1, 0), 10);
        $validCa2 = min(max($ca2, 0), 10);
        $validCa3 = min(max($ca3, 0), 10);
        $validCa4 = min(max($ca4, 0), 10);
        $validExam = min(max($exam, 0), 60);

        return $validCa1 + $validCa2 + $validCa3 + $validCa4 + $validExam;
    }

    /**
     * Calculates the grade based on the total score.
     * @param float $total
     * @return string
     */
    public function calculateGrade($total)
    {
        if ($total >= 75) return 'A1';
        if ($total >= 70) return 'B2';
        if ($total >= 65) return 'B3';
        if ($total >= 60) return 'C4';
        if ($total >= 55) return 'C5';
        if ($total >= 50) return 'C6';
        if ($total >= 45) return 'D7';
        if ($total >= 40) return 'E8';
        return 'F9';
    }

    /**
     * Renders the Livewire component's view.
     * @return \Illuminate\Contracts\View\View
     */
    public function render()
    {
        return view('livewire.teacher-dashboard', [
            'subjects' => $this->subjects,
            'academicYearId' => $this->academicYearId,
            'semesterId' => $this->semesterId,
            'teacherClasses' => $this->teacherClasses,
            'upcomingEvents' => $this->upcomingEvents,
            'allAcademicYears' => $this->allAcademicYears,
            'allSemesters' => $this->allSemesters,
            'activeTab' => $this->activeTab,
            'showStudentsModal' => $this->showStudentsModal,
            'showUploadModal' => $this->showUploadModal,
            'loading' => $this->loading,
            'studentList' => $this->studentList,
            'studentResults' => $this->studentResults,
            'currentSubjectName' => $this->currentSubjectName,
            'uploadSubjectId' => $this->uploadSubjectId,
            'selectedAcademicYearForView' => $this->selectedAcademicYearForView,
            'selectedSemesterForView' => $this->selectedSemesterForView,
            'selectedSubjectForView' => $this->selectedSubjectForView,
            'semestersForSelectedYear' => $this->semestersForSelectedYear,
            'viewedResults' => $this->viewedResults,
            'viewResultsLoading' => $this->viewResultsLoading,
            'modal' => $this->modal,
        ]);
    }
}
