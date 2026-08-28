<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Exam;
use App\Models\ExamOption;
use App\Models\ExamQuestion;
use App\Models\ExamSession;
use App\Models\ExamSessionQuestionSnapshot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamSessionDetailAnswersTest extends TestCase
{
    use RefreshDatabase;

    public function test_session_detail_reads_saved_answers_from_snapshots(): void
    {
        $admin = User::factory()->create([
            'email_verified_at' => now(),
            'role' => UserRole::SuperAdmin,
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

        ExamOption::query()->create([
            'exam_question_id' => $question->id,
            'option_label' => 'B',
            'option_text' => 'Menjaga peserta tetap di halaman ujian',
            'is_correct' => true,
        ]);

        $session = ExamSession::query()->create([
            'user_id' => $admin->id,
            'exam_id' => $exam->id,
            'started_at' => now(),
            'expires_at' => now()->addMinutes(60),
            'finished_at' => now(),
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
                ['option_label' => 'B', 'option_text' => 'Menjaga peserta tetap di halaman ujian'],
            ],
            'selected_answer' => 'B',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.sessions.show', $session));

        $response->assertOk();
        $response->assertSee('Jawaban peserta:');
        $response->assertSee('B', false);
        $response->assertSee('Jawaban benar:');
    }
}
