<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Hash;

class ExamSession extends Model
{
    use HasFactory;

    protected $fillable = ['exam_id', 'teacher_id', 'visibility', 'password_hash', 'status', 'opens_at', 'closes_at'];

    protected $hidden = ['password_hash'];

    protected function casts(): array
    {
        return [
            'opens_at' => 'datetime',
            'closes_at' => 'datetime',
        ];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function isPrivate(): bool
    {
        return $this->visibility === 'private';
    }

    public function isOpen(): bool
    {
        if ($this->status !== 'open') {
            return false;
        }

        $now = now();

        if ($this->opens_at && $now->lt($this->opens_at)) {
            return false;
        }

        if ($this->closes_at && $now->gt($this->closes_at)) {
            return false;
        }

        return true;
    }

    public function isScheduledForLater(): bool
    {
        return $this->status === 'open' && $this->opens_at && now()->lt($this->opens_at);
    }

    public function checkPassword(?string $password): bool
    {
        if (! $this->isPrivate()) {
            return true;
        }

        return $password !== null && Hash::check($password, $this->password_hash);
    }

    public function setPassword(?string $password): void
    {
        $this->password_hash = $password ? Hash::make($password) : null;
    }
}
