<?php

namespace Tests\Feature;

use App\Models\Exam;
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

        $response = $this->actingAs($user)->get(route('exam.completed'));

        $response->assertOk();
        $response->assertSee('Ujian Selesai', false);
        $response->assertSee('Logout', false);
    }
}
