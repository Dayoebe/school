<?php

namespace App\Livewire\Fees;

use App\Models\FeeCategory;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ManageFeeCategories extends Component
{
    use WithPagination, AuthorizesRequests;

    public $mode = 'list';
    
    // Filters
    public $search = '';
    
    // Sorting & Pagination
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $perPage = 15;
    
    // Form fields
    public $feeCategoryId = null;
    public $name = '';
    public $description = '';

    protected $queryString = [
        'mode' => ['except' => 'list'],
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        $this->hydrateModeFromRoute();

        if ($this->mode === 'edit' && $this->feeCategoryId) {
            if ($this->modeIsAllowed('edit')) {
                $this->loadFeeCategoryForEdit();
            } else {
                $this->mode = 'list';
                $this->feeCategoryId = null;
            }
        } elseif ($this->mode === 'edit') {
            $this->mode = 'list';
            $this->feeCategoryId = null;
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

        if ($routeName === 'fee-categories.create') {
            $this->mode = 'create';
            $this->feeCategoryId = null;
            return;
        }

        if ($routeName === 'fee-categories.edit') {
            $this->mode = 'edit';
            $this->feeCategoryId = $this->resolveRouteModelId(request()->route('feeCategory'));
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

    public function switchMode($mode, $feeCategoryId = null)
    {
        if (!$this->modeIsAllowed($mode)) {
            $this->mode = 'list';
            $this->feeCategoryId = null;
            return;
        }

        $this->mode = $mode;
        $this->feeCategoryId = $feeCategoryId;
        $this->resetValidation();
        
        if ($mode === 'edit' && $feeCategoryId) {
            $this->loadFeeCategoryForEdit();
        } elseif ($mode === 'create') {
            $this->resetForm();
        }
    }

    public function loadFeeCategoryForEdit()
    {
        $this->ensurePermission('update fee category');

        $feeCategory = $this->getFeeCategoryForCurrentSchool($this->feeCategoryId);
        
        $this->fill([
            'name' => $feeCategory->name,
            'description' => $feeCategory->description ?? '',
        ]);
    }

    public function createFeeCategory()
    {
        $this->ensurePermission('create fee category');

        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () {
            FeeCategory::create([
                'name' => $this->name,
                'description' => $this->description ?: null,
                'school_id' => auth()->user()->school_id,
            ]);
        });

        session()->flash('success', 'Fee Category created successfully');
        $this->switchMode('list');
    }

    public function updateFeeCategory()
    {
        $this->ensurePermission('update fee category');

        $feeCategory = $this->getFeeCategoryForCurrentSchool($this->feeCategoryId);
        
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($feeCategory) {
            $feeCategory->update([
                'name' => $this->name,
                'description' => $this->description ?: null,
            ]);
        });

        session()->flash('success', 'Fee Category updated successfully');
        $this->switchMode('list');
    }

    public function deleteFeeCategory($feeCategoryId)
    {
        $this->ensurePermission('delete fee category');

        $feeCategory = $this->getFeeCategoryForCurrentSchool($feeCategoryId);
        
        DB::transaction(function () use ($feeCategory) {
            // Check if category has fees
            if ($feeCategory->fees()->count() > 0) {
                session()->flash('error', 'Cannot delete fee category with associated fees');
                return;
            }
            $feeCategory->delete();
        });

        if (!$feeCategory->exists) {
            session()->flash('success', 'Fee Category deleted successfully');
        }
    }

    public function clearFilters()
    {
        $this->reset(['search']);
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
            'feeCategoryId', 'name', 'description'
        ]);
    }

    protected function getFeeCategoryForCurrentSchool($feeCategoryId): FeeCategory
    {
        return FeeCategory::query()
            ->findOrFail($feeCategoryId);
    }

    protected function getFeeCategoriesQuery()
    {
        return FeeCategory::query()
            ->when($this->search, function($q) {
                $q->where(function($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                          ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection);
    }

    protected function modeIsAllowed(string $mode): bool
    {
        return match ($mode) {
            'create' => auth()->user()?->can('create fee category') ?? false,
            'edit' => auth()->user()?->can('update fee category') ?? false,
            default => true,
        };
    }

    protected function ensurePermission(string $permission): void
    {
        abort_unless(auth()->user()?->can($permission), 403);
    }

    public function render()
    {
        $feeCategories = collect();
        
        if ($this->mode === 'list') {
            $feeCategories = $this->getFeeCategoriesQuery()
                ->withCount('fees')
                ->paginate($this->perPage);
        }

        return view('livewire.fees.manage-fee-categories', compact('feeCategories'))
            ->layout('layouts.dashboard', [
                'breadcrumbs' => [
                    ['href' => route('dashboard'), 'text' => 'Dashboard'],
                    ['href' => route('fee-categories.index'), 'text' => 'Fee Categories', 'active' => true]
                ]
            ])
            ->title('Manage Fee Categories');
    }
}
