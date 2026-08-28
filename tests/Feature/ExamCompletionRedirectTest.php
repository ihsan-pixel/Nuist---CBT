<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\ExamOption;
use App\Models\ExamQuestion;
use App\Models\ExamSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamCompletionRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_exam_finish_redirects_to_completion_page(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Exam::query()->create([
            'title' => 'CBT Demo',
            'description' => 'Ujian contoh.',
            'duration_minutes' => 60,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post(route('exam.finish'));

        $response->assertRedirect(route('exam.completed'));
    }

    public function test_completion_page_shows_logout_button(): void
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
            'is_correct' => false,
        ]);

        ExamOption::query()->create([
            'exam_question_id' => $questionTwo->id,
            'option_label' => 'A',
            'option_text' => 'Jawaban A',
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

        $session->snapshots()->createMany([
            [
                'exam_question_id' => $questionOne->id,
                'sort_order' => 1,
                'question_text' => $questionOne->question_text,
                'option_snapshot' => [
                    [
                        'option_label' => 'A',
                        'option_text' => 'Jawaban A',
                    ],
                ],
                'selected_answer' => 'A',
            ],
            [
                'exam_question_id' => $questionTwo->id,
                'sort_order' => 2,
                'question_text' => $questionTwo->question_text,
                'option_snapshot' => [
                    [
                        'option_label' => 'A',
                        'option_text' => 'Jawaban A',
                    ],
                ],
                'selected_answer' => null,
            ],
        ]);

        $response = $this->actingAs($user)->get(route('exam.completed'));

        $response->assertOk();
        $response->assertSee('Ujian Selesai', false);
        $response->assertSee('Logout', false);
        $response->assertSee('Total Soal', false);
        $response->assertSee('Terjawab', false);
        $response->assertSee('Tidak Terjawab', false);
        $response->assertSee('2', false);
        $response->assertSee('1', false);
    }

    public function test_logout_redirects_to_login_page(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('login'));
    }
}
