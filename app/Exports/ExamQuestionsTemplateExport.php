<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExamQuestionsTemplateExport implements FromCollection, WithHeadings
{
    public function collection(): Collection
    {
        return collect([
            [
                1,
                'Apa fungsi utama mode ujian terkunci?',
                'Opsi A',
                'Opsi B',
                'Opsi C',
                'Opsi D',
                'B',
            ],
        ]);
    }

    public function headings(): array
    {
        return [
            'sort_order',
            'question_text',
            'option_a_text',
            'option_b_text',
            'option_c_text',
            'option_d_text',
            'correct_answer',
        ];
    }
}
