<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Manajemen Role</h2>
                <p class="text-sm text-gray-500">Atur role super admin, panitia, atau peserta.</p>
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

            <div class="mb-6 overflow-hidden rounded-lg bg-white shadow-sm">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900">Buat Peserta Ujian</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Nama peserta diinput manual. ID peserta dibuat acak berupa 6 digit angka, lalu email otomatis menjadi
                        <span class="font-medium">id@cbt.nuist.id</span>.
                    </p>

                    <form method="POST" action="{{ route('admin.users.store') }}" class="mt-6 grid gap-4 md:grid-cols-[minmax(0,1fr)_auto] md:items-end">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nama Peserta</label>
                            <input name="name" class="mt-1 w-full rounded-md border-gray-300" placeholder="Contoh: Budi Santoso">
                        </div>
                        <div>
                            <button type="submit" class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-700">
                                Buat Peserta
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50 text-left text-sm text-gray-500">
                        <tr>
                            <th class="px-4 py-3">ID Peserta</th>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Role</th>
                            <th class="px-4 py-3">Dibuat</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($users as $user)
                            @php
                                $roleValue = $user->role instanceof \App\Enums\UserRole ? $user->role->value : (string) $user->role;
                                $roleLabel = $roleValue === 'super_admin' ? 'Super Admin' : ucfirst($roleValue);
                            @endphp
                            <tr>
                                <td class="px-4 py-3 font-mono text-sm text-gray-700">{{ $user->participant_code ?? '-' }}</td>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $user->name }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $user->email }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $roleValue === 'super_admin' ? 'bg-emerald-100 text-emerald-800' : ($roleValue === 'panitia' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700') }}">
                                        {{ $roleLabel }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-700">{{ $user->created_at->format('d M Y H:i') }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="flex items-center gap-3">
                                            @csrf
                                            @method('PATCH')
                                            <select name="role" class="rounded-md border-gray-300 text-sm">
                                                <option value="super_admin" @selected($roleValue === 'super_admin')>Super Admin</option>
                                                <option value="panitia" @selected($roleValue === 'panitia')>Panitia</option>
                                                <option value="peserta" @selected($roleValue === 'peserta')>Peserta</option>
                                            </select>
                                            <button class="text-indigo-600 hover:underline">Simpan</button>
                                        </form>

                                        @if (! auth()->user()->is($user))
                                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Hapus user ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="text-sm font-semibold text-red-600 hover:underline">Hapus</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
