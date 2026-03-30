<?php

namespace App\Livewire\Result\View;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\{StudentRecord, MyClass, Section, Result, Subject};
use App\Traits\ResolvesAccessibleStudentResults;
use Illuminate\Support\Facades\DB;

class ClassResults extends Component
{
    use ResolvesAccessibleStudentResults;

    public $academicYearId;
    public $semesterId;
    public $selectedClass;
    public $selectedSection;
    public $classResults; // REMOVE = []
    public $subjects; // REMOVE = []
    public $resultPeriodNotice = null;

    public function mount()
    {
        $school = auth()->user()?->school;

        $this->academicYearId = $school?->academic_year_id;
        $this->semesterId = $school?->semester_id;
        $this->classResults = collect(); // ADD THIS
        $this->subjects = collect(); // ADD THIS
        $this->resultPeriodNotice = null;

        abort_unless(
            $this->canBrowseAllStudentResults() || $this->currentUserCanAccessClassOnlyResultTools(),
            403
        );
    }

    #[On('academic-period-changed')]
    public function handlePeriodChange($data)
    {
        $this->academicYearId = $data['academicYearId'];
        $this->semesterId = $data['semesterId'];
        $this->reset(['selectedClass', 'selectedSection']);
        $this->classResults = collect(); // CHANGE from [] to collect()
        $this->resultPeriodNotice = null;
    }

    public function loadResults()
    {
        if (!$this->selectedClass) {
            $this->dispatch('error', 'Please select a class');
            return;
        }

        $classExists = MyClass::where('id', $this->selectedClass)
            ->whereHas('classGroup', function ($query) {
                $query->where('school_id', auth()->user()->school_id);
            })
            ->exists();
        if (!$classExists) {
            $this->dispatch('error', 'Selected class is not in your current school.');
            return;
        }

        if (!$this->currentUserCanViewClassTeacherClass($this->selectedClass)) {
            $this->dispatch('error', 'You can only view class results for your assigned class.');
            return;
        }

        if ($this->selectedSection) {
            $sectionExists = Section::where('id', $this->selectedSection)
                ->where('my_class_id', $this->selectedClass)
                ->whereHas('myClass.classGroup', function ($query) {
                    $query->where('school_id', auth()->user()->school_id);
                })
                ->exists();

            if (!$sectionExists) {
                $this->dispatch('error', 'Selected section is not valid for your current school/class.');
                return;
            }
        }
    
        // Get students for this academic year
        $studentRecordIds = DB::table('academic_year_student_record')
            ->where('academic_year_id', $this->academicYearId)
            ->where('my_class_id', $this->selectedClass)
            ->when($this->selectedSection, fn($q) => $q->where('section_id', $this->selectedSection))
            ->pluck('student_record_id');
    
        if ($studentRecordIds->isEmpty()) {
            $this->dispatch('error', 'No students found for this class in current academic year');
            $this->classResults = collect();
            $this->resultPeriodNotice = null;
            return;
        }
    
        // Get subjects for this class
        $this->subjects = $this->classSubjectsQuery((int) $this->selectedClass)->get();
    
        // Load students with their results (filter out soft-deleted users)
        $students = StudentRecord::whereIn('student_records.id', $studentRecordIds) // CHANGE HERE
            ->with([
                'user' => function ($query) {
                    $query->whereNull('deleted_at');
                },
                'results' => function ($q) {
                    $q->where('academic_year_id', $this->academicYearId)
                        ->where('semester_id', $this->semesterId)
                        ->with('subject');
                }
            ])
            ->whereHas('user', fn($q) => $q->where('school_id', auth()->user()->school_id)->whereNull('deleted_at'))
            ->orderByName()
            ->get();
    
        // Process results for each student
        foreach ($students as $student) {
            // Skip if user is null (soft-deleted)
            if (!$student->user) {
                continue;
            }
            
            $totalScore = 0;
            $subjectResults = [];
    
            foreach ($this->subjects as $subject) {
                $result = $student->results->firstWhere('subject_id', $subject->id);
                if ($result) {
                    $subjectResults[$subject->id] = [
                        'total_score' => $result->total_score,
                        'grade' => $this->calculateGrade($result->total_score),
                    ];
                    $totalScore += $result->total_score;
                }
            }
    
            $student->setAttribute('subject_results', $subjectResults);
            $student->setAttribute('total_score', $totalScore);
            $student->setAttribute('average_score', 
                count($subjectResults) > 0 ? round($totalScore / count($subjectResults), 2) : 0
            );
        }
    
        // Filter out any students without users
        $students = $students->filter(fn($student) => $student->user);

        if ($students->flatMap->results->isEmpty()) {
            $this->resultPeriodNotice = $this->buildResultPeriodNotice($studentRecordIds);
        } else {
            $this->resultPeriodNotice = null;
        }
    
        // Calculate positions
        $rankedStudents = $students->sortByDesc('total_score')->values();
        $rank = 1;
        $prevScore = null;
        $studentsAtRank = 0;
    
        foreach ($rankedStudents as $student) {
            if ($prevScore !== null && $student->total_score < $prevScore) {
                $rank += $studentsAtRank;
                $studentsAtRank = 1;
            } else {
                $studentsAtRank++;
            }
            $student->setAttribute('position', $rank);
            $prevScore = $student->total_score;
        }
    
        $this->classResults = $students->sortBy('position');
    }

    protected function classSubjectsQuery(int $classId)
    {
        return Subject::query()
            ->where('subjects.school_id', auth()->user()->school_id)
            ->where(function ($query) use ($classId) {
                $query->where('subjects.my_class_id', $classId)
                    ->orWhereHas('classes', function ($classQuery) use ($classId) {
                        $classQuery->where('my_classes.id', $classId);
                    })
                    ->orWhereIn('subjects.id', function ($subQuery) use ($classId) {
                        $subQuery->from('student_subject')
                            ->where('my_class_id', $classId)
                            ->select('subject_id');
                    });
            })
            ->orderBy('subjects.name')
            ->distinct();
    }

    protected function buildResultPeriodNotice($studentRecordIds): ?string
    {
        if (!$this->academicYearId || !$this->semesterId || collect($studentRecordIds)->isEmpty()) {
            return null;
        }

        $availableSemesterIds = Result::query()
            ->whereIn('student_record_id', collect($studentRecordIds))
            ->where('academic_year_id', $this->academicYearId)
            ->where('semester_id', '!=', $this->semesterId)
            ->distinct()
            ->pluck('semester_id');

        if ($availableSemesterIds->isEmpty()) {
            return null;
        }

        $currentSemesterName = \App\Models\Semester::query()
            ->where('school_id', auth()->user()->school_id)
            ->where('id', $this->semesterId)
            ->value('name') ?? 'the active term';

        $availableTerms = \App\Models\Semester::query()
            ->where('school_id', auth()->user()->school_id)
            ->whereIn('id', $availableSemesterIds)
            ->orderBy('name')
            ->pluck('name')
            ->map(fn ($name) => trim($name))
            ->values();

        if ($availableTerms->isEmpty()) {
            return null;
        }

        return 'No uploaded results were found for ' . trim($currentSemesterName) .
            '. Results exist for ' . $availableTerms->join(', ') . '.';
    }
    protected function calculateGrade($score)
    {
        return match (true) {
            $score >= 75 => 'A1',
            $score >= 70 => 'B2',
            $score >= 65 => 'B3',
            $score >= 60 => 'C4',
            $score >= 55 => 'C5',
            $score >= 50 => 'C6',
            $score >= 45 => 'D7',
            $score >= 40 => 'E8',
            default => 'F9',
        };
    }

    public function render()
    {
        $classes = $this->accessibleClassTeacherClassesQuery()
            ->orderBy('name')
            ->get();
        $sections = Section::when($this->selectedClass, function ($q) {
            $q->where('my_class_id', $this->selectedClass)
                ->whereHas('myClass.classGroup', function ($query) {
                    $query->where('school_id', auth()->user()->school_id);
                });
        })->get();

        return view('livewire.result.view.class-results', compact('classes', 'sections'))
            ->layout('layouts.result', [
                'title' => 'View Class Results',
                'page_heading' => 'View Class Results'
            ]);
    }
}
