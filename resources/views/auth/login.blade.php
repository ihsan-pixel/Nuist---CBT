<x-guest-layout>
    <form method="POST" action="{{ route('login') }}" x-data="{ showPassword: false, submitting: false }" @submit="submitting = true">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <div class="relative mt-1">
                <x-text-input
                    id="password"
                    class="block w-full pr-12"
                    :type="showPassword ? 'text' : 'password'"
                    name="password"
                    required
                    autocomplete="current-password"
                />

                <button
                    type="button"
                    class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 hover:text-gray-700 focus:outline-none"
                    @click="showPassword = !showPassword"
                    :aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                >
                    <svg x-show="!showPassword" class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <path d="M2.5 10s2.75-5 7.5-5 7.5 5 7.5 5-2.75 5-7.5 5-7.5-5-7.5-5Z" stroke="currentColor" stroke-width="1.5"/>
                        <circle cx="10" cy="10" r="2.25" stroke="currentColor" stroke-width="1.5"/>
                    </svg>
                    <svg x-show="showPassword" x-cloak class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <path d="m3 3 14 14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        <path d="M4.75 6.25A10.7 10.7 0 0 1 10 4.75c4.75 0 7.5 5.25 7.5 5.25a15.56 15.56 0 0 1-2.76 3.58" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        <path d="M8.25 8.32A2.25 2.25 0 0 0 10 13.25a2.25 2.25 0 0 0 1.74-.82" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4 flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">Ingat saya</span>
            </label>

            @if (Route::has('password.request'))
                <a class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2" href="{{ route('password.request') }}">
                    Lupa kata sandi?
                </a>
            @endif
        </div>

        <div class="mt-4 flex items-center justify-end">
            <x-primary-button class="ms-4" :disabled="submitting">
                <span x-text="submitting ? 'Memproses...' : 'Masuk'">Masuk</span>
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
