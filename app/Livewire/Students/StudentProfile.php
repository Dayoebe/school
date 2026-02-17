<?php

namespace App\Livewire\Students;

use Livewire\Component;
use App\Models\User;
// You might need to import other models like StudentRecord, MyClass, Section, etc.
// use App\Models\StudentRecord;

class StudentProfile extends Component
{
    public User $student;
    public $profileData = [];
    public $loading = true;

    public function mount(User $student)
    {
        $this->student = $student;
        $this->loadProfileData();
    }

    public function loadProfileData()
    {
        $this->loading = true;
        $this->profileData = [];

        if (!$this->student) {
            $this->loading = false;
            return;
        }

        // Eager load necessary relationships for the student's profile
        $this->student->load([
            'studentRecord.myClass',
            'studentRecord.section',
            // Add any other relationships you need for the profile (e.g., parents, address, etc.)
        ]);

        // Prepare data for display
        $this->profileData = [
            'name' => $this->student->name,
            'email' => $this->student->email,
            'admission_number' => $this->student->studentRecord->admission_number ?? 'N/A',
            'class' => $this->student->studentRecord->myClass->name ?? 'N/A',
            'section' => $this->student->studentRecord->section->name ?? 'N/A',
            'gender' => $this->student->gender ?? 'N/A',
            'birthday' => $this->student->birthday ? \Carbon\Carbon::parse($this->student->birthday)->format('M d, Y') : 'N/A',
            'phone' => $this->student->phone ?? 'N/A',
            'address' => $this->student->address ?? 'N/A',
            'blood_group' => $this->student->blood_group ?? 'N/A',
            'religion' => $this->student->religion ?? 'N/A',
            'nationality' => $this->student->nationality ?? 'N/A',
            'state' => $this->student->state ?? 'N/A',
            'city' => $this->student->city ?? 'N/A',
            'admission_date' => $this->student->studentRecord->admission_date ? \Carbon\Carbon::parse($this->student->studentRecord->admission_date)->format('M d, Y') : 'N/A',
            'profile_photo_url' => $this->student->profile_photo_url,
        ];

        $this->loading = false;
    }

    public function render()
    {
        return view('livewire.students.student-profile');
    }
}
