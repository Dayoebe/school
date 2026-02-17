<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'gender',
        'birthday',
        'phone',
        'address',
        'blood_group',
        'religion',
        'nationality',
        'state',
        'city',
        'school_id',
        'locked',
        'profile_photo_path',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'birthday' => 'date', 
        'locked' => 'boolean',
    ];

    protected $appends = ['profile_photo_url'];

    // Scopes
    public function scopeStudents($query)
    {
        return $query->role('student');
    }

    public function scopeTeachers($query)
    {
        return $query->role('teacher');
    }

    public function scopeParents($query)
    {
        return $query->role('parent');
    }

    public function scopeInSchool($query, $schoolId = null)
    {
        $schoolId = $schoolId ?? auth()->user()?->school_id;
        return $query->where('school_id', $schoolId);
    }

    /**
     * Scope to get only active (non-graduated) students
     */
    public function scopeActiveStudents($query)
    {
        return $query->whereHas('studentRecord', function($q) {
            $q->where('is_graduated', false);
        });
    }

    /**
     * Scope to get only graduated students (Alumni)
     */
    public function scopeGraduatedStudents($query)
    {
        return $query->whereHas('studentRecord', function($q) {
            $q->where('is_graduated', true);
        });
    }

    // Relationships
    public function school()
    {
        return $this->belongsTo(School::class);
    }
    public function subjects()
{
    return $this->belongsToMany(Subject::class, 'subject_user', 'user_id', 'subject_id');
}

public function teachingSubjects()
{
    return $this->subjects();
}

// Add these relationship methods to your User.php model:

/**
 * Get the account application for this user (if applicant)
 */
public function accountApplication()
{
    return $this->hasOne(AccountApplication::class);
}

/**
 * Get the parent record for this user (if parent)
 */
public function parentRecord()
{
    return $this->hasOne(ParentRecord::class);
}

/**
 * Get the teacher record for this user (if teacher)
 */
public function teacherRecord()
{
    return $this->hasOne(TeacherRecord::class);
}
    public function studentRecord()
    {
        return $this->hasOne(StudentRecord::class);
    }

    public function feeInvoices()
    {
        return $this->hasMany(FeeInvoice::class, 'user_id'); 
    }

    public function parents()
    {
        return $this->belongsToMany(User::class, 'parent_records', 'student_id', 'user_id');
    }

    public function children()
    {
        return $this->belongsToMany(User::class, 'parent_records', 'user_id', 'student_id');
    }

    // Profile Photo
    public function getProfilePhotoUrlAttribute()
    {
        return $this->profile_photo_path
            ? asset('storage/' . $this->profile_photo_path)
            : asset('images/default-avatar.png');
    }

    // Helper Methods
    public function isStudent(): bool
    {
        return $this->hasRole('student');
    }

    public function isTeacher(): bool
    {
        return $this->hasRole('teacher');
    }

    public function isParent(): bool
    {
        return $this->hasRole('parent');
    }

    public function isAdmin(): bool
    {
        return $this->hasAnyRole(['admin', 'super-admin', 'super_admin']);
    }

    /**
     * Get the home route based on user role
     */
    public function getHomeRoute(): string
    {
        return 'dashboard';
    }
}
