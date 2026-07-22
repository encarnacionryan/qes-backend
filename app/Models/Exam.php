<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id', 'title', 'description', 'time_limit_minutes', 'total_points',
        'status', 'show_score_immediately', 'allow_retake', 'anonymize_leaderboard',
    ];

    protected function casts(): array
    {
        return [
            'show_score_immediately' => 'boolean',
            'allow_retake' => 'boolean',
            'anonymize_leaderboard' => 'boolean',
        ];
    }

    /** FR-3.6: lock editing once at least one submission exists. */
    public function hasStartedSubmissions(): bool
    {
        return $this->submissions()->exists();
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }

    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'exam_class')
            ->withPivot(['opens_at', 'closes_at'])
            ->withTimestamps();
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    /** New: browsable/hostable instances of this exam (replaces class-gated publishing). */
    public function sessions(): HasMany
    {
        return $this->hasMany(ExamSession::class);
    }

    public function leaderboardEntries(): HasMany
    {
        return $this->hasMany(LeaderboardEntry::class)->orderBy('rank');
    }
}
