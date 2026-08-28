<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $appSettings->app_name ?? config('app.name', 'Laravel') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 font-sans text-slate-900 antialiased">
    <main class="flex min-h-screen items-center justify-center px-4 py-10">
        <div class="w-full max-w-3xl rounded-3xl border border-slate-200 bg-white p-8 text-center shadow-xl">
            <p class="text-sm font-semibold uppercase tracking-[0.35em] text-sky-700">Ujian Selesai</p>
            <h1 class="mt-4 text-3xl font-bold text-slate-900 sm:text-4xl">Terima kasih, {{ $user->name }}.</h1>
            <p class="mt-4 text-base leading-7 text-slate-600">
                Jawaban Anda sudah terkirim dan sesi ujian telah ditutup dengan aman.
            </p>

            <div class="mt-8 grid gap-4 sm:grid-cols-3">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <div class="text-xs uppercase tracking-[0.25em] text-slate-500">Total Soal</div>
                    <div class="mt-2 text-2xl font-bold text-slate-900">{{ $totalQuestions ?? 0 }}</div>
                </div>
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                    <div class="text-xs uppercase tracking-[0.25em] text-emerald-700">Terjawab</div>
                    <div class="mt-2 text-2xl font-bold text-emerald-700">{{ $answeredQuestions ?? 0 }}</div>
                </div>
                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4">
                    <div class="text-xs uppercase tracking-[0.25em] text-amber-700">Tidak Terjawab</div>
                    <div class="mt-2 text-2xl font-bold text-amber-700">{{ $unansweredQuestions ?? 0 }}</div>
                </div>
            </div>

            <p class="mt-2 text-sm leading-6 text-slate-500">
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
    </main>
</body>
</html>
