<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Pengaturan Aplikasi</h2>
                <p class="text-sm text-gray-500">Atur identitas aplikasi, logo, versi, yayasan naungan, dan kontak resmi.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <div class="p-6">
                    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="grid gap-6 md:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nama Aplikasi</label>
                                <input name="app_name" value="{{ old('app_name', $settings->app_name ?? config('app.name')) }}" class="mt-1 w-full rounded-md border-gray-300">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Versi Aplikasi</label>
                                <input name="app_version" value="{{ old('app_version', $settings->app_version ?? '1.0.0') }}" class="mt-1 w-full rounded-md border-gray-300">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Yayasan Naungan</label>
                                <input name="foundation_name" value="{{ old('foundation_name', $settings->foundation_name) }}" class="mt-1 w-full rounded-md border-gray-300">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email Aplikasi</label>
                                <input name="app_email" type="email" value="{{ old('app_email', $settings->app_email) }}" class="mt-1 w-full rounded-md border-gray-300">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Deskripsi Aplikasi</label>
                            <textarea name="app_description" rows="4" class="mt-1 w-full rounded-md border-gray-300">{{ old('app_description', $settings->app_description) }}</textarea>
                        </div>

                        <div class="grid gap-6 md:grid-cols-[160px_minmax(0,1fr)]">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Logo Aplikasi</label>
                            <div class="mt-2 flex h-20 w-20 items-center justify-center overflow-hidden rounded-lg border border-dashed border-gray-300 bg-gray-50">
                                @if (! empty($settings->app_logo_path))
                                    <img src="{{ asset('storage/'.$settings->app_logo_path) }}" alt="Logo aplikasi" class="h-full w-full object-contain p-2">
                                @else
                                    <span class="text-sm text-gray-400">Belum ada logo</span>
                                @endif
                            </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Upload Logo</label>
                                <input type="file" name="app_logo" accept="image/*" class="mt-1 w-full rounded-md border-gray-300">
                                <p class="mt-2 text-xs text-gray-500">Format disarankan PNG atau SVG raster-like, ukuran maksimal 2 MB.</p>
                            </div>
                        </div>

                        <div class="flex justify-end border-t border-gray-200 pt-4">
                            <x-primary-button>Simpan Pengaturan</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
