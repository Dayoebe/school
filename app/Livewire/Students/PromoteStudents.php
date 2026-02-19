<?php

namespace App\Livewire\Students;

use App\Models\MyClass;
use App\Models\Section;
use App\Models\StudentRecord;
use App\Models\Promotion;
use App\Models\User;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class PromoteStudents extends Component
{
    use WithPagination;

    public $currentView = 'promote';

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

    public $promotions = [];
    public $selectedPromotion = null;
    public $promotionStudents = [];

    // Search & Filter
    public $searchStudent = '';
    public $filterStatus = 'all';

    protected $rules = [
        'oldClass' => 'required|exists:my_classes,id',
        'newClass' => 'required|exists:my_classes,id',
        'fromAcademicYear' => 'required|exists:academic_years,id',
        'toAcademicYear' => 'required|exists:academic_years,id',
        'selectedStudents' => 'required|array|min:1',
    ];

    public function mount()
    {
        $this->classes = MyClass::whereHas('classGroup', function ($query) {
            $query->where('school_id', auth()->user()->school_id);
        })->with('sections')->orderBy('name')->get();

        $this->academicYears = AcademicYear::query()
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

    public function updatedOldClass()
    {
        $this->loadOldSections();
        $this->oldSection = 'all';
        $this->students = [];
        $this->selectedStudents = [];
        $this->selectAll = false;
    }

    public function updatedNewClass()
    {
        $this->loadNewSections();
        $this->newSection = 'none';
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedStudents = collect($this->getFilteredStudents())
                ->where('already_promoted', false)
                ->pluck('id')
                ->toArray();
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
        $this->students = [];
        $this->selectedStudents = [];
        $this->selectAll = false;

        if (!$this->oldClass || !$this->fromAcademicYear) {
            session()->flash('info', 'Please select a class and academic year.');
            return;
        }

        if (!$this->classBelongsToCurrentSchool($this->oldClass) || !$this->classBelongsToCurrentSchool($this->newClass)) {
            session()->flash('error', 'Selected class is not in your current school.');
            return;
        }

        if ($this->oldSection && $this->oldSection !== 'all' && !$this->sectionBelongsToClassInCurrentSchool($this->oldSection, $this->oldClass)) {
            session()->flash('error', 'Selected source section is not valid for the selected class.');
            return;
        }

        if ($this->newSection && $this->newSection !== 'none' && !$this->sectionBelongsToClassInCurrentSchool($this->newSection, $this->newClass)) {
            session()->flash('error', 'Selected target section is not valid for the selected class.');
            return;
        }

        $fromYear = AcademicYear::query()->find($this->fromAcademicYear);
        $toYear = $this->toAcademicYear
            ? AcademicYear::query()->find($this->toAcademicYear)
            : null;

        if (!$fromYear) {
            session()->flash('error', 'Invalid academic year selected.');
            return;
        }

        $query = DB::table('academic_year_student_record')
            ->where('academic_year_id', $fromYear->id)
            ->where('my_class_id', $this->oldClass);

        if ($this->oldSection && $this->oldSection !== 'all') {
            $query->where('section_id', (int) $this->oldSection);
        }

        $pivotRecords = $query->get();
        $studentRecordIds = $pivotRecords->pluck('student_record_id');

        if ($studentRecordIds->isEmpty()) {
            session()->flash('info', 'No students found in this class for the selected academic year.');
            return;
        }

        $alreadyInToYearAndClass = collect();
        if ($toYear) {
            $alreadyInToYearAndClass = DB::table('academic_year_student_record')
                ->where('academic_year_id', $toYear->id)
                ->where('my_class_id', $this->newClass)
                ->whereIn('student_record_id', $studentRecordIds)
                ->pluck('student_record_id');
        }

        $classIds = $pivotRecords->pluck('my_class_id')->unique();
        $sectionIds = $pivotRecords->pluck('section_id')->filter()->unique();

        $classes = MyClass::whereIn('id', $classIds)
            ->whereHas('classGroup', function ($query) {
                $query->where('school_id', auth()->user()->school_id);
            })
            ->get()
            ->keyBy('id');
        $sections = Section::whereIn('id', $sectionIds)
            ->whereHas('myClass.classGroup', function ($query) {
                $query->where('school_id', auth()->user()->school_id);
            })
            ->get()
            ->keyBy('id');

        $toYearClassInfo = collect();
        if ($toYear && $alreadyInToYearAndClass->isNotEmpty()) {
            $toYearRecords = DB::table('academic_year_student_record')
                ->where('academic_year_id', $toYear->id)
                ->whereIn('student_record_id', $alreadyInToYearAndClass)
                ->get()
                ->keyBy('student_record_id');

            $toYearClassIds = $toYearRecords->pluck('my_class_id')->unique();
            $toYearSectionIds = $toYearRecords->pluck('section_id')->filter()->unique();

            $toYearClasses = MyClass::whereIn('id', $toYearClassIds)
                ->whereHas('classGroup', function ($query) {
                    $query->where('school_id', auth()->user()->school_id);
                })
                ->get()
                ->keyBy('id');
            $toYearSections = Section::whereIn('id', $toYearSectionIds)
                ->whereHas('myClass.classGroup', function ($query) {
                    $query->where('school_id', auth()->user()->school_id);
                })
                ->get()
                ->keyBy('id');

            $toYearClassInfo = [
                'records' => $toYearRecords,
                'classes' => $toYearClasses,
                'sections' => $toYearSections,
            ];
        }

        $users = User::role('student')->whereIn('id', function ($q) use ($studentRecordIds) {
            $q->select('user_id')
                ->from('student_records')
                ->whereIn('id', $studentRecordIds);
        })
            ->where('school_id', auth()->user()->school_id)
            ->with('studentRecord')
            ->get();

        $pivotMap = $pivotRecords->keyBy('student_record_id');

        $this->students = $users->map(function ($user) use ($pivotMap, $classes, $sections, $toYear, $alreadyInToYearAndClass, $toYearClassInfo) {
            if (!$user->studentRecord) return null;

            $pivot = $pivotMap->get($user->studentRecord->id);
            if (!$pivot) return null;

            $originalClass = $classes->get($pivot->my_class_id);
            $originalSection = $pivot->section_id ? $sections->get($pivot->section_id) : null;

            $alreadyPromoted = false;
            $promotedClass = null;
            $promotedSection = null;

            if ($toYear && $alreadyInToYearAndClass->contains($user->studentRecord->id)) {
                $alreadyPromoted = true;
                if (isset($toYearClassInfo['records'])) {
                    $toYearRecord = $toYearClassInfo['records']->get($user->studentRecord->id);
                    if ($toYearRecord) {
                        $promotedClass = $toYearClassInfo['classes']->get($toYearRecord->my_class_id);
                        $promotedSection = $toYearRecord->section_id ?
                            $toYearClassInfo['sections']->get($toYearRecord->section_id) : null;
                    }
                }
            }

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'admission_number' => $user->studentRecord->admission_number ?? '—',
                'original_class' => $originalClass?->name ?? 'Unknown',
                'original_section' => $originalSection?->name,
                'promoted_class' => $promotedClass?->name,
                'promoted_section' => $promotedSection?->name,
                'already_promoted' => $alreadyPromoted,
            ];
        })
            ->filter()
            ->sortBy('name')
            ->values()
            ->toArray();

        $notPromotedCount = collect($this->students)->where('already_promoted', false)->count();
        $promotedCount = collect($this->students)->where('already_promoted', true)->count();

        if (empty($this->students)) {
            session()->flash('info', 'No students found.');
        } else {
            $message = count($this->students) . " student(s) found";
            if ($promotedCount > 0) {
                $message .= " ({$notPromotedCount} ready, {$promotedCount} already in {$toYear->name})";
            }
            session()->flash('success', $message);
        }
    }

    private function getFilteredStudents()
    {
        $filtered = collect($this->students);

        if ($this->searchStudent) {
            $filtered = $filtered->filter(function($student) {
                return stripos($student['name'], $this->searchStudent) !== false ||
                       stripos($student['email'], $this->searchStudent) !== false ||
                       stripos($student['admission_number'], $this->searchStudent) !== false;
            });
        }

        if ($this->filterStatus === 'ready') {
            $filtered = $filtered->where('already_promoted', false);
        } elseif ($this->filterStatus === 'promoted') {
            $filtered = $filtered->where('already_promoted', true);
        }

        return $filtered->values()->toArray();
    }

    public function promoteStudents()
    {
        $this->validate();

        if (empty($this->selectedStudents)) {
            session()->flash('error', 'Please select at least one student to promote.');
            return;
        }

        if (!$this->classBelongsToCurrentSchool($this->oldClass) || !$this->classBelongsToCurrentSchool($this->newClass)) {
            session()->flash('error', 'Selected class is not in your current school.');
            return;
        }

        if ($this->oldSection && $this->oldSection !== 'all' && !$this->sectionBelongsToClassInCurrentSchool($this->oldSection, $this->oldClass)) {
            session()->flash('error', 'Selected source section is not valid for the selected class.');
            return;
        }

        if ($this->newSection && $this->newSection !== 'none' && !$this->sectionBelongsToClassInCurrentSchool($this->newSection, $this->newClass)) {
            session()->flash('error', 'Selected target section is not valid for the selected class.');
            return;
        }

        $fromYear = AcademicYear::query()->find($this->fromAcademicYear);
        $toYear = AcademicYear::query()->find($this->toAcademicYear);

        if (!$fromYear || !$toYear) {
            session()->flash('error', 'Invalid academic year selected.');
            return;
        }

        if ($toYear->start_year <= $fromYear->start_year) {
            session()->flash('error', 'Cannot promote to a past or same academic year.');
            return;
        }

        $successCount = 0;
        $promotedStudents = [];
        $allowedStudentIds = collect($this->students)
            ->where('already_promoted', false)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->toArray();
        $selectedStudents = array_values(array_intersect(array_map('intval', $this->selectedStudents), $allowedStudentIds));

        if (empty($selectedStudents)) {
            session()->flash('error', 'Selected students are not valid for this promotion context.');
            return;
        }

        DB::transaction(function () use ($fromYear, $toYear, $selectedStudents, &$successCount, &$promotedStudents) {
            $students = User::role('student')
                ->where('school_id', auth()->user()->school_id)
                ->whereIn('id', $selectedStudents)
                ->with('studentRecord')
                ->get()
                ->keyBy('id');

            foreach ($selectedStudents as $userId) {
                $student = $students->get($userId);
                if (!$student || !$student->studentRecord) continue;

                $studentRecord = $student->studentRecord;

                $existingInToYear = DB::table('academic_year_student_record')
                    ->where('student_record_id', $studentRecord->id)
                    ->where('academic_year_id', $toYear->id)
                    ->first();

                if ($existingInToYear && $existingInToYear->my_class_id == $this->newClass) {
                    continue;
                }

                $pivotData = [
                    'my_class_id' => $this->newClass,
                    'section_id' => ($this->newSection && $this->newSection !== 'none') ? $this->newSection : null,
                ];

                if ($existingInToYear) {
                    DB::table('academic_year_student_record')
                        ->where('id', $existingInToYear->id)
                        ->update(array_merge($pivotData, ['updated_at' => now()]));
                } else {
                    $studentRecord->academicYears()->syncWithoutDetaching([
                        $toYear->id => $pivotData
                    ]);
                }

                if ($toYear->id == auth()->user()->school->academic_year_id) {
                    $studentRecord->update([
                        'my_class_id' => $this->newClass,
                        'section_id' => $pivotData['section_id'],
                    ]);
                }

                $promotedStudents[] = $student->id;
                $successCount++;
            }

            if (empty($promotedStudents)) {
                throw new \Exception('No students were successfully promoted.');
            }

            Promotion::create([
                'old_class_id' => $this->oldClass,
                'new_class_id' => $this->newClass,
                'old_section_id' => ($this->oldSection && $this->oldSection !== 'all') ? $this->oldSection : null,
                'new_section_id' => ($this->newSection && $this->newSection !== 'none') ? $this->newSection : null,
                'students' => $promotedStudents,
                'academic_year_id' => $toYear->id,
                'school_id' => auth()->user()->school_id,
            ]);
        });

        $this->students = [];
        $this->selectedStudents = [];
        $this->selectAll = false;
        $this->loadPromotions();

        session()->flash('success', "{$successCount} student(s) promoted successfully!");
    }

    public function loadPromotions()
    {
        $this->promotions = Promotion::query()
            ->with(['oldClass', 'newClass', 'oldSection', 'newSection', 'academicYear'])
            ->latest()
            ->get();
    }

    public function resetPromotion($promotionId)
    {
        $promotion = Promotion::query()->findOrFail($promotionId);

        $currentAcademicYearId = auth()->user()->school->academic_year_id;

        DB::transaction(function () use ($promotion, $currentAcademicYearId) {
            $studentIds = is_array($promotion->students) ? $promotion->students : json_decode($promotion->students, true);

            if (!is_array($studentIds)) {
                throw new \Exception('Invalid promotion data.');
            }

            $students = User::role('student')
                ->where('school_id', auth()->user()->school_id)
                ->whereIn('id', $studentIds)
                ->with('studentRecord')
                ->get()
                ->keyBy('id');

            foreach ($studentIds as $userId) {
                $student = $students->get($userId);
                if (!$student || !$student->studentRecord) continue;

                $studentRecord = $student->studentRecord;

                DB::table('academic_year_student_record')
                    ->where('student_record_id', $studentRecord->id)
                    ->where('academic_year_id', $promotion->academic_year_id)
                    ->where('my_class_id', $promotion->new_class_id)
                    ->delete();

                DB::table('academic_year_student_record')->updateOrInsert(
                    [
                        'student_record_id' => $studentRecord->id,
                        'academic_year_id' => $promotion->academic_year_id,
                    ],
                    [
                        'my_class_id' => $promotion->old_class_id,
                        'section_id' => $promotion->old_section_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                if ($promotion->academic_year_id == $currentAcademicYearId) {
                    $studentRecord->update([
                        'my_class_id' => $promotion->old_class_id,
                        'section_id' => $promotion->old_section_id,
                    ]);
                }
            }

            $promotion->delete();
        });

        $this->loadPromotions();
        $this->loadStudents();

        session()->flash('success', 'Promotion reset successfully.');
    }

    public function viewPromotion($promotionId)
    {
        $this->selectedPromotion = Promotion::query()
            ->with(['oldClass', 'newClass', 'oldSection', 'newSection', 'academicYear'])
            ->findOrFail($promotionId);

        $this->promotionStudents = User::role('student')
            ->where('school_id', auth()->user()->school_id)
            ->whereIn('id', $this->selectedPromotion->students)
            ->with('studentRecord')
            ->get();
        $this->currentView = 'view';
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

    public function backToHistory()
    {
        $this->selectedPromotion = null;
        $this->promotionStudents = [];
        $this->currentView = 'history';
    }

    public function render()
    {
        $filteredStudents = $this->getFilteredStudents();

        return view('livewire.students.promote-students', [
            'students' => $filteredStudents,
        ])
            ->layout('layouts.dashboard', [
                'breadcrumbs' => [
                    ['href' => route('dashboard'), 'text' => 'Dashboard'],
                    ['href' => route('students.index'), 'text' => 'Students'],
                    ['href' => route('students.promote'), 'text' => 'Promote Students', 'active' => true],
                ],
                'page_heading' => 'Promote Students'
            ])
            ->title('Promote Students');
    }
}
