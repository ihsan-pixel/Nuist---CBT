<nav
    x-data="{ open: false }"
    class="border-b border-gray-100 bg-white"
>
    <!-- Primary Navigation Menu -->
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 justify-between">
            <div class="flex">
                <!-- Logo -->
                <div class="flex shrink-0 items-center">
                    <a
                        href="{{ route('dashboard') }}"
                        aria-label="Buka dashboard NUIST CBT"
                    >
                        <x-application-logo
                            class="block h-9 w-auto fill-current text-gray-800"
                        />
                    </a>
                </div>

                <!-- Desktop Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link
                        :href="route('dashboard')"
                        :active="request()->routeIs('dashboard')"
                    >
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    <x-nav-link
                        :href="route('exam.room')"
                        :active="request()->routeIs('exam.*')"
                    >
                        {{ __('Ujian') }}
                    </x-nav-link>

                    @can('access-exam-panel')
                        <x-nav-link
                            :href="route('admin.exams.index')"
                            :active="request()->routeIs('admin.exams.*')"
                        >
                            {{ __('Kelola Ujian') }}
                        </x-nav-link>
                    @endcan

                    @can('access-admin')
                        <x-nav-link
                            :href="route('admin.users.index')"
                            :active="request()->routeIs('admin.users.*')"
                        >
                            {{ __('Kelola Pengguna') }}
                        </x-nav-link>

                        <x-nav-link
                            :href="route('admin.settings.edit')"
                            :active="request()->routeIs('admin.settings.*')"
                        >
                            {{ __('Pengaturan') }}
                        </x-nav-link>
                    @endcan
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:ms-6 sm:flex sm:items-center">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            type="button"
                            class="inline-flex items-center rounded-md border border-transparent px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#00866A] focus:ring-offset-2"
                            aria-label="Buka menu akun"
                        >
                            <span class="max-w-48 truncate">
                                {{ auth()->user()->name }}
                            </span>

                            <svg
                                class="ms-1 h-4 w-4 fill-current"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                                aria-hidden="true"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profil') }}
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <button
                                type="submit"
                                class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 transition duration-150 ease-in-out hover:bg-gray-100 focus:bg-gray-100 focus:outline-none"
                            >
                                {{ __('Keluar') }}
                            </button>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Mobile Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button
                    type="button"
                    @click="open = !open"
                    :aria-expanded="open.toString()"
                    aria-controls="mobile-navigation"
                    aria-label="Buka atau tutup menu navigasi"
                    class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none focus:ring-2 focus:ring-[#00866A] focus:ring-offset-2"
                >
                    <svg
                        class="h-6 w-6"
                        stroke="currentColor"
                        fill="none"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <!-- Hamburger Icon -->
                        <path
                            :class="{ 'hidden': open, 'inline-flex': !open }"
                            class="inline-flex"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"
                        />

                        <!-- Close Icon -->
                        <path
                            :class="{ 'hidden': !open, 'inline-flex': open }"
                            class="hidden"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div
        id="mobile-navigation"
        x-cloak
        :class="{ 'block': open, 'hidden': !open }"
        class="hidden sm:hidden"
    >
        <div class="space-y-1 pb-3 pt-2">
            <x-responsive-nav-link
                :href="route('dashboard')"
                :active="request()->routeIs('dashboard')"
            >
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link
                :href="route('exam.room')"
                :active="request()->routeIs('exam.*')"
            >
                {{ __('Ujian') }}
            </x-responsive-nav-link>

            @can('access-exam-panel')
                <x-responsive-nav-link
                    :href="route('admin.exams.index')"
                    :active="request()->routeIs('admin.exams.*')"
                >
                    {{ __('Kelola Ujian') }}
                </x-responsive-nav-link>
            @endcan

            @can('access-admin')
                <x-responsive-nav-link
                    :href="route('admin.users.index')"
                    :active="request()->routeIs('admin.users.*')"
                >
                    {{ __('Kelola Pengguna') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link
                    :href="route('admin.settings.edit')"
                    :active="request()->routeIs('admin.settings.*')"
                >
                    {{ __('Pengaturan') }}
                </x-responsive-nav-link>
            @endcan
        </div>

        <!-- Responsive Settings Options -->
        <div class="border-t border-gray-200 pb-1 pt-4">
            <div class="px-4">
                <div class="truncate text-base font-medium text-gray-800">
                    {{ auth()->user()->name }}
                </div>

                <div class="truncate text-sm font-medium text-gray-500">
                    {{ auth()->user()->email }}
                </div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profil') }}
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button
                        type="submit"
                        class="block w-full border-l-4 border-transparent py-2 pe-4 ps-3 text-start text-base font-medium text-gray-600 transition duration-150 ease-in-out hover:border-gray-300 hover:bg-gray-50 hover:text-gray-800 focus:border-gray-300 focus:bg-gray-50 focus:text-gray-800 focus:outline-none"
                    >
                        {{ __('Keluar') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
