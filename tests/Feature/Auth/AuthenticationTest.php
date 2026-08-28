<?php

namespace Tests\Feature\Auth;

use App\Models\Exam;
use App\Models\ExamOption;
use App\Models\ExamQuestion;
use App\Models\ExamSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_peserta_with_saved_exam_answers_is_redirected_to_thank_you_page_after_login(): void
    {
        $user = User::factory()->create([
            'role' => 'peserta',
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

        $session->snapshots()->create([
            'exam_question_id' => $question->id,
            'sort_order' => 1,
            'question_text' => $question->question_text,
            'option_snapshot' => [
                [
                    'option_label' => 'A',
                    'option_text' => 'Membuka halaman lain lebih cepat',
                ],
            ],
            'selected_answer' => 'A',
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('exam.completed', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect(route('login'));
    }
}
