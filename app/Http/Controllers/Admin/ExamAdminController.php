<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamOption;
use App\Models\ExamQuestion;
use App\Models\ExamSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExamAdminController extends Controller
{
    public function index(): View
    {
        return view('admin.exams.index', [
            'exams' => Exam::query()->withCount('questions')->latest()->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.exams.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $exam = Exam::query()->create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'duration_minutes' => $data['duration_minutes'],
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        return redirect()->route('admin.exams.edit', $exam)->with('status', 'Ujian berhasil dibuat.');
    }

    public function edit(Exam $exam): View
    {
        $exam->load(['questions.options' => fn ($query) => $query->orderBy('option_label')]);

        return view('admin.exams.edit', compact('exam'));
    }

    public function destroy(Exam $exam): RedirectResponse
    {
        $exam->delete();

        return redirect()->route('admin.exams.index')->with('status', 'Ujian berhasil dihapus.');
    }

    public function update(Request $request, Exam $exam): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
            'questions' => ['sometimes', 'array', 'min:1'],
            'questions.*.id' => ['nullable', 'integer'],
            'questions.*.question_text' => ['required', 'string'],
            'questions.*.sort_order' => ['required', 'integer', 'min:0'],
            'questions.*.options' => ['required', 'array', 'min:2'],
            'questions.*.options.*.id' => ['nullable', 'integer'],
            'questions.*.options.*.option_label' => ['required', 'string', 'max:10'],
            'questions.*.options.*.option_text' => ['required', 'string'],
            'questions.*.options.*.is_correct' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($data, $exam) {
            $exam->update([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'duration_minutes' => $data['duration_minutes'],
                'is_active' => (bool) ($data['is_active'] ?? false),
            ]);

            if (! isset($data['questions'])) {
                return;
            }

            $keepQuestionIds = [];

            foreach ($data['questions'] as $questionData) {
                $question = ! empty($questionData['id'])
                    ? $exam->questions()->whereKey($questionData['id'])->firstOrFail()
                    : $exam->questions()->make();

                $question->fill([
                    'question_text' => $questionData['question_text'],
                    'sort_order' => $questionData['sort_order'],
                ]);
                $question->exam()->associate($exam);
                $question->save();

                $keepQuestionIds[] = $question->id;
                $keepOptionIds = [];

                foreach ($questionData['options'] as $optionData) {
                    $option = ! empty($optionData['id'])
                        ? $question->options()->whereKey($optionData['id'])->firstOrFail()
                        : $question->options()->make();

                    $option->fill([
                        'option_label' => $optionData['option_label'],
                        'option_text' => $optionData['option_text'],
                        'is_correct' => (bool) ($optionData['is_correct'] ?? false),
                    ]);
                    $option->question()->associate($question);
                    $option->save();

                    $keepOptionIds[] = $option->id;
                }

                $question->options()->whereNotIn('id', $keepOptionIds)->delete();
            }

            $exam->questions()->whereNotIn('id', $keepQuestionIds)->delete();
        });

        return redirect()->route('admin.exams.edit', $exam)->with('status', 'Ujian berhasil diperbarui.');
    }

    public function storeQuestion(Request $request, Exam $exam): RedirectResponse
    {
        $data = $request->validate([
            'question_text' => ['required', 'string'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $exam->questions()->create($data);

        return back()->with('status', 'Soal berhasil ditambahkan.');
    }

    public function storeOption(Request $request, ExamQuestion $question): RedirectResponse
    {
        $data = $request->validate([
            'option_label' => ['required', 'string', 'max:10'],
            'option_text' => ['required', 'string'],
            'is_correct' => ['nullable', 'boolean'],
        ]);

        $question->options()->create([
            'option_label' => $data['option_label'],
            'option_text' => $data['option_text'],
            'is_correct' => (bool) ($data['is_correct'] ?? false),
        ]);

        return back()->with('status', 'Opsi berhasil ditambahkan.');
    }

    public function destroyQuestion(ExamQuestion $question): RedirectResponse
    {
        $exam = $question->exam;
        $question->delete();

        return redirect()->route('admin.exams.edit', $exam)->with('status', 'Soal berhasil dihapus.');
    }

    public function destroyOption(ExamOption $option): RedirectResponse
    {
        $question = $option->question;
        $option->delete();

        return redirect()->route('admin.exams.edit', $question->exam)->with('status', 'Opsi berhasil dihapus.');
    }

    public function results(Exam $exam): View
    {
        $exam->load('questions.options');

        $sessions = ExamSession::query()
            ->where('exam_id', $exam->id)
            ->with('user')
            ->latest('finished_at')
            ->get()
            ->map(function (ExamSession $session) use ($exam) {
                $total = $exam->questions->count();
                $correct = 0;

                foreach ($exam->questions as $question) {
                    $selected = data_get($session->answers, (string) $question->id);
                    $correctOption = $question->options->firstWhere('is_correct', true);

                    if ($correctOption && $selected === $correctOption->option_label) {
                        $correct++;
                    }
                }

                return [
                    'session' => $session,
                    'score' => $total > 0 ? round(($correct / $total) * 100, 2) : 0,
                    'correct' => $correct,
                    'total' => $total,
                ];
            });

        return view('admin.exams.results', [
            'exam' => $exam,
            'sessions' => $sessions,
        ]);
    }

    public function exportResults(Exam $exam): StreamedResponse
    {
        $exam->load('questions.options');

        $sessions = ExamSession::query()
            ->where('exam_id', $exam->id)
            ->with('user')
            ->latest('finished_at')
            ->get();

        $filename = 'hasil-ujian-'.str($exam->title)->slug('-').'-'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($exam, $sessions): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Nama', 'Email', 'Skor', 'Benar', 'Total', 'Mulai', 'Selesai', 'Status']);

            foreach ($sessions as $session) {
                $total = $exam->questions->count();
                $correct = 0;

                foreach ($exam->questions as $question) {
                    $selected = data_get($session->answers, (string) $question->id);
                    $correctOption = $question->options->firstWhere('is_correct', true);

                    if ($correctOption && $selected === $correctOption->option_label) {
                        $correct++;
                    }
                }

                $score = $total > 0 ? round(($correct / $total) * 100, 2) : 0;

                fputcsv($handle, [
                    $session->user->name,
                    $session->user->email,
                    $score,
                    $correct,
                    $total,
                    optional($session->started_at)->format('Y-m-d H:i:s'),
                    optional($session->finished_at)->format('Y-m-d H:i:s'),
                    $session->is_locked ? 'Berjalan' : 'Selesai',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportSession(ExamSession $session): StreamedResponse
    {
        $session->load(['exam.questions.options', 'user']);
        $exam = $session->exam;

        $filename = 'detail-peserta-'.str($session->user->name)->slug('-').'-'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($session, $exam): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Soal', 'Jawaban Peserta', 'Jawaban Benar', 'Status']);

            foreach ($exam->questions as $question) {
                $selected = data_get($session->answers, (string) $question->id);
                $correctOption = $question->options->firstWhere('is_correct', true);

                fputcsv($handle, [
                    $question->question_text,
                    $selected ?? '-',
                    $correctOption?->option_label ?? '-',
                    $selected === $correctOption?->option_label ? 'Benar' : 'Salah',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function sessionDetail(ExamSession $session): View
    {
        $session->load(['exam.questions.options', 'user', 'violations']);
        $exam = $session->exam;

        $details = $exam->questions->map(function ($question) use ($session) {
            $selected = data_get($session->answers, (string) $question->id);
            $correctOption = $question->options->firstWhere('is_correct', true);

            return [
                'question' => $question,
                'selected' => $selected,
                'correct' => $correctOption?->option_label,
                'is_correct' => $selected === $correctOption?->option_label,
            ];
        });

        return view('admin.exams.session-detail', [
            'session' => $session,
            'exam' => $exam,
            'details' => $details,
        ]);
    }
}
