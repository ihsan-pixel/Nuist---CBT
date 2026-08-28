<?php

namespace Database\Seeders;

use App\Models\Exam;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $dummyUsers = [
            [
                'name' => 'Super Admin',
                'email' => 'admin@example.com',
                'role' => 'super_admin',
            ],
            [
                'name' => 'Panitia CBT',
                'email' => 'panitia@example.com',
                'role' => 'panitia',
            ],
            [
                'name' => 'Peserta Demo',
                'email' => 'peserta@example.com',
                'role' => 'peserta',
            ],
            [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'role' => 'peserta',
            ],
        ];

        foreach ($dummyUsers as $userData) {
            User::query()->updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('password'),
                    'role' => $userData['role'],
                    'email_verified_at' => now(),
                ]
            );
        }

        $exam = Exam::query()->updateOrCreate(
            ['title' => 'CBT Demo'],
            [
                'description' => 'Ujian contoh untuk mode penguncian browser.',
                'duration_minutes' => 60,
                'is_active' => true,
            ]
        );

        if ($exam->questions()->count() === 0) {
            $questions = [
                [
                    'question_text' => 'Apa fungsi utama mode ujian terkunci?',
                    'sort_order' => 1,
                    'options' => [
                        ['A', 'Membuka halaman lain lebih cepat', false],
                        ['B', 'Menjaga peserta tetap di halaman ujian', true],
                        ['C', 'Menonaktifkan login aplikasi', false],
                    ],
                ],
                [
                    'question_text' => 'Saat tab hilang fokus, sistem sebaiknya...',
                    'sort_order' => 2,
                    'options' => [
                        ['A', 'Mencatat pelanggaran dan memberi peringatan', true],
                        ['B', 'Menghapus semua jawaban', false],
                        ['C', 'Menutup aplikasi server', false],
                    ],
                ],
                [
                    'question_text' => 'Untuk proteksi paling kuat, CBT web perlu dipadukan dengan...',
                    'sort_order' => 3,
                    'options' => [
                        ['A', 'Safe Exam Browser atau kiosk mode', true],
                        ['B', 'Mode gelap', false],
                        ['C', 'Cache browser lebih besar', false],
                    ],
                ],
            ];

            foreach ($questions as $questionData) {
                $question = $exam->questions()->create([
                    'question_text' => $questionData['question_text'],
                    'sort_order' => $questionData['sort_order'],
                ]);

                foreach ($questionData['options'] as $optionData) {
                    $question->options()->create([
                        'option_label' => $optionData[0],
                        'option_text' => $optionData[1],
                        'is_correct' => $optionData[2],
                    ]);
                }
            }
        }
    }
}
