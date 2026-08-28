<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2">
            {{-- <p class="text-sm font-medium uppercase tracking-[0.25em] text-gray-500">Ringkasan Akun</p> --}}
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ __('Dashboard') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            <section class="grid gap-4 lg:grid-cols-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Akun Anda</p>
                    <div class="mt-4 space-y-2">
                        <h3 class="text-2xl font-bold text-slate-900">{{ $user->name }}</h3>
                        <p class="text-sm text-slate-600">Kode peserta: <span class="font-semibold text-slate-900">{{ $user->participant_code ?? '-' }}</span></p>
                        <p class="text-sm text-slate-600">Email: <span class="font-semibold text-slate-900">{{ $user->email }}</span></p>
                        <p class="text-sm text-slate-600">Role : <span class="font-semibold text-slate-900">{{ $user->role?->value ?? $user->role }}</span></p>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Status Ujian</p>
                    <div class="mt-4 space-y-2 text-sm text-slate-600">
                        <p>Ujian aktif: <span class="font-semibold text-slate-900">{{ $activeExam?->title ?? 'Belum ada' }}</span></p>
                        <p>Durasi: <span class="font-semibold text-slate-900">{{ $activeExam?->duration_minutes ? $activeExam->duration_minutes.' menit' : '-' }}</span></p>
                        <p>Sesi terakhir: <span class="font-semibold text-slate-900">{{ $latestSession?->started_at?->format('d M Y H:i') ?? '-' }}</span></p>
                        <p>Status sesi: <span class="font-semibold text-slate-900">{{ $latestSession?->finished_at ? 'Selesai' : ($latestSession?->started_at ? 'Berjalan' : 'Belum mulai') }}</span></p>
                    </div>
                    <a href="{{ route('exam.room') }}" class="mt-5 inline-flex items-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700">
                        Masuk Ruang Ujian
                    </a>
                    <a href="{{ route('exam.start-seb') }}" class="mt-3 inline-flex items-center rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-2.5 text-sm font-semibold text-emerald-800 transition hover:bg-emerald-100">
                        Mulai dengan Safe Exam Browser
                    </a>
                    <a href="{{ route('exam.seb-config') }}" class="mt-3 inline-flex items-center rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        Unduh Konfigurasi SEB
                    </a>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Status SEB</p>
                    <div class="mt-4 space-y-2 text-sm text-slate-600">
                        <p>SEB aktif: <span class="font-semibold text-slate-900">{{ $sebEnabled ? 'Ya' : 'Tidak' }}</span></p>
                        <p>Terverifikasi: <span class="font-semibold text-slate-900">{{ session('seb.verified') ? 'Ya' : 'Belum' }}</span></p>
                        <p>Audit gagal terakhir: <span class="font-semibold text-slate-900">{{ $sebLatestFailure?->created_at?->format('d M Y H:i') ?? '-' }}</span></p>
                        <p class="text-xs text-slate-500">{{ $sebLatestFailure?->message ?? 'Belum ada audit gagal.' }}</p>
                    </div>
                    <a href="{{ route('exam.start-seb') }}" class="mt-5 inline-flex items-center rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-2.5 text-sm font-semibold text-emerald-800 transition hover:bg-emerald-100">
                        Buka Start SEB
                    </a>
                </div>
            </section>

            <section class="grid gap-4 lg:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Informasi Singkat</p>
                    <div class="mt-4 space-y-3 text-sm leading-6 text-slate-600">
                        <p>Gunakan dashboard ini untuk melihat identitas akun, ringkasan pengguna, dan status ujian aktif.</p>
                        <p>Jika ujian sudah dimulai, navigasi akan dibatasi dan Anda akan diarahkan ke mode ujian penuh layar.</p>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Akses Cepat</p>
                    <div class="mt-4 flex flex-wrap gap-3">
                        <a href="{{ route('profile.edit') }}" class="inline-flex items-center rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            Edit Profil
                        </a>
                        <a href="{{ route('exam.room') }}" class="inline-flex items-center rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-500">
                            Buka Ruang Ujian
                        </a>
                        <a href="{{ route('exam.start-seb') }}" class="inline-flex items-center rounded-xl border border-emerald-300 px-4 py-2.5 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-50">
                            Start SEB
                        </a>
                        <a href="{{ route('exam.seb-config') }}" class="inline-flex items-center rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            Download SEB
                        </a>
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
