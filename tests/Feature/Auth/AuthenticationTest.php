<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
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
        $response->assertSee('Selamat Datang');
        $response->assertSee('Masuk');
        $response->assertSee('Lupa kata sandi?');
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Panitia,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_peserta_is_redirected_to_exam_room_after_login(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Peserta,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('exam.start-seb', absolute: false));
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
        $response->assertRedirect(route('login', absolute: false));
    }

    public function test_peserta_does_not_see_exam_management_menu(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Peserta,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertDontSee('Kelola Ujian');
    }

    public function test_exam_answer_creates_snapshot_lazily_for_peserta(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Peserta,
        ]);

        $exam = Exam::query()->create([
            'title' => 'Ujian Percobaan',
            'description' => 'Deskripsi',
            'duration_minutes' => 30,
            'is_active' => true,
        ]);

        $question = ExamQuestion::query()->create([
            'exam_id' => $exam->id,
            'question_text' => '2 + 2 = ?',
            'sort_order' => 1,
        ]);

        ExamOption::query()->create([
            'exam_question_id' => $question->id,
            'option_label' => 'A',
            'option_text' => '3',
            'is_correct' => false,
        ]);

        ExamOption::query()->create([
            'exam_question_id' => $question->id,
            'option_label' => 'B',
            'option_text' => '4',
            'is_correct' => true,
        ]);

        ExamSession::query()->create([
            'user_id' => $user->id,
            'exam_id' => $exam->id,
            'started_at' => now(),
            'expires_at' => now()->addMinutes(30),
            'warning_count' => 0,
            'is_locked' => true,
        ]);

        $this->actingAs($user)->post(route('exam.answer'), [
            'question_id' => $question->id,
            'answer' => 'B',
        ])->assertRedirect();

        $this->assertDatabaseHas('exam_session_question_snapshots', [
            'exam_question_id' => $question->id,
            'selected_answer' => 'B',
        ]);
    }
}
