<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\ExamOption;
use App\Models\ExamQuestion;
use App\Models\ExamSession;
use App\Models\ExamSessionQuestionSnapshot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamSessionRestartPreventionTest extends TestCase
{
    use RefreshDatabase;

    public function test_finished_session_with_saved_answers_cannot_start_again(): void
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

        $question = ExamQuestion::query()->create([
            'exam_id' => $exam->id,
            'question_text' => 'Apa fungsi utama mode ujian terkunci?',
            'sort_order' => 1,
        ]);

        ExamOption::query()->create([
            'exam_question_id' => $question->id,
            'option_label' => 'A',
            'option_text' => 'Membuka halaman lain lebih cepat',
            'is_correct' => false,
        ]);

        $session = ExamSession::query()->create([
            'user_id' => $user->id,
            'exam_id' => $exam->id,
            'started_at' => now()->subHour(),
            'expires_at' => now()->subMinutes(10),
            'finished_at' => now()->subMinutes(5),
            'warning_count' => 0,
            'is_locked' => false,
        ]);

        ExamSessionQuestionSnapshot::query()->create([
            'exam_session_id' => $session->id,
            'exam_question_id' => $question->id,
            'sort_order' => 1,
            'question_text' => $question->question_text,
            'option_snapshot' => [
                ['option_label' => 'A', 'option_text' => 'Membuka halaman lain lebih cepat'],
            ],
            'selected_answer' => 'A',
        ]);

        $response = $this->actingAs($user)->post(route('exam.start'));

        $response->assertRedirect(route('exam.completed'));
        $response->assertSessionHas('status', 'Sesi ujian sudah selesai dan jawaban Anda telah tersimpan.');
    }
}
