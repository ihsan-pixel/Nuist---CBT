<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Hasil Ujian</h2>
                <p class="text-sm text-gray-500">{{ $exam->title }}</p>
            </div>
            <div class="flex items-center gap-4">
                <a class="text-indigo-600 hover:underline" href="{{ route('admin.exams.results.export', $exam) }}">Export CSV</a>
                <a class="text-indigo-600 hover:underline" href="{{ route('admin.exams.edit', $exam) }}">Kembali Edit</a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="rounded-lg bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900">{{ $exam->title }}</h3>
                <p class="text-sm text-gray-500">{{ $exam->description }}</p>
            </div>

            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50 text-left text-sm text-gray-500">
                        <tr>
                            <th class="py-3 px-4">Peserta</th>
                            <th class="py-3 px-4">Skor</th>
                            <th class="py-3 px-4">Benar</th>
                            <th class="py-3 px-4">Selesai</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($sessions as $row)
                            @php
                                $isLockedByViolation = $row['session']->warning_count >= 3;
                            @endphp
                            <tr>
                                <td class="py-3 px-4 text-gray-900">{{ $row['session']->user->name }}</td>
                                <td class="py-3 px-4">{{ $row['score'] }}%</td>
                                <td class="py-3 px-4">{{ $row['correct'] }}/{{ $row['total'] }}</td>
                                <td class="py-3 px-4">{{ optional($row['session']->finished_at)->format('d M Y H:i') ?? '-' }}</td>
                                <td class="py-3 px-4">
                                    @if ($isLockedByViolation)
                                        Ditutup otomatis
                                    @else
                                        {{ $row['session']->is_locked ? 'Berjalan' : 'Selesai' }}
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <a class="text-indigo-600 hover:underline" href="{{ route('admin.sessions.show', $row['session']) }}">Detail</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
