<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ExamQuestionExcelImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_exam_question_template_can_be_downloaded(): void
    {
        $admin = User::factory()->create([
            'email_verified_at' => now(),
            'role' => UserRole::SuperAdmin,
        ]);

        $exam = Exam::query()->create([
            'title' => 'CBT Demo',
            'description' => 'Ujian contoh.',
            'duration_minutes' => 60,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.exams.questions.template', $exam));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_exam_questions_can_be_imported_from_excel_file(): void
    {
        $admin = User::factory()->create([
            'email_verified_at' => now(),
            'role' => UserRole::SuperAdmin,
        ]);

        $exam = Exam::query()->create([
            'title' => 'CBT Demo',
            'description' => 'Ujian contoh.',
            'duration_minutes' => 60,
            'is_active' => true,
        ]);

        $filePath = $this->createImportFile([
            ['sort_order', 'question_text', 'option_a_text', 'option_b_text', 'option_c_text', 'option_d_text', 'correct_answer'],
            [1, 'Apa fungsi utama mode ujian terkunci?', 'Membuka halaman lain lebih cepat', 'Menjaga peserta tetap di halaman ujian', 'Menonaktifkan login aplikasi', '', 'B'],
        ]);

        $response = $this->actingAs($admin)->post(route('admin.exams.questions.import', $exam), [
            'questions_file' => new UploadedFile($filePath, 'questions.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true),
        ]);

        $response->assertRedirect(route('admin.exams.edit', $exam));
        $response->assertSessionHas('status', 'Soal berhasil diimpor dari file Excel.');

        $question = ExamQuestion::query()->where('exam_id', $exam->id)->first();

        $this->assertNotNull($question);
        $this->assertSame('Apa fungsi utama mode ujian terkunci?', $question->question_text);
        $this->assertCount(3, $question->options);
        $this->assertSame('B', $question->options->firstWhere('is_correct', true)?->option_label);
    }

    /**
     * @param  array<int, array<int, string|int|null>>  $rows
     */
    private function createImportFile(array $rows): string
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($rows as $rowIndex => $row) {
            foreach ($row as $columnIndex => $value) {
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($columnIndex + 1).($rowIndex + 1), $value);
            }
        }

        $filePath = tempnam(sys_get_temp_dir(), 'exam-import-').'.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return $filePath;
    }
}
