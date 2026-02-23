<?php

namespace App\Livewire\Fees;

use App\Models\FeeInvoice;
use App\Models\Fee;
use App\Models\FeeCategory;
use App\Models\User;
use App\Models\MyClass;
use App\Models\Section;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ManageFeeInvoices extends Component
{
    use WithPagination, AuthorizesRequests;

    public $mode = 'list';
    
    // Filters
    public $search = '';
    public $statusFilter = 'all';
    public $yearFilter = '';
    
    // Sorting & Pagination
    public $sortField = 'due_date';
    public $sortDirection = 'desc';
    public $perPage = 15;
    
    // Form fields for invoice
    public $feeInvoiceId = null;
    public $issue_date = '';
    public $due_date = '';
    public $note = '';
    
    // For creating invoices
    public $selectedStudents = [];
    public $selectedFees = [];
    public $classes = [];
    public $sections = [];
    public $students = [];
    public $feeCategories = [];
    public $fees = [];
    public $selectedClass = '';
    public $selectedSection = '';
    public $selectedStudent = '';
    public $selectedFeeCategory = '';
    public $selectedFee = '';
    public $feeAmounts = [];

    protected $queryString = [
        'mode' => ['except' => 'list'],
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'all'],
    ];

    public function mount()
    {
        $this->hydrateModeFromRoute();

        $this->yearFilter = date('Y');
        
        if ($this->mode === 'create') {
            if ($this->modeIsAllowed('create')) {
                $this->loadDataForCreate();
            } else {
                $this->mode = 'list';
            }
        } elseif ($this->mode === 'edit' && $this->feeInvoiceId) {
            if ($this->modeIsAllowed('edit')) {
                $this->loadFeeInvoiceForEdit();
            } else {
                $this->mode = 'list';
                $this->feeInvoiceId = null;
            }
        } elseif ($this->mode === 'edit') {
            $this->mode = 'list';
            $this->feeInvoiceId = null;
        }
    }

    protected function hydrateModeFromRoute(): void
    {
        $routeName = request()->route()?->getName();

        if ($routeName === 'fee-invoices.create') {
            $this->mode = 'create';
            $this->feeInvoiceId = null;
            return;
        }

        if ($routeName === 'fee-invoices.edit') {
            $this->mode = 'edit';
            $this->feeInvoiceId = $this->resolveRouteModelId(request()->route('feeInvoice'));
        }
    }

    protected function resolveRouteModelId(mixed $value): ?int
    {
        if ($value instanceof \Illuminate\Database\Eloquent\Model) {
            return (int) $value->getKey();
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }

    public function loadDataForCreate()
    {
        $this->ensurePermission('create fee invoice');

        $this->classes = MyClass::whereHas('classGroup', function($q) {
            $q->where('school_id', auth()->user()->school_id);
        })->get();
        
        $this->feeCategories = FeeCategory::query()->get();
        
        $this->issue_date = date('Y-m-d');
        $this->due_date = date('Y-m-d', strtotime('+30 days'));
    }

    public function updatedSelectedClass()
    {
        if ($this->selectedClass) {
            $class = $this->getClassForCurrentSchool($this->selectedClass);
            $this->sections = $class
                ? $class->sections()->orderBy('name')->get()
                : collect();
            $this->selectedSection = '';
            $this->selectedStudent = '';
            $this->updateStudentsList();
        }
    }

    public function updatedSelectedSection()
    {
        $this->selectedStudent = '';
        $this->updateStudentsList();
    }

    public function updateStudentsList()
    {
        if ($this->selectedSection) {
            $section = $this->getSectionForCurrentSchool($this->selectedSection, $this->selectedClass ?: null);
            $this->students = $section ? $section->students() : collect();
        } elseif ($this->selectedClass) {
            $class = $this->getClassForCurrentSchool($this->selectedClass);
            $this->students = $class ? $class->students() : collect();
        } else {
            $this->students = collect();
        }
    }

    public function addStudent()
    {
        $this->ensurePermission('create fee invoice');

        if ($this->selectedStudent) {
            $student = $this->getStudentForCurrentSchool($this->selectedStudent);
            if ($student && !isset($this->selectedStudents[$student->id])) {
                $this->selectedStudents[$student->id] = [
                    'id' => $student->id,
                    'name' => $student->name,
                    'email' => $student->email,
                ];
            }
        } elseif ($this->selectedSection) {
            $section = $this->getSectionForCurrentSchool($this->selectedSection, $this->selectedClass ?: null);
            if ($section) {
                foreach ($section->students() as $student) {
                    if (!isset($this->selectedStudents[$student->id])) {
                        $this->selectedStudents[$student->id] = [
                            'id' => $student->id,
                            'name' => $student->name,
                            'email' => $student->email,
                        ];
                    }
                }
            }
        } elseif ($this->selectedClass) {
            $class = $this->getClassForCurrentSchool($this->selectedClass);
            if ($class) {
                foreach ($class->students() as $student) {
                    if (!isset($this->selectedStudents[$student->id])) {
                        $this->selectedStudents[$student->id] = [
                            'id' => $student->id,
                            'name' => $student->name,
                            'email' => $student->email,
                        ];
                    }
                }
            }
        }
    }

    public function removeStudent($studentId)
    {
        unset($this->selectedStudents[$studentId]);
    }

    public function updatedSelectedFeeCategory()
    {
        if ($this->selectedFeeCategory) {
            $category = $this->getFeeCategoryForCurrentSchool($this->selectedFeeCategory);
            $this->fees = $category ? $category->fees : collect();
            $this->selectedFee = '';
        }
    }

    public function addFee()
    {
        $this->ensurePermission('create fee invoice');

        if ($this->selectedFee) {
            $fee = $this->getFeeForCurrentSchool($this->selectedFee);
            if ($fee && !isset($this->selectedFees[$fee->id])) {
                $this->selectedFees[$fee->id] = [
                    'id' => $fee->id,
                    'name' => $fee->name,
                    'amount' => 0,
                    'waiver' => 0,
                    'fine' => 0,
                ];
            }
        } elseif ($this->selectedFeeCategory) {
            $category = $this->getFeeCategoryForCurrentSchool($this->selectedFeeCategory);
            if ($category) {
                foreach ($category->fees as $fee) {
                    if (!isset($this->selectedFees[$fee->id])) {
                        $this->selectedFees[$fee->id] = [
                            'id' => $fee->id,
                            'name' => $fee->name,
                            'amount' => 0,
                            'waiver' => 0,
                            'fine' => 0,
                        ];
                    }
                }
            }
        }
    }

    public function removeFee($feeId)
    {
        unset($this->selectedFees[$feeId]);
    }

    public function switchMode($mode, $feeInvoiceId = null)
    {
        if (!$this->modeIsAllowed($mode)) {
            $this->mode = 'list';
            $this->feeInvoiceId = null;
            return;
        }

        $this->mode = $mode;
        $this->feeInvoiceId = $feeInvoiceId;
        $this->resetValidation();
        
        if ($mode === 'create') {
            $this->resetForm();
            $this->loadDataForCreate();
        } elseif ($mode === 'edit' && $feeInvoiceId) {
            $this->loadFeeInvoiceForEdit();
        }
    }

    public function loadFeeInvoiceForEdit()
    {
        $this->ensurePermission('update fee invoice');

        $feeInvoice = $this->getFeeInvoiceForCurrentSchool($this->feeInvoiceId);
        
        $this->fill([
            'issue_date' => $feeInvoice->issue_date->format('Y-m-d'),
            'due_date' => $feeInvoice->due_date->format('Y-m-d'),
            'note' => $feeInvoice->note ?? '',
        ]);
    }

    public function createFeeInvoice()
    {
        $this->ensurePermission('create fee invoice');

        $this->validate([
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'note' => 'nullable|string|max:1000',
        ]);

        if (empty($this->selectedStudents)) {
            session()->flash('error', 'Please select at least one student');
            return;
        }

        if (empty($this->selectedFees)) {
            session()->flash('error', 'Please select at least one fee');
            return;
        }

        $studentIds = collect($this->selectedStudents)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
        $students = User::role('student')
            ->where('school_id', auth()->user()->school_id)
            ->whereIn('id', $studentIds)
            ->get(['id', 'name', 'email'])
            ->keyBy('id');

        if ($students->count() !== $studentIds->count()) {
            session()->flash('error', 'One or more selected students are not in your current school.');
            return;
        }

        $selectedFeesById = collect($this->selectedFees)->keyBy('id');
        $feeIds = $selectedFeesById->keys()->map(fn ($id) => (int) $id)->values();
        $fees = Fee::whereIn('id', $feeIds)
            ->whereHas('feeCategory', function ($query) {
                $query->where('school_id', auth()->user()->school_id);
            })
            ->get(['id', 'name'])
            ->keyBy('id');

        if ($fees->count() !== $feeIds->count()) {
            session()->flash('error', 'One or more selected fees are not in your current school.');
            return;
        }

        DB::transaction(function () use ($students, $fees, $selectedFeesById) {
            foreach ($students as $student) {
                $invoiceName = $this->generateInvoiceNumber();
                
                $invoice = FeeInvoice::create([
                    'name' => $invoiceName,
                    'issue_date' => $this->issue_date,
                    'due_date' => $this->due_date,
                    'note' => $this->note ?: null,
                    'user_id' => $student->id,
                ]);

                foreach ($fees as $fee) {
                    $feeData = $selectedFeesById->get($fee->id, []);
                    $invoice->feeInvoiceRecords()->create([
                        'fee_id' => $fee->id,
                        'amount' => $feeData['amount'] ?? 0,
                        'waiver' => $feeData['waiver'] ?? 0,
                        'fine' => $feeData['fine'] ?? 0,
                        'paid' => 0,
                    ]);
                }
            }
        });

        session()->flash('success', 'Fee invoices created successfully');
        $this->switchMode('list');
    }

    public function updateFeeInvoice()
    {
        $this->ensurePermission('update fee invoice');

        $feeInvoice = $this->getFeeInvoiceForCurrentSchool($this->feeInvoiceId);
        
        $this->validate([
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'note' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($feeInvoice) {
            $feeInvoice->update([
                'issue_date' => $this->issue_date,
                'due_date' => $this->due_date,
                'note' => $this->note ?: null,
            ]);
        });

        session()->flash('success', 'Fee invoice updated successfully');
        $this->switchMode('list');
    }

    public function deleteFeeInvoice($feeInvoiceId)
    {
        $this->ensurePermission('delete fee invoice');

        $feeInvoice = $this->getFeeInvoiceForCurrentSchool($feeInvoiceId);
        
        DB::transaction(function () use ($feeInvoice) {
            $feeInvoice->delete();
        });
        
        session()->flash('success', 'Fee invoice deleted successfully');
    }

    protected function generateInvoiceNumber()
    {
        $school = auth()->user()->school;
        $schoolInitials = $school->initials ? $school->initials . '-' : '';

        do {
            $invoiceNumber = "Fee-Invoice-{$schoolInitials}" . mt_rand(100000000, 999999999);
            $exists = FeeInvoice::where('name', $invoiceNumber)->exists();
        } while ($exists);

        return $invoiceNumber;
    }

    public function clearFilters()
    {
        $this->reset(['search', 'statusFilter']);
        $this->yearFilter = date('Y');
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

    public function resetForm()
    {
        $this->reset([
            'feeInvoiceId', 'issue_date', 'due_date', 'note',
            'selectedStudents', 'selectedFees', 'selectedClass',
            'selectedSection', 'selectedStudent', 'selectedFeeCategory',
            'selectedFee', 'feeAmounts'
        ]);
    }

    protected function getFeeInvoicesQuery()
    {
        $query = FeeInvoice::whereHas('user', function($q) {
                $q->where('school_id', auth()->user()->school_id);
            })
            ->with(['user', 'user.studentRecord.myClass', 'user.studentRecord.section', 'feeInvoiceRecords'])
            ->when($this->search, function($q) {
                $q->where(function($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                          ->orWhereHas('user', function($q) {
                              $q->where('name', 'like', '%' . $this->search . '%');
                          });
                });
            })
            ->when($this->yearFilter, function($q) {
                $q->whereYear('due_date', $this->yearFilter);
            });

        // Apply status filter
        if ($this->statusFilter === 'due') {
            $query->whereHas('feeInvoiceRecords', function($q) {
                $q->whereRaw('(amount + fine) > (paid + waiver)');
            });
        } elseif ($this->statusFilter === 'paid') {
            $query->whereDoesntHave('feeInvoiceRecords', function($q) {
                $q->whereRaw('(amount + fine) > (paid + waiver)');
            });
        }

        return $query->orderBy($this->sortField, $this->sortDirection);
    }

    protected function getClassForCurrentSchool($classId): ?MyClass
    {
        if (!$classId) {
            return null;
        }

        return MyClass::whereHas('classGroup', function ($query) {
            $query->where('school_id', auth()->user()->school_id);
        })->find($classId);
    }

    protected function getSectionForCurrentSchool($sectionId, $classId = null): ?Section
    {
        if (!$sectionId) {
            return null;
        }

        $query = Section::whereHas('myClass.classGroup', function ($q) {
            $q->where('school_id', auth()->user()->school_id);
        })->where('id', $sectionId);

        if ($classId) {
            $query->where('my_class_id', $classId);
        }

        return $query->first();
    }

    protected function getStudentForCurrentSchool($studentId): ?User
    {
        if (!$studentId) {
            return null;
        }

        return User::role('student')
            ->where('school_id', auth()->user()->school_id)
            ->with('studentRecord')
            ->find($studentId);
    }

    protected function getFeeCategoryForCurrentSchool($feeCategoryId): ?FeeCategory
    {
        if (!$feeCategoryId) {
            return null;
        }

        return FeeCategory::query()
            ->find($feeCategoryId);
    }

    protected function getFeeForCurrentSchool($feeId): ?Fee
    {
        if (!$feeId) {
            return null;
        }

        return Fee::whereHas('feeCategory', function ($query) {
            $query->where('school_id', auth()->user()->school_id);
        })->find($feeId);
    }

    protected function getFeeInvoiceForCurrentSchool($feeInvoiceId): FeeInvoice
    {
        return FeeInvoice::whereHas('user', function ($query) {
            $query->where('school_id', auth()->user()->school_id);
        })->with(['user', 'feeInvoiceRecords.fee'])->findOrFail($feeInvoiceId);
    }

    protected function modeIsAllowed(string $mode): bool
    {
        return match ($mode) {
            'create' => auth()->user()?->can('create fee invoice') ?? false,
            'edit' => auth()->user()?->can('update fee invoice') ?? false,
            default => true,
        };
    }

    protected function ensurePermission(string $permission): void
    {
        abort_unless(auth()->user()?->can($permission), 403);
    }

    public function render()
    {
        $feeInvoices = collect();
        
        if ($this->mode === 'list') {
            $feeInvoices = $this->getFeeInvoicesQuery()->paginate($this->perPage);
        }

        return view('livewire.fees.manage-fee-invoices', compact('feeInvoices'))
            ->layout('layouts.dashboard', [
                'breadcrumbs' => [
                    ['href' => route('dashboard'), 'text' => 'Dashboard'],
                    ['href' => route('fee-invoices.index'), 'text' => 'Fee Invoices', 'active' => true]
                ]
            ])
            ->title('Manage Fee Invoices');
    }
}
