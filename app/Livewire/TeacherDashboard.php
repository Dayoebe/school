<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\MyClass;
use App\Models\Subject;
use App\Models\StudentRecord;
use App\Models\Result;
use App\Models\TermReport;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Log;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection; // Import Collection for type hinting

class TeacherDashboard extends Component
{
    use WithPagination;

    #[Layout('layouts.pages')]
    public $activeTab = 'overview';

    // Properties for Overview Tab - will be populated in mount from cached data
    public $assignedSubjectsCount = 0;
    public $classesTaughtCount = 0;
    public $totalStudentsInMyClasses = 0;

    // Properties for My Subjects Tab - will be loaded on demand
    public array $subjectsAssignedToTeacher = []; // Use array for simpler hydration

    // Properties for My Classes Tab - will be loaded on demand
    public array $teacherClasses = []; // Use array for simpler hydration

    // Properties for selection filters - always available
    public Collection $academicYears;
    public Collection $semesters;
    public Collection $classesForSelection;
    public Collection $subjectsForSelection;

    // Selected filter IDs
    public $selectedAcademicYearId;
    public $selectedSemesterId;
    public $selectedClassId;
    public $selectedSubjectId;

    // View Results properties - loaded on demand
    public array $viewResultsData = [];
    public Collection $viewStudentRecords; // Store as Collection for easier iteration in blade

    // Upload Results properties - loaded on demand
    public Collection $uploadStudents; // Store as Collection
    public array $uploadScores = [];

    // Comments properties - loaded on demand
    public Collection $commentStudents; // Store as Collection
    public array $commentsData = [];

    // Initial default values for psychomotor and affective traits for new term reports
    private array $defaultPsychomotorScores;
    private array $defaultAffectiveScores;
    private array $defaultCoCurricularScores;


    protected $rules = [
        'selectedAcademicYearId' => 'required|exists:academic_years,id',
        'selectedSemesterId' => 'required|exists:semesters,id',
        'selectedClassId' => 'required|exists:my_classes,id',
        'selectedSubjectId' => 'nullable|exists:subjects,id',
        
        // Upload Results rules
        'uploadScores.*.ca1_score' => 'nullable|integer|min:0|max:20',
        'uploadScores.*.ca2_score' => 'nullable|integer|min:0|max:20',
        'uploadScores.*.ca3_score' => 'nullable|integer|min:0|max:20',
        'uploadScores.*.ca4_score' => 'nullable|integer|min:0|max:20',
        'uploadScores.*.exam_score' => 'nullable|integer|min:0|max:60',
        'uploadScores.*.teacher_comment' => 'nullable|string|max:500',
        
        // Comments rules
        'commentsData.*.class_teacher_comment' => 'nullable|string|max:500',
        'commentsData.*.principal_comment' => 'nullable|string|max:500',
        'commentsData.*.resumption_date' => 'nullable|date',
        'commentsData.*.present_days' => 'nullable|integer|min:0',
        'commentsData.*.absent_days' => 'nullable|integer|min:0',
        'commentsData.*.psychomotorScores.*' => 'nullable|integer|min:1|max:5',
        'commentsData.*.affectiveScores.*' => 'nullable|integer|min:1|max:5',
        'commentsData.*.coCurricularScores.*' => 'nullable|integer|min:1|max:5',
    ];

    /**
     * The mount method is called once when the component is initially rendered.
     * It's used to set up initial data that doesn't change frequently.
     */
    public function mount()
    {
        Log::info('TeacherDashboard mount method started.');
        $user = auth()->user();
        $school = $user->school; // Ensure school is loaded for the user

        // Initialize default scores for comments tab
        $this->defaultPsychomotorScores = TermReport::getDefaultPsychomotorScores();
        $this->defaultAffectiveScores = TermReport::getDefaultAffectiveScores();
        $this->defaultCoCurricularScores = TermReport::getDefaultCoCurricularScores();

        // Load academic years for the filter dropdowns. This is relatively static.
        $this->academicYears = AcademicYear::select('id', 'start_year', 'stop_year')
            ->where('school_id', $school->id ?? null) // Filter by school if school exists
            ->orderBy('start_year', 'desc')
            ->get();
        \Log::info('Academic years loaded.');

        // Set initial selected academic year and semester based on school defaults or first available
        $this->selectedAcademicYearId = $school?->academic_year_id ?? $this->academicYears->first()?->id;
        $this->selectedSemesterId = $school?->semester_id ?? null;

        // Initialize empty collections for reactive properties that will be loaded on demand
        $this->semesters = collect();
        $this->classesForSelection = collect();
        $this->subjectsForSelection = collect();
        $this->viewStudentRecords = collect();
        $this->uploadStudents = collect();
        $this->commentStudents = collect();

        // Load initial dashboard overview data and selection data
        $this->loadInitialDashboardAndSelectionData($user);
        \Log::info('Initial dashboard and selection data loaded.');

        // Load semesters and subjects for selection based on initial academic year and class
        $this->loadSemestersAndSubjectsForSelection();
        \Log::info('Semesters and subjects for selection loaded.');
        \Log::info('TeacherDashboard mount method finished.');
    }

    /**
     * Loads initial dashboard overview data (counts) and data for selection dropdowns.
     * This data is cached to improve performance on subsequent loads.
     *
     * @param \App\Models\User $user The authenticated user (teacher).
     */
    private function loadInitialDashboardAndSelectionData($user)
    {
        $cacheKey = "teacher_dashboard_overview_data_{$user->id}";
        
        // Cache for 5 minutes (300 seconds)
        $overviewData = Cache::remember($cacheKey, 300, function () use ($user) {
            \Log::info('Loading initial dashboard data from DB (or cache miss).');
            // Fetch subjects assigned to the teacher with minimal data
            $assignedSubjects = $user->assignedSubjects()
                ->select('subjects.id', 'subjects.name', 'subjects.short_name', 'subjects.my_class_id')
                ->get();

            $assignedSubjectsCount = $assignedSubjects->count();
            
            // Get unique class IDs from assigned subjects
            $classIds = $assignedSubjects->pluck('my_class_id')->unique()->filter()->values();
            $classesTaughtCount = $classIds->count();

            // Get total student count across all classes taught by the teacher
            $totalStudentsInMyClasses = $classIds->isNotEmpty() 
                ? StudentRecord::whereIn('my_class_id', $classIds)->count()
                : 0;

            // Get classes with their total student counts for 'My Classes' tab and selection dropdowns
            $teacherClasses = $classIds->isNotEmpty() 
                ? MyClass::select('id', 'name')
                    ->whereIn('id', $classIds)
                    ->withCount('studentRecords') // Counts all students in the class
                    ->get()
                : collect();

            return [
                'assignedSubjectsCount' => $assignedSubjectsCount,
                'classesTaughtCount' => $classesTaughtCount,
                'totalStudentsInMyClasses' => $totalStudentsInMyClasses,
                // Convert collections to arrays to reduce Livewire hydration overhead for public properties
                'subjectsAssignedToTeacher' => $assignedSubjects->map(fn($s) => [
                    'id' => $s->id, 
                    'name' => $s->name, 
                    'short_name' => $s->short_name, 
                    'my_class_id' => $s->my_class_id
                ])->toArray(),
                'teacherClasses' => $teacherClasses->map(fn($c) => [
                    'id' => $c->id, 
                    'name' => $c->name, 
                    'student_records_count' => $c->student_records_count
                ])->toArray(),
                'classesForSelection' => $teacherClasses->map(fn($class) => [
                    'id' => $class->id, 
                    'name' => $class->name
                ])->toArray(),
            ];
        });

        // Set public properties from the cached data
        $this->assignedSubjectsCount = $overviewData['assignedSubjectsCount'];
        $this->classesTaughtCount = $overviewData['classesTaughtCount'];
        $this->totalStudentsInMyClasses = $overviewData['totalStudentsInMyClasses'];
        $this->subjectsAssignedToTeacher = $overviewData['subjectsAssignedToTeacher'];
        $this->teacherClasses = $overviewData['teacherClasses'];
        $this->classesForSelection = collect($overviewData['classesForSelection']); // Convert back to Collection for Livewire
    }

    /**
     * Listener for when the selected academic year changes.
     * Resets other selections and reloads dependent dropdowns and clears tab data.
     */
    public function updatedSelectedAcademicYearId()
    {
        \Log::info('Selected Academic Year changed: ' . $this->selectedAcademicYearId);
        $this->reset(['selectedSemesterId', 'selectedClassId', 'selectedSubjectId']); // Reset all dependent filters
        $this->loadSemestersAndSubjectsForSelection();
        $this->clearTabData();
    }

    /**
     * Listener for when the selected semester changes.
     * Clears tab data as results depend on semester.
     */
    public function updatedSelectedSemesterId()
    {
        \Log::info('Selected Semester changed: ' . $this->selectedSemesterId);
        $this->clearTabData();
        // If the active tab is one of the results/comments tabs, reload it
        if (in_array($this->activeTab, ['view-results', 'upload-results', 'comments'])) {
            $this->updatedActiveTab($this->activeTab);
        }
    }

    /**
     * Listener for when the selected class changes.
     * Resets subject selection and reloads subjects for selection and clears tab data.
     */
    public function updatedSelectedClassId()
    {
        \Log::info('Selected Class changed: ' . $this->selectedClassId);
        $this->reset('selectedSubjectId'); // Reset subject as it's class-dependent
        $this->loadSemestersAndSubjectsForSelection();
        $this->clearTabData();
        // If the active tab is one of the results/comments tabs, reload it
        if (in_array($this->activeTab, ['view-results', 'upload-results', 'comments'])) {
            $this->updatedActiveTab($this->activeTab);
        }
    }

    /**
     * Listener for when the selected subject changes.
     * Triggers loading of data for results-related tabs.
     */
    public function updatedSelectedSubjectId()
    {
        \Log::info('Selected Subject changed: ' . $this->selectedSubjectId);
        // Only load data for tabs that need subject selection
        if ($this->activeTab === 'view-results') {
            $this->loadViewResultsData();
        } elseif ($this->activeTab === 'upload-results') {
            $this->loadUploadStudentsAndResults();
        }
    }

    /**
     * Clears all data related to specific tabs to ensure fresh loading.
     */
    private function clearTabData()
    {
        \Log::info('Clearing tab data.');
        $this->viewResultsData = [];
        $this->viewStudentRecords = collect();
        $this->uploadStudents = collect();
        $this->uploadScores = [];
        $this->commentStudents = collect();
        $this->commentsData = [];
    }

    /**
     * Loads semesters and subjects for the selection dropdowns based on
     * the currently selected academic year and class.
     */
    private function loadSemestersAndSubjectsForSelection()
    {
        \Log::info('Loading semesters and subjects for selection.');
        $this->semesters = collect();
        $this->subjectsForSelection = collect();

        if ($this->selectedAcademicYearId) {
            $this->semesters = Semester::select('id', 'name', 'academic_year_id')
                ->where('academic_year_id', $this->selectedAcademicYearId)
                ->get();
        }

        if ($this->selectedClassId) {
            // Get subjects assigned to the current teacher
            $teacherSubjectIds = auth()->user()->assignedSubjects()->pluck('subjects.id');
            
            // Filter subjects by the selected class and those assigned to the teacher
            $this->subjectsForSelection = Subject::select('id', 'name', 'my_class_id')
                ->where('my_class_id', $this->selectedClassId)
                ->whereIn('id', $teacherSubjectIds)
                ->get();
        }
        \Log::info('Finished loading semesters and subjects for selection.');
    }

    /**
     * Loads student results data for the 'View Results' tab.
     * Optimized with eager loading and selective columns.
     */
    private function loadViewResultsData()
    {
        \Log::info('Loading view results data.');
        $this->viewResultsData = [];
        $this->viewStudentRecords = collect();

        if (!$this->canLoadResults()) {
            \Log::info('Cannot load view results: criteria not met.');
            return;
        }

        // --- Optimization for 'whereHas' without new indexes ---
        // Fetch student_record_ids that are associated with the selected subject
        $studentRecordsWithSubjectIds = DB::table('student_subject')
            ->where('subject_id', $this->selectedSubjectId)
            ->pluck('student_record_id');

        if ($studentRecordsWithSubjectIds->isEmpty()) {
            \Log::info('No students found for the selected subject in student_subject pivot table.');
            return; // No students for this subject, so nothing to load
        }
        // --- End Optimization ---

        $this->viewStudentRecords = StudentRecord::select('id', 'user_id', 'my_class_id')
            ->where('my_class_id', $this->selectedClassId)
            ->whereIn('id', $studentRecordsWithSubjectIds) // Use whereIn with pre-fetched IDs
            ->with([
                'user:id,name', // Only fetch ID and name for the user
                'results' => function ($query) {
                    $query->select('id', 'student_record_id', 'ca1_score', 'ca2_score', 'ca3_score', 'ca4_score', 'exam_score', 'total_score', 'teacher_comment', 'approved')
                          ->where('academic_year_id', $this->selectedAcademicYearId)
                          ->where('semester_id', $this->selectedSemesterId)
                          ->where('subject_id', $this->selectedSubjectId);
                }
            ])
            ->get();

        foreach ($this->viewStudentRecords as $student) {
            $result = $student->results->first(); // Get the single result for this subject/semester/year
            $this->viewResultsData[$student->id] = [
                'student_name' => $student->user->name,
                'ca1_score' => $result?->ca1_score,
                'ca2_score' => $result?->ca2_score,
                'ca3_score' => $result?->ca3_score,
                'ca4_score' => $result?->ca4_score,
                'exam_score' => $result?->exam_score,
                'total_score' => $result?->total_score,
                'teacher_comment' => $result?->teacher_comment,
                'approved' => $result?->approved ?? false,
            ];
        }
        \Log::info('Finished loading view results data.');
    }

    /**
     * Loads student data and existing scores for the 'Upload Results' tab.
     * Optimized with eager loading and selective columns.
     */
    public function loadUploadStudentsAndResults()
    {
        \Log::info('Loading upload students and results data.');
        $this->uploadStudents = collect();
        $this->uploadScores = [];

        if (!$this->canLoadResults()) {
            \Log::info('Cannot load upload results: criteria not met.');
            return;
        }

        // --- Optimization for 'whereHas' without new indexes ---
        // Fetch student_record_ids that are associated with the selected subject
        $studentRecordsWithSubjectIds = DB::table('student_subject')
            ->where('subject_id', $this->selectedSubjectId)
            ->pluck('student_record_id');

        if ($studentRecordsWithSubjectIds->isEmpty()) {
            \Log::info('No students found for the selected subject in student_subject pivot table.');
            return; // No students for this subject, so nothing to load
        }
        // --- End Optimization ---

        // Fetch student records with their user info and results for the current selections
        $this->uploadStudents = StudentRecord::select('id', 'user_id', 'my_class_id')
            ->where('my_class_id', $this->selectedClassId)
            ->whereIn('id', $studentRecordsWithSubjectIds) // Use whereIn with pre-fetched IDs
            ->with([
                'user:id,name', // Only fetch ID and name for the user
                'results' => function ($query) {
                    $query->select('id', 'student_record_id', 'ca1_score', 'ca2_score', 'ca3_score', 'ca4_score', 'exam_score', 'teacher_comment')
                          ->where('academic_year_id', $this->selectedAcademicYearId)
                          ->where('semester_id', $this->selectedSemesterId)
                          ->where('subject_id', $this->selectedSubjectId);
                }
            ])
            ->get();

        // Populate uploadScores array with existing data or default values
        foreach ($this->uploadStudents as $student) {
            $result = $student->results->first();
            $this->uploadScores[$student->id] = [
                'ca1_score' => $result?->ca1_score,
                'ca2_score' => $result?->ca2_score,
                'ca3_score' => $result?->ca3_score,
                'ca4_score' => $result?->ca4_score,
                'exam_score' => $result?->exam_score,
                'teacher_comment' => $result?->teacher_comment ?? '',
                'total_score' => $result?->total_score, // Include total score for display if exists
            ];
        }
        \Log::info('Finished loading upload students and results data.');
    }

    /**
     * Checks if all necessary filter selections (Academic Year, Semester, Class, Subject)
     * are made to load results or comments.
     *
     * @return bool
     */
    private function canLoadResults(): bool
    {
        return $this->selectedAcademicYearId && 
               $this->selectedSemesterId && 
               $this->selectedClassId && 
               $this->selectedSubjectId;
    }

    /**
     * Calculates the total score for a student and auto-generates a teacher comment.
     * This method is called when input fields for scores are updated.
     *
     * @param int $studentId The ID of the student.
     */
    public function calculateTotal($studentId)
    {
        \Log::info('Calculating total for student: ' . $studentId);
        // Ensure the scores are treated as integers, defaulting to 0 if null
        $ca1 = (int)($this->uploadScores[$studentId]['ca1_score'] ?? 0);
        $ca2 = (int)($this->uploadScores[$studentId]['ca2_score'] ?? 0);
        $ca3 = (int)($this->uploadScores[$studentId]['ca3_score'] ?? 0);
        $ca4 = (int)($this->uploadScores[$studentId]['ca4_score'] ?? 0);
        $exam = (int)($this->uploadScores[$studentId]['exam_score'] ?? 0);
        
        $total = $ca1 + $ca2 + $ca3 + $ca4 + $exam;
        
        // Auto-generate comment based on total score
        $this->uploadScores[$studentId]['total_score'] = $total; // Store total score in the array
        $this->uploadScores[$studentId]['teacher_comment'] = match(true) {
            $total >= 75 => 'Excellent work, keep it up!',
            $total >= 70 => 'Very good performance.',
            $total >= 60 => 'Good effort, continue to improve.',
            $total >= 50 => 'Fair performance, more effort needed.',
            $total >= 40 => 'Pass mark, requires significant improvement.',
            default => 'Needs serious attention and extra effort.'
        };
        \Log::info('Total calculated for student ' . $studentId . ': ' . $total);
    }

    /**
     * Saves or updates student results for the selected subject, class, academic year, and semester.
     * Uses database transactions and upsert for efficiency.
     */
    public function saveResults()
    {
        \Log::info('Attempting to save results.');
        // Validate inputs before saving
        $this->validate([
            'selectedAcademicYearId' => 'required|exists:academic_years,id',
            'selectedSemesterId' => 'required|exists:semesters,id',
            'selectedClassId' => 'required|exists:my_classes,id',
            'selectedSubjectId' => 'required|exists:subjects,id',
            'uploadScores.*.ca1_score' => 'nullable|integer|min:0|max:20',
            'uploadScores.*.ca2_score' => 'nullable|integer|min:0|max:20',
            'uploadScores.*.ca3_score' => 'nullable|integer|min:0|max:20',
            'uploadScores.*.ca4_score' => 'nullable|integer|min:0|max:20',
            'uploadScores.*.exam_score' => 'nullable|integer|min:0|max:60',
            'uploadScores.*.teacher_comment' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $resultsData = [];
            foreach ($this->uploadScores as $studentId => $data) {
                // Calculate total score again to ensure consistency
                $ca1 = (int)($data['ca1_score'] ?? 0);
                $ca2 = (int)($data['ca2_score'] ?? 0);
                $ca3 = (int)($data['ca3_score'] ?? 0);
                $ca4 = (int)($data['ca4_score'] ?? 0);
                $exam = (int)($data['exam_score'] ?? 0);
                $total = $ca1 + $ca2 + $ca3 + $ca4 + $exam;

                $resultsData[] = [
                    'student_record_id' => $studentId,
                    'subject_id' => $this->selectedSubjectId,
                    'academic_year_id' => $this->selectedAcademicYearId,
                    'semester_id' => $this->selectedSemesterId,
                    'ca1_score' => $ca1,
                    'ca2_score' => $ca2,
                    'ca3_score' => $ca3,
                    'ca4_score' => $ca4,
                    'exam_score' => $exam,
                    'total_score' => $total,
                    'teacher_comment' => $data['teacher_comment'],
                    'approved' => false, // Results are not approved by default by teacher
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Use upsert to insert new records or update existing ones in a single query
            Result::upsert(
                $resultsData,
                ['student_record_id', 'subject_id', 'academic_year_id', 'semester_id'], // Unique by these columns
                ['ca1_score', 'ca2_score', 'ca3_score', 'ca4_score', 'exam_score', 'total_score', 'teacher_comment', 'updated_at'] // Columns to update
            );

            DB::commit();
            session()->flash('success', 'Results saved successfully!');
            \Log::info('Results saved successfully.');
            
            // Clear cache for this teacher's overview data as counts might change
            Cache::forget("teacher_dashboard_overview_data_" . auth()->id());
            
            // Reload the data for the upload tab to reflect saved changes
            $this->loadUploadStudentsAndResults();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to save results: ' . $e->getMessage());
            // Log the exception for debugging
            \Log::error("Failed to save results: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
        }
    }

    /**
     * Loads student data and existing term reports for the 'Comments' tab.
     * Optimized with eager loading and selective columns.
     */
    public function loadCommentStudentsAndTermReports()
    {
        \Log::info('Loading comment students and term reports.');
        $this->commentStudents = collect();
        $this->commentsData = [];

        if (!($this->selectedAcademicYearId && $this->selectedSemesterId && $this->selectedClassId)) {
            \Log::info('Cannot load comments: criteria not met.');
            return;
        }

        $this->commentStudents = StudentRecord::select('id', 'user_id', 'my_class_id')
            ->where('my_class_id', $this->selectedClassId)
            ->with([
                'user:id,name', // Only fetch ID and name for the user
                'termReports' => function ($query) {
                    $query->select('id', 'student_record_id', 'class_teacher_comment', 'principal_comment', 'resumption_date', 'present_days', 'absent_days', 'psychomotor_traits', 'affective_traits', 'co_curricular_activities')
                          ->where('academic_year_id', $this->selectedAcademicYearId)
                          ->where('semester_id', $this->selectedSemesterId);
                }
            ])
            ->get();

        foreach ($this->commentStudents as $student) {
            $termReport = $student->termReports->first();
            
            $this->commentsData[$student->id] = [
                'class_teacher_comment' => $termReport?->class_teacher_comment ?? '',
                'principal_comment' => $termReport?->principal_comment ?? '',
                'resumption_date' => $termReport?->resumption_date ? Carbon::parse($termReport->resumption_date)->format('Y-m-d') : '',
                'present_days' => $termReport?->present_days,
                'absent_days' => $termReport?->absent_days,
                // Ensure default arrays are used if no existing data
                'psychomotorScores' => $termReport ? $termReport->psychomotor_traits : $this->defaultPsychomotorScores,
                'affectiveScores' => $termReport ? $termReport->affective_traits : $this->defaultAffectiveScores,
                'coCurricularScores' => $termReport ? $termReport->co_curricular_activities : $this->defaultCoCurricularScores,
            ];
        }
        \Log::info('Finished loading comment students and term reports.');
    }

    /**
     * Saves or updates student comments and traits for the selected class, academic year, and semester.
     * Uses database transactions and upsert for efficiency.
     */
    public function saveComments()
    {
        \Log::info('Attempting to save comments.');
        // Validate inputs before saving
        $this->validate([
            'selectedAcademicYearId' => 'required|exists:academic_years,id',
            'selectedSemesterId' => 'required|exists:semesters,id',
            'selectedClassId' => 'required|exists:my_classes,id',
            'commentsData.*.class_teacher_comment' => 'nullable|string|max:500',
            'commentsData.*.principal_comment' => 'nullable|string|max:500',
            'commentsData.*.resumption_date' => 'nullable|date',
            'commentsData.*.present_days' => 'nullable|integer|min:0',
            'commentsData.*.absent_days' => 'nullable|integer|min:0',
            'commentsData.*.psychomotorScores.*' => 'nullable|integer|min:1|max:5',
            'commentsData.*.affectiveScores.*' => 'nullable|integer|min:1|max:5',
            'commentsData.*.coCurricularScores.*' => 'nullable|integer|min:1|max:5',
        ]);

        DB::beginTransaction();
        try {
            $termReportsData = [];
            foreach ($this->commentsData as $studentId => $data) {
                $termReportsData[] = [
                    'student_record_id' => $studentId,
                    'academic_year_id' => $this->selectedAcademicYearId,
                    'semester_id' => $this->selectedSemesterId,
                    'class_teacher_comment' => $data['class_teacher_comment'],
                    'principal_comment' => $data['principal_comment'],
                    'resumption_date' => $data['resumption_date'],
                    'present_days' => $data['present_days'],
                    'absent_days' => $data['absent_days'],
                    // Encode arrays to JSON strings for database storage
                    'psychomotor_traits' => json_encode($data['psychomotorScores']),
                    'affective_traits' => json_encode($data['affectiveScores']),
                    'co_curricular_activities' => json_encode($data['coCurricularScores']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Use upsert to insert new records or update existing ones
            TermReport::upsert(
                $termReportsData,
                ['student_record_id', 'academic_year_id', 'semester_id'], // Unique by these columns
                ['class_teacher_comment', 'principal_comment', 'resumption_date', 'present_days', 'absent_days', 'psychomotor_traits', 'affective_traits', 'co_curricular_activities', 'updated_at'] // Columns to update
            );

            DB::commit();
            session()->flash('success', 'Comments and traits saved successfully!');
            \Log::info('Comments and traits saved successfully.');
            // Reload the data for the comments tab to reflect saved changes
            $this->loadCommentStudentsAndTermReports();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to save comments: ' . $e->getMessage());
            // Log the exception for debugging
            \Log::error("Failed to save comments: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
        }
    }

    /**
     * Handles tab switching. This method is called by Livewire when `activeTab` property changes.
     * It ensures data for a tab is loaded only when that tab is activated and its data is not already present.
     *
     * @param string $tab The name of the tab being activated.
     */
    public function updatedActiveTab($tab)
    {
        \Log::info('Active tab changed to: ' . $tab);
        // Clear data for previous tab if needed, but only if the new tab requires a fresh load
        $this->clearTabData(); 

        switch ($tab) {
            case 'view-results':
                if ($this->canLoadResults()) { // Always attempt to load if criteria met
                    $this->loadViewResultsData();
                }
                break;
            case 'upload-results':
                if ($this->canLoadResults()) { // Always attempt to load if criteria met
                    $this->loadUploadStudentsAndResults();
                }
                break;
            case 'comments':
                if ($this->selectedAcademicYearId && $this->selectedSemesterId && $this->selectedClassId) {
                    $this->loadCommentStudentsAndTermReports();
                }
                break;
            // 'overview', 'my-subjects', 'my-classes' tabs might not need specific data loading methods here
            // as their data is often loaded in mount or is simple enough to be directly available.
            // If 'my-subjects' or 'my-classes' become complex, create dedicated load methods.
        }
        \Log::info('Finished processing updatedActiveTab for: ' . $tab);
    }

    /**
     * The render method is called on every Livewire request.
     * Keep it lean by only passing data that is already prepared by other methods.
     */
    public function render()
    {
        \Log::info('TeacherDashboard render method started.');
        // The academicYear and semester are already available as public properties
        // or can be derived from selected IDs if the full model is needed for display.
        $currentAcademicYear = $this->selectedAcademicYearId ? AcademicYear::find($this->selectedAcademicYearId) : null;
        $currentSemester = $this->selectedSemesterId ? Semester::find($this->selectedSemesterId) : null;

        \Log::info('TeacherDashboard render method finished.');
        return view('dashboard', [
            'academicYear' => $currentAcademicYear,
            'semester' => $currentSemester,
            // Other data like subjectsAssignedToTeacher, teacherClasses, etc., are public properties
            // and are directly accessible in the blade view.
        ]);
    }
}

