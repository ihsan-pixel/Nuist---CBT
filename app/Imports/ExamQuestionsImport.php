<?php

namespace App\Imports;

use App\Models\Exam;
use App\Models\ExamQuestion;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ExamQuestionsImport implements ToCollection, WithHeadingRow
{
    use Importable;

    public function __construct(private readonly Exam $exam) {}

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     */
    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $questionText = trim((string) ($row['question_text'] ?? ''));

            if ($questionText === '') {
                continue;
            }

            $sortOrder = (int) ($row['sort_order'] ?? 0);
            $question = ExamQuestion::query()->updateOrCreate(
                [
                    'exam_id' => $this->exam->id,
                    'sort_order' => $sortOrder,
                ],
                [
                    'question_text' => $questionText,
                ]
            );

            $question->options()->delete();

            $options = [
                'A' => trim((string) ($row['option_a_text'] ?? '')),
                'B' => trim((string) ($row['option_b_text'] ?? '')),
                'C' => trim((string) ($row['option_c_text'] ?? '')),
                'D' => trim((string) ($row['option_d_text'] ?? '')),
            ];

            $correctAnswer = strtoupper(trim((string) ($row['correct_answer'] ?? '')));

            foreach ($options as $label => $text) {
                if ($text === '') {
                    continue;
                }

                $question->options()->create([
                    'option_label' => $label,
                    'option_text' => $text,
                    'is_correct' => $correctAnswer === $label,
                ]);
            }
        }
    }
}
