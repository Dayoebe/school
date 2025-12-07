<?php

namespace App\Livewire;

use App\Models\MyClass;
use App\Models\Section;
use App\Models\StudentRecord;
use App\Models\Promotion;
use App\Models\User;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class PromoteStudents extends Component
{
    // Main view toggle
    public $currentView = 'promote'; // 'promote' or 'history'
    
    // Promotion form properties
    public $classes;
    public $academicYears;
    public $oldClass;
    public $oldSections = [];
    public $oldSection = 'all';
    public $newClass;
    public $newSections = [];
    public $newSection = 'none';
    public $fromAcademicYear;
    public $toAcademicYear;
    public $students = [];
    public $selectedStudents = [];
    public $selectAll = false;
    public $sectionFilter = 'all';
    
    // History properties
    public $promotions = [];
    public $selectedPromotion = null;
    public $promotionStudents = [];

    protected $rules = [
        'oldClass' => 'required|exists:my_classes,id',
        'newClass' => 'required|exists:my_classes,id',
        'fromAcademicYear' => 'required|exists:academic_years,id',
        'toAcademicYear' => 'required|exists:academic_years,id',
        'selectedStudents' => 'required|array|min:1',
        'selectedStudents.*' => 'exists:users,id',
    ];

    protected $messages = [
        'selectedStudents.required' => 'Please select at least one student to promote.',
        'selectedStudents.min' => 'Please select at least one student to promote.',
        'fromAcademicYear.required' => 'Please select the academic year students are being promoted from.',
        'toAcademicYear.required' => 'Please select the academic year students are being promoted to.',
    ];

    public function mount()
    {
        $this->classes = MyClass::whereHas('classGroup', function($query) {
            $query->where('school_id', auth()->user()->school_id);
        })->with('sections')->orderBy('name')->get();

        $this->academicYears = AcademicYear::where('school_id', auth()->user()->school_id)
            ->orderBy('start_year', 'desc')
            ->get();

        if ($this->classes->isNotEmpty()) {
            $this->oldClass = $this->classes->first()->id;
            $this->newClass = $this->classes->first()->id;
            $this->loadOldSections();
            $this->loadNewSections();
        }

        $currentAcademicYear = auth()->user()->school->academicYear;
        if ($currentAcademicYear) {
            $this->fromAcademicYear = $currentAcademicYear->id;
            
            $nextYear = $this->academicYears->where('start_year', '>', $currentAcademicYear->start_year)->first();
            $this->toAcademicYear = $nextYear ? $nextYear->id : $currentAcademicYear->id;
        }
        
        $this->loadPromotions();
    }

    public function switchView($view)
    {
        $this->currentView = $view;
        if ($view === 'history') {
            $this->loadPromotions();
        }
    }

    public function loadPromotions()
    {
        $this->promotions = Promotion::where('school_id', auth()->user()->school_id)
            ->with(['oldClass', 'newClass', 'oldSection', 'newSection', 'academicYear'])
            ->latest()
            ->get();
    }

    public function viewPromotion($promotionId)
    {
        $this->selectedPromotion = Promotion::with(['oldClass', 'newClass', 'oldSection', 'newSection', 'academicYear'])
            ->findOrFail($promotionId);
        
        $this->promotionStudents = User::whereIn('id', $this->selectedPromotion->students)
            ->with('studentRecord')
            ->get();
        $this->currentView = 'view';
    }

    public function backToHistory()
    {
        $this->selectedPromotion = null;
        $this->promotionStudents = [];
        $this->currentView = 'history';
    }

    public function resetPromotion($promotionId)
    {
        try {
            $promotion = Promotion::findOrFail($promotionId);
            
            DB::transaction(function () use ($promotion) {
                $students = User::whereIn('id', $promotion->students)->get();
                
                foreach ($students as $student) {
                    $studentRecord = $student->studentRecord;
                    
                    if ($studentRecord) {
                        // Remove the new academic year record
                        $studentRecord->academicYears()->detach($promotion->academic_year_id);
                        
                        // If this was the current academic year, revert to previous class
                        if ($promotion->academic_year_id == auth()->user()->school->academic_year_id) {
                            $studentRecord->update([
                                'my_class_id' => $promotion->old_class_id,
                                'section_id' => $promotion->old_section_id,
                            ]);
                        }
                    }
                }
                
                $promotion->delete();
            });
            
            $this->loadPromotions();
            session()->flash('success', 'Promotion reset successfully. Students removed from new academic year.');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error resetting promotion: ' . $e->getMessage());
        }
    }
    
    public function updatedOldClass()
    {
        $this->loadOldSections();
        $this->oldSection = 'all';
        $this->sectionFilter = 'all';
        $this->students = [];
        $this->selectedStudents = [];
        $this->selectAll = false;
    }

    public function updatedNewClass()
    {
        $this->loadNewSections();
        $this->newSection = 'none';
    }

    public function updatedSectionFilter()
    {
        $this->selectedStudents = [];
        $this->selectAll = false;
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $filteredStudents = $this->getFilteredStudents();
            $this->selectedStudents = collect($filteredStudents)->pluck('id')->toArray();
        } else {
            $this->selectedStudents = [];
        }
    }

    private function loadOldSections()
    {
        $class = $this->classes->firstWhere('id', $this->oldClass);
        $this->oldSections = $class ? $class->sections : collect();
    }

    private function loadNewSections()
    {
        $class = $this->classes->firstWhere('id', $this->newClass);
        $this->newSections = $class ? $class->sections : collect();
    }

    public function loadStudents()
    {
        $this->validate([
            'oldClass' => 'required|exists:my_classes,id',
            'fromAcademicYear' => 'required|exists:academic_years,id',
        ]);

        try {
            \Log::info('Loading students for promotion', [
                'class_id' => $this->oldClass,
                'from_academic_year' => $this->fromAcademicYear
            ]);

            // FIXED: Query academic year-specific records instead of current student_records
            // This gets student_record_ids from the pivot table for this academic year and class
            $studentRecordIds = DB::table('academic_year_student_record')
                ->where('my_class_id', $this->oldClass)
                ->where('academic_year_id', $this->fromAcademicYear)
                ->pluck('student_record_id');

            \Log::info('Found student records in academic year', [
                'count' => $studentRecordIds->count()
            ]);

            // If no records found in pivot table, fall back to current student_records
            // This handles the case where academic year records haven't been created yet
            if ($studentRecordIds->isEmpty()) {
                \Log::info('No academic year records found, falling back to current records');
                
                $studentRecords = StudentRecord::with(['user', 'myClass', 'section'])
                    ->where('my_class_id', $this->oldClass)
                    ->where('is_graduated', false)
                    ->whereHas('user', function($q) {
                        $q->where('school_id', auth()->user()->school_id);
                    })
                    ->get();
                
                session()->flash('info', 'Loading students from current records (no academic year records found yet).');
            } else {
                // Load students based on the academic year records
                $studentRecords = StudentRecord::with(['user', 'myClass', 'section'])
                    ->whereIn('id', $studentRecordIds)
                    ->where('is_graduated', false)
                    ->whereHas('user', function($q) {
                        $q->where('school_id', auth()->user()->school_id);
                    })
                    ->get();
            }

            // Get the actual class and section from the pivot table for display
            $this->students = $studentRecords->map(function($record) {
                // Try to get academic year specific class/section
                $pivotRecord = DB::table('academic_year_student_record')
                    ->where('student_record_id', $record->id)
                    ->where('academic_year_id', $this->fromAcademicYear)
                    ->first();

                if ($pivotRecord) {
                    $class = MyClass::find($pivotRecord->my_class_id);
                    $section = $pivotRecord->section_id ? Section::find($pivotRecord->section_id) : null;
                } else {
                    // Fallback to current record
                    $class = $record->myClass;
                    $section = $record->section;
                }

                return [
                    'id' => $record->user_id,
                    'name' => $record->user->name,
                    'admission_number' => $record->admission_number,
                    'class' => $class->name,
                    'section' => $section?->name,
                    'section_id' => $section?->id,
                ];
            })->toArray();

            $this->selectedStudents = [];
            $this->selectAll = false;
            $this->sectionFilter = 'all';

            if (empty($this->students)) {
                session()->flash('info', 'No students found in the selected class for this academic year.');
            } else {
                session()->flash('success', count($this->students) . ' student(s) loaded successfully.');
            }

        } catch (\Exception $e) {
            \Log::error('Error loading students', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error loading students: ' . $e->getMessage());
        }
    }

    private function getFilteredStudents()
    {
        if ($this->sectionFilter === 'all') {
            return $this->students;
        }

        return array_filter($this->students, function($student) {
            return $student['section_id'] == $this->sectionFilter;
        });
    }

    public function promoteStudents()
    {
        $this->validate();

        if (!$this->toAcademicYear) {
            session()->flash('error', 'Please select the academic year to promote students to.');
            return;
        }

        $fromYear = AcademicYear::find($this->fromAcademicYear);
        $toYear = AcademicYear::find($this->toAcademicYear);

        if ($toYear->start_year <= $fromYear->start_year) {
            session()->flash('error', 'Cannot promote to a past or same academic year.');
            return;
        }

        try {
            DB::transaction(function () use ($toYear, $fromYear) {
                $promotedCount = 0;

                foreach ($this->selectedStudents as $studentId) {
                    $studentRecord = StudentRecord::where('user_id', $studentId)->first();
                    
                    if (!$studentRecord) continue;

                    // Ensure the student has a record for the FROM academic year
                    $hasFromYearRecord = $studentRecord->academicYears()
                        ->where('academic_year_id', $fromYear->id)
                        ->exists();

                    if (!$hasFromYearRecord) {
                        $studentRecord->academicYears()->attach($fromYear->id, [
                            'my_class_id' => $studentRecord->my_class_id,
                            'section_id' => $studentRecord->section_id,
                        ]);
                    }

                    // FIXED: Always set section_id explicitly
                    $pivotData = [
                        'my_class_id' => $this->newClass,
                        'section_id' => ($this->newSection && $this->newSection !== 'none') 
                            ? $this->newSection 
                            : null,
                    ];

                    // Attach to new academic year
                    $studentRecord->academicYears()->syncWithoutDetaching([
                        $toYear->id => $pivotData
                    ]);

                    // Update current student record ONLY if promoting to current academic year
                    if ($toYear->id == auth()->user()->school->academic_year_id) {
                        $studentRecord->update([
                            'my_class_id' => $this->newClass,
                            'section_id' => $pivotData['section_id'],
                        ]);
                    }

                    $promotedCount++;
                }

                Promotion::create([
                    'old_class_id' => $this->oldClass,
                    'new_class_id' => $this->newClass,
                    'old_section_id' => $this->oldSection && $this->oldSection !== 'all' ? $this->oldSection : null,
                    'new_section_id' => $this->newSection && $this->newSection !== 'none' ? $this->newSection : null,
                    'students' => $this->selectedStudents,
                    'academic_year_id' => $toYear->id,
                    'school_id' => auth()->user()->school_id,
                ]);

                session()->flash('success', "Successfully promoted {$promotedCount} student(s) to {$toYear->name}.");
            });

            $this->students = [];
            $this->selectedStudents = [];
            $this->selectAll = false;
            $this->loadPromotions();

        } catch (\Exception $e) {
            \Log::error('Promotion error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            session()->flash('error', 'An error occurred while promoting students: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $breadcrumbs = [
            ['href' => route('dashboard'), 'text' => 'Dashboard'],
            ['href' => route('students.index'), 'text' => 'Students'],
            ['href' => route('students.promote'), 'text' => 'Promote Students', 'active' => true],
        ];

        $filteredStudents = $this->getFilteredStudents();

        return view('livewire.promote-students', [
            'filteredStudents' => $filteredStudents,
        ])
            ->layout('layouts.new', [
                'breadcrumbs' => $breadcrumbs,
                'page_heading' => 'Promote Students'
            ])
            ->title('Promote Students');
    }
}