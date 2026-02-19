<?php

namespace App\Livewire\AccountApplications;

use App\Events\AccountStatusChanged;
use App\Models\User;
use App\Models\AccountApplication;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ManageAccountApplications extends Component
{
    use WithPagination, AuthorizesRequests;

    public $mode = 'list'; // list, view, change-status
    public $filter = 'pending'; // pending, rejected
    public $search = '';
    public $selectedApplicant = null;
    
    // Change Status Form
    public $status = '';
    public $reason = '';
    public $studentRecordFields = false;
    
    // Student Record Fields (for approved students)
    public $admission_number = '';
    public $admission_date = '';
    public $my_class_id = '';
    public $section_id = '';
    
    public $statuses = [];
    public $classes = [];
    public $sections = [];

    protected $queryString = [
        'mode' => ['except' => 'list'],
        'filter' => ['except' => 'pending'],
        'search' => ['except' => ''],
    ];

    protected $listeners = ['refreshApplications' => '$refresh'];

    public function mount()
    {
        $this->authorize('viewAny', [User::class, 'applicant']);
        
        if ($this->mode === 'view' || $this->mode === 'change-status') {
            if (!$this->selectedApplicant) {
                return redirect()->route('account-applications.index');
            }
        }
    }

    public function switchMode($mode, $applicantId = null)
    {
        $this->mode = $mode;
        
        if ($applicantId) {
            $this->selectedApplicant = User::with(['accountApplication.role', 'accountApplication.statuses'])
                ->findOrFail($applicantId);
            
            if ($mode === 'change-status') {
                $this->loadChangeStatusForm();
            }
        } else {
            $this->selectedApplicant = null;
            $this->reset(['status', 'reason', 'studentRecordFields', 'admission_number', 'admission_date', 'my_class_id', 'section_id']);
        }
        
        $this->resetValidation();
    }

    public function loadChangeStatusForm()
    {
        if (!$this->selectedApplicant->accountApplication) {
            session()->flash('error', 'Application not found or already processed');
            return $this->switchMode('list');
        }

        $this->statuses = $this->selectedApplicant->accountApplication->getAllStatuses()->toArray();
        $this->status = $this->statuses[0] ?? '';
        $this->admission_date = now()->format('Y-m-d');
        
        // Load classes for student role
        $this->classes = \App\Models\MyClass::orderBy('name')->get();
        
        $this->updatedStatus();
    }

    public function updatedStatus()
    {
        if ($this->status === 'approved' && 
            $this->selectedApplicant && 
            $this->selectedApplicant->accountApplication->role->name === 'student') {
            $this->studentRecordFields = true;
        } else {
            $this->studentRecordFields = false;
        }
    }

    public function updatedMyClassId()
    {
        $this->sections = \App\Models\Section::where('my_class_id', $this->my_class_id)
            ->orderBy('name')->get();
        $this->section_id = '';
    }

    public function changeStatus()
    {
        $this->authorize('update', [$this->selectedApplicant, 'applicant']);

        $rules = [
            'status' => 'required|string',
            'reason' => 'nullable|string|max:500',
        ];

        if ($this->studentRecordFields) {
            $rules['admission_number'] = 'nullable|string|unique:student_records,admission_number';
            $rules['admission_date'] = 'required|date';
            $rules['my_class_id'] = 'required|exists:my_classes,id';
            $rules['section_id'] = 'nullable|exists:sections,id';
        }

        $this->validate($rules);

        DB::transaction(function () {
            $this->selectedApplicant->accountApplication->setStatus($this->status, $this->reason);

            if ($this->status === 'approved') {
                $this->processApprovedApplication();
            }
        });

        AccountStatusChanged::dispatch($this->selectedApplicant, $this->status, $this->reason);

        session()->flash('success', 'Application status updated successfully');
        $this->switchMode('list');
    }

    protected function processApprovedApplication()
    {
        $role = $this->selectedApplicant->accountApplication->role;

        switch ($role->name) {
            case 'student':
                $admissionNumber = $this->admission_number ?: $this->generateAdmissionNumber();
                
                $studentRecord = $this->selectedApplicant->studentRecord()->create([
                    'admission_number' => $admissionNumber,
                    'admission_date' => $this->admission_date,
                    'my_class_id' => $this->my_class_id,
                    'section_id' => $this->section_id ?: null,
                ]);

                // Add to current academic year
                $currentAcademicYear = auth()->user()->school->academicYear;
                if ($currentAcademicYear) {
                    $studentRecord->academicYears()->syncWithoutDetaching([
                        $currentAcademicYear->id => [
                            'my_class_id' => $this->my_class_id,
                            'section_id' => $this->section_id ?: null,
                        ]
                    ]);
                }
                break;

            case 'parent':
                $this->selectedApplicant->parentRecord()->create();
                break;

            case 'teacher':
                $this->selectedApplicant->teacherRecord()->create();
                break;
        }

        // Assign new role and remove applicant role
        $this->selectedApplicant->syncRoles([$role->name]);
        
        // Delete application record
        $this->selectedApplicant->accountApplication->delete();
    }

    protected function generateAdmissionNumber()
    {
        $schoolInitials = auth()->user()->school->initials ?? 'SCH';
        $currentYear = date('y');
        
        do {
            $admissionNumber = "{$schoolInitials}/{$currentYear}/" . mt_rand(100000, 999999);
        } while (\App\Models\StudentRecord::where('admission_number', $admissionNumber)->exists());

        return $admissionNumber;
    }

    public function deleteApplicant($applicantId)
    {
        $applicant = User::findOrFail($applicantId);
        $this->authorize('delete', [$applicant, 'applicant']);
        
        $applicant->delete();
        
        session()->flash('success', 'Application deleted successfully');
    }

    public function render()
    {
        $applicants = collect();

        if ($this->mode === 'list') {
            $query = User::role('applicant')
                ->with(['accountApplication.role', 'accountApplication.statuses'])
                ->inSchool()
                ->when($this->search, function($q) {
                    $q->where(function($query) {
                        $query->where('name', 'like', '%' . $this->search . '%')
                              ->orWhere('email', 'like', '%' . $this->search . '%');
                    });
                });

if ($this->filter === 'pending') {
    $query->whereHas('accountApplication.statuses', function($q) {
        $q->where('name', '!=', 'rejected')
          ->whereRaw('statuses.id = (
              SELECT MAX(id) FROM statuses 
              WHERE statuses.model_id = account_applications.id 
              AND statuses.model_type = ?
          )', [\App\Models\AccountApplication::class]);
    });
} else {
    $query->whereHas('accountApplication.statuses', function($q) {
        $q->where('name', 'rejected')
          ->whereRaw('statuses.id = (
              SELECT MAX(id) FROM statuses 
              WHERE statuses.model_id = account_applications.id 
              AND statuses.model_type = ?
          )', [\App\Models\AccountApplication::class]);
    });
}

            $applicants = $query->latest()->paginate(15);
        }

        return view('livewire.account-applications.manage-account-applications', compact('applicants'))
            ->layout('layouts.dashboard', [
                'breadcrumbs' => $this->getBreadcrumbs()
            ])
            ->title($this->getPageTitle());
    }

    protected function getBreadcrumbs()
    {
        $breadcrumbs = [
            ['href' => route('dashboard'), 'text' => 'Dashboard'],
        ];

        if ($this->mode === 'list') {
            $breadcrumbs[] = [
                'href' => route('account-applications.index'), 
                'text' => $this->filter === 'pending' ? 'Account Applications' : 'Rejected Applications',
                'active' => true
            ];
        } elseif ($this->mode === 'view' && $this->selectedApplicant) {
            $breadcrumbs[] = ['href' => route('account-applications.index'), 'text' => 'Account Applications'];
            $breadcrumbs[] = [
                'href' => route('account-applications.show', $this->selectedApplicant->id), 
                'text' => 'View ' . $this->selectedApplicant->name,
                'active' => true
            ];
        } elseif ($this->mode === 'change-status' && $this->selectedApplicant) {
            $breadcrumbs[] = ['href' => route('account-applications.index'), 'text' => 'Account Applications'];
            $breadcrumbs[] = [
                'href' => route('account-applications.change-status', $this->selectedApplicant->id), 
                'text' => 'Change Status',
                'active' => true
            ];
        }

        return $breadcrumbs;
    }

    protected function getPageTitle()
    {
        if ($this->mode === 'view' && $this->selectedApplicant) {
            return $this->selectedApplicant->name . "'s Application";
        } elseif ($this->mode === 'change-status') {
            return 'Change Application Status';
        }
        
        return $this->filter === 'pending' ? 'Account Applications' : 'Rejected Applications';
    }
}