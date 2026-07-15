<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_lead_teacher',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_lead_teacher' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    // ---- Role helpers (FR-1.5) ----
    public function isTeacher(): bool
    {
        return $this->role === 'teacher';
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    // ---- Relationships ----

    /** Classes this user teaches (role = teacher). */
    public function classesTaught(): HasMany
    {
        return $this->hasMany(SchoolClass::class, 'teacher_id');
    }

    /** Exams this user authored (role = teacher). */
    public function examsCreated(): HasMany
    {
        return $this->hasMany(Exam::class, 'teacher_id');
    }

    /** Classes this user is enrolled in (role = student). */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'student_id');
    }

    /** Exam attempts made by this user (role = student). */
    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class, 'student_id');
    }
}
