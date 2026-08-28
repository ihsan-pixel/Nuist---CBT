

import Alpine from 'alpinejs';

window.Alpine = Alpine;

window.examLock = ({
    started = false,
    remainingSeconds = 0,
    violationUrl = null,
    answerUrl = null,
    refreshUrl = null,
    savedAnswers = {},
    questionIds = [],
    autoSave = false,
    enforceFullscreen = false,
} = {}) => ({
    started,
    statusText: started ? 'Sesi aktif' : 'Menunggu mulai',
    fullscreenState: 'OFF',
    focusState: 'AKTIF',
    warningCount: 0,
    violationUrl,
    answerUrl,
    refreshUrl,
    savedAnswers,
    questionIds,
    autoSave,
    enforceFullscreen,
    remainingSeconds: Number(remainingSeconds ?? 0),
    timerLabel: '--:--',
    timerHandle: null,
    fullscreenRetryHandle: null,
    init() {
        this.updateFullscreenState();
        this.updateFocusState();
        this.updateTimerLabel();

        if (this.started) {
            window.history.pushState({ exam: true }, '', window.location.href);
        }

        if (this.started && this.remainingSeconds > 0) {
            this.timerHandle = window.setInterval(() => {
                this.remainingSeconds = Math.max(0, this.remainingSeconds - 1);
                this.updateTimerLabel();

                if (this.remainingSeconds === 0) {
                    this.autoFinish();
                }
            }, 1000);
        }

        document.addEventListener('fullscreenchange', () => {
            this.updateFullscreenState();
            if (!document.fullscreenElement) {
                this.raiseWarning('Keluar dari fullscreen terdeteksi.', 'fullscreen_exit');
                if (this.enforceFullscreen) {
                    window.clearTimeout(this.fullscreenRetryHandle);
                    this.fullscreenRetryHandle = window.setTimeout(() => {
                        this.enterFullscreen();
                    }, 150);
                }
            }
        });

        window.addEventListener('focus', () => {
            this.focusState = 'AKTIF';
        });

        window.addEventListener('blur', () => {
            this.focusState = 'TERBLOKIR';
            this.raiseWarning('Jangan pindah dari halaman ujian.', 'window_blur');
        });

        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.focusState = 'TERBLOKIR';
                this.raiseWarning('Tab lain terdeteksi.', 'tab_hidden');
            } else {
                this.focusState = 'AKTIF';
            }
        });

        document.addEventListener('contextmenu', (event) => event.preventDefault());
        document.addEventListener('keydown', (event) => this.blockShortcuts(event));
        window.addEventListener('popstate', () => {
            if (this.started) {
                window.history.pushState({ exam: true }, '', window.location.href);
                this.raiseWarning('Navigasi browser diblokir selama ujian.', 'browser_back');
            }
        });
        document.addEventListener('copy', () => this.raiseWarning('Copy/paste diblokir.', 'copy_attempt'));
        document.addEventListener('paste', () => this.raiseWarning('Copy/paste diblokir.', 'paste_attempt'));
        document.querySelectorAll('[data-autosave-form]').forEach((form) => {
            form.querySelectorAll('input[type="radio"]').forEach((input) => {
                input.addEventListener('change', () => {
                    if (this.autoSave) {
                        this.saveAnswer(form);
                    }
                });
            });
        });
        window.addEventListener('pagehide', () => {
            this.sendBackgroundViolation('page_hide', 'Peserta meninggalkan halaman ujian.');
        });
        window.addEventListener('beforeunload', () => {
            this.sendBackgroundViolation('before_unload', 'Halaman ujian dimuat ulang atau ditinggalkan.');
        });
        if (this.refreshUrl) {
            this.keepSessionAlive();
        }
    },
    autoFinish() {
        const form = document.getElementById('exam-heartbeat-form');
        if (form) {
            form.submit();
        }
    },
    async enterFullscreen() {
        const element = document.documentElement;

        if (document.fullscreenElement) {
            return;
        }

        try {
            if (element.requestFullscreen) {
                await element.requestFullscreen({ navigationUI: 'hide', keyboardLock: 'browser' });
                if (navigator.keyboard?.lock) {
                    await navigator.keyboard.lock();
                }
                return;
            }

            if (element.webkitRequestFullscreen) {
                await element.webkitRequestFullscreen();
                if (navigator.keyboard?.lock) {
                    await navigator.keyboard.lock();
                }
                return;
            }

            this.raiseWarning('Browser tidak mendukung fullscreen API.', 'fullscreen_unsupported');
        } catch (error) {
            this.raiseWarning('Browser menolak fullscreen. Gunakan browser yang mendukung mode penuh.');
        }
    },
    updateFullscreenState() {
        this.fullscreenState = document.fullscreenElement ? 'ON' : 'OFF';
    },
    updateFocusState() {
        this.focusState = document.hidden ? 'TERBLOKIR' : 'AKTIF';
    },
    updateTimerLabel() {
        const minutes = Math.floor(this.remainingSeconds / 60);
        const seconds = this.remainingSeconds % 60;
        this.timerLabel = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    },
    async raiseWarning(message, type = 'client_warning') {
        this.warningCount += 1;
        this.statusText = `Peringatan ${this.warningCount}`;
        console.warn(message);
        await this.logViolation(type, message);
    },
    blockShortcuts(event) {
        const key = event.key.toLowerCase();

        if (
            key === 'escape' ||
            (event.altKey && key === 'tab') ||
            (event.ctrlKey && ['l', 'r', 't', 'w'].includes(key)) ||
            (event.metaKey && ['l', 'r', 't', 'w', 'q'].includes(key)) ||
            key === 'f5' ||
            (event.ctrlKey && event.shiftKey && ['i', 'j', 'c'].includes(key))
        ) {
            event.preventDefault();
            event.stopPropagation();
            this.raiseWarning('Shortcut diblokir selama ujian.', 'shortcut_blocked');
            if (key === 'escape' && this.enforceFullscreen) {
                window.clearTimeout(this.fullscreenRetryHandle);
                this.fullscreenRetryHandle = window.setTimeout(() => {
                    this.enterFullscreen();
                }, 50);
            }
        }
    },
    async saveAnswer(form) {
        if (!this.answerUrl) {
            form.submit();
            return;
        }

        const formData = new FormData(form);

        try {
            const response = await fetch(this.answerUrl, {
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
            this.savedAnswers[String(payload.question_id)] = payload.answer;
            this.statusText = 'Jawaban tersimpan';
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
    keepSessionAlive() {
        window.setInterval(async () => {
            try {
                await fetch(this.refreshUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    },
                });
            } catch (error) {
                console.warn('Session refresh failed', error);
            }
        }, 30000);
    },
    async logViolation(type, message) {
        if (!this.violationUrl) {
            return;
        }

        try {
            await fetch(this.violationUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    type,
                    message,
                    meta: {
                        visibility: document.hidden ? 'hidden' : 'visible',
                        fullscreen: Boolean(document.fullscreenElement),
                        user_agent: navigator.userAgent,
                    },
                }),
            });
        } catch (error) {
            console.warn('Gagal mencatat pelanggaran.', error);
        }
    },
    sendBackgroundViolation(type, message) {
        if (!this.violationUrl || !navigator.sendBeacon) {
            return;
        }

        const blob = new Blob([JSON.stringify({
            type,
            message,
            meta: {
                visibility: document.hidden ? 'hidden' : 'visible',
                fullscreen: Boolean(document.fullscreenElement),
                user_agent: navigator.userAgent,
            },
        })], { type: 'application/json' });

        navigator.sendBeacon(this.violationUrl, blob);
    },
});

window.examEditor = (initial = {}) => ({
    form: {
        title: initial.title ?? '',
        description: initial.description ?? '',
        duration_minutes: initial.duration_minutes ?? 60,
        is_active: Boolean(initial.is_active),
        questions: (initial.questions ?? []).map((question, index) => ({
            client_id: question.id ? `q-${question.id}` : `q-new-${index}-${Date.now()}`,
            id: question.id ?? null,
            question_text: question.question_text ?? '',
            sort_order: question.sort_order ?? index + 1,
            options: (question.options ?? []).map((option, optionIndex) => ({
                client_id: option.id ? `o-${option.id}` : `o-new-${index}-${optionIndex}-${Date.now()}`,
                id: option.id ?? null,
                option_label: option.option_label ?? '',
                option_text: option.option_text ?? '',
                is_correct: Boolean(option.is_correct),
            })),
        })),
    },
    nextQuestionIndex: 0,
    init() {
        this.nextQuestionIndex = this.form.questions.length;
    },
    addQuestion() {
        this.form.questions.push({
            client_id: `q-new-${this.nextQuestionIndex++}-${Date.now()}`,
            id: null,
            question_text: '',
            sort_order: this.form.questions.length + 1,
            options: [
                this.makeOption('A'),
                this.makeOption('B'),
            ],
        });
    },
    removeQuestion(index) {
        this.form.questions.splice(index, 1);
        this.form.questions.forEach((question, questionIndex) => {
            question.sort_order = questionIndex + 1;
        });
    },
    addOption(questionIndex) {
        const question = this.form.questions[questionIndex];
        const label = String.fromCharCode(65 + question.options.length);
        question.options.push(this.makeOption(label));
    },
    removeOption(questionIndex, optionIndex) {
        this.form.questions[questionIndex].options.splice(optionIndex, 1);
    },
    makeOption(label = 'A') {
        return {
            client_id: `o-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`,
            id: null,
            option_label: label,
            option_text: '',
            is_correct: false,
        };
    },
});

Alpine.start();
