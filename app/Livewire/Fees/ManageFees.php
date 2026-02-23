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
        $this->hydrateModeFromRoute();

        $this->loadFeeCategories();
        
        if ($this->mode === 'edit' && $this->feeId) {
            if ($this->modeIsAllowed('edit')) {
                $this->loadFeeForEdit();
            } else {
                $this->mode = 'list';
                $this->feeId = null;
            }
        } elseif ($this->mode === 'edit') {
            $this->mode = 'list';
            $this->feeId = null;
        } elseif ($this->mode === 'create') {
            if ($this->modeIsAllowed('create')) {
                $this->resetForm();
            } else {
                $this->mode = 'list';
            }
        }
    }

    protected function hydrateModeFromRoute(): void
    {
        $routeName = request()->route()?->getName();

        if ($routeName === 'fees.create') {
            $this->mode = 'create';
            $this->feeId = null;
            return;
        }

        if ($routeName === 'fees.edit') {
            $this->mode = 'edit';
            $this->feeId = $this->resolveRouteModelId(request()->route('fee'));
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

    public function loadFeeCategories()
    {
        $this->feeCategories = FeeCategory::query()
            ->orderBy('name')
            ->get();
    }

    public function switchMode($mode, $feeId = null)
    {
        if (!$this->modeIsAllowed($mode)) {
            $this->mode = 'list';
            $this->feeId = null;
            return;
        }

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
        $this->ensurePermission('update fee');

        $fee = $this->getFeeForCurrentSchool($this->feeId);
        
        $this->fill([
            'name' => $fee->name,
            'description' => $fee->description ?? '',
            'fee_category_id' => $fee->fee_category_id,
        ]);
    }

    public function createFee()
    {
        $this->ensurePermission('create fee');

        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'fee_category_id' => 'required|exists:fee_categories,id',
        ]);

        // Verify category belongs to school
        $this->getFeeCategoryForCurrentSchool($this->fee_category_id);

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
        $this->ensurePermission('update fee');

        $fee = $this->getFeeForCurrentSchool($this->feeId);
        
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'fee_category_id' => 'required|exists:fee_categories,id',
        ]);

        // Verify new category belongs to school
        $this->getFeeCategoryForCurrentSchool($this->fee_category_id);

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
        $this->ensurePermission('delete fee');

        $fee = $this->getFeeForCurrentSchool($feeId);
        
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
                $q->whereHas('feeCategory', function ($query) {
                    $query->where('id', $this->filterCategory)
                        ->where('school_id', auth()->user()->school_id);
                });
            })
            ->orderBy($this->sortField, $this->sortDirection);
    }

    protected function getFeeCategoryForCurrentSchool($feeCategoryId): FeeCategory
    {
        return FeeCategory::query()
            ->findOrFail($feeCategoryId);
    }

    protected function getFeeForCurrentSchool($feeId): Fee
    {
        return Fee::whereHas('feeCategory', function ($query) {
            $query->where('school_id', auth()->user()->school_id);
        })->with('feeCategory')->findOrFail($feeId);
    }

    protected function modeIsAllowed(string $mode): bool
    {
        return match ($mode) {
            'create' => auth()->user()?->can('create fee') ?? false,
            'edit' => auth()->user()?->can('update fee') ?? false,
            default => true,
        };
    }

    protected function ensurePermission(string $permission): void
    {
        abort_unless(auth()->user()?->can($permission), 403);
    }

    public function render()
    {
        $fees = collect();
        
        if ($this->mode === 'list') {
            $fees = $this->getFeesQuery()->paginate($this->perPage);
        }

        return view('livewire.fees.manage-fees', compact('fees'))
            ->layout('layouts.dashboard', [
                'breadcrumbs' => [
                    ['href' => route('dashboard'), 'text' => 'Dashboard'],
                    ['href' => route('fees.index'), 'text' => 'Fees', 'active' => true]
                ]
            ])
            ->title('Manage Fees');
    }
}
