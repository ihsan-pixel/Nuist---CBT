<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamSession extends Model
{
    protected $fillable = [
        'user_id',
        'exam_id',
        'started_at',
        'expires_at',
        'finished_at',
        'warning_count',
        'is_locked',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'expires_at' => 'datetime',
            'finished_at' => 'datetime',
            'is_locked' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function violations(): HasMany
    {
        return $this->hasMany(ExamViolation::class, 'exam_session_id');
    }

    public function activeViolationsCount(): int
    {
        return (int) ($this->warning_count ?? 0);
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(ExamSessionQuestionSnapshot::class);
    }
}
