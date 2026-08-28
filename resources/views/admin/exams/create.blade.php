<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Buat Ujian</h2>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('admin.exams.store') }}" class="mx-auto max-w-3xl space-y-6 overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700">Judul</label>
                    <input name="title" class="mt-1 w-full rounded-md border-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Deskripsi</label>
                    <textarea name="description" class="mt-1 w-full rounded-md border-gray-300"></textarea>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Durasi Menit</label>
                        <input type="number" name="duration_minutes" value="60" class="mt-1 w-full rounded-md border-gray-300">
                    </div>
                    <label class="flex items-center gap-2 pt-6 text-sm text-gray-700">
                        <input type="checkbox" name="is_active" value="1" checked>
                        Aktif
                    </label>
                </div>
                <div class="flex justify-end">
                    <x-primary-button>Buat Ujian</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
