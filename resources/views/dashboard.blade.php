<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-emerald-700">NUIST CBT</p>
                <h2 class="mt-2 text-2xl font-semibold leading-tight text-slate-900 sm:text-3xl">
                    Selamat datang, {{ $user->name }}
                </h2>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
                    Kelola ujian, peserta, dan keamanan SEB dalam satu dashboard yang ringkas dan mudah dipindai.
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                <div class="flex items-center gap-3 text-sm text-slate-600">
                    <svg class="h-5 w-5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M5 11h14M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z" />
                    </svg>
                    <span>{{ now()->translatedFormat('l, d F Y') }}</span>
                </div>
                <div class="mt-2 flex items-center gap-2 text-sm font-medium text-emerald-700">
                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                    <span>Sistem normal</span>
                </div>
            </div>
        </div>
    </x-slot>

    @php
        $examStatusLabel = $activeExam ? ($latestSession?->started_at ? ($latestSession?->finished_at ? 'Selesai' : 'Berjalan') : 'Belum dimulai') : 'Belum ada ujian';
        $sebStatusLabel = $sebEnabled ? 'Aktif' : 'Tidak aktif';
        $sebVerificationLabel = session('seb.verified') ? 'Terverifikasi' : 'Belum verifikasi';
        $appVersion = $appSettings->app_version ?? config('app.version', '1.0.0');
    @endphp

    <div class="bg-slate-50">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            <section class="grid gap-4 lg:grid-cols-3">
                <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-start gap-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl border border-emerald-100 bg-emerald-50 text-emerald-700">
                            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4zm7 9a7 7 0 1 0-14 0" />
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-slate-500">Profil Akun</p>
                            <h3 class="mt-2 truncate text-2xl font-semibold text-slate-900">{{ $user->name }}</h3>
                            <div class="mt-4 space-y-2 text-sm text-slate-600">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-500">@</span>
                                    <span class="truncate">{{ $user->email }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-500">#</span>
                                    <span>Kode peserta: <span class="font-semibold text-slate-900">{{ $user->participant_code ?? '-' }}</span></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-500">⚙</span>
                                    <span>Role: <span class="font-semibold text-slate-900">{{ $user->role?->value ?? $user->role }}</span></span>
                                </div>
                            </div>

                            <div class="mt-5">
                                <a href="{{ route('profile.edit') }}" class="inline-flex items-center rounded-xl border border-emerald-300 px-4 py-2.5 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-50">
                                    Edit Profil
                                </a>
                            </div>
                        </div>
                    </div>
                </article>

                <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-start gap-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl border border-sky-100 bg-sky-50 text-sky-700">
                            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m-6 4h6m-6 4h6M5 3h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z" />
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-slate-500">Ujian Aktif</p>
                            <h3 class="mt-2 text-xl font-semibold uppercase tracking-wide text-slate-900">
                                {{ $activeExam?->title ?? 'Belum ada ujian' }}
                            </h3>
                            <div class="mt-4 flex flex-wrap gap-2">
                                <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                    {{ $examStatusLabel }}
                                </span>
                                <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-600">
                                    Durasi {{ $activeExam?->duration_minutes ? $activeExam->duration_minutes.' menit' : '-' }}
                                </span>
                            </div>
                            <div class="mt-4 text-sm text-slate-600">
                                <p>Sesi terakhir: <span class="font-semibold text-slate-900">{{ $latestSession?->started_at?->format('d M Y H:i') ?? '-' }}</span></p>
                            </div>
                        </div>
                    </div>
                </article>

                <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-start gap-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl border border-emerald-100 bg-emerald-50 text-emerald-700">
                            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 22s8-4 8-10V6l-8-4-8 4v6c0 6 8 10 8 10z" />
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-slate-500">Keamanan SEB</p>
                            <h3 class="mt-2 text-2xl font-semibold text-slate-900">{{ $sebStatusLabel }}</h3>
                            <div class="mt-4 flex flex-wrap gap-2">
                                <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                    {{ $sebEnabled ? 'Aktif' : 'Nonaktif' }}
                                </span>
                                <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">
                                    {{ $sebVerificationLabel }}
                                </span>
                            </div>
                            <div class="mt-4 text-sm text-slate-600">
                                <p>Audit gagal terakhir: <span class="font-semibold text-slate-900">{{ $sebLatestFailure?->created_at?->format('d M Y H:i') ?? '-' }}</span></p>
                            </div>
                        </div>
                    </div>
                </article>
            </section>

            <section class="mt-6 grid gap-4 lg:grid-cols-[1.3fr_0.9fr]">
                <article class="rounded-3xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-white p-6 shadow-sm">
                    <div class="flex flex-col gap-6 lg:flex-row lg:items-center">
                        <div class="flex h-28 w-28 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                            <svg class="h-14 w-14" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m4-3.5V6a2 2 0 0 0-2-2c-3 0-5.5-1.5-6-2-0.5 0.5-3 2-6 2a2 2 0 0 0-2 2v2.5c0 6 4.3 10.4 8 12.5 3.7-2.1 8-6.5 8-12.5z" />
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold uppercase tracking-[0.28em] text-emerald-700">Siap memulai ujian?</p>
                            <h3 class="mt-3 text-2xl font-semibold text-slate-900">Mulai dengan SEB untuk menjaga sesi tetap aman.</h3>
                            <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">
                                Pastikan perangkat Anda sudah terverifikasi. Gunakan tombol utama di bawah untuk masuk ke alur ujian yang terkunci.
                            </p>

                            <div class="mt-5 flex flex-wrap gap-3">
                                <a href="{{ route('exam.start-seb') }}" class="inline-flex items-center justify-center rounded-2xl bg-emerald-700 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-600">
                                    Mulai dengan SEB
                                </a>
                                <a href="{{ route('exam.room') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                    Buka Ruang Ujian
                                </a>
                                <a href="{{ route('exam.seb-config') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                    Unduh SEB
                                </a>
                            </div>
                        </div>
                    </div>
                </article>

                <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-50 text-emerald-700">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11v5m0 4h.01M12 3a10 10 0 1 0 10 10A10 10 0 0 0 12 3z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900">Informasi Penting</h3>
                    </div>

                    <div class="mt-5 space-y-4">
                        <div class="flex gap-3">
                            <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-emerald-700">✓</div>
                            <div>
                                <p class="text-sm font-semibold text-slate-900">Verifikasi SEB sebelum ujian</p>
                                <p class="mt-1 text-sm leading-6 text-slate-600">Pastikan perangkat Anda terverifikasi agar ujian berjalan lancar.</p>
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-emerald-700">✓</div>
                            <div>
                                <p class="text-sm font-semibold text-slate-900">Navigasi dibatasi saat ujian</p>
                                <p class="mt-1 text-sm leading-6 text-slate-600">Selama ujian, Anda tidak dapat keluar dari aplikasi atau membuka halaman lain.</p>
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-emerald-700">✓</div>
                            <div>
                                <p class="text-sm font-semibold text-slate-900">Akses cepat tersedia</p>
                                <p class="mt-1 text-sm leading-6 text-slate-600">Edit profil, buka ruang ujian, atau unduh konfigurasi SEB dari tombol cepat.</p>
                            </div>
                        </div>
                    </div>
                </article>
            </section>
        </div>

        <footer class="border-t border-emerald-900/10 bg-emerald-950 text-emerald-50">
            <div class="mx-auto grid max-w-7xl gap-8 px-4 py-8 sm:px-6 lg:grid-cols-[1.2fr_1fr_1fr] lg:px-8">
                <div>
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10">
                            <x-application-logo class="h-8 w-8 fill-current text-white" />
                        </div>
                        <div>
                            <p class="text-lg font-semibold">NUIST CBT</p>
                            <p class="text-sm text-emerald-100/80">Sistem Ujian Berbasis Komputer LP Ma'arif NU DIY</p>
                        </div>
                    </div>
                </div>

                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.25em] text-emerald-100/70">Tautan Cepat</p>
                    <div class="mt-4 flex flex-wrap gap-x-4 gap-y-2 text-sm text-emerald-100">
                        <a href="{{ route('exam.start-seb') }}" class="hover:text-white">Panduan Ujian</a>
                        <span class="text-emerald-100/40">|</span>
                        <a href="{{ route('profile.edit') }}" class="hover:text-white">Bantuan</a>
                        <span class="text-emerald-100/40">|</span>
                        <a href="{{ route('dashboard') }}" class="hover:text-white">Dashboard</a>
                    </div>
                </div>

                <div class="text-sm text-emerald-100/90 lg:text-right">
                    <p>© {{ now()->year }} LP Ma'arif NU PWNU DIY</p>
                    <p class="mt-2">Versi {{ $appVersion }}</p>
                </div>
            </div>
        </footer>
    </div>
</x-app-layout>
