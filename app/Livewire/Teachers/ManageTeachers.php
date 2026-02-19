<?php

namespace App\Livewire\Teachers;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Carbon\Carbon;

class ManageTeachers extends Component
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
    
    // Teacher form
    public $teacherId = null;
    public $name = '';
    public $email = '';
    public $password = '';
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
        if ($this->mode === 'edit' && $this->teacherId) {
            $this->loadTeacherForEdit();
        } elseif ($this->mode === 'create') {
            $this->resetForm();
        }
    }

    public function switchMode($mode, $teacherId = null)
    {
        $this->mode = $mode;
        $this->teacherId = $teacherId;
        $this->resetValidation();
        
        if ($mode === 'edit' && $teacherId) {
            $this->loadTeacherForEdit();
        } elseif ($mode === 'create') {
            $this->resetForm();
        }
    }

    public function loadTeacherForEdit()
    {
        $teacher = User::role('teacher')
            ->where('school_id', auth()->user()->school_id)
            ->findOrFail($this->teacherId);

        $this->authorize('update', [$teacher, 'teacher']);
        
        $this->fill([
            'name' => $teacher->name,
            'email' => $teacher->email,
            'gender' => $teacher->gender ?? '',
            'birthday' => $teacher->birthday ? 
                ($teacher->birthday instanceof Carbon ? $teacher->birthday->format('Y-m-d') : $teacher->birthday) : '',
            'phone' => $teacher->phone ?? '',
            'address' => $teacher->address ?? '',
            'blood_group' => $teacher->blood_group ?? '',
            'religion' => $teacher->religion ?? '',
            'nationality' => $teacher->nationality ?? '',
            'state' => $teacher->state ?? '',
            'city' => $teacher->city ?? '',
        ]);
    }

    public function createTeacher()
    {
        $this->authorize('create', [User::class, 'teacher']);
        
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'gender' => 'required|in:male,female',
            'birthday' => 'nullable|date|before:today',
            'phone' => 'nullable|string|max:20',
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

            $user->assignRole('teacher');
        });

        session()->flash('success', 'Teacher created successfully');
        $this->switchMode('list');
    }

    public function updateTeacher()
    {
        $teacher = User::role('teacher')
            ->where('school_id', auth()->user()->school_id)
            ->findOrFail($this->teacherId);

        $this->authorize('update', [$teacher, 'teacher']);
        
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->teacherId,
            'gender' => 'required|in:male,female',
            'birthday' => 'nullable|date|before:today',
            'phone' => 'nullable|string|max:20',
        ]);

        DB::transaction(function () use ($teacher) {
            $teacher->update([
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
                $teacher->update(['password' => bcrypt($this->password)]);
            }
        });

        session()->flash('success', 'Teacher updated successfully');
        $this->switchMode('list');
    }

    public function deleteTeacher($teacherId)
    {
        $teacher = User::role('teacher')
            ->where('school_id', auth()->user()->school_id)
            ->findOrFail($teacherId);

        $this->authorize('delete', [$teacher, 'teacher']);
        
        DB::transaction(function () use ($teacher) {
            $teacher->subjects()->detach();
            $teacher->delete();
        });
        
        session()->flash('success', 'Teacher deleted successfully');
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
            'teacherId', 'name', 'email', 'password', 'gender', 'birthday',
            'phone', 'address', 'blood_group', 'religion', 'nationality',
            'state', 'city'
        ]);
    }

    protected function getTeachersQuery()
    {
        return User::role('teacher')
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
        $teachers = collect();
        
        if ($this->mode === 'list') {
            $teachers = $this->getTeachersQuery()
                ->withCount('subjects')
                ->paginate($this->perPage);
        }

        return view('livewire.teachers.manage-teachers', compact('teachers'))
            ->layout('layouts.dashboard', [
                'breadcrumbs' => [
                    ['href' => route('dashboard'), 'text' => 'Dashboard'],
                    ['href' => route('teachers.index'), 'text' => 'Teachers', 'active' => true]
                ]
            ])
            ->title('Manage Teachers');
    }
}
