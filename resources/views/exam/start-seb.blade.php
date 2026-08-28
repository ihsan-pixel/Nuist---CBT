<x-exam-layout>
    <div class="min-h-screen bg-slate-950 text-white">
        <div class="mx-auto flex min-h-screen max-w-6xl items-center px-4 py-10 sm:px-6 lg:px-8">
            <div class="grid w-full gap-6 lg:grid-cols-[minmax(0,1.1fr)_minmax(340px,0.9fr)]">
                <section class="rounded-[2rem] border border-white/10 bg-white/5 p-8 shadow-2xl backdrop-blur">
                    <p class="text-xs font-semibold uppercase tracking-[0.35em] text-emerald-300">Safe Exam Browser</p>
                    <h1 class="mt-4 text-4xl font-bold leading-tight text-white">{{ $exam->title }}</h1>
                    <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-300">{{ $exam->description }}</p>

                    <div class="mt-8 grid gap-3 sm:grid-cols-3">
                        <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                            <div class="text-xs uppercase tracking-[0.25em] text-slate-400">Durasi</div>
                            <div class="mt-2 text-2xl font-bold text-white">{{ $exam->duration_minutes }}m</div>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                            <div class="text-xs uppercase tracking-[0.25em] text-slate-400">Status</div>
                            <div class="mt-2 text-2xl font-bold text-emerald-300">{{ $sebVerified ? 'Terverifikasi' : 'Siap' }}</div>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                            <div class="text-xs uppercase tracking-[0.25em] text-slate-400">Mode</div>
                            <div class="mt-2 text-2xl font-bold text-sky-300">Khusus Ujian</div>
                        </div>
                    </div>

                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="{{ $configDownloadUrl }}" class="inline-flex items-center rounded-2xl bg-white px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-slate-100">
                            Unduh Konfigurasi SEB
                        </a>
                        <a href="{{ $sebEntryUrl }}" class="inline-flex items-center rounded-2xl border border-emerald-300 px-5 py-3 text-sm font-semibold text-emerald-100 transition hover:bg-emerald-400/10">
                            Masuk ke Ujian
                        </a>
                    </div>

                    <div class="mt-8 rounded-2xl border border-emerald-400/20 bg-emerald-400/10 p-5 text-sm leading-7 text-emerald-50">
                        <p class="font-semibold">Instruksi singkat</p>
                        <ol class="mt-3 list-decimal space-y-2 pl-5">
                            <li>Unduh konfigurasi SEB lalu buka file tersebut dengan aplikasi Safe Exam Browser.</li>
                            <li>Pastikan aplikasi SEB menampilkan halaman ini, bukan browser biasa.</li>
                            <li>Jika verifikasi berhasil, tombol masuk ujian akan tersedia.</li>
                        </ol>
                    </div>
                </section>

                <aside class="space-y-4 rounded-[2rem] border border-white/10 bg-slate-900/80 p-6 shadow-xl backdrop-blur">
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-5">
                        <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Status SEB</p>
                        <div class="mt-4 space-y-3 text-sm text-slate-200">
                            <div class="flex items-center justify-between gap-3">
                                <span>SEB terdeteksi</span>
                                <span class="font-semibold {{ $sebDetected ? 'text-emerald-300' : 'text-amber-300' }}">{{ $sebDetected ? 'Ya' : 'Tidak' }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <span>SEB terverifikasi</span>
                                <span class="font-semibold {{ $sebVerified ? 'text-emerald-300' : 'text-amber-300' }}">{{ $sebVerified ? 'Ya' : 'Belum' }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <span>Konfigurasi</span>
                                <span class="font-semibold text-sky-300">.seb siap</span>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-white/5 p-5">
                        <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Panduan</p>
                        <p class="mt-3 text-sm leading-7 text-slate-300">
                            Gunakan halaman ini sebagai titik masuk resmi. Sistem akan menolak akses jika Config Key atau handshake SEB tidak sesuai.
                        </p>
                    </div>
                </aside>
            </div>
        </div>
    </div>
</x-exam-layout>
