<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamPanelAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_open_exam_panel(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'role' => UserRole::SuperAdmin,
        ]);

        $response = $this->actingAs($user)->get(route('admin.exams.index'));

        $response->assertOk();
    }

    public function test_peserta_can_open_exam_panel(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'role' => UserRole::Peserta,
        ]);

        $response = $this->actingAs($user)->get(route('admin.exams.index'));

        $response->assertOk();
    }

    public function test_panitia_cannot_open_exam_panel(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'role' => UserRole::Panitia,
        ]);

        $response = $this->actingAs($user)->get(route('admin.exams.index'));

        $response->assertForbidden();
    }
}
