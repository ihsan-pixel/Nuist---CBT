<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureExamLock
{
    /**
     * Allow only exam-related routes while an exam is active.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->get('exam.active')) {
            return $next($request);
        }

        $allowedRoutes = [
            'exam.room',
            'exam.start',
            'exam.answer',
            'exam.finish',
            'exam.heartbeat',
            'logout',
        ];

        if ($request->routeIs($allowedRoutes)) {
            return $next($request);
        }

        return redirect()
            ->route('exam.room')
            ->with('exam_warning', 'Sesi ujian sedang aktif. Akses halaman lain diblokir sampai ujian selesai.');
    }
}
