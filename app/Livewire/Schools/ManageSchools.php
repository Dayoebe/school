<?php

namespace App\Livewire\Schools;

use App\Models\School;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ManageSchools extends Component
{
    use WithPagination, WithFileUploads, AuthorizesRequests;

    public $mode = 'list'; // list, create, edit
    public $search = '';
    public $perPage = 15;

    // Form fields
    public $schoolId = null;
    public $name = '';
    public $address = '';
    public $initials = '';
    public $phone = '';
    public $email = '';
    public $logo = null;
    public $existingLogoPath = '';

    // Current school selection
    public $selectedSchoolId = '';

    protected $queryString = [
        'mode' => ['except' => 'list'],
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        $this->selectedSchoolId = auth()->user()->school_id;
        
        if ($this->mode === 'edit' && $this->schoolId) {
            $this->loadSchoolForEdit();
        }
    }

    public function switchMode($mode, $schoolId = null)
    {
        $this->mode = $mode;
        $this->schoolId = $schoolId;
        $this->resetValidation();
        
        if ($mode === 'edit' && $schoolId) {
            $this->loadSchoolForEdit();
        } elseif ($mode === 'create') {
            $this->resetForm();
        }
    }

    public function loadSchoolForEdit()
    {
        $school = School::findOrFail($this->schoolId);
        $this->authorize('update', $school);
        
        $this->fill([
            'name' => $school->name,
            'address' => $school->address,
            'initials' => $school->initials ?? '',
            'phone' => $school->phone ?? '',
            'email' => $school->email ?? '',
            'existingLogoPath' => $school->logo_path ?? '',
        ]);
    }

    public function createSchool()
    {
        $this->authorize('create', School::class);
        
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'initials' => 'nullable|string|max:10',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'logo' => 'nullable|image|max:2048',
        ]);

        $data = [
            'name' => $this->name,
            'address' => $this->address,
            'initials' => $this->initials,
            'phone' => $this->phone,
            'email' => $this->email,
            'code' => $this->generateSchoolCode(),
        ];

        if ($this->logo) {
            $data['logo_path'] = $this->logo->store('schools', 'public');
        }

        School::create($data);

        session()->flash('success', 'School created successfully');
        $this->switchMode('list');
    }

    public function updateSchool()
    {
        $school = School::findOrFail($this->schoolId);
        $this->authorize('update', $school);
        
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'initials' => 'nullable|string|max:10',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'logo' => 'nullable|image|max:2048',
        ]);

        $data = [
            'name' => $this->name,
            'address' => $this->address,
            'initials' => $this->initials,
            'phone' => $this->phone,
            'email' => $this->email,
        ];

        if ($this->logo) {
            // Delete old logo if exists
            if ($school->logo_path) {
                Storage::disk('public')->delete($school->logo_path);
            }
            $data['logo_path'] = $this->logo->store('schools', 'public');
        }

        $school->update($data);

        session()->flash('success', 'School updated successfully');
        $this->switchMode('list');
    }

    public function deleteSchool($schoolId)
    {
        $school = School::findOrFail($schoolId);
        $this->authorize('delete', $school);
        
        if ($school->users()->count() > 0) {
            session()->flash('error', 'Cannot delete school with associated users. Remove all users first.');
            return;
        }

        if ($school->logo_path) {
            Storage::disk('public')->delete($school->logo_path);
        }

        $school->delete();
        session()->flash('success', 'School deleted successfully');
    }

    public function setSchool()
    {
        $this->authorize('setSchool', School::class);
        
        $this->validate([
            'selectedSchoolId' => 'required|exists:schools,id',
        ]);

        $school = School::findOrFail($this->selectedSchoolId);
        
        auth()->user()->update(['school_id' => $school->id]);

        session()->flash('success', 'School set successfully');
        $this->dispatch('school-changed');
    }

    public function resetForm()
    {
        $this->reset([
            'schoolId', 'name', 'address', 'initials', 'phone', 
            'email', 'logo', 'existingLogoPath'
        ]);
    }

    protected function generateSchoolCode()
    {
        do {
            $code = Str::random(10);
        } while (School::where('code', $code)->exists());

        return $code;
    }

    public function render()
    {
        $schools = collect();
        $allSchools = School::orderBy('name')->get();
        
        if ($this->mode === 'list') {
            $schools = School::query()
                ->when($this->search, function($q) {
                    $q->where(function($query) {
                        $query->where('name', 'like', '%' . $this->search . '%')
                              ->orWhere('address', 'like', '%' . $this->search . '%')
                              ->orWhere('code', 'like', '%' . $this->search . '%');
                    });
                })
                ->orderBy('name')
                ->paginate($this->perPage);
        }

        return view('livewire.schools.manage-schools', compact('schools', 'allSchools'))
            ->layout('layouts.new', [
                'breadcrumbs' => [
                    ['href' => route('dashboard'), 'text' => 'Dashboard'],
                    ['href' => route('schools.index'), 'text' => 'Schools', 'active' => true]
                ]
            ])
            ->title('Manage Schools');
    }
}