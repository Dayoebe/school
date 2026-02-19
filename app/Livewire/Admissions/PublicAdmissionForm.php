<?php

namespace App\Livewire\Admissions;

use App\Models\AdmissionRegistration;
use App\Models\MyClass;
use App\Models\School;
use App\Models\Section;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class PublicAdmissionForm extends Component
{
    use WithFileUploads;

    public $school_id = '';
    public $my_class_id = '';
    public $section_id = '';

    public $student_name = '';
    public $student_email = '';
    public $gender = '';
    public $birthday = '';

    public $guardian_name = '';
    public $guardian_phone = '';
    public $guardian_email = '';
    public $guardian_relationship = '';

    public $address = '';
    public $previous_school = '';
    public $notes = '';
    public $document = null;

    public $schools = [];
    public $classes = [];
    public $sections = [];
    public string $classNotice = '';
    public string $sectionNotice = '';

    public bool $submitted = false;
    public string $submittedReference = '';

    public function mount(): void
    {
        $this->schools = School::query()
            ->select('id', 'name', 'address')
            ->orderBy('name')
            ->get()
            ->map(fn ($school) => [
                'id' => (string) $school->id,
                'name' => $school->name,
            ])
            ->values()
            ->all();

        if (count($this->schools) === 1) {
            $this->school_id = $this->schools[0]['id'];
            $this->loadClasses();
        }
    }

    public function updatedSchoolId(): void
    {
        $this->my_class_id = '';
        $this->section_id = '';
        $this->sections = [];
        $this->sectionNotice = '';
        $this->loadClasses();
    }

    public function updatedMyClassId(): void
    {
        $this->section_id = '';
        $this->loadSections();
    }

    protected function rules(): array
    {
        return [
            'school_id' => ['required', 'integer', 'exists:schools,id'],
            'my_class_id' => ['required', 'integer', 'exists:my_classes,id'],
            'section_id' => ['nullable', 'integer', 'exists:sections,id'],

            'student_name' => ['required', 'string', 'max:255'],
            'student_email' => ['nullable', 'email', 'max:255'],
            'gender' => ['required', 'in:male,female'],
            'birthday' => ['required', 'date', 'before:today'],

            'guardian_name' => ['required', 'string', 'max:255'],
            'guardian_phone' => ['required', 'string', 'max:30'],
            'guardian_email' => ['nullable', 'email', 'max:255'],
            'guardian_relationship' => ['nullable', 'string', 'max:100'],

            'address' => ['required', 'string', 'max:1000'],
            'previous_school' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:5120'],
        ];
    }

    public function submit(): void
    {
        $this->validate();

        if (!$this->selectedClassBelongsToSchool()) {
            $this->addError('my_class_id', 'Selected class does not belong to selected school.');
            return;
        }

        if (!$this->selectedSectionBelongsToClass()) {
            $this->addError('section_id', 'Selected section does not belong to selected class.');
            return;
        }

        $reference = DB::transaction(function () {
            $referenceNo = $this->generateReferenceNo();
            $documentPath = null;
            $documentName = null;

            if ($this->document) {
                $documentPath = $this->document->store('admissions/documents', 'public');
                $documentName = $this->document->getClientOriginalName();
            }

            AdmissionRegistration::create([
                'school_id' => (int) $this->school_id,
                'my_class_id' => (int) $this->my_class_id,
                'section_id' => $this->section_id ? (int) $this->section_id : null,
                'reference_no' => $referenceNo,

                'student_name' => trim($this->student_name),
                'student_email' => $this->student_email ?: null,
                'gender' => $this->gender,
                'birthday' => $this->birthday,

                'guardian_name' => trim($this->guardian_name),
                'guardian_phone' => trim($this->guardian_phone),
                'guardian_email' => $this->guardian_email ?: null,
                'guardian_relationship' => $this->guardian_relationship ?: null,

                'address' => trim($this->address),
                'previous_school' => $this->previous_school ?: null,
                'notes' => $this->notes ?: null,
                'document_path' => $documentPath,
                'document_name' => $documentName,
                'status' => 'pending',
            ]);

            return $referenceNo;
        });

        $this->submitted = true;
        $this->submittedReference = $reference;
        $this->resetFormFields();

        session()->flash('success', 'Admission form submitted successfully.');
    }

    public function submitAnother(): void
    {
        $this->submitted = false;
        $this->submittedReference = '';
        $this->resetFormFields();
        $this->resetValidation();
    }

    protected function loadClasses(): void
    {
        if (!$this->school_id) {
            $this->classes = [];
            $this->classNotice = '';
            $this->sectionNotice = '';
            return;
        }

        $school = School::find((int) $this->school_id);

        if (!$school) {
            $this->classes = [];
            $this->classNotice = 'Selected school not found.';
            return;
        }

        $this->classes = $school->myClasses()
            ->select('my_classes.id', 'my_classes.name')
            ->orderBy('my_classes.name')
            ->get()
            ->map(fn ($myClass) => [
                'id' => (string) $myClass->id,
                'name' => $myClass->name,
            ])
            ->values()
            ->all();

        $this->classNotice = empty($this->classes)
            ? 'No classes are set up for this school yet.'
            : '';
    }

    protected function loadSections(): void
    {
        if (!$this->my_class_id) {
            $this->sections = [];
            $this->sectionNotice = '';
            return;
        }

        $this->sections = Section::query()
            ->select('id', 'name', 'my_class_id')
            ->where('my_class_id', (int) $this->my_class_id)
            ->orderBy('name')
            ->get()
            ->map(fn ($section) => [
                'id' => (string) $section->id,
                'name' => $section->name,
            ])
            ->values()
            ->all();

        $this->sectionNotice = empty($this->sections)
            ? 'No sections configured for this class yet. You can continue without selecting a section.'
            : '';
    }

    protected function selectedClassBelongsToSchool(): bool
    {
        return MyClass::query()
            ->where('id', $this->my_class_id)
            ->whereHas('classGroup', function ($query) {
                $query->where('school_id', $this->school_id);
            })
            ->exists();
    }

    protected function selectedSectionBelongsToClass(): bool
    {
        if (!$this->section_id) {
            return true;
        }

        return Section::query()
            ->where('id', $this->section_id)
            ->where('my_class_id', $this->my_class_id)
            ->exists();
    }

    protected function generateReferenceNo(): string
    {
        do {
            $reference = 'ADM-' . now()->format('Y') . '-' . mt_rand(100000, 999999);
        } while (AdmissionRegistration::where('reference_no', $reference)->exists());

        return $reference;
    }

    protected function resetFormFields(): void
    {
        $school = $this->school_id;

        $this->reset([
            'my_class_id',
            'section_id',
            'student_name',
            'student_email',
            'gender',
            'birthday',
            'guardian_name',
            'guardian_phone',
            'guardian_email',
            'guardian_relationship',
            'address',
            'previous_school',
            'notes',
            'document',
        ]);

        $this->sections = [];
        $this->classNotice = '';
        $this->sectionNotice = '';
        $this->school_id = $school;
        $this->loadClasses();
    }

    public function render()
    {
        return view('livewire.admissions.public-admission-form');
    }
}
