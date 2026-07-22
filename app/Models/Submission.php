<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Submission extends Model
{
    protected $fillable = [
        'exam_id', 'exam_session_id', 'student_id', 'started_at', 'submitted_at', 'status', 'attempt_number',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
        ];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    /** New: the specific session join that produced this attempt. */
    public function examSession(): BelongsTo
    {
        return $this->belongsTo(ExamSession::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    public function score(): HasOne
    {
        return $this->hasOne(Score::class);
    }

    /** Seconds between start and submission — used for leaderboard tiebreaking (FR-6.1). */
    public function completionSeconds(): ?int
    {
        if (! $this->submitted_at) {
            return null;
        }

        return $this->started_at->diffInSeconds($this->submitted_at);
    }
}
