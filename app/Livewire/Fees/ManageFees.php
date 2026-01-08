<?php

namespace App\Livewire\Fees;

use App\Models\Fee;
use App\Models\FeeCategory;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ManageFees extends Component
{
    use WithPagination, AuthorizesRequests;

    public $mode = 'list';
    
    // Filters
    public $search = '';
    public $filterCategory = '';
    
    // Sorting & Pagination
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $perPage = 15;
    
    // Form fields
    public $feeId = null;
    public $name = '';
    public $description = '';
    public $fee_category_id = '';
    
    public $feeCategories = [];

    protected $queryString = [
        'mode' => ['except' => 'list'],
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        $this->loadFeeCategories();
        
        if ($this->mode === 'edit' && $this->feeId) {
            $this->loadFeeForEdit();
        } elseif ($this->mode === 'create') {
            $this->resetForm();
        }
    }

    public function loadFeeCategories()
    {
        $this->feeCategories = FeeCategory::where('school_id', auth()->user()->school_id)
            ->orderBy('name')
            ->get();
    }

    public function switchMode($mode, $feeId = null)
    {
        $this->mode = $mode;
        $this->feeId = $feeId;
        $this->resetValidation();
        
        if ($mode === 'edit' && $feeId) {
            $this->loadFeeForEdit();
        } elseif ($mode === 'create') {
            $this->resetForm();
        }
    }

    public function loadFeeForEdit()
    {
        $fee = Fee::with('feeCategory')->findOrFail($this->feeId);
        
        // Authorization check
        if ($fee->feeCategory->school_id !== auth()->user()->school_id) {
            abort(403);
        }
        
        $this->fill([
            'name' => $fee->name,
            'description' => $fee->description ?? '',
            'fee_category_id' => $fee->fee_category_id,
        ]);
    }

    public function createFee()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'fee_category_id' => 'required|exists:fee_categories,id',
        ]);

        // Verify category belongs to school
        $category = FeeCategory::findOrFail($this->fee_category_id);
        if ($category->school_id !== auth()->user()->school_id) {
            abort(403);
        }

        DB::transaction(function () {
            Fee::create([
                'name' => $this->name,
                'description' => $this->description ?: null,
                'fee_category_id' => $this->fee_category_id,
            ]);
        });

        session()->flash('success', 'Fee created successfully');
        $this->switchMode('list');
    }

    public function updateFee()
    {
        $fee = Fee::with('feeCategory')->findOrFail($this->feeId);
        
        if ($fee->feeCategory->school_id !== auth()->user()->school_id) {
            abort(403);
        }
        
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'fee_category_id' => 'required|exists:fee_categories,id',
        ]);

        // Verify new category belongs to school
        $category = FeeCategory::findOrFail($this->fee_category_id);
        if ($category->school_id !== auth()->user()->school_id) {
            abort(403);
        }

        DB::transaction(function () use ($fee) {
            $fee->update([
                'name' => $this->name,
                'description' => $this->description ?: null,
                'fee_category_id' => $this->fee_category_id,
            ]);
        });

        session()->flash('success', 'Fee updated successfully');
        $this->switchMode('list');
    }

    public function deleteFee($feeId)
    {
        $fee = Fee::with('feeCategory')->findOrFail($feeId);
        
        if ($fee->feeCategory->school_id !== auth()->user()->school_id) {
            abort(403);
        }
        
        DB::transaction(function () use ($fee) {
            $fee->delete();
        });
        
        session()->flash('success', 'Fee deleted successfully');
    }

    public function clearFilters()
    {
        $this->reset(['search', 'filterCategory']);
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
            'feeId', 'name', 'description', 'fee_category_id'
        ]);
        
        if ($this->feeCategories->isNotEmpty()) {
            $this->fee_category_id = $this->feeCategories->first()->id;
        }
    }

    protected function getFeesQuery()
    {
        return Fee::whereHas('feeCategory', function($q) {
                $q->where('school_id', auth()->user()->school_id);
            })
            ->with('feeCategory')
            ->when($this->search, function($q) {
                $q->where(function($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                          ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterCategory, function($q) {
                $q->where('fee_category_id', $this->filterCategory);
            })
            ->orderBy($this->sortField, $this->sortDirection);
    }

    public function render()
    {
        $fees = collect();
        
        if ($this->mode === 'list') {
            $fees = $this->getFeesQuery()->paginate($this->perPage);
        }

        return view('livewire.fees.manage-fees', compact('fees'))
            ->layout('layouts.new', [
                'breadcrumbs' => [
                    ['href' => route('dashboard'), 'text' => 'Dashboard'],
                    ['href' => route('fees.index'), 'text' => 'Fees', 'active' => true]
                ]
            ])
            ->title('Manage Fees');
    }
}