<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Detail Peserta</h2>
                <p class="text-sm text-gray-500">{{ $session->user->name }} - {{ $exam->title }}</p>
            </div>
            <div class="flex items-center gap-4">
                <a class="text-indigo-600 hover:underline" href="{{ route('admin.sessions.export', $session) }}">Export CSV</a>
                <a class="text-indigo-600 hover:underline" href="{{ route('admin.exams.results', $exam) }}">Kembali</a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                <div class="grid gap-4 md:grid-cols-4">
                    <div><div class="text-sm text-gray-500">Peserta</div><div class="font-semibold text-gray-900">{{ $session->user->name }}</div></div>
                    <div><div class="text-sm text-gray-500">Mulai</div><div class="font-semibold text-gray-900">{{ optional($session->started_at)->format('d M Y H:i') }}</div></div>
                    <div><div class="text-sm text-gray-500">Selesai</div><div class="font-semibold text-gray-900">{{ optional($session->finished_at)->format('d M Y H:i') ?? '-' }}</div></div>
                    <div><div class="text-sm text-gray-500">Status</div><div class="font-semibold text-gray-900">{{ $session->is_locked ? 'Berjalan' : 'Selesai' }}</div></div>
                    <div><div class="text-sm text-gray-500">Pelanggaran</div><div class="font-semibold text-gray-900">{{ $session->warning_count }}</div></div>
                </div>
            </div>

            <div class="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                <h3 class="text-lg font-semibold text-gray-900">Riwayat Pelanggaran</h3>
                <div class="mt-4 space-y-3">
                    @forelse ($session->violations as $violation)
                        <div class="rounded-md border border-gray-200 p-4">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ $violation->type }}</p>
                                    <p class="text-sm text-gray-500">{{ $violation->message }}</p>
                                </div>
                                <span class="text-xs text-gray-400">{{ $violation->created_at->format('d M Y H:i:s') }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">Belum ada pelanggaran tercatat.</p>
                    @endforelse
                </div>
            </div>

            <div class="space-y-4">
                @foreach ($details as $row)
                    <div class="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold text-indigo-600">Soal {{ $row['question']->sort_order }}</p>
                                <h3 class="text-lg font-semibold text-gray-900">{{ $row['question']->question_text }}</h3>
                            </div>
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $row['is_correct'] ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                {{ $row['is_correct'] ? 'Benar' : 'Salah' }}
                            </span>
                        </div>
                        <div class="mt-4 grid gap-2 text-sm text-gray-700">
                            <div>Jawaban peserta: <span class="font-semibold">{{ $row['selected'] ?? '-' }}</span></div>
                            <div>Jawaban benar: <span class="font-semibold">{{ $row['correct'] ?? '-' }}</span></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
