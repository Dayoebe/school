<?php

namespace App\Livewire\Students;

use App\Models\MyClass;
use App\Models\Section;
use App\Models\User;
use App\Models\StudentRecord;
use App\Models\Graduation;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Carbon\Carbon;

class GraduateStudents extends Component
{
    public $currentView = 'graduate'; // graduate, history, view

    // Graduation form
    public $classes;
    public $sections = [];
    public $academicYears;
    public $graduateClass = '';
    public $graduateSection = '';
    public $academicYearId = '';
    public $graduationDate = '';
    public $studentsToGraduate = [];
    public $graduationDecisions = [];
    public $remarks = [];
    public $certificateNumbers = [];

    // Search & Filter
    public $searchStudent = '';  // Add this line
    public $filterGradStatus = 'all';  // Add this line

    // Alumni class
    public $alumniClass = null;

    // History
    public $graduations = [];
    public $selectedGraduation = null;
    public $graduatedStudents = [];

    protected $rules = [
        'graduateClass' => 'required|exists:my_classes,id',
        'academicYearId' => 'required|exists:academic_years,id',
        'graduationDate' => 'required|date',
        'graduationDecisions' => 'required|array|min:1',
    ];

    protected $messages = [
        'graduationDecisions.required' => 'Please select at least one student to graduate.',
        'graduationDecisions.min' => 'Please select at least one student to graduate.',
    ];


    public function mount()
    {
        // ✅ Check if we should show history view based on route
        if (request()->route()->getName() === 'students.graduations') {
            $this->currentView = 'history';
        } else {
            $this->currentView = 'graduate';
        }

        $this->classes = MyClass::whereHas('classGroup', function ($query) {
            $query->where('school_id', auth()->user()->school_id);
        })->with('sections')->orderBy('name')->get();

        $this->academicYears = AcademicYear::query()
            ->orderBy('start_year', 'desc')
            ->get();

        if ($this->classes->isNotEmpty()) {
            $this->graduateClass = $this->classes->first()->id;
            $this->loadSections();
        }

        $currentAcademicYear = auth()->user()->school->academicYear;
        if ($currentAcademicYear) {
            $this->academicYearId = $currentAcademicYear->id;
        }

        $this->graduationDate = now()->format('Y-m-d');

        // Find or create Alumni class
        $this->alumniClass = $this->getOrCreateAlumniClass();

        $this->loadGraduations();
    }

    public function switchView($view)
    {
        $this->currentView = $view;
        if ($view === 'history') {
            $this->loadGraduations();
        }
    }

    public function updatedGraduateClass()
    {
        $this->loadSections();
        $this->graduateSection = '';
    }

    private function loadSections()
    {
        $class = $this->classes->firstWhere('id', $this->graduateClass);
        $this->sections = $class ? $class->sections : collect();
    }

    public function loadStudentsToGraduate()
    {
        $this->studentsToGraduate = [];
        $this->graduationDecisions = [];
        $this->remarks = [];
        $this->certificateNumbers = [];
    
        if (!$this->graduateClass || !$this->academicYearId) {
            session()->flash('info', 'Please select a class and academic year.');
            return;
        }

        if (!$this->classBelongsToCurrentSchool($this->graduateClass)) {
            session()->flash('error', 'Selected class is not in your current school.');
            return;
        }

        if (!AcademicYear::query()->where('id', $this->academicYearId)->exists()) {
            session()->flash('error', 'Selected academic year is not in your current school.');
            return;
        }

        if ($this->graduateSection && !$this->sectionBelongsToClassInCurrentSchool($this->graduateSection, $this->graduateClass)) {
            session()->flash('error', 'Selected section is not valid for the selected class.');
            return;
        }
    
        try {
            $query = DB::table('academic_year_student_record')
                ->where('academic_year_id', $this->academicYearId)
                ->where('my_class_id', $this->graduateClass);
    
            if ($this->graduateSection) {
                $query->where('section_id', $this->graduateSection);
            }
    
            $pivotRecords = $query->get();
            $studentRecordIds = $pivotRecords->pluck('student_record_id');
    
            if ($studentRecordIds->isEmpty()) {
                session()->flash('info', 'No students found in this class/section for the selected academic year.');
                return;
            }
    
            // Check for already graduated students
            $alreadyGraduated = Graduation::whereIn('student_record_id', $studentRecordIds)
                ->where('school_id', auth()->user()->school_id)
                ->pluck('student_record_id');

            $users = User::role('student')->whereIn('id', function ($q) use ($studentRecordIds) {
                $q->select('user_id')
                    ->from('student_records')
                    ->whereIn('id', $studentRecordIds)
                    ->where('is_graduated', false);
            })
                ->where('school_id', auth()->user()->school_id)
                ->with('studentRecord')
                ->get();
    
            $pivotMap = $pivotRecords->keyBy('student_record_id');
    
            $this->studentsToGraduate = $users->map(function ($user) use ($pivotMap, $alreadyGraduated) {
                if (!$user->studentRecord)
                    return null;
    
                $pivot = $pivotMap->get($user->studentRecord->id);
                if (!$pivot)
                    return null;
    
                $isAlreadyGraduated = $alreadyGraduated->contains($user->studentRecord->id);
    
                return [
                    'id' => $user->id,
                    'student_record_id' => $user->studentRecord->id,
                    'name' => $user->name,
                    'email' => $user->email, // Add this line
                    'admission_number' => $user->studentRecord->admission_number ?? '—',
                    'already_graduated' => $isAlreadyGraduated,
                ];
            })
                ->filter()
                ->sortBy('name')
                ->values()
                ->toArray();
    
            // Initialize graduation decisions (true for eligible, false for already graduated)
            foreach ($this->studentsToGraduate as $student) {
                $this->graduationDecisions[$student['id']] = !$student['already_graduated'];
            }
    
            $eligibleCount = collect($this->studentsToGraduate)->where('already_graduated', false)->count();
            $alreadyGraduatedCount = collect($this->studentsToGraduate)->where('already_graduated', true)->count();
    
            if (empty($this->studentsToGraduate)) {
                session()->flash('info', 'No students found.');
            } else {
                $message = count($this->studentsToGraduate) . " student(s) found";
                if ($alreadyGraduatedCount > 0) {
                    $message .= " ({$eligibleCount} eligible, {$alreadyGraduatedCount} already graduated)";
                }
                session()->flash('success', $message);
            }
    
        } catch (\Exception $e) {
            session()->flash('error', 'Error loading students: ' . $e->getMessage());
        }
    }
    public function setAllGraduate($value)
    {
        foreach ($this->studentsToGraduate as $student) {
            if (!$student['already_graduated']) {
                $this->graduationDecisions[$student['id']] = $value;
            }
        }
    }

    public function graduateStudents()
    {
        $this->validate();

        if (!$this->alumniClass) {
            session()->flash('error', 'Alumni class not found. Please contact administrator.');
            return;
        }

        if (!$this->classBelongsToCurrentSchool($this->graduateClass) || !$this->classBelongsToCurrentSchool($this->alumniClass->id)) {
            session()->flash('error', 'Selected class is not in your current school.');
            return;
        }

        if (!AcademicYear::query()->where('id', $this->academicYearId)->exists()) {
            session()->flash('error', 'Selected academic year is not in your current school.');
            return;
        }

        $selectedStudents = collect($this->graduationDecisions)
            ->filter(fn($decision) => $decision === true)
            ->keys()
            ->toArray();

        $allowedStudentIds = collect($this->studentsToGraduate)
            ->where('already_graduated', false)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->toArray();
        $selectedStudents = array_values(array_intersect(array_map('intval', $selectedStudents), $allowedStudentIds));

        if (empty($selectedStudents)) {
            session()->flash('error', 'No students selected for graduation');
            return;
        }

        try {
            DB::beginTransaction();

            $successCount = 0;

            // Eager load all students and their records
            $students = User::role('student')
                ->where('school_id', auth()->user()->school_id)
                ->whereIn('id', $selectedStudents)
                ->with('studentRecord')
                ->get()
                ->keyBy('id');

            foreach ($selectedStudents as $userId) {
                try {
                    $student = $students->get($userId);
                    if (!$student || !$student->studentRecord) {
                        continue;
                    }

                    $studentRecord = $student->studentRecord;

                    // Check if already graduated
                    $existingGraduation = Graduation::where('student_record_id', $studentRecord->id)
                        ->where('school_id', auth()->user()->school_id)
                        ->first();
                    if ($existingGraduation) {
                        continue;
                    }

                    // Get current class and section
                    $currentPivot = DB::table('academic_year_student_record')
                        ->where('student_record_id', $studentRecord->id)
                        ->where('academic_year_id', $this->academicYearId)
                        ->first();

                    if (!$currentPivot) {
                        continue;
                    }

                    // Create graduation record
                    $certificateNumber = $this->certificateNumbers[$userId] ?? $this->generateCertificateNumber();

                    Graduation::create([
                        'student_record_id' => $studentRecord->id,
                        'academic_year_id' => $this->academicYearId,
                        'graduation_class_id' => $currentPivot->my_class_id,
                        'graduation_section_id' => $currentPivot->section_id,
                        'graduation_date' => $this->graduationDate,
                        'certificate_number' => $certificateNumber,
                        'remarks' => $this->remarks[$userId] ?? null,
                        'school_id' => auth()->user()->school_id,
                    ]);

                    // Mark student as graduated
                    $studentRecord->update([
                        'is_graduated' => true,
                        'my_class_id' => $this->alumniClass->id,
                        'section_id' => null,
                    ]);

                    // Move student to alumni class in academic year record
                    DB::table('academic_year_student_record')
                        ->where('student_record_id', $studentRecord->id)
                        ->where('academic_year_id', $this->academicYearId)
                        ->update([
                            'my_class_id' => $this->alumniClass->id,
                            'section_id' => null,
                            'updated_at' => now(),
                        ]);

                    $successCount++;

                } catch (\Exception $e) {
                    \Log::error("Error graduating student {$userId}: " . $e->getMessage());
                    continue;
                }
            }

            DB::commit();

            $this->studentsToGraduate = [];
            $this->graduationDecisions = [];
            $this->remarks = [];
            $this->certificateNumbers = [];
            $this->loadGraduations();

            session()->flash('success', "{$successCount} student(s) graduated successfully and moved to Alumni!");

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error in graduation process: " . $e->getMessage());
            session()->flash('error', 'Error graduating students. Please try again.');
        }
    }

public function loadGraduations()
{
    $this->graduations = Graduation::query()
        ->with(['studentRecord.user', 'academicYear', 'graduationClass', 'graduationSection'])
        ->latest('graduation_date')
        ->get()
        ->filter(function ($graduation) {
            // Filter out graduations where studentRecord or user is null
            return $graduation->studentRecord && $graduation->studentRecord->user;
        });
}
public function viewGraduation($graduationId)
{
    $this->selectedGraduation = Graduation::query()->with([
        'studentRecord.user',
        'academicYear',
        'graduationClass',
        'graduationSection'
    ])->findOrFail($graduationId);

    // Check if student record exists
    if (!$this->selectedGraduation->studentRecord || !$this->selectedGraduation->studentRecord->user) {
        session()->flash('error', 'Student record not found. This graduation record may be orphaned.');
        $this->selectedGraduation = null;
        $this->currentView = 'history';
        return;
    }

    $this->currentView = 'view';
}
    public function reverseGraduation($graduationId)
    {
        try {
            $graduation = Graduation::query()->with(['studentRecord' => function($query) {
                $query->withTrashed();
            }])->findOrFail($graduationId);
    
            // Check if student record exists
            if (!$graduation->studentRecord) {
                session()->flash('error', 'Cannot reverse graduation: Student record no longer exists.');
                
                // Delete the orphaned graduation record
                $graduation->delete();
                
                $this->loadGraduations();
                return;
            }
    
            DB::transaction(function () use ($graduation) {
                $studentRecord = $graduation->studentRecord;
    
                // Restore student to original class
                $studentRecord->update([
                    'is_graduated' => false,
                    'my_class_id' => $graduation->graduation_class_id,
                    'section_id' => $graduation->graduation_section_id,
                ]);
    
                // Restore academic year record
                DB::table('academic_year_student_record')
                    ->where('student_record_id', $studentRecord->id)
                    ->where('academic_year_id', $graduation->academic_year_id)
                    ->update([
                        'my_class_id' => $graduation->graduation_class_id,
                        'section_id' => $graduation->graduation_section_id,
                        'updated_at' => now(),
                    ]);
    
                // Delete graduation record
                $graduation->delete();
            });
    
            $this->loadGraduations();
            $this->loadStudentsToGraduate();
    
            session()->flash('success', 'Graduation reversed successfully. Student restored to original class.');
    
        } catch (\Exception $e) {
            \Log::error("Error reversing graduation: " . $e->getMessage());
            session()->flash('error', 'Error reversing graduation: ' . $e->getMessage());
        }
    }

    public function cleanupOrphanedGraduations()
{
    try {
        $orphanedCount = Graduation::query()
            ->whereDoesntHave('studentRecord')
            ->delete();
            
        $this->loadGraduations();
        session()->flash('success', "Cleaned up {$orphanedCount} orphaned graduation records.");
        
    } catch (\Exception $e) {
        session()->flash('error', 'Error cleaning up orphaned records: ' . $e->getMessage());
    }
}

    public function backToHistory()
    {
        $this->selectedGraduation = null;
        $this->currentView = 'history';
    }

    private function getOrCreateAlumniClass()
    {
        // Find existing Alumni class
        $classGroup = auth()->user()->school->classGroups()->first();
        if (!$classGroup) {
            return null;
        }

        $alumniClass = MyClass::where('name', 'Alumni')
            ->where('class_group_id', $classGroup->id)
            ->first();

        if (!$alumniClass) {
            $alumniClass = MyClass::create([
                'name' => 'Alumni',
                'class_group_id' => $classGroup->id,
            ]);
        }

        return $alumniClass;
    }

    protected function classBelongsToCurrentSchool($classId): bool
    {
        if (!$classId) {
            return false;
        }

        return MyClass::where('id', $classId)
            ->whereHas('classGroup', function ($query) {
                $query->where('school_id', auth()->user()->school_id);
            })
            ->exists();
    }

    protected function sectionBelongsToClassInCurrentSchool($sectionId, $classId): bool
    {
        if (!$sectionId || !$classId) {
            return false;
        }

        return Section::where('id', $sectionId)
            ->where('my_class_id', $classId)
            ->whereHas('myClass.classGroup', function ($query) {
                $query->where('school_id', auth()->user()->school_id);
            })
            ->exists();
    }

    private function generateCertificateNumber()
    {
        $schoolInitials = auth()->user()->school->initials ?? 'SCH';
        $year = date('Y');
        
        do {
            $certificateNumber = "{$schoolInitials}/CERT/{$year}/" . mt_rand(10000, 99999);
        } while (Graduation::where('certificate_number', $certificateNumber)->exists());

        return $certificateNumber;
    }
    private function getFilteredStudents()
    {
        $filtered = collect($this->studentsToGraduate);
    
        if ($this->searchStudent) {
            $filtered = $filtered->filter(function($student) {
                return stripos($student['name'], $this->searchStudent) !== false ||
                       stripos($student['email'], $this->searchStudent) !== false ||
                       stripos($student['admission_number'], $this->searchStudent) !== false;
            });
        }
    
        // Add filtering by graduation status
        if ($this->filterGradStatus === 'eligible') {
            $filtered = $filtered->where('already_graduated', false);
        } elseif ($this->filterGradStatus === 'graduated') {
            $filtered = $filtered->where('already_graduated', true);
        }
    
        return $filtered->values()->toArray();
    }

    public function render()
    {
        $filteredStudents = $this->getFilteredStudents();
        
        $breadcrumbs = [
            ['href' => route('dashboard'), 'text' => 'Dashboard'],
            ['href' => route('students.index'), 'text' => 'Students'],
            ['href' => route('students.graduate'), 'text' => 'Graduate Students', 'active' => true],
        ];
    
        return view('livewire.students.graduate-students', [
            'studentsToGraduate' => $filteredStudents, // Use filtered students
        ])
            ->layout('layouts.dashboard', [
                'breadcrumbs' => $breadcrumbs,
                'page_heading' => 'Graduate Students'
            ])
            ->title('Graduate Students');
    }
}
