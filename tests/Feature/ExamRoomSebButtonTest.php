<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class ExamRoomSebButtonTest extends TestCase
{
    use RefreshDatabase;

    public function test_exam_room_keeps_start_button_clickable_when_seb_is_verified_in_session(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Exam::query()->create([
            'title' => 'CBT Demo',
            'description' => 'Ujian contoh untuk mode penguncian browser.',
            'duration_minutes' => 60,
            'is_active' => true,
        ]);

        Session::put('seb.verified', true);

        $response = $this->actingAs($user)->get(route('exam.room'));

        $response->assertOk();
        $response->assertSee('sebVerified: true', false);
        $response->assertSee('sebDetected: true', false);
        $response->assertSee('Mulai Ujian', false);
        $response->assertSee('Indikator Soal', false);
        $response->assertSee('Ringkasan status pengerjaan', false);
        $response->assertSee('Total 0 soal', false);
        $response->assertSee('Sebelumnya', false);
        $response->assertSee('Selanjutnya', false);
    }
}
