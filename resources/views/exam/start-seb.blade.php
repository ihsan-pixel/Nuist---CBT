<x-exam-layout>
    <div class="min-h-screen bg-slate-100 text-slate-900">
        <div class="mx-auto flex min-h-screen max-w-6xl items-center px-4 py-10 sm:px-6 lg:px-8">
            <div class="grid w-full gap-6 lg:grid-cols-[minmax(0,1.1fr)_minmax(340px,0.9fr)]">
                <section class="rounded-[2rem] border border-slate-200 bg-white p-8 shadow-xl">
                    <p class="text-xs font-semibold uppercase tracking-[0.35em] text-emerald-700">Safe Exam Browser</p>
                    <h1 class="mt-4 text-4xl font-bold leading-tight text-slate-900">{{ $exam->title }}</h1>
                    <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-600">{{ $exam->description }}</p>

                    <div class="mt-8 grid gap-3 sm:grid-cols-3">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-xs uppercase tracking-[0.25em] text-slate-500">Durasi</div>
                            <div class="mt-2 text-2xl font-bold text-slate-900">{{ $exam->duration_minutes }}m</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-xs uppercase tracking-[0.25em] text-slate-500">Status</div>
                            <div class="mt-2 text-2xl font-bold text-emerald-700">{{ $sebVerified ? 'Terverifikasi' : 'Siap' }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-xs uppercase tracking-[0.25em] text-slate-500">Mode</div>
                            <div class="mt-2 text-2xl font-bold text-sky-700">Khusus Ujian</div>
                        </div>
                    </div>

                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="{{ $configDownloadUrl }}" class="inline-flex items-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
                            Unduh Konfigurasi SEB
                        </a>
                        <a href="{{ $sebEntryUrl }}" class="inline-flex items-center rounded-2xl border border-emerald-300 px-5 py-3 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-50">
                            Buka Ruang SEB
                        </a>
                    </div>

                    <div class="mt-8 rounded-2xl border border-emerald-200 bg-emerald-50 p-5 text-sm leading-7 text-emerald-900">
                        <p class="font-semibold">Instruksi singkat</p>
                        <ol class="mt-3 list-decimal space-y-2 pl-5">
                            <li>Unduh konfigurasi SEB lalu buka file tersebut dengan aplikasi Safe Exam Browser.</li>
                            <li>Pastikan aplikasi SEB menampilkan halaman ini, bukan browser biasa.</li>
                            <li>Setelah masuk ke ruang SEB, sistem akan memverifikasi browser dan menyiapkan sesi ujian.</li>
                            <li>Jika perlu keluar dari SEB, gunakan shortcut <span class="font-semibold">Ctrl+Q</span> atau tombol quit yang disediakan SEB.</li>
                        </ol>
                    </div>
                </section>

                <aside class="space-y-4 rounded-[2rem] border border-slate-200 bg-white p-6 shadow-xl">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Status SEB</p>
                        <div class="mt-4 space-y-3 text-sm text-slate-700">
                            <div class="flex items-center justify-between gap-3">
                                <span>SEB terdeteksi</span>
                                <span class="font-semibold {{ $sebDetected ? 'text-emerald-700' : 'text-amber-700' }}">{{ $sebDetected ? 'Ya' : 'Tidak' }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <span>SEB terverifikasi</span>
                                <span class="font-semibold {{ $sebVerified ? 'text-emerald-700' : 'text-amber-700' }}">{{ $sebVerified ? 'Ya' : 'Belum' }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <span>Konfigurasi</span>
                                <span class="font-semibold text-sky-700">.seb siap</span>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Panduan</p>
                        <p class="mt-3 text-sm leading-7 text-slate-600">
                            Gunakan halaman ini sebagai titik masuk resmi. Sistem akan menolak akses jika Config Key atau handshake SEB tidak sesuai.
                        </p>
                    </div>
                </aside>
            </div>
        </div>
    </div>
</x-exam-layout>
