<?php

use App\Http\Controllers\Admin\ExamAdminController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\ProfileController;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\SebAuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->middleware('exam.lock');

Route::middleware(['auth', 'exam.lock', 'exam.headers'])->group(function () {
    Route::get('/dashboard', function () {
        $user = request()->user();
        $totalUsers = User::query()->count();
        $roleCounts = User::query()
            ->selectRaw('role, COUNT(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role')
            ->all();
        $activeExam = Exam::query()->where('is_active', true)->latest('id')->first();
        $latestSession = ExamSession::query()
            ->with('exam')
            ->where('user_id', $user->id)
            ->latest('id')
            ->first();
        $sebLatestFailure = SebAuditLog::query()->where('status', 'failed')->latest('id')->first();
        $sebEnabled = (bool) Exam::query()->where('is_active', true)->exists();

        return view('dashboard', compact('user', 'totalUsers', 'roleCounts', 'activeExam', 'latestSession', 'sebLatestFailure', 'sebEnabled'));
    })->middleware('verified')->name('dashboard');

    Route::get('/exam', [ExamController::class, 'room'])->name('exam.room');
    Route::get('/exam/start-seb', [ExamController::class, 'startSeb'])->name('exam.start-seb');
    Route::get('/exam/seb', [ExamController::class, 'seb'])
        ->middleware('seb.config')
        ->name('exam.seb');
    Route::get('/exam/seb-config', [ExamController::class, 'sebConfig'])->name('exam.seb-config');
    Route::post('/exam/seb-handshake', [ExamController::class, 'sebHandshake'])->name('exam.seb-handshake');
    Route::post('/exam/start', [ExamController::class, 'start'])->name('exam.start');
    Route::post('/exam/answer', [ExamController::class, 'answer'])->name('exam.answer');
    Route::post('/exam/finish', [ExamController::class, 'finish'])->name('exam.finish');
    Route::post('/exam/heartbeat', [ExamController::class, 'heartbeat'])->name('exam.heartbeat');
    Route::post('/exam/refresh-session', [ExamController::class, 'refreshSession'])->name('exam.refresh-session');
    Route::post('/exam/violation', [ExamController::class, 'violation'])->name('exam.violation');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'can:access-exam-panel', 'exam.lock', 'exam.headers'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/exams', [ExamAdminController::class, 'index'])->name('exams.index');
    Route::get('/exams/create', [ExamAdminController::class, 'create'])->name('exams.create');
    Route::post('/exams', [ExamAdminController::class, 'store'])->name('exams.store');
    Route::get('/exams/{exam}', [ExamAdminController::class, 'edit'])->name('exams.edit');
    Route::put('/exams/{exam}', [ExamAdminController::class, 'update'])->name('exams.update');
    Route::delete('/exams/{exam}', [ExamAdminController::class, 'destroy'])->name('exams.destroy');
    Route::post('/exams/{exam}/questions', [ExamAdminController::class, 'storeQuestion'])->name('exams.questions.store');
    Route::delete('/questions/{question}', [ExamAdminController::class, 'destroyQuestion'])->name('questions.destroy');
    Route::post('/questions/{question}/options', [ExamAdminController::class, 'storeOption'])->name('questions.options.store');
    Route::delete('/options/{option}', [ExamAdminController::class, 'destroyOption'])->name('options.destroy');
    Route::get('/exams/{exam}/results', [ExamAdminController::class, 'results'])->name('exams.results');
    Route::get('/exams/{exam}/results/export', [ExamAdminController::class, 'exportResults'])->name('exams.results.export');
    Route::get('/sessions/{session}', [ExamAdminController::class, 'sessionDetail'])->name('sessions.show');
    Route::get('/sessions/{session}/export', [ExamAdminController::class, 'exportSession'])->name('sessions.export');
});

Route::middleware(['auth', 'can:access-admin', 'exam.lock', 'exam.headers'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [UserAdminController::class, 'index'])->name('users.index');
    Route::post('/users', [UserAdminController::class, 'store'])->name('users.store');
    Route::patch('/users/{user}', [UserAdminController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserAdminController::class, 'destroy'])->name('users.destroy');
});

Route::middleware(['auth', 'can:access-admin', 'exam.lock', 'exam.headers'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
    });

require __DIR__.'/auth.php';
