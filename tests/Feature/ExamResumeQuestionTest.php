<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\ExamOption;
use App\Models\ExamQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamResumeQuestionTest extends TestCase
{
    use RefreshDatabase;

    public function test_answering_a_later_question_resumes_at_the_next_question_after_reload(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $exam = Exam::query()->create([
            'title' => 'CBT Demo',
            'description' => 'Ujian contoh.',
            'duration_minutes' => 60,
            'is_active' => true,
        ]);

        $questions = collect();

        for ($index = 1; $index <= 8; $index++) {
            $question = ExamQuestion::query()->create([
                'exam_id' => $exam->id,
                'question_text' => "Soal {$index}",
                'sort_order' => $index,
            ]);

            ExamOption::query()->create([
                'exam_question_id' => $question->id,
                'option_label' => 'A',
                'option_text' => "Jawaban {$index}",
                'is_correct' => false,
            ]);

            $questions->push($question);
        }

        $this->actingAs($user)->post(route('exam.start'));

        $response = $this->actingAs($user)->postJson(route('exam.answer'), [
            'question_id' => $questions[6]->id,
            'answer' => 'A',
        ]);

        $response->assertOk();

        $roomResponse = $this->actingAs($user)->get(route('exam.room'));

        $roomResponse->assertOk();
        $roomResponse->assertSee('initialQuestionIndex: 7', false);
    }
}
