<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Exam;
use App\Models\ExamSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $target = $this->redirectTarget($request);

        return redirect()->intended($target);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function redirectTarget(Request $request): string
    {
        if ($this->isSebRequest($request)) {
            return $this->sebRedirectTarget($request);
        }

        return route('dashboard', absolute: false);
    }

    private function sebRedirectTarget(Request $request): string
    {
        $exam = Exam::query()->where('is_active', true)->orderBy('id')->first();

        if (! $exam) {
            return route('dashboard', absolute: false);
        }

        $session = ExamSession::query()
            ->where('user_id', $request->user()->id)
            ->where('exam_id', $exam->id)
            ->whereNotNull('finished_at')
            ->whereHas('snapshots', fn ($query) => $query->whereNotNull('selected_answer'))
            ->latest('id')
            ->first();

        if ($session) {
            return route('exam.completed', absolute: false);
        }

        return route('exam.room', absolute: false);
    }

    private function isSebRequest(Request $request): bool
    {
        return $request->session()->get('seb.verified') || $request->header('X-SafeExamBrowser-ConfigKeyHash');
    }
}
