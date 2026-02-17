<?php

namespace App\Livewire\Dashboard;

use App\Models\School;
use App\Models\User;
use App\Models\MyClass;
use App\Models\Section;
use App\Models\ClassGroup;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class DashboardStats extends Component
{
    public $stats = [];
    public $loading = true;

    public function mount()
    {
        $this->loadStats();
        $this->loading = false;
    }

    private function loadStats()
    {
        $user = auth()->user();
        $schoolId = $user->school_id;

        // Get current academic year for accurate student counts
        $currentAcademicYearId = $user->school?->academic_year_id;

        $this->stats = [
            'schools' => $user->hasAnyRole(['super-admin', 'super_admin']) ? School::count() : 0,
            'class_groups' => $schoolId ? ClassGroup::where('school_id', $schoolId)->count() : 0,
            'classes' => $schoolId ? MyClass::whereHas('classGroup', function($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            })->count() : 0,
            'sections' => $schoolId ? Section::whereHas('myClass.classGroup', function($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            })->count() : 0,
            'active_students' => $this->getActiveStudentsCount($schoolId),
            'graduated_students' => $this->getGraduatedStudentsCount($schoolId),
            'teachers' => User::where('school_id', $schoolId)->role('teacher')->count(),
            'parents' => User::where('school_id', $schoolId)->role('parent')->count(),
        ];
    }

    private function getActiveStudentsCount($schoolId)
    {
        return User::where('school_id', $schoolId)
            ->role('student')
            ->whereHas('studentRecord', function($q) {
                $q->where('is_graduated', false);
            })
            ->count();
    }

    private function getGraduatedStudentsCount($schoolId)
    {
        return User::where('school_id', $schoolId)
            ->role('student')
            ->whereHas('studentRecord', function($q) {
                $q->where('is_graduated', true);
            })
            ->count();
    }

    public function render()
    {
        return view('livewire.dashboard.dashboard-stats');
    }
}
