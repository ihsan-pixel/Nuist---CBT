<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Edit Ujian</h2>
                <p class="text-sm text-gray-500">Kelola ujian, soal, dan opsi secara dinamis dari satu layar.</p>
            </div>
            <div class="flex items-center gap-3">
                <a class="text-indigo-600 hover:underline" href="{{ route('admin.exams.results', $exam) }}">Lihat Hasil</a>
                <form method="POST" action="{{ route('admin.exams.destroy', $exam) }}" onsubmit="return confirm('Hapus ujian ini?')">
                    @csrf
                    @method('DELETE')
                    <button class="text-sm font-semibold text-red-600 hover:underline">Hapus Ujian</button>
                </form>
            </div>
        </div>
    </x-slot>

    @php
        $editorData = [
            'title' => old('title', $exam->title),
            'description' => old('description', $exam->description),
            'duration_minutes' => old('duration_minutes', $exam->duration_minutes),
            'is_active' => (bool) old('is_active', $exam->is_active),
            'questions' => old('questions') ? array_values(old('questions')) : $exam->questions->map(function ($question) {
                return [
                    'id' => $question->id,
                    'question_text' => $question->question_text,
                    'sort_order' => $question->sort_order,
                    'options' => $question->options->map(function ($option) {
                        return [
                            'id' => $option->id,
                            'option_label' => $option->option_label,
                            'option_text' => $option->option_text,
                            'is_correct' => $option->is_correct,
                        ];
                    })->values()->all(),
                ];
            })->values()->all(),
        ];
    @endphp

    <div class="py-6" x-data="examEditor(@js($editorData))">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.exams.update', $exam) }}" class="space-y-6 overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                @csrf
                @method('PUT')

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Judul</label>
                        <input name="title" x-model="form.title" class="mt-1 w-full rounded-md border-gray-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Durasi Menit</label>
                        <input type="number" name="duration_minutes" x-model.number="form.duration_minutes" class="mt-1 w-full rounded-md border-gray-300">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Deskripsi</label>
                    <textarea name="description" x-model="form.description" class="mt-1 w-full rounded-md border-gray-300"></textarea>
                </div>

                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="is_active" value="1" x-model="form.is_active">
                    Aktif
                </label>

                <div class="flex justify-end">
                    <x-primary-button>Simpan Ujian</x-primary-button>
                </div>
            </form>

            <div class="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Bank Soal</h3>
                        <p class="text-sm text-gray-500">Tambah atau hapus soal dan opsi tanpa meninggalkan halaman.</p>
                    </div>
                    <button type="button" class="rounded-md bg-gray-900 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white" @click="addQuestion()">
                        Tambah Soal
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.exams.update', $exam) }}" class="mt-6 space-y-4">
                    @csrf
                    @method('PUT')

                    <template x-for="(question, qIndex) in form.questions" :key="question.client_id">
                        <section class="rounded-2xl border border-gray-200 bg-gray-50 p-5">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-semibold text-indigo-600">Soal <span x-text="qIndex + 1"></span></p>
                                    <p class="text-sm text-gray-500">Urutan otomatis tersimpan ke field sort_order.</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <button type="button" class="text-sm font-semibold text-red-600 hover:underline" @click="removeQuestion(qIndex)">
                                        Hapus Soal
                                    </button>
                                </div>
                            </div>

                            <input type="hidden" :name="`questions[${qIndex}][id]`" :value="question.id ?? ''">

                            <div class="mt-4 grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Teks Soal</label>
                                    <textarea :name="`questions[${qIndex}][question_text]`" x-model="question.question_text" class="mt-1 w-full rounded-md border-gray-300"></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Urutan</label>
                                    <input type="number" :name="`questions[${qIndex}][sort_order]`" x-model.number="question.sort_order" class="mt-1 w-full rounded-md border-gray-300">
                                </div>
                            </div>

                            <div class="mt-5 space-y-3">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-sm font-semibold uppercase tracking-wider text-gray-600">Opsi Jawaban</h4>
                                    <button type="button" class="text-sm font-semibold text-indigo-600 hover:underline" @click="addOption(qIndex)">
                                        Tambah Opsi
                                    </button>
                                </div>

                                <template x-for="(option, oIndex) in question.options" :key="option.client_id">
                                    <div class="rounded-xl border border-gray-200 bg-white p-4">
                                        <div class="flex items-start justify-between gap-4">
                                            <div class="grid flex-1 gap-3 md:grid-cols-4">
                                                <input type="hidden" :name="`questions[${qIndex}][options][${oIndex}][id]`" :value="option.id ?? ''">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">Label</label>
                                                    <input :name="`questions[${qIndex}][options][${oIndex}][option_label]`" x-model="option.option_label" class="mt-1 w-full rounded-md border-gray-300">
                                                </div>
                                                <div class="md:col-span-2">
                                                    <label class="block text-sm font-medium text-gray-700">Teks Opsi</label>
                                                    <input :name="`questions[${qIndex}][options][${oIndex}][option_text]`" x-model="option.option_text" class="mt-1 w-full rounded-md border-gray-300">
                                                </div>
                                                <div class="flex items-center gap-2 pt-6">
                                                    <input type="checkbox" :name="`questions[${qIndex}][options][${oIndex}][is_correct]`" value="1" x-model="option.is_correct">
                                                    <span class="text-sm text-gray-700">Benar</span>
                                                </div>
                                            </div>
                                            <button type="button" class="text-sm font-semibold text-red-600 hover:underline" @click="removeOption(qIndex, oIndex)">
                                                Hapus
                                            </button>
                                        </div>
                                    </div>
                                </template>

                                <div class="pt-2 text-xs text-gray-500">
                                    Minimal 2 opsi per soal. Controller akan tetap memvalidasi ini di server.
                                </div>
                            </div>
                        </section>
                    </template>

                    <div class="flex justify-end border-t border-gray-200 pt-4">
                        <x-primary-button>Simpan Semua Perubahan</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
