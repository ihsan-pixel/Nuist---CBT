<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\ExamOption;
use App\Models\ExamQuestion;
use App\Models\ExamSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ExamTimerStartsInRoomTest extends TestCase
{
    use RefreshDatabase;

    public function test_exam_timer_starts_when_exam_room_opens_not_when_start_button_is_clicked(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-28 10:00:00'));

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

        $startResponse = $this->actingAs($user)->post(route('exam.start'));

        $startResponse->assertRedirect(route('exam.room'));

        $pendingSession = ExamSession::query()->where('user_id', $user->id)->where('exam_id', $exam->id)->firstOrFail();

        $this->assertNull($pendingSession->started_at);
        $this->assertNull($pendingSession->expires_at);
        $this->assertSame(0, $pendingSession->snapshots()->count());

        $roomResponse = $this->actingAs($user)->get(route('exam.room'));

        $roomResponse->assertOk();
        $roomResponse->assertSee('active: true', false);

        $startedSession = ExamSession::query()->where('user_id', $user->id)->where('exam_id', $exam->id)->firstOrFail();

        $this->assertSame('2026-08-28 10:00:00', $startedSession->started_at?->format('Y-m-d H:i:s'));
        $this->assertSame('2026-08-28 11:00:00', $startedSession->expires_at?->format('Y-m-d H:i:s'));
        $this->assertSame(1, $startedSession->snapshots()->count());

        $this->actingAs($user)->get(route('exam.room'));

        $reloadedSession = ExamSession::query()->where('user_id', $user->id)->where('exam_id', $exam->id)->firstOrFail();
        $this->assertSame(1, $reloadedSession->snapshots()->count());

        Carbon::setTestNow();
    }
}
