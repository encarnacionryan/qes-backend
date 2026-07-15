<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SchoolClass extends Model
{
    use HasFactory;

    protected $fillable = ['teacher_id', 'name', 'subject', 'section', 'join_code', 'is_archived'];

    protected function casts(): array
    {
        return ['is_archived' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::creating(function (SchoolClass $class) {
            $class->join_code ??= static::generateUniqueJoinCode();
        });
    }

    /** FR-2.2: generate a short, unique, human-readable join code. */
    public static function generateUniqueJoinCode(): string
    {
        do {
            $code = strtoupper(Str::random(6));
        } while (static::where('join_code', $code)->exists());

        return $code;
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'enrollments', 'school_class_id', 'student_id')
            ->withPivot('joined_at');
    }

    public function exams(): BelongsToMany
    {
        return $this->belongsToMany(Exam::class, 'exam_class')
            ->withPivot(['opens_at', 'closes_at'])
            ->withTimestamps();
    }
}
