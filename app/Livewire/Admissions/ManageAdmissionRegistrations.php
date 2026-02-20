<?php

namespace App\Livewire\Admissions;

use App\Models\AdmissionStatusHistory;
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
    public string $adminNote = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'all'],
        'classFilter' => ['except' => ''],
    ];

    protected array $allowedStatuses = ['pending', 'reviewed', 'rejected', 'approved'];

    public function mount(): void
    {
        if (!auth()->check() || !auth()->user()->can('read admission registration')) {
            abort(403);
        }

        if ($this->statusFilter === 'contacted') {
            $this->statusFilter = 'reviewed';
        } elseif ($this->statusFilter === 'enrolled') {
            $this->statusFilter = 'approved';
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
        $this->adminNote = (string) ($admission->admin_notes ?? '');
    }

    public function clearSelected(): void
    {
        $this->selectedAdmission = null;
        $this->adminNote = '';
    }

    public function saveAdminNote(int $admissionId): void
    {
        $this->assertCanManageAdmission();

        $admission = $this->admissionQuery()->findOrFail($admissionId);
        $note = $this->normalizedNote($this->adminNote);

        $admission->update([
            'admin_notes' => $note,
            'processed_by' => auth()->id(),
            'processed_at' => now(),
        ]);

        $this->refreshSelectedAdmission($admission->id);
        session()->flash('success', 'Admin note saved.');
    }

    public function markReviewed(int $admissionId): void
    {
        $this->assertCanReviewAdmission();

        $admission = $this->admissionQuery()->findOrFail($admissionId);
        if ($admission->status === 'approved') {
            session()->flash('error', 'Approved admissions cannot be moved back to review.');
            return;
        }

        $this->transitionStatus($admission, 'reviewed', $this->normalizedNote($this->adminNote));
        $this->refreshSelectedAdmission($admission->id);
        session()->flash('success', 'Admission marked as reviewed.');
    }

    public function rejectAdmission(int $admissionId): void
    {
        $this->assertCanRejectAdmission();

        $admission = $this->admissionQuery()->findOrFail($admissionId);

        if ($admission->status === 'approved') {
            session()->flash('error', 'Approved admissions cannot be rejected.');
            return;
        }

        $this->transitionStatus($admission, 'rejected', $this->normalizedNote($this->adminNote));
        $this->refreshSelectedAdmission($admission->id);
        session()->flash('success', 'Admission rejected.');
    }

    public function approveAdmission(int $admissionId): void
    {
        $this->assertCanApproveAdmission();

        $admission = $this->admissionQuery()->findOrFail($admissionId);

        if ($admission->status === 'approved' && $admission->enrolled_user_id && $admission->enrolled_student_record_id) {
            session()->flash('error', 'This registration has already been approved and enrolled.');
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
                $lockedAdmission = AdmissionRegistration::query()
                    ->whereKey($admission->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (!$lockedAdmission->enrolled_user_id || !$lockedAdmission->enrolled_student_record_id) {
                    $email = $this->resolveStudentEmail($lockedAdmission, $school);

                    if (User::where('email', $email)->exists()) {
                        throw new \RuntimeException('Student email already exists. Update the admission email before approval.');
                    }

                    $user = User::create([
                        'name' => $lockedAdmission->student_name,
                        'email' => $email,
                        'password' => Hash::make('12345678'),
                        'gender' => $lockedAdmission->gender,
                        'birthday' => $lockedAdmission->birthday,
                        'phone' => $lockedAdmission->guardian_phone,
                        'address' => $lockedAdmission->address,
                        'school_id' => $lockedAdmission->school_id,
                    ]);

                    $user->assignRole('student');

                    $admissionNumber = $this->generateAdmissionNumber($school);

                    $studentRecord = $user->studentRecord()->create([
                        'my_class_id' => $lockedAdmission->my_class_id,
                        'section_id' => $lockedAdmission->section_id,
                        'admission_number' => $admissionNumber,
                        'admission_date' => now(),
                    ]);

                    if ($school->academicYear) {
                        $studentRecord->academicYears()->syncWithoutDetaching([
                            $school->academicYear->id => [
                                'my_class_id' => $lockedAdmission->my_class_id,
                                'section_id' => $lockedAdmission->section_id,
                            ],
                        ]);
                    }

                    $studentRecord->assignSubjectsAutomatically();

                    $lockedAdmission->update([
                        'enrolled_user_id' => $user->id,
                        'enrolled_student_record_id' => $studentRecord->id,
                        'enrolled_at' => now(),
                    ]);
                }

                $this->transitionStatus(
                    $lockedAdmission,
                    'approved',
                    $this->normalizedNote($this->adminNote)
                );
            });
        } catch (Throwable $e) {
            report($e);

            $message = $e instanceof \RuntimeException
                ? $e->getMessage()
                : 'Could not approve this registration right now. Please try again.';

            session()->flash('error', $message);
            return;
        }

        $this->refreshSelectedAdmission($admission->id);
        session()->flash('success', 'Admission approved and student record created. Default password: 12345678');
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
            'statusHistories' => fn ($history) => $history->with('changedBy:id,name')->latest('changed_at'),
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

    protected function normalizedNote(?string $note): ?string
    {
        $trimmed = trim((string) $note);

        return $trimmed === '' ? null : $trimmed;
    }

    protected function refreshSelectedAdmission(?int $admissionId = null): void
    {
        if (!$this->selectedAdmission && !$admissionId) {
            return;
        }

        $id = $admissionId ?: $this->selectedAdmission?->id;
        if (!$id) {
            return;
        }

        $fresh = $this->admissionQuery()->find($id);
        $this->selectedAdmission = $fresh;
        if ($fresh) {
            $this->adminNote = (string) ($fresh->admin_notes ?? '');
        }
    }

    protected function assertCanManageAdmission(): void
    {
        if (!auth()->user()?->can('update admission admin note')) {
            abort(403);
        }
    }

    protected function assertCanReviewAdmission(): void
    {
        if (!auth()->user()?->can('review admission registration')) {
            abort(403);
        }
    }

    protected function assertCanRejectAdmission(): void
    {
        if (!auth()->user()?->can('reject admission registration')) {
            abort(403);
        }
    }

    protected function assertCanApproveAdmission(): void
    {
        if (!auth()->user()?->can('approve admission registration')) {
            abort(403);
        }

        if (!auth()->user()?->can('create student')) {
            abort(403);
        }
    }

    protected function transitionStatus(AdmissionRegistration $admission, string $toStatus, ?string $note = null): void
    {
        if (!in_array($toStatus, $this->allowedStatuses, true)) {
            return;
        }

        $fromStatus = (string) $admission->status;
        $payload = [
            'status' => $toStatus,
            'processed_by' => auth()->id(),
            'processed_at' => now(),
        ];

        if ($note !== null) {
            $payload['admin_notes'] = $note;
        }

        $admission->update($payload);

        if ($fromStatus !== $toStatus) {
            AdmissionStatusHistory::create([
                'admission_registration_id' => $admission->id,
                'school_id' => $admission->school_id,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'note' => $note,
                'changed_by' => auth()->id(),
                'changed_at' => now(),
            ]);
        }
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
            'reviewed' => (clone $countBase)->where('status', 'reviewed')->count(),
            'rejected' => (clone $countBase)->where('status', 'rejected')->count(),
            'approved' => (clone $countBase)->where('status', 'approved')->count(),
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
