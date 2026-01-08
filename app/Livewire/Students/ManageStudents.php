<?php

namespace App\Livewire\Students;

use App\Models\MyClass;
use App\Models\Section;
use App\Models\User;
use App\Models\StudentRecord;
use App\Services\MyClass\MyClassService;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Carbon\Carbon;

class ManageStudents extends Component
{
    use WithPagination, AuthorizesRequests, WithFileUploads;

    public $mode = 'list';
    
    // Filters
    public $search = '';
    public $selectedClass = '';
    public $appliedClass = '';
    public $selectedStatus = '';
    public $appliedStatus = '';
    public $selectedSection = '';
    
    // Sorting & Pagination
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $perPage = 15;
    
    // Data
    public $classes = [];
    public $sections = [];
    
    // Bulk actions
    public $selectedStudents = [];
    public $selectAll = false;
    public $showBulkModal = false;
    public $bulkAction = '';
    public $bulkSection = '';
    public $bulkClass = '';
    public $bulkClassSections = [];
    
    // Student form
    public $studentId = null;
    public $name = '';
    public $email = '';
    public $password = '';
    public $gender = '';
    public $birthday = '';
    public $phone = '';
    public $address = '';
    public $blood_group = '';
    public $religion = '';
    public $nationality = '';
    public $state = '';
    public $city = '';
    public $my_class_id = '';
    public $section_id = '';
    public $admission_number = '';
    public $admission_date = '';
    public $profile_photo = null;

    protected $queryString = [
        'mode' => ['except' => 'list'],
        'search' => ['except' => ''],
        'appliedClass' => ['except' => ''],
    ];

    protected $listeners = ['refreshStudents' => '$refresh'];

    public function mount(MyClassService $classService)
    {
        $this->classes = $classService->getAllClasses();
        
        if ($this->mode === 'edit' && $this->studentId) {
            $this->loadStudentForEdit();
        } elseif ($this->mode === 'create') {
            $this->resetForm();
            $this->admission_date = now()->format('Y-m-d');
        }
    }

    public function updatedSelectedClass()
    {
        $this->sections = Section::where('my_class_id', $this->selectedClass)
            ->orderBy('name')->get();
        $this->selectedSection = '';
    }

    public function updatedMyClassId()
    {
        $this->sections = Section::where('my_class_id', $this->my_class_id)
            ->orderBy('name')->get();
        $this->section_id = '';
    }

    public function updatedBulkClass()
    {
        $this->bulkClassSections = Section::where('my_class_id', $this->bulkClass)
            ->orderBy('name')->get();
        $this->bulkSection = '';
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedStudents = $this->getStudentsQuery()
                ->pluck('users.id')
                ->toArray();
        } else {
            $this->selectedStudents = [];
        }
    }

    public function switchMode($mode, $studentId = null)
    {
        $this->mode = $mode;
        $this->studentId = $studentId;
        $this->resetValidation();
        
        if ($mode === 'edit' && $studentId) {
            $this->loadStudentForEdit();
        } elseif ($mode === 'create') {
            $this->resetForm();
            $this->admission_date = now()->format('Y-m-d');
        }
    }

    public function loadStudentForEdit()
    {
        $student = User::with('studentRecord')->findOrFail($this->studentId);
        $this->authorize('update', [$student, 'student']);
        
        $this->fill([
            'name' => $student->name,
            'email' => $student->email,
            'gender' => $student->gender ?? '',
            'birthday' => $student->birthday ? 
                ($student->birthday instanceof Carbon ? $student->birthday->format('Y-m-d') : $student->birthday) : '',
            'phone' => $student->phone ?? '',
            'address' => $student->address ?? '',
            'blood_group' => $student->blood_group ?? '',
            'religion' => $student->religion ?? '',
            'nationality' => $student->nationality ?? '',
            'state' => $student->state ?? '',
            'city' => $student->city ?? '',
        ]);
        
        if ($student->studentRecord) {
            $this->my_class_id = $student->studentRecord->my_class_id;
            $this->section_id = $student->studentRecord->section_id ?? '';
            $this->admission_number = $student->studentRecord->admission_number;
            $this->admission_date = $student->studentRecord->admission_date ?
                ($student->studentRecord->admission_date instanceof Carbon ? 
                    $student->studentRecord->admission_date->format('Y-m-d') : 
                    $student->studentRecord->admission_date) : '';
            $this->updatedMyClassId();
        }
    }

    public function createStudent()
    {
        $this->authorize('create', [User::class, 'student']);
        
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'gender' => 'required|in:male,female',
            'my_class_id' => 'required|exists:my_classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'birthday' => 'nullable|date|before:today',
            'phone' => 'nullable|string|max:20',
            'admission_date' => 'nullable|date',
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => bcrypt($this->password),
                'gender' => $this->gender,
                'birthday' => $this->birthday ?: null,
                'phone' => $this->phone,
                'address' => $this->address,
                'blood_group' => $this->blood_group,
                'religion' => $this->religion,
                'nationality' => $this->nationality,
                'state' => $this->state,
                'city' => $this->city,
                'school_id' => auth()->user()->school_id,
            ]);

            $user->assignRole('student');

            $admissionNumber = $this->admission_number ?: $this->generateAdmissionNumber();

            $studentRecord = $user->studentRecord()->create([
                'my_class_id' => $this->my_class_id,
                'section_id' => $this->section_id ?: null,
                'admission_number' => $admissionNumber,
                'admission_date' => $this->admission_date ?: now(),
            ]);

            $currentAcademicYear = auth()->user()->school->academicYear;
            if ($currentAcademicYear) {
                $studentRecord->academicYears()->syncWithoutDetaching([
                    $currentAcademicYear->id => [
                        'my_class_id' => $this->my_class_id,
                        'section_id' => $this->section_id ?: null,
                    ]
                ]);
            }
        });

        session()->flash('success', 'Student created successfully');
        $this->switchMode('list');
        $this->dispatch('refreshStudents');
    }

    public function updateStudent()
    {
        $student = User::findOrFail($this->studentId);
        $this->authorize('update', [$student, 'student']);
        
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->studentId,
            'my_class_id' => 'required|exists:my_classes,id',
            'gender' => 'required|in:male,female',
            'birthday' => 'nullable|date|before:today',
            'phone' => 'nullable|string|max:20',
        ]);

        DB::transaction(function () use ($student) {
            $student->update([
                'name' => $this->name,
                'email' => $this->email,
                'gender' => $this->gender,
                'birthday' => $this->birthday ?: null,
                'phone' => $this->phone,
                'address' => $this->address,
                'blood_group' => $this->blood_group,
                'religion' => $this->religion,
                'nationality' => $this->nationality,
                'state' => $this->state,
                'city' => $this->city,
            ]);

            if ($this->password) {
                $student->update(['password' => bcrypt($this->password)]);
            }

            if ($student->studentRecord) {
                $oldClassId = $student->studentRecord->my_class_id;
                
                $student->studentRecord->update([
                    'my_class_id' => $this->my_class_id,
                    'section_id' => $this->section_id ?: null,
                    'admission_date' => $this->admission_date ?: $student->studentRecord->admission_date,
                ]);

                if ($oldClassId != $this->my_class_id) {
                    $currentAcademicYear = auth()->user()->school->academicYear;
                    if ($currentAcademicYear) {
                        DB::table('academic_year_student_record')
                            ->where('student_record_id', $student->studentRecord->id)
                            ->where('academic_year_id', $currentAcademicYear->id)
                            ->update([
                                'my_class_id' => $this->my_class_id,
                                'section_id' => $this->section_id ?: null,
                                'updated_at' => now(),
                            ]);
                    }
                }
            }
        });

        session()->flash('success', 'Student updated successfully');
        $this->switchMode('list');
    }

    public function deleteStudent($studentId)
    {
        $student = User::findOrFail($studentId);
        $this->authorize('delete', [$student, 'student']);
        
        DB::transaction(function () use ($student) {
            if ($student->studentRecord) {
                $student->studentRecord->delete();
            }
            $student->delete();
        });
        
        session()->flash('success', 'Student deleted successfully');
    }

    public function applyFilters()
    {
        $this->appliedClass = $this->selectedClass;
        $this->appliedStatus = $this->selectedStatus;
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->reset(['search', 'selectedClass', 'appliedClass', 'selectedStatus', 'appliedStatus', 'selectedSection', 'sections']);
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function openBulkModal($action)
    {
        if (empty($this->selectedStudents)) {
            session()->flash('error', 'Please select at least one student');
            return;
        }

        $this->bulkAction = $action;
        $this->showBulkModal = true;
    }

    public function executeBulkAction()
    {
        if (empty($this->selectedStudents)) {
            session()->flash('error', 'No students selected');
            return;
        }

        DB::transaction(function () {
            if ($this->bulkAction === 'assign_section') {
                $this->bulkAssignSection();
            } elseif ($this->bulkAction === 'move_class') {
                $this->bulkMoveClass();
            }
        });

        $this->closeBulkModal();
        $this->reset(['selectedStudents', 'selectAll']);
    }

    protected function bulkAssignSection()
    {
        $currentAcademicYear = auth()->user()->school->academic_year_id;
        
        $studentRecords = DB::table('student_records')
            ->whereIn('user_id', $this->selectedStudents)
            ->pluck('id');

        DB::table('academic_year_student_record')
            ->whereIn('student_record_id', $studentRecords)
            ->where('academic_year_id', $currentAcademicYear)
            ->update(['section_id' => $this->bulkSection ?: null]);

        session()->flash('success', count($this->selectedStudents) . ' students assigned to section');
    }

    protected function bulkMoveClass()
    {
        $currentAcademicYear = auth()->user()->school->academic_year_id;
        
        $studentRecords = DB::table('student_records')
            ->whereIn('user_id', $this->selectedStudents)
            ->pluck('id');

        DB::table('academic_year_student_record')
            ->whereIn('student_record_id', $studentRecords)
            ->where('academic_year_id', $currentAcademicYear)
            ->update([
                'my_class_id' => $this->bulkClass,
                'section_id' => $this->bulkSection ?: null,
            ]);

        session()->flash('success', count($this->selectedStudents) . ' students moved to new class');
    }

    public function closeBulkModal()
    {
        $this->showBulkModal = false;
        $this->reset(['bulkAction', 'bulkSection', 'bulkClass', 'bulkClassSections']);
    }

    public function resetForm()
    {
        $this->reset([
            'studentId', 'name', 'email', 'password', 'gender', 'birthday',
            'phone', 'address', 'blood_group', 'religion', 'nationality',
            'state', 'city', 'my_class_id', 'section_id', 'admission_number', 'admission_date'
        ]);
    }

    protected function generateAdmissionNumber()
    {
        $schoolInitials = auth()->user()->school->initials ?? 'SCH';
        $currentYear = date('y');
        
        do {
            $admissionNumber = "{$schoolInitials}/{$currentYear}/" . mt_rand(100000, 999999);
        } while (StudentRecord::where('admission_number', $admissionNumber)->exists());

        return $admissionNumber;
    }

    protected function getStudentsQuery()
    {
        $currentAcademicYearId = auth()->user()->school->academic_year_id;
        
        $pivotData = DB::table('academic_year_student_record')
            ->where('academic_year_id', $currentAcademicYearId)
            ->when($this->appliedClass, fn($q) => $q->where('my_class_id', $this->appliedClass))
            ->when($this->selectedSection, fn($q) => $q->where('section_id', $this->selectedSection))
            ->select('student_record_id', 'my_class_id', 'section_id')
            ->get()
            ->keyBy('student_record_id');
    
        $studentRecordIds = $pivotData->pluck('student_record_id');
    
        return User::role('student')
            ->select('users.*')
            ->join('student_records', 'student_records.user_id', '=', 'users.id')
            ->when($studentRecordIds->isNotEmpty(), fn($q) => $q->whereIn('student_records.id', $studentRecordIds))
            ->when($this->search, function($q) {
                $q->where(function($query) {
                    $query->where('users.name', 'like', '%' . $this->search . '%')
                          ->orWhere('users.email', 'like', '%' . $this->search . '%')
                          ->orWhere('student_records.admission_number', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->appliedStatus !== '', fn($q) => $q->where('users.locked', $this->appliedStatus))
            ->orderBy($this->sortField, $this->sortDirection);
    }
    public function render()
    {
        $students = collect();
        
        if ($this->mode === 'list') {
            $students = $this->getStudentsQuery()
                ->with('studentRecord')
                ->paginate($this->perPage);
    
            $currentAcademicYearId = auth()->user()->school->academic_year_id;
            
            if ($currentAcademicYearId) {
                $pivotData = DB::table('academic_year_student_record')
                    ->where('academic_year_id', $currentAcademicYearId)
                    ->whereIn('student_record_id', $students->pluck('studentRecord.id')->filter())
                    ->get()
                    ->keyBy('student_record_id');
    
                $classIds = $pivotData->pluck('my_class_id')->unique();
                $sectionIds = $pivotData->pluck('section_id')->filter()->unique();
                
                $classes = MyClass::whereIn('id', $classIds)->get()->keyBy('id');
                $sections = Section::whereIn('id', $sectionIds)->get()->keyBy('id');
    
                $students->getCollection()->transform(function($student) use ($pivotData, $classes, $sections) {
                    // Initialize properties to avoid null errors
                    if (!$student->studentRecord) {
                        $student->studentRecord = (object)[
                            'current_year_class' => null,
                            'current_year_section' => null,
                            'admission_number' => 'N/A'
                        ];
                        return $student;
                    }
                    
                    if (isset($pivotData[$student->studentRecord->id])) {
                        $pivot = $pivotData[$student->studentRecord->id];
                        $student->studentRecord->current_year_class = $classes->get($pivot->my_class_id);
                        $student->studentRecord->current_year_section = $pivot->section_id 
                            ? $sections->get($pivot->section_id) 
                            : null;
                    } else {
                        // No pivot data for this academic year
                        $student->studentRecord->current_year_class = null;
                        $student->studentRecord->current_year_section = null;
                    }
                    return $student;
                });
            } else {
                // No current academic year set
                $students->getCollection()->transform(function($student) {
                    if (!$student->studentRecord) {
                        $student->studentRecord = (object)[
                            'current_year_class' => null,
                            'current_year_section' => null,
                            'admission_number' => 'N/A'
                        ];
                    } else {
                        $student->studentRecord->current_year_class = null;
                        $student->studentRecord->current_year_section = null;
                    }
                    return $student;
                });
            }
        }
    
        return view('livewire.students.manage-students', compact('students'))
            ->layout('layouts.new', [
                'breadcrumbs' => [
                    ['href' => route('dashboard'), 'text' => 'Dashboard'],
                    ['href' => route('students.index'), 'text' => 'Students', 'active' => true]
                ]
            ])
            ->title('Manage Students');
    }
}