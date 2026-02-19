<?php

namespace App\Livewire\Parents;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Carbon\Carbon;

class ManageParents extends Component
{
    use WithPagination, AuthorizesRequests, WithFileUploads;

    public $mode = 'list';
    
    // Filters
    public $search = '';
    public $selectedStatus = '';
    
    // Sorting & Pagination
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $perPage = 15;
    
    // Bulk actions
    public $selectedParents = [];
    public $selectAll = false;
    
    // Parent form
    public $parentId = null;
    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $gender = '';
    public $birthday = '';
    public $phone = '';
    public $address = '';
    public $blood_group = '';
    public $religion = '';
    public $nationality = '';
    public $state = '';
    public $city = '';
    public $profile_photo = null;

    protected $queryString = [
        'mode' => ['except' => 'list'],
        'search' => ['except' => ''],
    ];

    protected $listeners = ['refreshParents' => '$refresh'];

    public function mount()
    {
        if ($this->mode === 'edit' && $this->parentId) {
            $this->loadParentForEdit();
        } elseif ($this->mode === 'create') {
            $this->resetForm();
        }
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedParents = $this->getParentsQuery()
                ->pluck('id')
                ->toArray();
        } else {
            $this->selectedParents = [];
        }
    }

    public function switchMode($mode, $parentId = null)
    {
        $this->mode = $mode;
        $this->parentId = $parentId;
        $this->resetValidation();
        
        if ($mode === 'edit' && $parentId) {
            $this->loadParentForEdit();
        } elseif ($mode === 'create') {
            $this->resetForm();
        }
    }

    public function loadParentForEdit()
    {
        $parent = User::role('parent')
            ->where('school_id', auth()->user()->school_id)
            ->findOrFail($this->parentId);
        
        // Check if user is actually a parent
        if (!$parent->hasRole('parent')) {
            abort(404);
        }
        
        $this->fill([
            'name' => $parent->name,
            'email' => $parent->email,
            'gender' => $parent->gender ?? '',
            'birthday' => $parent->birthday ? 
                ($parent->birthday instanceof Carbon ? $parent->birthday->format('Y-m-d') : $parent->birthday) : '',
            'phone' => $parent->phone ?? '',
            'address' => $parent->address ?? '',
            'blood_group' => $parent->blood_group ?? '',
            'religion' => $parent->religion ?? '',
            'nationality' => $parent->nationality ?? '',
            'state' => $parent->state ?? '',
            'city' => $parent->city ?? '',
        ]);
    }

    public function createParent()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'gender' => 'required|in:male,female,Male,Female',
            'birthday' => 'nullable|date|before:today',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'blood_group' => 'nullable|string',
            'religion' => 'nullable|string',
            'nationality' => 'nullable|string',
            'state' => 'nullable|string',
            'city' => 'nullable|string',
        ]);

        DB::transaction(function () {
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

            $user->assignRole('parent');
        });

        session()->flash('success', 'Parent created successfully');
        $this->switchMode('list');
        $this->dispatch('refreshParents');
    }

    public function updateParent()
    {
        $parent = User::role('parent')
            ->where('school_id', auth()->user()->school_id)
            ->findOrFail($this->parentId);
        
        if (!$parent->hasRole('parent')) {
            abort(404);
        }
        
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->parentId,
            'gender' => 'required|in:male,female,Male,Female',
            'birthday' => 'nullable|date|before:today',
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|min:8|confirmed',
        ]);

        DB::transaction(function () use ($parent) {
            $parent->update([
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
                $parent->update(['password' => bcrypt($this->password)]);
            }
        });

        session()->flash('success', 'Parent updated successfully');
        $this->switchMode('list');
    }

    public function deleteParent($parentId)
    {
        $parent = User::role('parent')
            ->where('school_id', auth()->user()->school_id)
            ->findOrFail($parentId);
        
        if (!$parent->hasRole('parent')) {
            abort(404);
        }
        
        DB::transaction(function () use ($parent) {
            // Remove parent-student relationships
            $parent->children()->detach();
            $parent->delete();
        });
        
        session()->flash('success', 'Parent deleted successfully');
    }

    public function toggleLock($parentId)
    {
        $parent = User::role('parent')
            ->where('school_id', auth()->user()->school_id)
            ->findOrFail($parentId);
        
        if (!$parent->hasRole('parent')) {
            abort(404);
        }
        
        $parent->locked = !$parent->locked;
        $parent->save();
        
        session()->flash('success', $parent->locked ? 'Parent account locked' : 'Parent account unlocked');
    }

    public function applyFilters()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->reset(['search', 'selectedStatus']);
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
            'parentId', 'name', 'email', 'password', 'password_confirmation', 
            'gender', 'birthday', 'phone', 'address', 'blood_group', 
            'religion', 'nationality', 'state', 'city', 'profile_photo'
        ]);
    }

    protected function getParentsQuery()
    {
        return User::role('parent')
            ->where('school_id', auth()->user()->school_id)
            ->when($this->search, function($q) {
                $q->where(function($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                          ->orWhere('email', 'like', '%' . $this->search . '%')
                          ->orWhere('phone', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->selectedStatus !== '', fn($q) => $q->where('locked', $this->selectedStatus))
            ->orderBy($this->sortField, $this->sortDirection);
    }

    public function render()
    {
        $parents = collect();
        
        if ($this->mode === 'list') {
            $parents = $this->getParentsQuery()
                ->withCount('children')
                ->paginate($this->perPage);
        }

        return view('livewire.parents.manage-parents', compact('parents'))
            ->layout('layouts.dashboard', [
                'breadcrumbs' => [
                    ['href' => route('dashboard'), 'text' => 'Dashboard'],
                    ['href' => route('parents.index'), 'text' => 'Parents', 'active' => true]
                ]
            ])
            ->title('Manage Parents');
    }
}
