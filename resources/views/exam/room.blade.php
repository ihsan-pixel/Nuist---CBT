<x-exam-layout>
    <script>
        window.examRoom = window.examRoom || function examRoom(config = {}) {
            return {
                active: Boolean(config.active),
                submitting: false,
                sebDetected: Boolean(config.sebDetected),
                sebRequired: Boolean(config.sebRequired),
                sebVerified: Boolean(config.sebVerified),
                handshakeMessage: config.handshakeMessage || '',
                currentQuestionIndex: 0,
                answeredQuestions: config.answeredQuestions || {},
                questions: config.questions || [],
                remainingSeconds: Number(config.remainingSeconds ?? 0),
                violationUrl: config.violationUrl || null,
                answerUrl: config.answerUrl || null,
                refreshUrl: config.refreshUrl || null,
                timerLabel: '--:--',
                timerHandle: null,
                init() {
                    const sebApiDetected = Boolean(window.SafeExamBrowser?.version || window.SafeExamBrowser?.security);
                    this.sebDetected = this.sebDetected || sebApiDetected;

                    if (this.sebRequired && this.sebDetected && typeof this.verifySeb === 'function') {
                        this.verifySeb().then(() => {
                            if (this.sebVerified && !this.active) {
                                this.beginExam();
                            }
                        });
                    }

                    const firstUnansweredIndex = this.questions.findIndex((question) => !this.answeredQuestions[String(question.id)]);
                    this.currentQuestionIndex = firstUnansweredIndex >= 0 ? firstUnansweredIndex : 0;
                    this.updateTimerLabel();
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
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
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
                    if (this.submitting || this.active) {
                        return;
                    }

                    if (this.sebRequired && !this.sebDetected && !this.sebVerified) {
                        alert('Mode Safe Exam Browser wajib dibuka lewat aplikasi SEB, bukan browser biasa.');
                        return;
                    }

                    this.submitting = true;

                    try {
                        const response = await fetch(@js(route('exam.start')), {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
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
                setCurrentQuestion(index) {
                    if (index < 0 || index >= this.questions.length) {
                        return;
                    }

                    this.currentQuestionIndex = index;
                    this.scrollToCurrentQuestion();
                    this.updateIndicatorState();
                },
                nextQuestion() {
                    if (this.currentQuestionIndex < this.questions.length - 1) {
                        this.currentQuestionIndex += 1;
                        this.scrollToCurrentQuestion();
                        this.updateIndicatorState();
                    }
                },
                previousQuestion() {
                    if (this.currentQuestionIndex > 0) {
                        this.currentQuestionIndex -= 1;
                        this.scrollToCurrentQuestion();
                        this.updateIndicatorState();
                    }
                },
                scrollToCurrentQuestion() {
                    this.$nextTick(() => {
                        const currentCard = this.$root.querySelector(`[data-question-card="${this.questions[this.currentQuestionIndex]?.id}"]`);
                        currentCard?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    });
                },
                updateIndicatorState() {
                    this.$nextTick(() => {
                        this.$root.querySelectorAll('[data-question-indicator]').forEach((button) => {
                            button.dataset.active = button.dataset.index === String(this.currentQuestionIndex);
                        });
                    });
                },
                async saveCurrentAnswer(form) {
                    const formData = new FormData(form);

                    try {
                        const response = await fetch(this.answerUrl || @js(route('exam.answer')), {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                            },
                            body: formData,
                        });

                        if (!response.ok) {
                            form.submit();
                            return;
                        }

                        const payload = await response.json();
                        this.answeredQuestions[String(payload.question_id)] = Boolean(payload.answer);
                        this.markQuestionSaved(form);
                    } catch (error) {
                        form.submit();
                    }
                },
                markQuestionSaved(form) {
                    const card = form.closest('article');
                    if (!card) {
                        return;
                    }

                    const badge = card.querySelector('[data-answer-status]');
                    if (badge) {
                        badge.textContent = 'Tersimpan';
                        badge.className = 'rounded-full px-3 py-1 text-xs font-medium bg-emerald-100 text-emerald-800';
                    }
                },
                updateTimerLabel() {
                    const minutes = Math.floor(this.remainingSeconds / 60);
                    const seconds = this.remainingSeconds % 60;
                    this.timerLabel = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                },
            };
        };
    </script>
    <div
        x-data="window.examRoom({
            active: @js($hasStarted),
            sebDetected: @js((bool) ($sebDetected ?? false)),
            sebRequired: @js(! empty($sebMode)),
            sebVerified: @js((bool) ($sebVerified ?? false)),
            handshakeMessage: '',
            answeredQuestions: @js(collect($savedAnswers)->map(fn ($answer) => filled($answer))->all()),
            questions: @js($questions->map(fn ($question) => [
                'id' => $question->id,
                'sort_order' => $question->sort_order,
                'question_text' => $question->question_text,
                'options' => $question->options->map(fn ($option) => [
                    'option_label' => $option->option_label,
                    'option_text' => $option->option_text,
                ])->values(),
            ])->values()),
            remainingSeconds: @js($remainingSeconds ?? 0),
            violationUrl: @js(route('exam.violation')),
            answerUrl: @js(route('exam.answer')),
            refreshUrl: @js(route('exam.refresh-session')),
        })"
        x-init="init()"
    >
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
                        class="inline-flex items-center justify-center rounded-2xl bg-white px-6 py-3 text-sm font-semibold text-slate-950 transition hover:bg-slate-100 disabled:opacity-60"
                        x-bind:disabled="submitting || (sebRequired && !sebDetected && !sebVerified)"
                    >
                        <span x-show="!submitting" x-cloak>Mulai Ujian</span>
                        <span x-show="submitting" x-cloak>Memulai...</span>
                    </button>
                </div>

                @if (! empty($sebMode))
                    <div class="mt-6 rounded-2xl border border-amber-400/30 bg-amber-400/10 p-4 text-left text-sm text-amber-50" x-show="sebRequired && !sebDetected && !sebVerified" x-cloak>
                        <p class="font-semibold">Safe Exam Browser belum terdeteksi</p>
                        <p class="mt-1 leading-6">Tutup browser ini, lalu buka kembali ujian melalui aplikasi Safe Exam Browser. Jika ingin keluar dari SEB, gunakan Ctrl+Q atau tombol quit yang diizinkan oleh konfigurasi SEB.</p>
                    </div>
                    <div class="mt-6 rounded-2xl border border-emerald-400/20 bg-emerald-400/10 p-4 text-left text-sm text-emerald-50" x-show="sebDetected || sebVerified" x-cloak>
                        <p class="font-semibold">SEB terdeteksi</p>
                        <p class="mt-1 leading-6">Sistem akan memverifikasi browser lalu memulai sesi ujian otomatis.</p>
                    </div>
                    <div class="mt-4 rounded-2xl border border-slate-300 bg-slate-50 p-4 text-left text-sm text-slate-700" x-show="sebDetected || sebVerified" x-cloak>
                        <p class="font-semibold">Status verifikasi</p>
                        <p class="mt-1 leading-6" x-text="handshakeMessage || (sebVerified ? 'SEB terverifikasi.' : 'Menunggu verifikasi SEB...')"></p>
                    </div>
                    <div class="mt-4 rounded-2xl border border-slate-300 bg-slate-50 p-4 text-left text-sm text-slate-700" x-show="sebRequired && !sebDetected && !sebVerified" x-cloak>
                        <p class="font-semibold">Tombol mulai belum aktif</p>
                        <p class="mt-1 leading-6">Pastikan halaman ini dibuka dari aplikasi SEB. Saat SEB sudah terdeteksi, tombol mulai ujian akan aktif otomatis.</p>
                    </div>
                @endif
            </div>
        </main>

        <template x-if="active">
            <div
                class="min-h-screen bg-slate-950 text-white"
                x-data="window.examLock({
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

                <div class="mx-auto flex min-h-screen max-w-7xl gap-6 px-4 py-4 sm:px-6 lg:px-8">
                    <main class="flex min-w-0 flex-1 flex-col gap-6">
                        <div class="rounded-3xl border border-white/10 bg-white/5 px-5 py-4 backdrop-blur">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0">
                                    <h2 class="truncate text-xl font-semibold text-white sm:text-2xl">{{ $exam->title }}</h2>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="rounded-full border border-white/10 bg-slate-900 px-4 py-2 text-sm font-semibold text-white" x-text="timerLabel"></div>
                                    <form method="POST" action="{{ route('exam.finish') }}">
                                        @csrf
                                        <x-secondary-button type="submit">
                                            {{ __('Selesai Ujian') }}
                                        </x-secondary-button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-3xl border border-white/10 bg-white/5 p-5 backdrop-blur">
                            <div class="flex items-center justify-between gap-4 border-b border-white/10 pb-4">
                                <div>
                                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Soal</p>
                                    <p class="mt-2 text-sm text-slate-300">Pilih satu soal, jawab, lalu pindah ke soal berikutnya atau sebelumnya.</p>
                                </div>
                                <div class="text-sm text-slate-300">
                                    <span x-text="currentQuestionIndex + 1"></span>/<span x-text="questions.length"></span>
                                </div>
                            </div>

                            <div class="mt-5 space-y-4">
                                @foreach ($questions as $question)
                                    @php
                                        $selectedAnswer = data_get($savedAnswers, $question->id);
                                    @endphp
                                    <article
                                        data-question-card="{{ $question->id }}"
                                        x-show="currentQuestionIndex === {{ $loop->index }}"
                                        x-cloak
                                        class="rounded-2xl border border-white/10 bg-slate-900/60 p-5"
                                    >
                                        <div class="flex items-start justify-between gap-4">
                                            <div>
                                                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-sky-300">Soal {{ $question->sort_order }}</p>
                                                <h3 class="mt-3 text-lg font-semibold leading-8 text-white">{{ $question->question_text }}</h3>
                                            </div>
                                            <span data-answer-status class="rounded-full px-3 py-1 text-xs font-medium {{ $selectedAnswer ? 'bg-emerald-400/20 text-emerald-200' : 'bg-white/10 text-slate-300' }}">
                                                {{ $selectedAnswer ? 'Tersimpan' : 'Belum dijawab' }}
                                            </span>
                                        </div>

                                        <form method="POST" action="{{ route('exam.answer') }}" class="mt-6 space-y-4" data-autosave-form @submit.prevent="saveCurrentAnswer($event.currentTarget)">
                                            @csrf
                                            <input type="hidden" name="question_id" value="{{ $question->id }}">
                                            <div class="grid gap-2">
                                                @foreach ($question->options as $option)
                                                    <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-white/10 bg-black/20 px-4 py-3 transition hover:border-sky-300/50 hover:bg-white/10">
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
                                            <div class="flex flex-col gap-3 border-t border-white/10 pt-4 sm:flex-row sm:items-center sm:justify-between">
                                                <p class="text-xs text-slate-400">Jawaban disimpan ke sesi ujian pengguna.</p>
                                                <x-primary-button class="js-manual-save">
                                                    {{ __('Simpan Jawaban') }}
                                                </x-primary-button>
                                            </div>
                                        </form>
                                    </article>
                                @endforeach
                            </div>

                            <div class="mt-6 flex items-center justify-between gap-3 border-t border-white/10 pt-4">
                                <x-secondary-button type="button" @click="previousQuestion()" x-bind:disabled="currentQuestionIndex === 0">
                                    Sebelumnya
                                </x-secondary-button>
                                <x-secondary-button type="button" @click="nextQuestion()" x-bind:disabled="currentQuestionIndex >= questions.length - 1">
                                    Selanjutnya
                                </x-secondary-button>
                            </div>
                        </div>
                    </main>

                    <aside class="hidden w-full max-w-xs shrink-0 lg:block">
                        <div class="sticky top-4 space-y-4">
                            <div class="rounded-3xl border border-white/10 bg-white/5 p-5 backdrop-blur">
                                <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Indikator Soal</p>
                                <div class="mt-4 grid grid-cols-5 gap-2">
                                    @foreach ($questions as $question)
                                        <button
                                            type="button"
                                            @click="setCurrentQuestion({{ $loop->index }})"
                                            data-question-indicator
                                            data-index="{{ $loop->index }}"
                                            x-bind:data-active="currentQuestionIndex === {{ $loop->index }}"
                                            class="flex h-11 items-center justify-center rounded-2xl border text-sm font-semibold transition"
                                            x-bind:class="currentQuestionIndex === {{ $loop->index }} ? 'border-sky-300 bg-sky-400/20 text-sky-100' : (answeredQuestions['{{ $question->id }}'] ? 'border-emerald-300 bg-emerald-400/15 text-emerald-100' : 'border-white/10 bg-black/20 text-slate-300 hover:border-sky-300/40 hover:bg-white/10')"
                                        >
                                            {{ $loop->iteration }}
                                        </button>
                                    @endforeach
                                </div>
                                <div class="mt-4 space-y-2 text-xs text-slate-300">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-block h-3 w-3 rounded-full bg-emerald-400/70"></span>
                                        <span>Sudah terjawab</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="inline-block h-3 w-3 rounded-full bg-white/20"></span>
                                        <span>Belum terjawab</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="inline-block h-3 w-3 rounded-full bg-sky-400/70"></span>
                                        <span>Soal aktif</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
        </template>
    </div>
</x-exam-layout>
