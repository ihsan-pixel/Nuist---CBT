<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\Exam;
use App\Models\ExamOption;
use App\Models\ExamQuestion;
use App\Models\ExamSession;
use App\Models\ExamSessionQuestionSnapshot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamResultsSummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_exam_results_page_shows_answered_and_unanswered_summary(): void
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

        $questionOne = ExamQuestion::query()->create([
            'exam_id' => $exam->id,
            'question_text' => 'Soal 1',
            'sort_order' => 1,
        ]);

        $questionTwo = ExamQuestion::query()->create([
            'exam_id' => $exam->id,
            'question_text' => 'Soal 2',
            'sort_order' => 2,
        ]);

        ExamOption::query()->create([
            'exam_question_id' => $questionOne->id,
            'option_label' => 'A',
            'option_text' => 'Jawaban A',
            'is_correct' => true,
        ]);

        ExamOption::query()->create([
            'exam_question_id' => $questionTwo->id,
            'option_label' => 'A',
            'option_text' => 'Jawaban A',
            'is_correct' => true,
        ]);

        $session = ExamSession::query()->create([
            'user_id' => $admin->id,
            'exam_id' => $exam->id,
            'started_at' => now()->subHour(),
            'expires_at' => now()->addHour(),
            'finished_at' => now(),
            'warning_count' => 0,
            'is_locked' => false,
        ]);

        ExamSessionQuestionSnapshot::query()->create([
            'exam_session_id' => $session->id,
            'exam_question_id' => $questionOne->id,
            'sort_order' => 1,
            'question_text' => $questionOne->question_text,
            'option_snapshot' => [
                ['option_label' => 'A', 'option_text' => 'Jawaban A'],
            ],
            'selected_answer' => 'A',
        ]);

        ExamSessionQuestionSnapshot::query()->create([
            'exam_session_id' => $session->id,
            'exam_question_id' => $questionTwo->id,
            'sort_order' => 2,
            'question_text' => $questionTwo->question_text,
            'option_snapshot' => [
                ['option_label' => 'A', 'option_text' => 'Jawaban A'],
            ],
            'selected_answer' => null,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.exams.results', $exam));

        $response->assertOk();
        $response->assertSee('Total Soal', false);
        $response->assertSee('Terjawab', false);
        $response->assertSee('Tidak Terjawab', false);
        $response->assertSee('1/2', false);
        $response->assertSee('1', false);
    }
}
