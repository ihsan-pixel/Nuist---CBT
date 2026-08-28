<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\ExamViolation;
use App\Models\SebAuditLog;
use App\Support\SebConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ExamController extends Controller
{
    private const VIOLATION_LIMIT = 3;

    public function room(Request $request): View
    {
        $exam = Exam::query()
            ->with(['questions.options' => fn ($query) => $query->orderBy('option_label')])
            ->where('is_active', true)
            ->orderBy('id')
            ->first();

        abort_unless($exam, 404, 'Belum ada ujian aktif.');

        $session = $this->currentSession($request, $exam);
        $startedAt = $session?->started_at;
        $endsAt = $session?->expires_at;
        $remainingSeconds = $endsAt ? (int) max(0, round(now()->diffInSeconds($endsAt, false))) : null;
        $sebDetected = (bool) ($request->header('X-SafeExamBrowser-ConfigKeyHash') || $request->session()->get('seb.verified'));

        return view('exam.room', [
            'exam' => $exam,
            'session' => $session,
            'hasStarted' => (bool) $startedAt && ! $session?->finished_at,
            'hasEnded' => (bool) $session?->finished_at,
            'questions' => $exam->questions->sortBy('sort_order')->values(),
            'startedAt' => $startedAt,
            'endsAt' => $endsAt,
            'remainingSeconds' => $remainingSeconds,
            'warningCount' => $session?->warning_count ?? 0,
            'savedAnswers' => $session
                ? $session->snapshots()->pluck('selected_answer', 'exam_question_id')->all()
                : [],
            'isExpired' => (bool) ($endsAt && now()->greaterThanOrEqualTo($endsAt)),
            'sebDetected' => $sebDetected,
            'sebVerified' => (bool) $request->session()->get('seb.verified'),
        ]);
    }

    public function seb(Request $request): View
    {
        $exam = Exam::query()
            ->with(['questions.options' => fn ($query) => $query->orderBy('option_label')])
            ->where('is_active', true)
            ->orderBy('id')
            ->first();

        abort_unless($exam, 404, 'Belum ada ujian aktif.');

        $session = $this->currentSession($request, $exam);
        $startedAt = $session?->started_at;
        $endsAt = $session?->expires_at;
        $remainingSeconds = $endsAt ? (int) max(0, round(now()->diffInSeconds($endsAt, false))) : null;
        $sebDetected = (bool) ($request->header('X-SafeExamBrowser-ConfigKeyHash') || $request->session()->get('seb.verified'));

        return view('exam.room', [
            'exam' => $exam,
            'session' => $session,
            'hasStarted' => (bool) $startedAt && ! $session?->finished_at,
            'questions' => $exam->questions->sortBy('sort_order')->values(),
            'startedAt' => $startedAt,
            'endsAt' => $endsAt,
            'remainingSeconds' => $remainingSeconds,
            'warningCount' => $session?->warning_count ?? 0,
            'savedAnswers' => $session
                ? $session->snapshots()->pluck('selected_answer', 'exam_question_id')->all()
                : [],
            'sebMode' => true,
            'sebDetected' => $sebDetected,
            'sebVerified' => (bool) $request->session()->get('seb.verified'),
        ]);
    }

    public function startSeb(Request $request): View
    {
        $exam = Exam::query()
            ->with(['questions.options' => fn ($query) => $query->orderBy('option_label')])
            ->where('is_active', true)
            ->orderBy('id')
            ->first();

        abort_unless($exam, 404, 'Belum ada ujian aktif.');

        $session = $this->currentSession($request, $exam);

        return view('exam.start-seb', [
            'exam' => $exam,
            'session' => $session,
            'sebVerified' => (bool) $request->session()->get('seb.verified'),
            'sebDetected' => (bool) ($request->header('X-SafeExamBrowser-ConfigKeyHash') || $request->session()->get('seb.verified')),
            'configDownloadUrl' => route('exam.seb-config'),
            'sebEntryUrl' => route('exam.seb'),
        ]);
    }

    public function sebConfig(Request $request)
    {
        $exam = Exam::query()->where('is_active', true)->orderBy('id')->firstOrFail();
        $settings = SebConfig::settings($request, $exam);
        $plist = SebConfig::plistXml($settings);
        $sebBinary = SebConfig::sebBinary($plist);
        $filename = Str::slug($exam->title ?: 'nuist-cbt').'.seb';

        return response($sebBinary, 200, [
            'Content-Type' => 'application/seb',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Content-Length' => (string) strlen($sebBinary),
            'Cache-Control' => 'private, no-store, no-cache, must-revalidate',
        ]);
    }

    public function sebHandshake(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'browser_exam_key' => ['required', 'string', 'max:255'],
            'config_key' => ['required', 'string', 'max:255'],
        ]);

        $exam = Exam::query()->where('is_active', true)->orderBy('id')->firstOrFail();
        $settings = SebConfig::settings($request, $exam);
        $expectedConfigKey = SebConfig::configKey($settings);

        if (! hash_equals($expectedConfigKey, $data['config_key'])) {
            SebAuditLog::query()->create([
                'user_id' => $request->user()->id,
                'exam_id' => $exam->id,
                'event_type' => 'seb_handshake_failed',
                'status' => 'failed',
                'message' => 'Safe Exam Browser Config Key tidak valid.',
                'meta' => [
                    'received_config_key' => $data['config_key'],
                    'received_browser_exam_key' => $data['browser_exam_key'],
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ],
            ]);

            abort(403, 'Safe Exam Browser Config Key tidak valid.');
        }

        $request->session()->put('seb.browser_exam_key', $data['browser_exam_key']);
        $request->session()->put('seb.config_key', $data['config_key']);
        $request->session()->put('seb.verified', true);

        SebAuditLog::query()->create([
            'user_id' => $request->user()->id,
            'exam_id' => $exam->id,
            'event_type' => 'seb_handshake_success',
            'status' => 'success',
            'message' => 'Safe Exam Browser berhasil terverifikasi.',
            'meta' => [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ],
        ]);

        return redirect()->route('exam.seb')->with('status', 'Safe Exam Browser terverifikasi.');
    }

    public function start(Request $request): RedirectResponse
    {
        $exam = Exam::query()->where('is_active', true)->orderBy('id')->firstOrFail();
        $session = $this->currentSession($request, $exam);

        if ($session && $session->started_at && ! $session->finished_at) {
            return redirect()->route('exam.room')->with('status', 'Sesi ujian sudah berjalan.');
        }

        DB::transaction(function () use ($request, $exam) {
            $session = ExamSession::updateOrCreate(
                [
                    'user_id' => $request->user()->id,
                    'exam_id' => $exam->id,
                ],
                [
                    'started_at' => now(),
                    'expires_at' => now()->addMinutes($exam->duration_minutes),
                    'finished_at' => null,
                    'warning_count' => 0,
                    'is_locked' => true,
                ]
            );

            $session->snapshots()->delete();

            foreach ($exam->questions()->with('options')->orderBy('sort_order')->get() as $question) {
                $session->snapshots()->create([
                    'exam_question_id' => $question->id,
                    'sort_order' => $question->sort_order,
                    'question_text' => $question->question_text,
                    'option_snapshot' => $question->options
                        ->sortBy('option_label')
                        ->values()
                        ->map(fn ($option) => [
                            'option_label' => $option->option_label,
                            'option_text' => $option->option_text,
                        ])
                        ->all(),
                    'selected_answer' => null,
                ]);
            }
        });

        $request->session()->put('exam.active', true);

        return redirect()
            ->route('exam.room')
            ->with('status', 'Sesi ujian dimulai. Navigasi dibatasi sampai ujian selesai.');
    }

    public function answer(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'question_id' => ['required', 'integer'],
            'answer' => ['required', 'string', 'max:255'],
        ]);

        $exam = Exam::query()->where('is_active', true)->firstOrFail();
        $session = $this->currentSession($request, $exam);

        abort_unless($session && $session->is_locked && ! $session->finished_at, 403, 'Sesi ujian tidak aktif.');

        $snapshot = $session->snapshots()->where('exam_question_id', $data['question_id'])->firstOrFail();
        $snapshot->forceFill(['selected_answer' => $data['answer']])->save();

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'question_id' => $data['question_id'],
                'answer' => $data['answer'],
            ]);
        }

        return back()->with('status', 'Jawaban tersimpan.');
    }

    public function finish(Request $request): RedirectResponse
    {
        $exam = Exam::query()->where('is_active', true)->first();

        if ($exam) {
            $session = $this->currentSession($request, $exam);

            if ($session) {
                $session->forceFill([
                    'finished_at' => now(),
                    'is_locked' => false,
                ])->save();
            }
        }

        $request->session()->forget('exam.active');

        return redirect()
            ->route('dashboard')
            ->with('status', 'Sesi ujian selesai dan penguncian dibuka.');
    }

    private function forceFinish(Request $request, ExamSession $session, string $reason): RedirectResponse
    {
        $session->forceFill([
            'finished_at' => now(),
            'is_locked' => false,
        ])->save();

        $request->session()->forget('exam.active');

        return redirect()
            ->route('dashboard')
            ->with('status', $reason);
    }

    public function heartbeat(Request $request): RedirectResponse
    {
        $exam = Exam::query()->where('is_active', true)->firstOrFail();
        $session = $this->currentSession($request, $exam);

        if (! $session || $session->finished_at) {
            return redirect()->route('exam.room');
        }

        $expired = $session->expires_at && now()->greaterThanOrEqualTo($session->expires_at);

        if ($expired) {
            return $this->finish($request);
        }

        return back();
    }

    public function refreshSession(Request $request): RedirectResponse
    {
        $exam = Exam::query()->where('is_active', true)->firstOrFail();
        $session = $this->currentSession($request, $exam);

        abort_unless($session && $session->is_locked && ! $session->finished_at, 403, 'Sesi ujian tidak aktif.');

        return back();
    }

    public function violation(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'string', 'max:50'],
            'message' => ['nullable', 'string', 'max:500'],
            'meta' => ['nullable', 'array'],
        ]);

        $exam = Exam::query()->where('is_active', true)->firstOrFail();
        $session = $this->currentSession($request, $exam);

        abort_unless($session && $session->is_locked && ! $session->finished_at, 403, 'Sesi ujian tidak aktif.');

        $violation = ExamViolation::query()->create([
            'exam_session_id' => $session->id,
            'type' => $data['type'],
            'message' => $data['message'] ?? null,
            'meta' => $data['meta'] ?? [],
        ]);

        $newWarningCount = $session->warning_count + 1;

        $session->forceFill([
            'warning_count' => $newWarningCount,
        ])->save();

        if ($newWarningCount >= self::VIOLATION_LIMIT) {
            $session->forceFill([
                'finished_at' => now(),
                'is_locked' => false,
            ])->save();

            $request->session()->forget('exam.active');

            return response()->json([
                'ok' => true,
                'warning_count' => $newWarningCount,
                'violation_id' => $violation->id,
                'locked' => true,
                'redirect' => route('dashboard'),
                'reason' => 'Sesi ditutup otomatis karena batas pelanggaran terlampaui.',
            ], 423, [
                'X-Redirect-To' => route('dashboard'),
            ]);
        }

        return response()->json([
            'ok' => true,
            'warning_count' => $newWarningCount,
            'violation_id' => $violation->id,
        ]);
    }

    private function currentSession(Request $request, Exam $exam): ?ExamSession
    {
        return ExamSession::query()
            ->with('snapshots')
            ->where('user_id', $request->user()->id)
            ->where('exam_id', $exam->id)
            ->first();
    }

    private function sebStatusSummary(Request $request, ?Exam $exam = null): array
    {
        $exam ??= Exam::query()->where('is_active', true)->orderBy('id')->first();

        if (! $exam) {
            return [
                'enabled' => false,
                'verified' => false,
                'handshakeOk' => false,
                'latestFailure' => null,
            ];
        }

        $latestFailure = SebAuditLog::query()
            ->where('exam_id', $exam->id)
            ->where('status', 'failed')
            ->latest('id')
            ->first();

        return [
            'enabled' => true,
            'verified' => (bool) $request->session()->get('seb.verified'),
            'handshakeOk' => (bool) $request->session()->get('seb.verified'),
            'latestFailure' => $latestFailure,
        ];
    }
}
