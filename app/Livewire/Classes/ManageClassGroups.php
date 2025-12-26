<?php

namespace App\Livewire\Classes;

use App\Models\ClassGroup;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class ManageClassGroups extends Component
{
    use WithPagination, AuthorizesRequests;

    public $view = 'list'; // list, create, edit, view
    public $name = '';
    public $search = '';
    public $selectedClassGroup = null;

    protected function rules()
    {
        $rules = [
            'name' => [
                'required',
                'max:255',
                function ($attribute, $value, $fail) {
                    $query = ClassGroup::where('school_id', auth()->user()->school_id)
                        ->where('name', $value);
                    
                    if ($this->view === 'edit' && $this->selectedClassGroup) {
                        $query->where('id', '!=', $this->selectedClassGroup->id);
                    }
                    
                    if ($query->exists()) {
                        $fail('This class group name already exists.');
                    }
                },
            ]
        ];
        
        return $rules;
    }

    public function mount()
    {
        $action = request()->query('action');
        if ($action === 'create') {
            $this->showCreate();
        }
    }

    public function showList()
    {
        $this->reset(['view', 'name', 'selectedClassGroup']);
        $this->view = 'list';
    }

    public function showCreate()
    {
        $this->authorize('create', ClassGroup::class);
        $this->reset(['name']);
        $this->view = 'create';
    }

    public function showEdit($id)
    {
        $this->selectedClassGroup = ClassGroup::findOrFail($id);
        $this->authorize('update', $this->selectedClassGroup);
        $this->name = $this->selectedClassGroup->name;
        $this->view = 'edit';
    }

    public function showView($id)
    {
        $this->selectedClassGroup = ClassGroup::with('classes.sections')->findOrFail($id);
        $this->authorize('view', $this->selectedClassGroup);
        $this->view = 'view';
    }

    public function create()
    {
        $this->authorize('create', ClassGroup::class);
        $this->validate();

        ClassGroup::create([
            'name' => $this->name,
            'school_id' => auth()->user()->school_id,
        ]);

        session()->flash('success', 'Class Group created successfully!');
        $this->showList();
    }

    public function update()
    {
        $this->authorize('update', $this->selectedClassGroup);
        $this->validate();

        $this->selectedClassGroup->update(['name' => $this->name]);

        session()->flash('success', 'Class Group updated successfully!');
        $this->showList();
    }

    public function delete($id)
    {
        $classGroup = ClassGroup::findOrFail($id);
        $this->authorize('delete', $classGroup);

        if ($classGroup->classes()->count() > 0) {
            session()->flash('error', 'Cannot delete class group that contains classes.');
            return;
        }

        $classGroup->delete();
        session()->flash('success', 'Class Group deleted successfully!');
    }

    public function render()
    {
        $classGroups = ClassGroup::where('school_id', auth()->user()->school_id)
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->withCount('classes')
            ->paginate(12);

        return view('livewire.classes.manage-class-groups', [
            'classGroups' => $classGroups,
        ])->layout('layouts.new');
    }
}