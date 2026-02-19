<?php

namespace App\Livewire\Admissions;

use App\Models\AdmissionRegistration;
use App\Models\MyClass;
use App\Models\School;
use App\Models\Section;
use App\Models\StudentRecord;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Throwable;
use Livewire\Component;
use Livewire\WithPagination;

class ManageAdmissionRegistrations extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = 'all';
    public string $classFilter = '';
    public int $perPage = 15;

    public $classes = [];
    public ?AdmissionRegistration $selectedAdmission = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'all'],
        'classFilter' => ['except' => ''],
    ];

    protected array $allowedStatuses = ['pending', 'contacted', 'rejected', 'enrolled'];

    public function mount(): void
    {
        if (!auth()->check() || !auth()->user()->hasAnyRole(['super-admin', 'super_admin', 'admin', 'principal', 'teacher'])) {
            abort(403);
        }

        $this->classes = $this->classesForCurrentContext();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingClassFilter(): void
    {
        $this->resetPage();
    }

    public function viewAdmission(int $admissionId): void
    {
        $admission = $this->admissionQuery()->findOrFail($admissionId);
        $this->selectedAdmission = $admission;
    }

    public function clearSelected(): void
    {
        $this->selectedAdmission = null;
    }

    public function markStatus(int $admissionId, string $status): void
    {
        if (!in_array($status, ['pending', 'contacted', 'rejected'], true)) {
            return;
        }

        $admission = $this->admissionQuery()->findOrFail($admissionId);

        if ($admission->status === 'enrolled') {
            session()->flash('error', 'Enrolled registrations cannot be changed to this status.');
            return;
        }

        $admission->update([
            'status' => $status,
            'processed_by' => auth()->id(),
            'processed_at' => now(),
        ]);

        if ($this->selectedAdmission && $this->selectedAdmission->id === $admission->id) {
            $this->selectedAdmission = $admission->fresh([
                'school', 'myClass', 'section', 'processedBy', 'enrolledUser', 'enrolledStudentRecord',
            ]);
        }

        session()->flash('success', 'Admission status updated successfully.');
    }

    public function enrollStudent(int $admissionId): void
    {
        $admission = $this->admissionQuery()->findOrFail($admissionId);

        if ($admission->enrolled_user_id || $admission->status === 'enrolled') {
            session()->flash('error', 'This registration has already been enrolled.');
            return;
        }

        if (!$admission->my_class_id) {
            session()->flash('error', 'This admission has no class assigned.');
            return;
        }

        if (!$this->classBelongsToSchool($admission->my_class_id, $admission->school_id)) {
            session()->flash('error', 'Assigned class does not belong to selected school.');
            return;
        }

        if ($admission->section_id && !$this->sectionBelongsToClass($admission->section_id, $admission->my_class_id)) {
            session()->flash('error', 'Assigned section does not belong to selected class.');
            return;
        }

        $school = School::find($admission->school_id);
        if (!$school) {
            session()->flash('error', 'School record not found for this registration.');
            return;
        }

        try {
            DB::transaction(function () use ($admission, $school) {
                $email = $this->resolveStudentEmail($admission, $school);

                if (User::where('email', $email)->exists()) {
                    throw new \RuntimeException('Student email already exists. Update the admission email before enrollment.');
                }

                $user = User::create([
                    'name' => $admission->student_name,
                    'email' => $email,
                    'password' => Hash::make('12345678'),
                    'gender' => $admission->gender,
                    'birthday' => $admission->birthday,
                    'phone' => $admission->guardian_phone,
                    'address' => $admission->address,
                    'school_id' => $admission->school_id,
                ]);

                $user->assignRole('student');

                $admissionNumber = $this->generateAdmissionNumber($school);

                $studentRecord = $user->studentRecord()->create([
                    'my_class_id' => $admission->my_class_id,
                    'section_id' => $admission->section_id,
                    'admission_number' => $admissionNumber,
                    'admission_date' => now(),
                ]);

                if ($school->academicYear) {
                    $studentRecord->academicYears()->syncWithoutDetaching([
                        $school->academicYear->id => [
                            'my_class_id' => $admission->my_class_id,
                            'section_id' => $admission->section_id,
                        ],
                    ]);
                }

                $admission->update([
                    'status' => 'enrolled',
                    'processed_by' => auth()->id(),
                    'processed_at' => now(),
                    'enrolled_user_id' => $user->id,
                    'enrolled_student_record_id' => $studentRecord->id,
                    'enrolled_at' => now(),
                ]);
            });
        } catch (Throwable $e) {
            report($e);

            $message = $e instanceof \RuntimeException
                ? $e->getMessage()
                : 'Could not enroll this registration right now. Please try again.';

            session()->flash('error', $message);
            return;
        }

        if ($this->selectedAdmission && $this->selectedAdmission->id === $admission->id) {
            $this->selectedAdmission = $this->admissionQuery()->find($admission->id);
        }

        session()->flash('success', 'Student enrolled successfully. Default password: 12345678');
    }

    protected function admissionQuery()
    {
        $query = AdmissionRegistration::query()->with([
            'school:id,name',
            'myClass:id,name',
            'section:id,name',
            'processedBy:id,name',
            'enrolledUser:id,name,email',
            'enrolledStudentRecord:id,admission_number,user_id',
        ]);

        $schoolId = auth()->user()?->school_id;
        if ($schoolId) {
            $query->where('school_id', $schoolId);
        } else {
            $query->whereRaw('1 = 0');
        }

        return $query;
    }

    protected function classesForCurrentContext()
    {
        $schoolId = auth()->user()?->school_id;

        return MyClass::query()
            ->select('my_classes.id', 'my_classes.name')
            ->when($schoolId, function ($query) use ($schoolId) {
                $query->whereHas('classGroup', fn ($q) => $q->where('school_id', $schoolId));
            })
            ->when(!$schoolId, fn ($query) => $query->whereRaw('1 = 0'))
            ->orderBy('name')
            ->get();
    }

    protected function classBelongsToSchool(int $classId, int $schoolId): bool
    {
        return MyClass::query()
            ->where('id', $classId)
            ->whereHas('classGroup', fn ($q) => $q->where('school_id', $schoolId))
            ->exists();
    }

    protected function sectionBelongsToClass(int $sectionId, int $classId): bool
    {
        return Section::query()
            ->where('id', $sectionId)
            ->where('my_class_id', $classId)
            ->exists();
    }

    protected function resolveStudentEmail(AdmissionRegistration $admission, School $school): string
    {
        if (!empty($admission->student_email) && trim($admission->student_email) !== '') {
            return strtolower(trim($admission->student_email));
        }

        $baseName = Str::slug($admission->student_name ?: 'student') ?: 'student';
        $schoolPart = Str::slug($school->initials ?: $school->name ?: 'school') ?: 'school';
        $email = $baseName . '.' . strtolower($admission->reference_no) . '@' . $schoolPart . '.admission.local';

        $attempt = 1;
        while (User::where('email', $email)->exists()) {
            $email = $baseName . '.' . strtolower($admission->reference_no) . '.' . $attempt . '@' . $schoolPart . '.admission.local';
            $attempt++;
        }

        return $email;
    }

    protected function generateAdmissionNumber(School $school): string
    {
        $prefix = strtoupper($school->initials ?: 'SCH');
        $year = now()->format('y');

        do {
            $number = $prefix . '/' . $year . '/' . mt_rand(100000, 999999);
        } while (StudentRecord::where('admission_number', $number)->exists());

        return $number;
    }

    public function render()
    {
        $query = $this->admissionQuery()
            ->when($this->statusFilter !== 'all', function ($q) {
                $q->where('status', $this->statusFilter);
            })
            ->when($this->classFilter !== '', function ($q) {
                $q->where('my_class_id', $this->classFilter);
            })
            ->when($this->search !== '', function ($q) {
                $search = '%' . trim($this->search) . '%';
                $q->where(function ($inner) use ($search) {
                    $inner->where('reference_no', 'like', $search)
                        ->orWhere('student_name', 'like', $search)
                        ->orWhere('guardian_name', 'like', $search)
                        ->orWhere('guardian_phone', 'like', $search)
                        ->orWhere('student_email', 'like', $search);
                });
            })
            ->latest();

        $registrations = $query->paginate($this->perPage);

        $countBase = $this->admissionQuery();
        $statusCounts = [
            'all' => (clone $countBase)->count(),
            'pending' => (clone $countBase)->where('status', 'pending')->count(),
            'contacted' => (clone $countBase)->where('status', 'contacted')->count(),
            'rejected' => (clone $countBase)->where('status', 'rejected')->count(),
            'enrolled' => (clone $countBase)->where('status', 'enrolled')->count(),
        ];

        return view('livewire.admissions.manage-admission-registrations', [
            'registrations' => $registrations,
            'statusCounts' => $statusCounts,
        ])->layout('layouts.dashboard', [
            'breadcrumbs' => [
                ['href' => route('dashboard'), 'text' => 'Dashboard'],
                ['href' => route('admissions.registrations.index'), 'text' => 'Admission Registrations', 'active' => true],
            ],
        ])->title('Admission Registrations');
    }
}
