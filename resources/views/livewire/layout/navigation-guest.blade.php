@push('styles')
    <style>
        [x-cloak] {
            display: none !important;
        }

        .nav-scroll-effect {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .dropdown-enter-active {
            transition: all 0.2s ease-out;
        }

        .dropdown-leave-active {
            transition: all 0.15s ease-in;
        }

        .dropdown-enter-from,
        .dropdown-leave-to {
            opacity: 0;
            transform: translateY(-10px);
        }

        .mobile-menu-enter-active {
            transition: all 0.3s ease-out;
        }

        .mobile-menu-leave-active {
            transition: all 0.25s ease-in;
        }

        .mobile-menu-enter-from,
        .mobile-menu-leave-to {
            opacity: 0;
            transform: translateY(-10px);
        }
    </style>
@endpush

<nav x-data="{ mobileMenuOpen: false, scrolled: false }" @scroll.window="scrolled = window.scrollY > 50"
    x-bind:class="{
        'bg-white shadow-lg': scrolled || mobileMenuOpen,
        'bg-transparent': !scrolled && !mobileMenuOpen
    }"
    class="fixed w-full z-50 top-0 nav-scroll-effect backdrop-blur-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20">
            <!-- Logo -->
            <div class="flex-shrink-0 flex items-center">
                <a href="{{ route('home.index') }}"
                    class="flex items-center space-x-3 focus:outline-none focus:ring-2 focus:ring-[#FEC006] focus:ring-offset-2 rounded-md">
                    <img src="{{ asset('images/logo.png') }}" class="h-10 w-auto" alt="Sinyora Logo" />
                    <span
                        :class="{
                            'text-white': !scrolled && !mobileMenuOpen,
                            'text-[#825700]': scrolled || mobileMenuOpen
                        }"
                        class="text-2xl font-bold transition-colors duration-300">
                        Sinyora
                    </span>
                </a>
            </div>

            <!-- Desktop Menu -->
            <div class="hidden md:flex items-center space-x-8">
                <x-nav-link :href="route('home.index')" :active="request()->routeIs('home.index')"
                    x-bind:class="{
                        'text-[#825700] hover:text-[#FEC006]': scrolled,
                        'text-white hover:text-[#FEC006]': !scrolled
                    }">
                    {{ __('Beranda') }}
                </x-nav-link>

                <x-nav-link href="http://parokisayuka.org/" target="_blank"
                    x-bind:class="{
                        'text-[#825700] hover:text-[#FEC006]': scrolled,
                        'text-white hover:text-[#FEC006]': !scrolled
                    }">
                    {{ __('Paroki Sayuka') }}
                </x-nav-link>

                <!-- Dropdown: Kegiatan Kapel -->
                <div class="relative" x-data="{ openDropdown: false }">
                    <button @mouseenter="openDropdown = true" @mouseleave="openDropdown = false"
                        @click="openDropdown = !openDropdown" @focus="openDropdown = true"
                        @keydown.escape="openDropdown = false"
                        x-bind:class="{
                            'text-[#FEC006]': openDropdown,
                            'text-[#825700] hover:text-[#FEC006]': scrolled && !openDropdown,
                            'text-white hover:text-[#FEC006]': !scrolled && !openDropdown
                        }"
                        class="flex items-center space-x-1 text-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-[#FEC006] focus:ring-offset-2 rounded-md px-3 py-2">
                        <span>{{ __('Kegiatan Kapel') }}</span>
                        <svg x-bind:class="{ 'rotate-180 text-[#FEC006]': openDropdown }"
                            class="w-4 h-4 transition-transform duration-200" xmlns="http://www.w3.org/2000/svg"
                            fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>

                    <div x-show="openDropdown" x-transition:enter="dropdown-enter-active"
                        x-transition:enter-start="dropdown-enter-from" x-transition:enter-end="dropdown-enter-to"
                        x-transition:leave="dropdown-leave-active" x-transition:leave-start="dropdown-leave-from"
                        x-transition:leave-end="dropdown-leave-to" @mouseenter="openDropdown = true"
                        @mouseleave="openDropdown = false"
                        class="absolute z-10 mt-2 w-56 origin-top-right rounded-lg bg-white shadow-xl ring-1 ring-black ring-opacity-5 focus:outline-none py-2"
                        x-cloak>
                        <x-dropdown-link href="{{ route('home.index') }}#jadwal-misa" class="group">
                            <div class="flex items-center space-x-3">
                                <div
                                    class="p-1 rounded-lg bg-[#FEC006]/10 group-hover:bg-[#FEC006]/20 transition-colors">
                                    <x-heroicon-s-calendar class="h-5 w-5 text-[#FEC006]" />
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ __('Jadwal Misa') }}</p>
                                    <p class="text-xs text-gray-500">{{ __('Lihat jadwal misa kapel') }}</p>
                                </div>
                            </div>
                        </x-dropdown-link>
                        <x-dropdown-link href="{{ route('events.index') }}" class="group">
                            <div class="flex items-center space-x-3">
                                <div
                                    class="p-1 rounded-lg bg-[#FEC006]/10 group-hover:bg-[#FEC006]/20 transition-colors">
                                    <x-heroicon-s-calendar-days class="h-5 w-5 text-[#FEC006]" />
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ __('Jadwal Kegiatan') }}</p>
                                    <p class="text-xs text-gray-500">{{ __('Kegiatan kapel terbaru') }}</p>
                                </div>
                            </div>
                        </x-dropdown-link>
                        <x-dropdown-link href="{{ route('borrowing.assets.index') }}" class="group">
                            <div class="flex items-center space-x-3">
                                <div
                                    class="p-1 rounded-lg bg-[#FEC006]/10 group-hover:bg-[#FEC006]/20 transition-colors">
                                    <x-heroicon-s-clipboard-document-list class="h-5 w-5 text-[#FEC006]" />
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ __('Jadwal Peminjaman Aset') }}</p>
                                    <p class="text-xs text-gray-500">{{ __('lihat peminjaman aset kapel') }}</p>
                                </div>
                            </div>
                        </x-dropdown-link>
                    </div>
                </div>

                <x-nav-link href="{{ route('articles.index') }}" :active="request()->routeIs('articles.index')"
                    x-bind:class="{
                        'text-[#825700] hover:text-[#FEC006]': scrolled,
                        'text-white hover:text-[#FEC006]': !scrolled
                    }">
                    {{ __('Artikel') }}
                </x-nav-link>
            </div>

            <!-- Mobile Menu Button -->
            <div class="md:hidden flex items-center">
                <button @click="mobileMenuOpen = !mobileMenuOpen" type="button"
                    x-bind:class="{
                        'text-[#825700]': scrolled || mobileMenuOpen,
                        'text-white': !scrolled && !mobileMenuOpen
                    }"
                    class="inline-flex items-center justify-center p-2 rounded-md hover:bg-gray-100/10 focus:outline-none focus:ring-2 focus:ring-[#FEC006] focus:ring-offset-2 transition-colors"
                    :aria-expanded="mobileMenuOpen.toString()">
                    <span class="sr-only">Buka menu utama</span>
                    <svg x-show="!mobileMenuOpen" class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg x-show="mobileMenuOpen" class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu Panel -->
    <div x-show="mobileMenuOpen" x-transition:enter="mobile-menu-enter-active"
        x-transition:enter-start="mobile-menu-enter-from" x-transition:enter-end="mobile-menu-enter-to"
        x-transition:leave="mobile-menu-leave-active" x-transition:leave-start="mobile-menu-leave-from"
        x-transition:leave-end="mobile-menu-leave-to" @click.away="mobileMenuOpen = false"
        class="md:hidden bg-white/95 backdrop-blur-lg shadow-xl" x-cloak>
        <div class="px-2 pt-2 pb-4 space-y-1 sm:px-3">
            <x-mobile-nav-link :href="route('home.index')" :active="request()->routeIs('home.index')">
                {{ __('Beranda') }}
            </x-mobile-nav-link>

            <x-mobile-nav-link href="http://parokisayuka.org/" target="_blank">
                {{ __('Paroki Sayuka') }}
            </x-mobile-nav-link>

            <!-- Mobile Dropdown -->
            <div x-data="{ open: false }" class="space-y-1">
                <button @click="open = !open" @keydown.enter="open = !open" @keydown.escape="open = false"
                    x-bind:class="{
                        'bg-gray-50 text-[#825700]': open,
                        'text-gray-700 hover:bg-gray-50': !open
                    }"
                    class="w-full flex justify-between items-center px-3 py-2 rounded-md text-base font-medium focus:outline-none focus:ring-2 focus:ring-[#FEC006]">
                    <span>{{ __('Kegiatan Kapel') }}</span>
                    <svg x-bind:class="{ 'rotate-180 text-[#FEC006]': open }"
                        class="w-5 h-5 transition-transform duration-200" xmlns="http://www.w3.org/2000/svg"
                        fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                    </svg>
                </button>

                <div x-show="open" x-collapse class="pl-4 space-y-1">
                    <x-mobile-nav-link href="{{ route('home.index') }}#jadwal-misa" class="pl-4">
                        <div class="flex items-center space-x-3">
                            <x-heroicon-s-calendar class="h-5 w-5 text-[#FEC006]" />
                            <span>{{ __('Jadwal Misa') }}</span>
                        </div>
                    </x-mobile-nav-link>
                    <x-mobile-nav-link href="{{ route('events.index') }}" class="pl-4">
                        <div class="flex items-center space-x-3">
                            <x-heroicon-s-clipboard-document-list class="h-5 w-5 text-[#FEC006]" />
                            <span>{{ __('Jadwal Kegiatan') }}</span>
                        </div>
                    </x-mobile-nav-link>
                    <x-mobile-nav-link href="{{ route('borrowing.assets.index') }}" class="pl-4">
                        <div class="flex items-center space-x-3">
                            <x-heroicon-s-clipboard-document-list class="h-5 w-5 text-[#FEC006]" />
                            <span>{{ __('Jadwal Pinjaman Aset') }}</span>
                        </div>
                    </x-mobile-nav-link>
                </div>
            </div>

            <x-mobile-nav-link href="{{ route('articles.index') }}">
                {{ __('Artikel') }}
            </x-mobile-nav-link>
            <x-mobile-nav-link href="{{ route('home.index') }}#contact">
                {{ __('Kontak') }}
            </x-mobile-nav-link>
        </div>
    </div>
</nav>
