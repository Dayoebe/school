<?php

namespace App\Livewire\Syllabi;

use App\Models\MyClass;
use App\Models\Subject;
use App\Models\Syllabus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ManageSyllabi extends Component
{
    use WithFileUploads;
    use WithPagination;

    public string $mode = 'list';
    public ?int $syllabusId = null;
    public ?Syllabus $selectedSyllabus = null;

    public string $search = '';
    public int $perPage = 10;

    public $class = '';
    public $subject = '';
    public string $name = '';
    public string $description = '';
    public $file;

    public $classes;
    public $subjects;

    protected $queryString = [
        'mode' => ['except' => 'list'],
        'search' => ['except' => ''],
        'class' => ['except' => ''],
    ];

    public function mount($syllabus = null): void
    {
        $this->classes = collect();
        $this->subjects = collect();

        $this->hydrateModeFromRoute($syllabus);
        $this->loadClasses();

        if ($this->mode === 'create' && !$this->modeIsAllowed('create')) {
            $this->mode = 'list';
        }

        if ($this->mode === 'show') {
            if (!$this->modeIsAllowed('show') || !$this->syllabusId) {
                $this->mode = 'list';
                $this->syllabusId = null;
            } else {
                $this->loadSyllabusForShow();
            }
        }
    }

    protected function hydrateModeFromRoute($syllabus = null): void
    {
        $routeName = request()->route()?->getName();

        if ($routeName === 'syllabi.create') {
            $this->mode = 'create';
            $this->syllabusId = null;
            return;
        }

        if ($routeName === 'syllabi.show') {
            $this->mode = 'show';
            $this->syllabusId = $this->resolveRouteModelId($syllabus ?? request()->route('syllabus'));
            return;
        }

        $this->mode = 'list';
        $this->syllabusId = null;
    }

    protected function resolveRouteModelId(mixed $value): ?int
    {
        if ($value instanceof Model) {
            return (int) $value->getKey();
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }

    protected function loadClasses(): void
    {
        $user = auth()->user();
        $studentClassId = $user->studentRecord?->myClass?->id;

        if ($user->hasRole('student')) {
            $this->class = $studentClassId ?: '';
            $this->loadSubjectsForClass();
            return;
        }

        $this->classes = $user->school
            ->myClasses()
            ->orderBy('my_classes.name')
            ->get(['my_classes.id', 'my_classes.name']);

        if ($this->classes->isNotEmpty() && !$this->classes->contains('id', (int) $this->class)) {
            $this->class = (string) $this->classes->first()->id;
        }

        $this->loadSubjectsForClass();
    }

    public function updatedClass(): void
    {
        if (auth()->user()->hasRole('student')) {
            $this->class = (string) (auth()->user()->studentRecord?->myClass?->id ?: '');
        } elseif ($this->class !== '' && !$this->classBelongsToCurrentSchool((int) $this->class)) {
            $this->class = (string) ($this->classes->first()?->id ?: '');
        }

        $this->loadSubjectsForClass();
        $this->resetPage();
    }

    protected function loadSubjectsForClass(): void
    {
        $classId = $this->class !== '' ? (int) $this->class : null;

        if (!$classId) {
            $this->subjects = collect();
            $this->subject = '';
            return;
        }

        $this->subjects = Subject::query()
            ->where('school_id', auth()->user()->school_id)
            ->where('is_legacy', false)
            ->where(function (Builder $query) use ($classId) {
                $query->where('my_class_id', $classId)
                    ->orWhereHas('classes', function (Builder $classQuery) use ($classId) {
                        $classQuery->where('my_classes.id', $classId);
                    });
            })
            ->orderBy('name')
            ->get()
            ->unique('id')
            ->values();

        if (!$this->subjects->contains('id', (int) $this->subject)) {
            $this->subject = (string) ($this->subjects->first()?->id ?: '');
        }
    }

    public function switchMode(string $mode, ?int $syllabusId = null)
    {
        if (!$this->modeIsAllowed($mode)) {
            return;
        }

        $this->mode = $mode;
        $this->syllabusId = $syllabusId;
        $this->resetValidation();

        if ($mode === 'create') {
            $this->resetCreateForm();
            $this->loadSubjectsForClass();
            return;
        }

        if ($mode === 'show' && $syllabusId) {
            $this->loadSyllabusForShow();
            return;
        }

        $this->selectedSyllabus = null;
    }

    protected function resetCreateForm(): void
    {
        $this->reset([
            'subject',
            'name',
            'description',
            'file',
        ]);
    }

    public function createSyllabus()
    {
        $this->ensurePermission('create syllabus');

        $semesterId = auth()->user()->school?->semester_id;
        if (!$semesterId) {
            $this->addError('semester', 'Set an active semester before creating a syllabus.');
            return;
        }

        $this->validate([
            'class' => 'required|integer',
            'subject' => 'required|integer',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'file' => 'required|file|mimes:pdf|max:10240',
        ]);

        $classId = (int) $this->class;
        if (!$this->classBelongsToCurrentSchool($classId)) {
            $this->addError('class', 'Selected class is not in your current school.');
            return;
        }

        $subject = $this->getSubjectForCurrentSchool((int) $this->subject);
        if (!$subject || !$this->subjectMatchesClass($subject, $classId)) {
            $this->addError('subject', 'Selected subject is not valid for the selected class.');
            return;
        }

        $filePath = $this->file->store('syllabi', 'public');

        Syllabus::create([
            'name' => $this->name,
            'description' => $this->description ?: null,
            'file' => $filePath,
            'subject_id' => $subject->id,
            'semester_id' => $semesterId,
        ]);

        session()->flash('success', 'Syllabus created successfully.');

        return $this->redirectRoute('syllabi.index', navigate: true);
    }

    public function loadSyllabusForShow(): void
    {
        $this->selectedSyllabus = $this->getSyllabusForCurrentSchool($this->syllabusId);
    }

    public function deleteSyllabus(int $syllabusId)
    {
        $this->ensurePermission('delete syllabus');

        $syllabus = $this->getSyllabusForCurrentSchool($syllabusId);
        Storage::disk('public')->delete($syllabus->file);
        $syllabus->delete();

        session()->flash('success', 'Syllabus deleted successfully.');

        if ($this->mode === 'show' && $this->syllabusId === $syllabusId) {
            return $this->redirectRoute('syllabi.index', navigate: true);
        }
    }

    protected function getSyllabiQuery(): Builder
    {
        return Syllabus::query()
            ->with('subject')
            ->forSchool(auth()->user()->school_id)
            ->forSemester(auth()->user()->school?->semester_id)
            ->forClass($this->class !== '' ? (int) $this->class : null)
            ->when($this->search, function (Builder $query) {
                $query->where(function (Builder $searchQuery) {
                    $searchValue = '%'.$this->search.'%';
                    $searchQuery->where('name', 'like', $searchValue)
                        ->orWhere('description', 'like', $searchValue)
                        ->orWhereHas('subject', function (Builder $subjectQuery) use ($searchValue) {
                            $subjectQuery->where('name', 'like', $searchValue);
                        });
                });
            })
            ->latest();
    }

    protected function classBelongsToCurrentSchool(int $classId): bool
    {
        return MyClass::query()
            ->whereKey($classId)
            ->whereHas('classGroup', function (Builder $query) {
                $query->where('school_id', auth()->user()->school_id);
            })
            ->exists();
    }

    protected function getSubjectForCurrentSchool(int $subjectId): ?Subject
    {
        return Subject::query()
            ->where('school_id', auth()->user()->school_id)
            ->whereKey($subjectId)
            ->first();
    }

    protected function subjectMatchesClass(Subject $subject, int $classId): bool
    {
        if ((int) $subject->my_class_id === $classId) {
            return true;
        }

        return $subject->classes()->where('my_classes.id', $classId)->exists();
    }

    protected function getSyllabusForCurrentSchool(?int $syllabusId): Syllabus
    {
        return Syllabus::query()
            ->with('subject')
            ->forSchool(auth()->user()->school_id)
            ->findOrFail($syllabusId);
    }

    protected function modeIsAllowed(string $mode): bool
    {
        return match ($mode) {
            'create' => auth()->user()?->can('create syllabus') ?? false,
            'show' => auth()->user()?->can('read syllabus') ?? false,
            default => auth()->user()?->can('read syllabus') ?? false,
        };
    }

    protected function ensurePermission(string $permission): void
    {
        abort_unless(auth()->user()?->can($permission), 403);
    }

    public function render()
    {
        $syllabi = collect();

        if ($this->mode === 'list') {
            $syllabi = $this->getSyllabiQuery()->paginate($this->perPage);
        }

        $breadcrumbs = [
            ['href' => route('dashboard'), 'text' => 'Dashboard'],
            ['href' => route('syllabi.index'), 'text' => 'Syllabi'],
        ];

        if ($this->mode === 'create') {
            $breadcrumbs[] = ['href' => route('syllabi.create'), 'text' => 'Create', 'active' => true];
        } elseif ($this->mode === 'show' && $this->selectedSyllabus) {
            $breadcrumbs[] = ['href' => route('syllabi.show', $this->selectedSyllabus->id), 'text' => $this->selectedSyllabus->name, 'active' => true];
        } else {
            $breadcrumbs[1]['active'] = true;
        }

        return view('livewire.syllabi.manage-syllabi', [
            'syllabi' => $syllabi,
        ])
            ->layout('layouts.dashboard', ['breadcrumbs' => $breadcrumbs])
            ->title('Syllabi');
    }
}
