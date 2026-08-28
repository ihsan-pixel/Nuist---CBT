<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Admin Ujian</h2>
            <a href="{{ route('admin.exams.create') }}" class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700">
                Buat Ujian
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="space-y-4 p-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Daftar Ujian</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="text-left text-sm text-gray-500">
                                    <th class="py-2 pr-4">Judul</th>
                                    <th class="py-2 pr-4">Durasi</th>
                                    <th class="py-2 pr-4">Soal</th>
                                    <th class="py-2 pr-4">Aktif</th>
                                    <th class="py-2 pr-4"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($exams as $exam)
                                    <tr>
                                        <td class="py-3 pr-4 font-medium text-gray-900">{{ $exam->title }}</td>
                                        <td class="py-3 pr-4 text-gray-700">{{ $exam->duration_minutes }} menit</td>
                                        <td class="py-3 pr-4 text-gray-700">{{ $exam->questions_count }}</td>
                                        <td class="py-3 pr-4 text-gray-700">{{ $exam->is_active ? 'Ya' : 'Tidak' }}</td>
                                        <td class="py-3 pr-4">
                                            <a class="text-indigo-600 hover:underline" href="{{ route('admin.exams.edit', $exam) }}">Edit</a>
                                            <span class="mx-2 text-gray-300">|</span>
                                            <a class="text-indigo-600 hover:underline" href="{{ route('admin.exams.results', $exam) }}">Hasil</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
