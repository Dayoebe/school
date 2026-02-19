<?php

namespace App\Livewire\Contacts;

use App\Mail\NewContactMessageAlert;
use App\Models\ContactMessage;
use App\Models\School;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Throwable;
use Livewire\Component;

class PublicContactForm extends Component
{
    public $school_id = '';
    public $full_name = '';
    public $email = '';
    public $phone = '';
    public $subject = '';
    public $message = '';

    public array $schools = [];
    public bool $submitted = false;
    public bool $contactTableReady = true;

    protected function rules(): array
    {
        return [
            'school_id' => ['required', 'integer', 'exists:schools,id'],
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:3000'],
        ];
    }

    public function mount(): void
    {
        $this->contactTableReady = $this->contactTableExists();

        $this->schools = School::query()
            ->select('id', 'name')
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
        }
    }

    public function submit(): void
    {
        if (!$this->contactTableReady) {
            session()->flash('error', 'Contact setup is incomplete. Please run migrations and try again.');
            return;
        }

        $this->validate();

        $contactMessage = DB::transaction(function () {
            return ContactMessage::create([
                'school_id' => (int) $this->school_id,
                'full_name' => trim($this->full_name),
                'email' => strtolower(trim($this->email)),
                'phone' => $this->phone ? trim($this->phone) : null,
                'subject' => trim($this->subject),
                'message' => trim($this->message),
                'status' => 'new',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });

        $this->notifyStaffByEmail($contactMessage->fresh('school'));

        $selectedSchool = $this->school_id;

        $this->reset([
            'full_name',
            'email',
            'phone',
            'subject',
            'message',
        ]);

        $this->school_id = $selectedSchool;
        $this->submitted = true;

        session()->flash('success', 'Your message has been sent successfully.');
    }

    public function sendAnother(): void
    {
        $this->submitted = false;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.contacts.public-contact-form');
    }

    protected function contactTableExists(): bool
    {
        try {
            return Schema::hasTable('contact_messages');
        } catch (Throwable $e) {
            report($e);
            return false;
        }
    }

    protected function notifyStaffByEmail(ContactMessage $contactMessage): void
    {
        $schoolId = (int) $contactMessage->school_id;
        $staffRoles = ['super-admin', 'super_admin', 'admin', 'principal', 'teacher'];
        $globalSuperAdminRoles = ['super-admin', 'super_admin'];

        $emails = User::query()
            ->whereNotNull('email')
            ->where(function ($query) use ($schoolId, $staffRoles, $globalSuperAdminRoles) {
                $query->where(function ($schoolStaff) use ($schoolId, $staffRoles) {
                    $schoolStaff->where('school_id', $schoolId)
                        ->whereHas('roles', function ($roleQuery) use ($staffRoles) {
                            $roleQuery->whereIn('name', $staffRoles);
                        });
                })->orWhere(function ($superAdmin) use ($globalSuperAdminRoles) {
                    $superAdmin->whereHas('roles', function ($roleQuery) use ($globalSuperAdminRoles) {
                        $roleQuery->whereIn('name', $globalSuperAdminRoles);
                    });
                });
            })
            ->pluck('email')
            ->map(fn ($email) => strtolower(trim((string) $email)))
            ->filter()
            ->unique()
            ->values();

        if ($emails->isEmpty()) {
            return;
        }

        try {
            Mail::to(config('mail.from.address'))
                ->bcc($emails->all())
                ->send(new NewContactMessageAlert($contactMessage));
        } catch (Throwable $e) {
            report($e);
        }
    }
}
