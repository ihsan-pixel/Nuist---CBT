<x-exam-layout>
    <div
        x-data="{
            active: @js($hasStarted),
            submitting: false,
            sebDetected: false,
            sebRequired: @js(! empty($sebMode)),
            sebVerified: @js((bool) session('seb.verified')),
            handshakeMessage: '',
            init() {
                this.sebDetected = Boolean(window.SafeExamBrowser?.version || window.SafeExamBrowser?.security);
                if (this.sebRequired && this.sebDetected) {
                    this.verifySeb();
                }
            },
            async verifySeb() {
                if (!window.SafeExamBrowser?.security) {
                    this.handshakeMessage = 'SEB JS API belum tersedia.';
                    return;
                }

                try {
                    if (typeof window.SafeExamBrowser.security.updateKeys === 'function') {
                        await new Promise((resolve) => {
                            window.SafeExamBrowser.security.updateKeys(() => resolve());
                        });
                    }

                    const browserExamKey = window.SafeExamBrowser.security.browserExamKey ?? '';
                    const configKey = window.SafeExamBrowser.security.configKey ?? '';

                    if (!browserExamKey || !configKey) {
                        this.handshakeMessage = 'Key SEB belum tersedia.';
                        return;
                    }

                    const response = await fetch(@js(route('exam.seb-handshake')), {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=&quot;csrf-token&quot;]')?.content ?? '',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            browser_exam_key: browserExamKey,
                            config_key: configKey,
                        }),
                    });

                    if (!response.ok) {
                        throw new Error('SEB handshake failed');
                    }

                    this.sebVerified = true;
                    this.handshakeMessage = 'SEB terverifikasi.';
                } catch (error) {
                    this.sebVerified = false;
                    this.handshakeMessage = 'Verifikasi SEB gagal.';
                }
            },
            async beginExam() {
                if (this.submitting || this.active) return;
                if (this.sebRequired && !this.sebDetected) {
                    alert('Mode Safe Exam Browser wajib dibuka lewat aplikasi SEB, bukan browser biasa.');
                    return;
                }
                if (this.sebRequired && !this.sebVerified) {
                    alert('Verifikasi SEB belum selesai.');
                    return;
                }

                this.submitting = true;

                const requestFullscreen = async () => {
                    const target = document.documentElement;
                    try {
                        if (target.requestFullscreen) {
                            await target.requestFullscreen({ navigationUI: 'hide' });
                            return true;
                        }

                        if (target.webkitRequestFullscreen) {
                            target.webkitRequestFullscreen();
                            return true;
                        }
                    } catch (error) {
                        console.warn(error);
                    }

                    return false;
                };

                if (!this.sebRequired) {
                    await requestFullscreen();
                }

                try {
                    const response = await fetch(@js(route('exam.start')), {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=&quot;csrf-token&quot;]')?.content ?? '',
                            'Accept': 'application/json',
                        },
                    });

                    if (!response.ok && response.status !== 302) {
                        throw new Error('Failed to start exam');
                    }

                    this.active = true;
                } catch (error) {
                    console.warn(error);
                    this.submitting = false;
                }
            },
        }"
    >
        @if (! empty($sebMode))
            <div class="border-b border-emerald-400/20 bg-emerald-400/10 px-4 py-3 text-sm text-emerald-100">
                <div class="mx-auto flex max-w-7xl flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <p class="font-medium">Mode Safe Exam Browser aktif. Halaman ini harus dibuka dari aplikasi SEB, bukan browser biasa.</p>
                    <a href="https://safeexambrowser.org/start/" target="_blank" rel="noreferrer" class="font-semibold text-emerald-200 underline decoration-emerald-200/60 underline-offset-4">
                        Panduan resmi SEB
                    </a>
                </div>
            </div>
        @endif

        <main x-show="!active" class="flex min-h-screen items-center justify-center px-4 py-10">
            <div class="w-full max-w-2xl rounded-3xl border border-white/10 bg-white/5 p-8 text-center shadow-2xl backdrop-blur">
                <p class="text-sm font-semibold uppercase tracking-[0.35em] text-sky-300">Ruang Ujian</p>
                <h1 class="mt-4 text-3xl font-bold text-white sm:text-4xl">{{ $exam->title }}</h1>
                <p class="mt-3 text-sm leading-6 text-slate-300">{{ $exam->description }}</p>

                <div class="mt-8 grid gap-3 rounded-2xl border border-white/10 bg-black/20 p-5 text-left text-sm text-slate-200 sm:grid-cols-2">
                    <div>
                        <div class="text-xs uppercase tracking-[0.25em] text-slate-400">Durasi</div>
                        <div class="mt-2 text-lg font-semibold text-white">{{ $exam->duration_minutes }} menit</div>
                    </div>
                    <div>
                        <div class="text-xs uppercase tracking-[0.25em] text-slate-400">Status</div>
                        <div class="mt-2 text-lg font-semibold text-emerald-300">Siap dimulai</div>
                    </div>
                </div>

                <div class="mt-8">
                    <button
                        type="button"
                        @click="beginExam()"
                        x-show="!sebRequired || sebDetected"
                        class="inline-flex items-center justify-center rounded-2xl bg-white px-6 py-3 text-sm font-semibold text-slate-950 transition hover:bg-slate-100 disabled:opacity-60"
                        :disabled="submitting"
                    >
                        <span x-show="!submitting">Mulai Ujian</span>
                        <span x-show="submitting" x-cloak>Memulai...</span>
                    </button>
                </div>

                @if (! empty($sebMode))
                    <div class="mt-6 rounded-2xl border border-amber-400/30 bg-amber-400/10 p-4 text-left text-sm text-amber-50" x-show="!sebDetected" x-cloak>
                        <p class="font-semibold">Safe Exam Browser belum terdeteksi</p>
                        <p class="mt-1 leading-6">Tutup browser ini, lalu buka kembali ujian melalui aplikasi Safe Exam Browser.</p>
                    </div>
                    <div class="mt-6 rounded-2xl border border-emerald-400/20 bg-emerald-400/10 p-4 text-left text-sm text-emerald-50" x-show="sebDetected" x-cloak>
                        <p class="font-semibold">SEB terdeteksi</p>
                        <p class="mt-1 leading-6">Anda dapat memulai ujian dalam mode aman.</p>
                    </div>
                    <div class="mt-4 rounded-2xl border border-slate-300 bg-slate-50 p-4 text-left text-sm text-slate-700" x-show="sebDetected" x-cloak>
                        <p class="font-semibold">Status verifikasi</p>
                        <p class="mt-1 leading-6" x-text="handshakeMessage || (sebVerified ? 'SEB terverifikasi.' : 'Menunggu verifikasi SEB...')"></p>
                    </div>
                @endif
            </div>
        </main>
        <template x-if="active">
            <div
                class="min-h-screen bg-slate-950 text-white"
                x-data="examLock({
                    started: true,
                    remainingSeconds: @js($remainingSeconds ?? 0),
                    violationUrl: @js(route('exam.violation')),
                    answerUrl: @js(route('exam.answer')),
                    refreshUrl: @js(route('exam.refresh-session')),
                    savedAnswers: @js($savedAnswers),
                    questionIds: @js($questions->pluck('id')->values()),
                    autoSave: true,
                    enforceFullscreen: true
                })"
                x-init="init(); $nextTick(() => enterFullscreen())"
            >
                <form id="exam-heartbeat-form" method="POST" action="{{ route('exam.heartbeat') }}" class="hidden">
                    @csrf
                </form>

                <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur">
                        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                            <div>
                                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Mode Ujian Aktif</p>
                                <h2 class="mt-2 text-2xl font-bold text-white">{{ $exam->title }}</h2>
                                <p class="mt-1 text-sm text-slate-300">{{ $exam->description }}</p>
                                @if (! empty($sebMode))
                                    <p class="mt-2 text-xs font-semibold uppercase tracking-[0.25em] text-emerald-300">Safe Exam Browser Mode</p>
                                @endif
                            </div>
                            <div class="flex flex-wrap items-center gap-3">
                                <div class="rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-950" x-text="timerLabel"></div>
                                <form method="POST" action="{{ route('exam.finish') }}">
                                    @csrf
                                    <x-secondary-button type="submit">
                                        {{ __('Selesai Ujian') }}
                                    </x-secondary-button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                @if (session('status'))
                    <div class="mx-auto mt-2 max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                            {{ session('status') }}
                        </div>
                    </div>
                @endif

                <div class="mx-auto grid max-w-7xl gap-6 px-4 py-4 sm:px-6 lg:grid-cols-[minmax(0,1.6fr)_minmax(300px,0.8fr)] lg:px-8">
                    <section class="space-y-4">
                        @foreach ($questions as $question)
                            @php
                                $selectedAnswer = data_get($savedAnswers, $question->id);
                            @endphp
                            <article class="rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-sky-300">Soal {{ $question->sort_order }}</p>
                                        <h3 class="mt-2 text-lg font-semibold text-white">{{ $question->question_text }}</h3>
                                    </div>
                                    <span data-answer-status class="rounded-full px-3 py-1 text-xs font-medium {{ $selectedAnswer ? 'bg-emerald-400/20 text-emerald-200' : 'bg-white/10 text-slate-300' }}">
                                        {{ $selectedAnswer ? 'Tersimpan' : 'Belum dijawab' }}
                                    </span>
                                </div>

                                <form method="POST" action="{{ route('exam.answer') }}" class="mt-5 space-y-3" data-autosave-form>
                                    @csrf
                                    <input type="hidden" name="question_id" value="{{ $question->id }}">
                                    <div class="grid gap-2">
                                        @foreach ($question->options as $option)
                                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-white/10 bg-black/20 px-4 py-3 transition hover:border-sky-300/50 hover:bg-white/10">
                                                <input
                                                    type="radio"
                                                    name="answer"
                                                    value="{{ $option->option_label }}"
                                                    class="text-sky-400 focus:ring-sky-500"
                                                    required
                                                    @checked($selectedAnswer === $option->option_label)
                                                >
                                                <span class="text-sm text-slate-200">{{ $option->option_label }}. {{ $option->option_text }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <div class="flex items-center justify-between gap-4">
                                        <p class="text-xs text-slate-400">Jawaban disimpan ke sesi ujian pengguna.</p>
                                        <x-primary-button class="js-manual-save">
                                            {{ __('Simpan Jawaban') }}
                                        </x-primary-button>
                                    </div>
                                </form>
                            </article>
                        @endforeach
                    </section>

                    <aside class="space-y-4">
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-5">
                            <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Status Sesi</p>
                            <div class="mt-4 space-y-3 text-sm text-slate-200">
                                <div class="flex items-center justify-between gap-3">
                                    <span>Fullscreen</span>
                                    <span x-text="fullscreenState"></span>
                                </div>
                                <div class="flex items-center justify-between gap-3">
                                    <span>Tab aktif</span>
                                    <span x-text="focusState"></span>
                                </div>
                                <div class="flex items-center justify-between gap-3">
                                    <span>Soal tersimpan</span>
                                    <span>{{ $session ? $session->snapshots->whereNotNull('selected_answer')->count() : 0 }}</span>
                                </div>
                                <div class="flex items-center justify-between gap-3">
                                    <span>Pelanggaran</span>
                                    <span>{{ $warningCount }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-amber-400/20 bg-amber-400/10 p-5 text-amber-50">
                            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-amber-200">Catatan</p>
                            <p class="mt-3 text-sm leading-6 text-amber-50/90">
                                Keluar tab, refresh, dan shortcut umum dipantau. Untuk penguncian yang lebih ketat, gunakan kiosk mode atau aplikasi pengawas khusus.
                            </p>
                        </div>
                    </aside>
                </div>
            </div>
        </template>
    </div>
</x-exam-layout>
