<?php

namespace App\Http\Middleware;

use App\Models\Exam;
use App\Support\SebConfig;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateSebConfigKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $exam = Exam::query()->where('is_active', true)->orderBy('id')->first();

        if (! $exam) {
            abort(404, 'Belum ada ujian aktif.');
        }

        $settings = SebConfig::settings($request, $exam);
        $configKey = SebConfig::configKey($settings);
        $expectedHash = SebConfig::requestKeyHash($request, $configKey);
        $receivedHash = strtolower((string) $request->header('X-SafeExamBrowser-ConfigKeyHash'));

        abort_unless(
            $receivedHash !== '' && hash_equals($expectedHash, $receivedHash),
            403,
            'Safe Exam Browser Config Key tidak valid.'
        );

        return $next($request);
    }
}
