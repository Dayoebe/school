<?php

namespace App\Livewire\Discipline;

use App\Models\DisciplineIncident;
use App\Models\MyClass;
use App\Models\Section;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class ManageDisciplineIncidents extends Component
{
    use WithPagination;

    public string $search = '';

    public string $severityFilter = 'all';

    public string $filterClassId = '';

    public string $filterSectionId = '';

    public int $perPage = 12;

    public string $formClassId = '';

    public string $formSectionId = '';

    public string $studentRecordId = '';

    public string $incidentDate = '';

    public string $category = '';

    public string $severity = 'medium';

    public string $description = '';

    public string $actionTaken = '';

    public bool $parentVisible = true;

    /** @var array<int, array<string, mixed>> */
    public array $classes = [];

    /** @var array<int, array<string, mixed>> */
    public array $formSections = [];

    /** @var array<int, array<string, mixed>> */
    public array $filterSections = [];

    /** @var array<int, array<string, mixed>> */
    public array $formStudents = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'severityFilter' => ['except' => 'all'],
    ];

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('read discipline incident'), 403);

        $this->incidentDate = now()->toDateString();

        $this->loadClasses();
        $this->loadFormSections();
        $this->loadFormStudents();
        $this->loadFilterSections();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSeverityFilter(): void
    {
        $this->resetPage();
    }

    public function updatedFilterClassId(): void
    {
        $this->filterSectionId = '';
        $this->loadFilterSections();
        $this->resetPage();
    }

    public function updatedFilterSectionId(): void
    {
        $this->resetPage();
    }

    public function updatedFormClassId(): void
    {
        $this->formSectionId = '';
        $this->studentRecordId = '';
        $this->loadFormSections();
        $this->loadFormStudents();
    }

    public function updatedFormSectionId(): void
    {
        $this->studentRecordId = '';
        $this->loadFormStudents();
    }

    public function createIncident(): void
    {
        abort_unless(auth()->user()?->can('create discipline incident'), 403);

        $validated = $this->validate([
            'studentRecordId' => ['required', 'integer'],
            'incidentDate' => ['required', 'date'],
            'category' => ['required', 'string', 'max:120'],
            'severity' => ['required', 'in:low,medium,high,critical'],
            'description' => ['required', 'string', 'min:3', 'max:5000'],
            'actionTaken' => ['nullable', 'string', 'max:5000'],
            'parentVisible' => ['boolean'],
        ]);

        $studentExists = DB::table('student_records as sr')
            ->join('users as u', 'u.id', '=', 'sr.user_id')
            ->where('sr.id', (int) $validated['studentRecordId'])
            ->where('u.school_id', auth()->user()?->school_id)
            ->whereNull('u.deleted_at')
            ->exists();

        if (!$studentExists) {
            $this->addError('studentRecordId', 'Select a valid student.');
            return;
        }

        DisciplineIncident::query()->create([
            'student_record_id' => (int) $validated['studentRecordId'],
            'incident_date' => $validated['incidentDate'],
            'category' => trim($validated['category']),
            'severity' => $validated['severity'],
            'description' => trim($validated['description']),
            'action_taken' => trim((string) ($validated['actionTaken'] ?? '')) ?: null,
            'parent_visible' => (bool) $validated['parentVisible'],
            'reported_by' => auth()->id(),
        ]);

        $this->reset([
            'studentRecordId',
            'category',
            'description',
            'actionTaken',
        ]);
        $this->severity = 'medium';
        $this->parentVisible = true;
        $this->incidentDate = now()->toDateString();

        session()->flash('success', 'Discipline incident logged successfully.');
    }

    public function markResolved(int $incidentId): void
    {
        abort_unless(auth()->user()?->can('update discipline incident'), 403);

        $incident = DisciplineIncident::query()->findOrFail($incidentId);

        $incident->update([
            'resolved_at' => $incident->resolved_at ? null : now(),
        ]);

        session()->flash('success', $incident->resolved_at ? 'Incident marked as unresolved.' : 'Incident marked as resolved.');
    }

    public function toggleParentVisibility(int $incidentId): void
    {
        abort_unless(auth()->user()?->can('update discipline incident'), 403);

        $incident = DisciplineIncident::query()->findOrFail($incidentId);
        $incident->update([
            'parent_visible' => !$incident->parent_visible,
        ]);

        session()->flash('success', 'Parent visibility updated.');
    }

    protected function loadClasses(): void
    {
        $this->classes = MyClass::query()
            ->whereHas('classGroup', function ($query): void {
                $query->where('school_id', auth()->user()?->school_id);
            })
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (MyClass $class): array => [
                'id' => $class->id,
                'name' => $class->name,
            ])
            ->all();

        if ($this->classes !== [] && $this->formClassId === '') {
            $this->formClassId = (string) $this->classes[0]['id'];
        }
    }

    protected function loadFormSections(): void
    {
        if ($this->formClassId === '') {
            $this->formSections = [];
            return;
        }

        $this->formSections = Section::query()
            ->where('my_class_id', (int) $this->formClassId)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Section $section): array => [
                'id' => $section->id,
                'name' => $section->name,
            ])
            ->all();

        if ($this->formSectionId !== '' && !collect($this->formSections)->contains('id', (int) $this->formSectionId)) {
            $this->formSectionId = '';
        }
    }

    protected function loadFilterSections(): void
    {
        if ($this->filterClassId === '') {
            $this->filterSections = [];
            return;
        }

        $this->filterSections = Section::query()
            ->where('my_class_id', (int) $this->filterClassId)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Section $section): array => [
                'id' => $section->id,
                'name' => $section->name,
            ])
            ->all();

        if ($this->filterSectionId !== '' && !collect($this->filterSections)->contains('id', (int) $this->filterSectionId)) {
            $this->filterSectionId = '';
        }
    }

    protected function loadFormStudents(): void
    {
        $schoolId = (int) (auth()->user()?->school_id ?? 0);
        $classId = (int) $this->formClassId;

        if ($schoolId <= 0 || $classId <= 0) {
            $this->formStudents = [];
            return;
        }

        $query = DB::table('student_records as sr')
            ->join('users as u', 'u.id', '=', 'sr.user_id')
            ->where('u.school_id', $schoolId)
            ->where('sr.is_graduated', 0)
            ->where('sr.my_class_id', $classId)
            ->whereNull('u.deleted_at');

        if ($this->formSectionId !== '') {
            $query->where('sr.section_id', (int) $this->formSectionId);
        }

        $this->formStudents = $query
            ->orderBy('u.name')
            ->get([
                'sr.id as student_record_id',
                'u.name',
                'sr.admission_number',
            ])
            ->map(static fn ($row): array => [
                'student_record_id' => (int) $row->student_record_id,
                'name' => (string) $row->name,
                'admission_number' => (string) ($row->admission_number ?? ''),
            ])
            ->all();

        if ($this->studentRecordId !== '' && !collect($this->formStudents)->contains('student_record_id', (int) $this->studentRecordId)) {
            $this->studentRecordId = '';
        }
    }

    public function render()
    {
        $incidents = DisciplineIncident::query()
            ->with([
                'studentRecord.user:id,name',
                'reportedBy:id,name',
            ])
            ->when($this->severityFilter !== 'all', function ($query): void {
                $query->where('severity', $this->severityFilter);
            })
            ->when($this->filterClassId !== '', function ($query): void {
                $query->whereHas('studentRecord', function ($studentQuery): void {
                    $studentQuery->where('my_class_id', (int) $this->filterClassId);
                });
            })
            ->when($this->filterSectionId !== '', function ($query): void {
                $query->whereHas('studentRecord', function ($studentQuery): void {
                    $studentQuery->where('section_id', (int) $this->filterSectionId);
                });
            })
            ->when($this->search !== '', function ($query): void {
                $search = '%' . trim($this->search) . '%';
                $query->where(function ($inner) use ($search): void {
                    $inner->where('category', 'like', $search)
                        ->orWhere('description', 'like', $search)
                        ->orWhereHas('studentRecord.user', function ($studentQuery) use ($search): void {
                            $studentQuery->where('name', 'like', $search);
                        });
                });
            })
            ->orderByDesc('incident_date')
            ->orderByDesc('id')
            ->paginate($this->perPage);

        return view('livewire.discipline.manage-discipline-incidents', [
            'incidents' => $incidents,
            'canCreateIncident' => auth()->user()?->can('create discipline incident'),
            'canUpdateIncident' => auth()->user()?->can('update discipline incident'),
        ])
            ->layout('layouts.dashboard', [
                'breadcrumbs' => [
                    ['href' => route('dashboard'), 'text' => 'Dashboard'],
                    ['href' => route('discipline.index'), 'text' => 'Discipline Incidents', 'active' => true],
                ],
            ])
            ->title('Discipline Incidents');
    }
}
