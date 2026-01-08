<?php

namespace App\Livewire\Fees;

use App\Models\FeeInvoice;
use App\Models\Fee;
use App\Models\FeeCategory;
use App\Models\User;
use App\Models\MyClass;
use App\Models\Section;
use App\Models\School;
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
        $this->yearFilter = date('Y');
        
        if ($this->mode === 'create') {
            $this->loadDataForCreate();
        } elseif ($this->mode === 'edit' && $this->feeInvoiceId) {
            $this->loadFeeInvoiceForEdit();
        }
    }

    public function loadDataForCreate()
    {
        $this->classes = MyClass::whereHas('classGroup', function($q) {
            $q->where('school_id', auth()->user()->school_id);
        })->get();
        
        $this->feeCategories = FeeCategory::where('school_id', auth()->user()->school_id)->get();
        
        $this->issue_date = date('Y-m-d');
        $this->due_date = date('Y-m-d', strtotime('+30 days'));
    }

    public function updatedSelectedClass()
    {
        if ($this->selectedClass) {
            $class = MyClass::find($this->selectedClass);
            $this->sections = $class ? $class->sections : collect();
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
            $section = Section::find($this->selectedSection);
            $this->students = $section ? $section->students : collect();
        } elseif ($this->selectedClass) {
            $class = MyClass::find($this->selectedClass);
            $this->students = $class ? $class->students() : collect();
        } else {
            $this->students = collect();
        }
    }

    public function addStudent()
    {
        if ($this->selectedStudent) {
            $student = User::with('studentRecord')->find($this->selectedStudent);
            if ($student && !isset($this->selectedStudents[$student->id])) {
                $this->selectedStudents[$student->id] = [
                    'id' => $student->id,
                    'name' => $student->name,
                    'email' => $student->email,
                ];
            }
        } elseif ($this->selectedSection) {
            $section = Section::find($this->selectedSection);
            if ($section) {
                foreach ($section->students as $student) {
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
            $class = MyClass::find($this->selectedClass);
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
            $category = FeeCategory::find($this->selectedFeeCategory);
            $this->fees = $category ? $category->fees : collect();
            $this->selectedFee = '';
        }
    }

    public function addFee()
    {
        if ($this->selectedFee) {
            $fee = Fee::find($this->selectedFee);
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
            $category = FeeCategory::find($this->selectedFeeCategory);
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
        $feeInvoice = FeeInvoice::with(['user', 'feeInvoiceRecords.fee'])
            ->findOrFail($this->feeInvoiceId);
        
        if ($feeInvoice->user->school_id !== auth()->user()->school_id) {
            abort(403);
        }
        
        $this->fill([
            'issue_date' => $feeInvoice->issue_date->format('Y-m-d'),
            'due_date' => $feeInvoice->due_date->format('Y-m-d'),
            'note' => $feeInvoice->note ?? '',
        ]);
    }

    public function createFeeInvoice()
    {
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

        DB::transaction(function () {
            foreach ($this->selectedStudents as $student) {
                $invoiceName = $this->generateInvoiceNumber();
                
                $invoice = FeeInvoice::create([
                    'name' => $invoiceName,
                    'issue_date' => $this->issue_date,
                    'due_date' => $this->due_date,
                    'note' => $this->note ?: null,
                    'user_id' => $student['id'],
                ]);

                foreach ($this->selectedFees as $fee) {
                    $invoice->feeInvoiceRecords()->create([
                        'fee_id' => $fee['id'],
                        'amount' => $fee['amount'] ?? 0,
                        'waiver' => $fee['waiver'] ?? 0,
                        'fine' => $fee['fine'] ?? 0,
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
        $feeInvoice = FeeInvoice::findOrFail($this->feeInvoiceId);
        
        if ($feeInvoice->user->school_id !== auth()->user()->school_id) {
            abort(403);
        }
        
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
        $feeInvoice = FeeInvoice::findOrFail($feeInvoiceId);
        
        if ($feeInvoice->user->school_id !== auth()->user()->school_id) {
            abort(403);
        }
        
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
            ->with(['user', 'user.studentRecord.myClass', 'user.studentRecord.section'])
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

    public function render()
    {
        $feeInvoices = collect();
        
        if ($this->mode === 'list') {
            $feeInvoices = $this->getFeeInvoicesQuery()->paginate($this->perPage);
        }

        return view('livewire.fees.manage-fee-invoices', compact('feeInvoices'))
            ->layout('layouts.new', [
                'breadcrumbs' => [
                    ['href' => route('dashboard'), 'text' => 'Dashboard'],
                    ['href' => route('fee-invoices.index'), 'text' => 'Fee Invoices', 'active' => true]
                ]
            ])
            ->title('Manage Fee Invoices');
    }
}