<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamSessionQuestionSnapshot extends Model
{
    protected $fillable = [
        'exam_session_id',
        'exam_question_id',
        'sort_order',
        'question_text',
        'option_snapshot',
        'selected_answer',
    ];

    protected function casts(): array
    {
        return [
            'option_snapshot' => 'array',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ExamSession::class, 'exam_session_id');
    }
}
