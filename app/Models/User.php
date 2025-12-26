<?php

namespace App\Models;

use App\Traits\InSchool;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Models\StudentResult;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    use SoftDeletes;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasRoles;
    use InSchool;

    protected $fillable = [
        'name',
        'email',
        'password',
        'birthday',
        'address',
        'blood_group',
        'religion',
        'nationality',
        'phone',
        'state',
        'city',
        'gender',
        'school_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'birthday'          => 'datetime:Y-m-d',
    ];

    protected $appends = [
        'profile_photo_url',
    ];

    protected static function booted()
    {
        static::addGlobalScope('orderByName', fn(Builder $builder) => $builder->orderBy('name'));
    }

    /**
     * Get the home route for the user based on their role.
     * All roles will now redirect to the main 'dashboard' route,
     * and the DashboardController will handle rendering the specific view.
     *
     * @return string
     */
    public function getHomeRoute(): string
    {
        if ($this->hasRole('super-admin')) {
            return 'dashboard'; // Super-admin goes to the main dashboard
        } elseif ($this->hasRole('admin')) {
            return 'dashboard'; // Admin goes to the main dashboard
        } elseif ($this->hasRole('teacher')) {
            return 'dashboard'; // Teacher goes to the main dashboard
        } elseif ($this->hasRole('student')) {
            return 'dashboard'; // Student goes to the main dashboard
        } elseif ($this->hasRole('parent')) {
            return 'dashboard'; // Parent goes to the main dashboard
        }
        // Fallback for any other roles or if no role is assigned
        return 'dashboard';
    }

    public function classes()
    {
        return $this->belongsToMany(MyClass::class, 'class_teacher', 'teacher_id', 'class_id');
    }

    public function results()
    {
        return $this->hasMany(Result::class, 'student_id');
    }

    public function studentRecords()
    {
        return $this->hasMany(StudentRecord::class);
    }

    public function scopeStudents($query)
    {
        return $query->role('student');
    }

    public function scopeApplicants($query)
    {
        return $query->whereHas('accountApplication', fn(Builder $query) => $query->otherCurrentStatus('rejected'))->role('applicant');
    }

    public function scopeRejectedApplicants($query)
    {
        return $query->role('applicant')->whereHas('accountApplication', fn(Builder $query) => $query->currentStatus('rejected'));
    }

    public function scopeActiveStudents($query)
    {
        return $query->whereRelation('studentRecord', 'is_graduated', 0);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function studentRecord(): HasOne
    {
        return $this->hasOne(StudentRecord::class);
    }

    public function graduatedStudentRecord(): HasOne
    {
        return $this->hasOne(StudentRecord::class)->withoutGlobalScopes()->where('is_Graduated', true);
    }

    public function allStudentRecords(): HasOne
    {
        return $this->hasOne(StudentRecord::class)->withoutGlobalScopes();
    }

    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(ParentRecord::class);
    }

    public function teacherRecord(): HasOne
    {
        return $this->hasOne(TeacherRecord::class);
    }

    public function parentRecord(): HasOne
    {
        return $this->hasOne(ParentRecord::class);
    }

    public function accountApplication(): HasOne
    {
        return $this->hasOne(AccountApplication::class);
    }

    public function feeInvoices(): HasMany
    {
        return $this->hasMany(FeeInvoice::class);
    }

    public function firstName()
    {
        return explode(' ', $this->name)[0];
    }

    public function getFirstNameAttribute()
    {
        return $this->firstName();
    }

    public function lastName()
    {
        return explode(' ', $this->name)[1];
    }

    public function getLastNameAttribute()
    {
        return $this->lastName();
    }

    public function otherNames()
    {
        $names = array_diff_key(explode(' ', $this->name), array_flip([0, 1]));
        return implode(' ', $names);
    }

    public function getOtherNamesAttribute()
    {
        return $this->otherNames();
    }

    public function defaultProfilePhotoUrl()
    {
        $name = trim(collect(explode(' ', $this->name))->map(fn($segment) => mb_substr($segment, 0, 1))->join(' '));
        $email = md5(strtolower(trim($this->email)));
        
        // Use a more reliable default avatar service
        return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&color=7F9CF5&background=EBF4FF';
    }
    public function getProfilePhotoUrlAttribute()
{
    // If user has no profile photo path, return default
    if (!$this->profile_photo_path) {
        return $this->defaultProfilePhotoUrl();
    }
    
    // Check if file exists in storage
    $path = storage_path('app/public/' . $this->profile_photo_path);
    if (!file_exists($path)) {
        return $this->defaultProfilePhotoUrl();
    }
    
    // Return the actual URL
    return asset('storage/' . $this->profile_photo_path);
}

    public function getBirthdayAttribute($value)
    {
        return Carbon::parse($value)->format('Y-m-d');
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class);
    }
    public function registeredSubjects()
    {
        return $this->belongsToMany(Subject::class, 'student_subject')
            ->withTimestamps()
            ->withPivot('my_class_id', 'section_id');
    }

    public function assignedSubjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'subject_user');
    }
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isTeacher(): bool
    {
        return $this->hasRole('teacher');
    }
}
