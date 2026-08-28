<x-app-layout>
    <div class="min-h-screen bg-slate-950 px-4 py-10 text-white">
        <div class="mx-auto flex min-h-[calc(100vh-5rem)] max-w-3xl items-center justify-center">
            <div class="w-full rounded-3xl border border-white/10 bg-white/5 p-8 text-center shadow-2xl backdrop-blur">
                <p class="text-sm font-semibold uppercase tracking-[0.35em] text-sky-300">Ujian Selesai</p>
                <h1 class="mt-4 text-3xl font-bold text-white sm:text-4xl">Terima kasih, {{ $user->name }}.</h1>
                <p class="mt-4 text-base leading-7 text-slate-300">
                    Jawaban Anda sudah terkirim dan sesi ujian telah ditutup dengan aman.
                </p>
                <p class="mt-2 text-sm leading-6 text-slate-400">
                    Silakan logout untuk mengakhiri sesi Safe Exam Browser dan kembali ke halaman login.
                </p>

                <div class="mt-8 flex justify-center">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-primary-button type="submit">
                            Logout
                        </x-primary-button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
