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

            <div class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <p class="text-xs uppercase tracking-[0.25em] text-gray-500">Total Soal</p>
                    <p class="mt-3 text-3xl font-bold text-gray-900" data-total-questions>{{ $sessions->first()['total'] ?? $exam->questions->count() }}</p>
                </div>
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <p class="text-xs uppercase tracking-[0.25em] text-emerald-700">Terjawab</p>
                    <p class="mt-3 text-3xl font-bold text-emerald-700" data-total-answered>{{ $sessions->sum('answered') }}</p>
                </div>
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <p class="text-xs uppercase tracking-[0.25em] text-amber-700">Tidak Terjawab</p>
                    <p class="mt-3 text-3xl font-bold text-amber-700" data-total-unanswered>{{ $sessions->sum('unanswered') }}</p>
                </div>
            </div>

            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 text-sm text-gray-500">
                    <span>Pembaruan otomatis setiap 5 detik</span>
                    <span data-last-updated>Memuat data...</span>
                </div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50 text-left text-sm text-gray-500">
                        <tr>
                            <th class="py-3 px-4">Peserta</th>
                            <th class="py-3 px-4">Skor</th>
                            <th class="py-3 px-4">Terjawab</th>
                            <th class="py-3 px-4">Tidak Terjawab</th>
                            <th class="py-3 px-4">Benar</th>
                            <th class="py-3 px-4">Selesai</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100" data-results-body>
                        @foreach ($sessions as $row)
                            <tr>
                                <td class="py-3 px-4 text-gray-900">{{ $row['session']->user->name }}</td>
                                <td class="py-3 px-4">{{ $row['score'] }}%</td>
                                <td class="py-3 px-4">{{ $row['answered'] }}/{{ $row['total'] }}</td>
                                <td class="py-3 px-4">{{ $row['unanswered'] }}</td>
                                <td class="py-3 px-4">{{ $row['correct'] }}/{{ $row['total'] }}</td>
                                <td class="py-3 px-4">{{ optional($row['session']->finished_at)->format('d M Y H:i') ?? '-' }}</td>
                                <td class="py-3 px-4">
                                    @if ($row['is_locked_by_violation'])
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

    <script>
        (() => {
            const resultsUrl = @json(route('admin.exams.results.data', $exam));
            const tbody = document.querySelector('[data-results-body]');
            const totalQuestionsEl = document.querySelector('[data-total-questions]');
            const totalAnsweredEl = document.querySelector('[data-total-answered]');
            const totalUnansweredEl = document.querySelector('[data-total-unanswered]');
            const lastUpdatedEl = document.querySelector('[data-last-updated]');

            if (!tbody || !totalQuestionsEl || !totalAnsweredEl || !totalUnansweredEl || !lastUpdatedEl) {
                return;
            }

            const rowClass = 'border-b border-gray-100';

            const renderRows = (sessions) => {
                if (!sessions.length) {
                    tbody.innerHTML = '<tr><td class="px-4 py-6 text-sm text-gray-500" colspan="8">Belum ada data hasil ujian.</td></tr>';
                    return;
                }

                tbody.innerHTML = sessions.map((session) => `
                    <tr class="${rowClass}">
                        <td class="py-3 px-4 text-gray-900">${escapeHtml(session.user_name)}</td>
                        <td class="py-3 px-4">${session.score}%</td>
                        <td class="py-3 px-4">${session.answered}/${session.total}</td>
                        <td class="py-3 px-4">${session.unanswered}</td>
                        <td class="py-3 px-4">${session.correct}/${session.total}</td>
                        <td class="py-3 px-4">${session.finished_at ?? '-'}</td>
                        <td class="py-3 px-4">${escapeHtml(session.status)}</td>
                        <td class="py-3 px-4"><a class="text-indigo-600 hover:underline" href="${session.detail_url}">Detail</a></td>
                    </tr>
                `).join('');
            };

            const updateSummary = (summary) => {
                totalQuestionsEl.textContent = summary.total_questions;
                totalAnsweredEl.textContent = summary.answered;
                totalUnansweredEl.textContent = summary.unanswered;
            };

            const refresh = async () => {
                try {
                    const response = await fetch(resultsUrl, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        cache: 'no-store',
                    });

                    if (!response.ok) {
                        return;
                    }

                    const payload = await response.json();

                    renderRows(payload.sessions);
                    updateSummary(payload.summary);
                    lastUpdatedEl.textContent = `Diperbarui ${new Date().toLocaleTimeString('id-ID')}`;
                } catch (error) {
                    console.error('Gagal memuat hasil ujian terbaru.', error);
                }
            };

            const escapeHtml = (value) => {
                return String(value)
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#39;');
            };

            refresh();
            setInterval(refresh, 5000);
        })();
    </script>
</x-app-layout>
