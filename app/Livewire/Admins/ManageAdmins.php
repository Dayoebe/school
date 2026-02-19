<?php

namespace App\Livewire\Admins;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Carbon\Carbon;

class ManageAdmins extends Component
{
    use WithPagination, AuthorizesRequests, WithFileUploads;

    public $mode = 'list'; // list, create, edit
    
    // Filters
    public $search = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $perPage = 15;
    
    // Form fields
    public $adminId = null;
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

    public function mount()
    {
        if ($this->mode === 'edit' && $this->adminId) {
            $this->loadAdminForEdit();
        } elseif ($this->mode === 'create') {
            $this->resetForm();
        }
    }

    public function switchMode($mode, $adminId = null)
    {
        $this->mode = $mode;
        $this->adminId = $adminId;
        $this->resetValidation();
        
        if ($mode === 'edit' && $adminId) {
            $this->loadAdminForEdit();
        } elseif ($mode === 'create') {
            $this->resetForm();
        }
    }

    public function loadAdminForEdit()
    {
        $admin = User::role('admin')
            ->where('school_id', auth()->user()->school_id)
            ->findOrFail($this->adminId);

        $this->authorize('update', [$admin, 'admin']);
        
        $this->fill([
            'name' => $admin->name,
            'email' => $admin->email,
            'gender' => $admin->gender ?? '',
            'birthday' => $admin->birthday ? 
                ($admin->birthday instanceof Carbon ? $admin->birthday->format('Y-m-d') : $admin->birthday) : '',
            'phone' => $admin->phone ?? '',
            'address' => $admin->address ?? '',
            'blood_group' => $admin->blood_group ?? '',
            'religion' => $admin->religion ?? '',
            'nationality' => $admin->nationality ?? '',
            'state' => $admin->state ?? '',
            'city' => $admin->city ?? '',
        ]);
    }

    public function createAdmin()
    {
        $this->authorize('create', [User::class, 'admin']);
        
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'gender' => 'required|in:male,female',
            'birthday' => 'nullable|date|before:today',
            'phone' => 'nullable|string|max:20',
            'profile_photo' => 'nullable|image|max:2048',
        ]);

        DB::transaction(function () {
            $data = [
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
            ];

            if ($this->profile_photo) {
                $data['profile_photo_path'] = $this->profile_photo->store('profile-photos', 'public');
            }

            $user = User::create($data);
            $user->assignRole('admin');
        });

        session()->flash('success', 'Admin created successfully');
        $this->switchMode('list');
    }

    public function updateAdmin()
    {
        $admin = User::role('admin')
            ->where('school_id', auth()->user()->school_id)
            ->findOrFail($this->adminId);

        $this->authorize('update', [$admin, 'admin']);
        
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->adminId,
            'password' => 'nullable|min:8|confirmed',
            'gender' => 'required|in:male,female',
            'birthday' => 'nullable|date|before:today',
            'phone' => 'nullable|string|max:20',
            'profile_photo' => 'nullable|image|max:2048',
        ]);

        DB::transaction(function () use ($admin) {
            $data = [
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
            ];

            if ($this->password) {
                $data['password'] = bcrypt($this->password);
            }

            if ($this->profile_photo) {
                $data['profile_photo_path'] = $this->profile_photo->store('profile-photos', 'public');
            }

            $admin->update($data);
        });

        session()->flash('success', 'Admin updated successfully');
        $this->switchMode('list');
    }

    public function deleteAdmin($adminId)
    {
        $admin = User::role('admin')
            ->where('school_id', auth()->user()->school_id)
            ->findOrFail($adminId);

        $this->authorize('delete', [$admin, 'admin']);
        
        $admin->delete();
        session()->flash('success', 'Admin deleted successfully');
    }

    public function toggleLock($adminId)
    {
        $admin = User::role('admin')
            ->where('school_id', auth()->user()->school_id)
            ->findOrFail($adminId);
        
        if (auth()->user()->can('lock user')) {
            $admin->update(['locked' => !$admin->locked]);
            session()->flash('success', $admin->locked ? 'Admin account locked' : 'Admin account unlocked');
        }
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
            'adminId', 'name', 'email', 'password', 'password_confirmation',
            'gender', 'birthday', 'phone', 'address', 'blood_group',
            'religion', 'nationality', 'state', 'city', 'profile_photo'
        ]);
    }

    public function render()
    {
        $admins = collect();
        
        if ($this->mode === 'list') {
            $admins = User::role('admin')
                ->inSchool()
                ->when($this->search, function($q) {
                    $q->where(function($query) {
                        $query->where('name', 'like', '%' . $this->search . '%')
                              ->orWhere('email', 'like', '%' . $this->search . '%');
                    });
                })
                ->orderBy($this->sortField, $this->sortDirection)
                ->paginate($this->perPage);
        }

        return view('livewire.admins.manage-admins', compact('admins'))
            ->layout('layouts.dashboard', [
                'breadcrumbs' => [
                    ['href' => route('dashboard'), 'text' => 'Dashboard'],
                    ['href' => route('admins.index'), 'text' => 'Admins', 'active' => true]
                ]
            ])
            ->title('Manage Admins');
    }
}
