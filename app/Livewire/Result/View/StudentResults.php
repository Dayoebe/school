<?php

namespace App\Livewire\Result\View;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use App\Models\{StudentRecord, MyClass, Section, Result, Subject};
use App\Traits\ResolvesAccessibleStudentResults;
use Illuminate\Support\Facades\DB;

class StudentResults extends Component
{
    use WithPagination;
    use ResolvesAccessibleStudentResults;

    public $academicYearId;
    public $semesterId;
    public $selectedClass;
    public $selectedSection;
    public $searchTerm = '';
    public $perPage = 10;
    
    // View student details
    public $viewingStudent = false;
    public $studentRecord;
    public $subjects = [];
    public $results = [];
    public $studentPosition;
    public $totalStudents;
    public $resultPeriodNotice = null;

    protected $paginationTheme = 'tailwind';

    protected function activeStudentRecordIdsForSelectedClass(?int $classId = null, ?int $sectionId = null)
    {
        return StudentRecord::activeStudentRecordIdsForSchoolAcademicYear(
            auth()->user()?->school_id,
            $this->academicYearId,
            $classId,
            $sectionId
        );
    }

    public function mount()
    {
        $school = auth()->user()?->school;

        $this->academicYearId = $school?->academic_year_id;
        $this->semesterId = $school?->semester_id;
        $this->resultPeriodNotice = null;

        if ($this->isRestrictedTeacherResultViewer() && !$this->currentUserCanAccessClassOnlyResultTools()) {
            abort(403);
        }

        if ($this->isStudentResultViewer() && auth()->user()?->studentRecord) {
            $this->viewStudent(auth()->user()->studentRecord->id);
        }
    }

    #[On('academic-period-changed')]
    public function handlePeriodChange($data)
    {
        $activeStudentId = $this->viewingStudent && $this->studentRecord
            ? $this->studentRecord->id
            : null;

        $this->academicYearId = $data['academicYearId'];
        $this->semesterId = $data['semesterId'];

        $this->reset([
            'selectedClass',
            'selectedSection',
            'viewingStudent',
            'studentRecord',
            'subjects',
            'results',
            'studentPosition',
            'totalStudents',
            'resultPeriodNotice',
        ]);

        if ($activeStudentId) {
            $this->viewStudent($activeStudentId);
            return;
        }

        if ($this->isStudentResultViewer() && auth()->user()?->studentRecord) {
            $this->viewStudent(auth()->user()->studentRecord->id);
        }
    }

    public function viewStudent($studentId)
    {
        $this->studentRecord = $this->accessibleStudentRecordsQuery()
            ->with(['user', 'myClass', 'section'])
            ->findOrFail($studentId);

        $classId = DB::table('academic_year_student_record')
            ->where('student_record_id', $this->studentRecord->id)
            ->where('academic_year_id', $this->academicYearId)
            ->value('my_class_id') ?: $this->studentRecord->my_class_id;

        // Get subjects for this student
        $this->subjects = Subject::query()
            ->where('school_id', auth()->user()->school_id)
            ->where(function ($query) use ($classId) {
                $query->where('my_class_id', $classId)
                    ->orWhereHas('classes', function ($classQuery) use ($classId) {
                        $classQuery->where('my_classes.id', $classId);
                    });
            })
            ->where('school_id', auth()->user()->school_id)
            ->orderBy('name')
            ->distinct()
            ->get();

        // Load results
        $resultsCollection = Result::where('student_record_id', $this->studentRecord->id)
            ->where('academic_year_id', $this->academicYearId)
            ->where('semester_id', $this->semesterId)
            ->with('subject')
            ->get();

        $this->resultPeriodNotice = $resultsCollection->isEmpty()
            ? $this->buildResultPeriodNotice(collect([$this->studentRecord->id]))
            : null;

        $this->results = [];
        foreach ($resultsCollection as $result) {
            $this->results[$result->subject_id] = [
                'ca1_score' => $result->ca1_score,
                'ca2_score' => $result->ca2_score,
                'ca3_score' => $result->ca3_score,
                'ca4_score' => $result->ca4_score,
                'exam_score' => $result->exam_score,
                'total_score' => $result->total_score,
                'grade' => $this->calculateGrade($result->total_score),
                'comment' => $result->teacher_comment,
            ];
        }

        // Calculate position
        $this->calculatePosition();

        $this->viewingStudent = true;
    }

    protected function calculatePosition()
    {
        $classId = DB::table('academic_year_student_record')
            ->where('student_record_id', $this->studentRecord->id)
            ->where('academic_year_id', $this->academicYearId)
            ->value('my_class_id') ?: $this->studentRecord->my_class_id;

        $studentRecordIds = $this->activeStudentRecordIdsForSelectedClass((int) $classId);

        $classStudents = StudentRecord::with(['results' => function ($query) {
                $query->where('academic_year_id', $this->academicYearId)
                    ->where('semester_id', $this->semesterId);
            }])
            ->whereIn('student_records.id', $studentRecordIds)
            ->whereHas('user', fn($q) => $q->where('school_id', auth()->user()->school_id)->whereNull('deleted_at'))
            ->get();

        $this->totalStudents = $classStudents->count();

        $scores = $classStudents->map(function ($record) {
            return [
                'id' => $record->id,
                'total_score' => $record->results->sum('total_score'),
            ];
        })->sortByDesc('total_score')->values();

        $rank = 1;
        $prevScore = null;
        $studentsAtRank = 0;

        foreach ($scores as $data) {
            if ($prevScore !== null && $data['total_score'] < $prevScore) {
                $rank += $studentsAtRank;
                $studentsAtRank = 1;
            } else {
                $studentsAtRank++;
            }

            if ($data['id'] == $this->studentRecord->id) {
                $this->studentPosition = $rank;
                break;
            }
            $prevScore = $data['total_score'];
        }
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

    public function backToList()
    {
        $this->viewingStudent = false;
        $this->reset(['studentRecord', 'subjects', 'results', 'resultPeriodNotice']);
    }

    public function render()
    {
        $canBrowseAllStudents = $this->canBrowseAllStudentResults();
        $isStudentResultViewer = $this->isStudentResultViewer();
        $isParentResultViewer = $this->isParentResultViewer();
        $isRestrictedTeacherResultViewer = $this->isRestrictedTeacherResultViewer();

        $classes = $canBrowseAllStudents
            ? MyClass::whereHas('classGroup', function ($query) {
                $query->where('school_id', auth()->user()->school_id);
            })->orderBy('name')->get()
            : collect();

        $sections = $canBrowseAllStudents
            ? Section::when($this->selectedClass, function ($q) {
                $q->where('my_class_id', $this->selectedClass)
                    ->whereHas('myClass.classGroup', function ($query) {
                        $query->where('school_id', auth()->user()->school_id);
                    });
            })->get()
            : collect();
    
        $students = collect();

        if (!$this->viewingStudent) {
            if ($canBrowseAllStudents && $this->selectedClass) {
                $classExists = MyClass::where('id', $this->selectedClass)
                    ->whereHas('classGroup', function ($query) {
                        $query->where('school_id', auth()->user()->school_id);
                    })
                    ->exists();

                if (!$classExists) {
                    return view('livewire.result.view.student-results', compact(
                        'classes',
                        'sections',
                        'students',
                        'canBrowseAllStudents',
                        'isStudentResultViewer',
                        'isParentResultViewer',
                        'isRestrictedTeacherResultViewer'
                    ))
                        ->layout('layouts.result', [
                            'title' => 'View Student Results',
                            'page_heading' => 'View Student Results'
                        ]);
                }

                $studentRecordIds = $this->activeStudentRecordIdsForSelectedClass(
                    (int) $this->selectedClass,
                    $this->selectedSection ? (int) $this->selectedSection : null
                );

                $students = $this->accessibleStudentRecordsQuery()
                    ->whereIn('student_records.id', $studentRecordIds)
                    ->with(['user' => function ($query) {
                        $query->where('school_id', auth()->user()->school_id)
                            ->whereNull('deleted_at');
                    }, 'results' => function ($q) {
                        $q->where('academic_year_id', $this->academicYearId)
                            ->where('semester_id', $this->semesterId);
                    }])
                    ->when($this->searchTerm, function ($q) {
                        $q->whereHas('user', function ($query) {
                            $query->where('name', 'like', '%' . $this->searchTerm . '%')
                                ->where('school_id', auth()->user()->school_id)
                                ->whereNull('deleted_at');
                        });
                    })
                    ->orderByName()
                    ->paginate($this->perPage);
            } elseif (!$canBrowseAllStudents) {
                $students = $this->accessibleStudentRecordsQuery()
                    ->when($isRestrictedTeacherResultViewer, function ($query) {
                        $query->whereIn(
                            'student_records.id',
                            $this->activeStudentRecordIdsForSelectedClass()
                        );
                    })
                    ->with(['user' => function ($query) {
                        $query->where('school_id', auth()->user()->school_id)
                            ->whereNull('deleted_at');
                    }, 'results' => function ($q) {
                        $q->where('academic_year_id', $this->academicYearId)
                            ->where('semester_id', $this->semesterId);
                    }])
                    ->when($this->searchTerm, function ($q) {
                        $q->whereHas('user', function ($query) {
                            $query->where('name', 'like', '%' . $this->searchTerm . '%')
                                ->where('school_id', auth()->user()->school_id)
                                ->whereNull('deleted_at');
                        });
                    })
                    ->orderByName()
                    ->paginate($this->perPage);
            }
        }    
    
        return view('livewire.result.view.student-results', compact(
            'classes',
            'sections',
            'students',
            'canBrowseAllStudents',
            'isStudentResultViewer',
            'isParentResultViewer',
            'isRestrictedTeacherResultViewer'
        ))
            ->layout('layouts.result', [
                'title' => 'View Student Results',
                'page_heading' => 'View Student Results'
            ]);
    }
}
