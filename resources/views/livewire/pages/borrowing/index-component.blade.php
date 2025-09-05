<div class="font-sans antialiased bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">

    {{-- Bagian Hero Section --}}
    <section class="relative bg-cover bg-center bg-no-repeat py-24 sm:py-32 px-4 sm:px-6 lg:px-8"
        style="background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('{{ asset('images/1.jpg') }}');">
        <div class="container mx-auto text-center text-white max-w-4xl">
            <h1 class="text-4xl sm:text-5xl font-bold mb-4 leading-tight">
                Peminjaman Aset Kapel
            </h1>
            <p class="text-lg sm:text-xl mb-8 opacity-90 max-w-3xl mx-auto">
                Temukan, cek ketersediaan, dan ajukan peminjaman aset untuk kegiatan Anda dengan mudah.
            </p>
            <div class="flex flex-wrap justify-center gap-4">
                <button wire:click="createRequest"
                    class="inline-flex items-center justify-center px-8 py-3 bg-[#FFD24C] hover:bg-[#FEC006] text-[#825700] font-semibold rounded-lg shadow-lg transition-transform transform hover:scale-105 duration-300">
                    <x-heroicon-o-plus-circle class="h-6 w-6 mr-2" />
                    Ajukan Peminjaman
                </button>
            </div>
        </div>
    </section>

    {{-- Konten Utama: Filter dan Daftar Aset --}}
    <main class="py-12 sm:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="text-center mb-10">
                <h2 class="text-3xl font-bold text-gray-800 dark:text-gray-200">Cek Ketersediaan Aset</h2>
                <p class="mt-2 text-md text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                    Pilih rentang tanggal untuk melihat aset yang tersedia, lalu gunakan filter untuk mempersempit
                    pencarian Anda.
                </p>
            </div>

            {{-- Panel Filter --}}
            {{-- Filter Panel dengan Responsive Design --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md mb-8" x-data="{ filterOpen: false, isMobile: window.innerWidth < 768 }"
                x-init="$watch('filterOpen', value => { if (value && isMobile) { document.body.style.overflow = 'hidden' } else { document.body.style.overflow = 'auto' } })" @resize.window="isMobile = window.innerWidth < 768">

                {{-- Desktop: Always visible --}}
                <div class="hidden md:block">
                    <div class="p-4 sm:p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                            {{-- Filter Tanggal Mulai --}}
                            <div class="w-full">
                                <label for="start_date"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Tanggal Mulai
                                </label>
                                <input wire:model.live="startDate" id="start_date" type="date"
                                    class="block w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-[#FFD24C] focus:border-[#FFD24C] sm:text-sm dark:bg-gray-700">
                            </div>

                            {{-- Filter Tanggal Selesai --}}
                            <div class="w-full">
                                <label for="end_date"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Tanggal Selesai
                                </label>
                                <input wire:model.live="endDate" id="end_date" type="date"
                                    class="block w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-[#FFD24C] focus:border-[#FFD24C] sm:text-sm dark:bg-gray-700">
                            </div>

                            {{-- Pencarian --}}
                            <div class="w-full lg:col-span-2">
                                <label for="search"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Cari Aset
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <x-heroicon-s-magnifying-glass class="h-5 w-5 text-gray-400" />
                                    </div>
                                    <input wire:model.live.debounce.300ms="search" id="search" type="text"
                                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg leading-5 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-200 placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#FFD24C] focus:border-[#FFD24C] sm:text-sm"
                                        placeholder="Cari nama aset...">
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            {{-- Filter Kategori --}}
                            <div>
                                <label for="category" class="sr-only">Kategori</label>
                                <select wire:model.live="selectedCategory" id="category"
                                    class="block w-full py-2 px-3 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-[#FFD24C] focus:border-[#FFD24C] sm:text-sm">
                                    <option value="">Semua Kategori</option>
                                    @foreach ($assetCategories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            {{-- Urutkan --}}
                            <div>
                                <label for="sort" class="sr-only">Urutkan</label>
                                <select wire:model.live="sortBy" id="sort"
                                    class="block w-full py-2 px-3 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-[#FFD24C] focus:border-[#FFD24C] sm:text-sm">
                                    <option value="latest">Terbaru Ditambahkan</option>
                                    <option value="name_asc">Nama (A-Z)</option>
                                    <option value="name_desc">Nama (Z-A)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Mobile: Compact header with toggle button --}}
                <div class="md:hidden">
                    {{-- Collapsed state: Show selected filters summary --}}
                    <div class="p-4 border-b border-gray-200 dark:border-gray-600" x-show="!filterOpen">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-2 text-sm text-gray-600 dark:text-gray-400">
                                    <span class="font-medium">Filter:</span>
                                    <span class="truncate">
                                        {{ \Carbon\Carbon::parse($startDate)->isoFormat('D MMM') }} -
                                        {{ \Carbon\Carbon::parse($endDate)->isoFormat('D MMM Y') }}
                                        <span x-show="'{{ $search }}'" class="ml-1">•
                                            "{{ $search }}"</span>
                                        <span x-show="'{{ $selectedCategory }}'" class="ml-1">• Kategori</span>
                                    </span>
                                </div>
                            </div>
                            <button @click="filterOpen = true"
                                class="inline-flex items-center px-3 py-1.5 bg-[#FFD24C] hover:bg-[#FEC006] text-[#825700] text-sm font-medium rounded-lg transition-colors">
                                <x-heroicon-s-adjustments-horizontal class="h-4 w-4 mr-1" />
                                Edit
                            </button>
                        </div>
                    </div>

                    {{-- Expanded state: Full-screen filter modal --}}
                    <div class="fixed inset-0 z-50 bg-white dark:bg-gray-800" x-show="filterOpen" x-cloak
                        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

                        {{-- Header with close button --}}
                        <div
                            class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-600">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Filter Aset</h3>
                            <button @click="filterOpen = false"
                                class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                <x-heroicon-s-x-mark class="h-6 w-6" />
                            </button>
                        </div>

                        {{-- Filter content --}}
                        <div class="p-4 space-y-6 overflow-y-auto" style="max-height: calc(100vh - 120px)">
                            {{-- Quick date selection --}}
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3">Pilih Cepat</h4>
                                <div class="grid grid-cols-2 gap-2">
                                    {{-- PERBAIKAN DI SINI: Memanggil satu metode `setDateRange` --}}
                                    <button
                                        wire:click="setDateRange('{{ now()->format('Y-m-d') }}', '{{ now()->format('Y-m-d') }}')"
                                        class="px-3 py-2 text-sm bg-gray-100 dark:bg-gray-700 hover:bg-[#FFD24C] hover:text-[#825700] rounded-lg transition-colors">
                                        Hari Ini
                                    </button>
                                    <button
                                        wire:click="setDateRange('{{ now()->addDay()->format('Y-m-d') }}', '{{ now()->addDay()->format('Y-m-d') }}')"
                                        class="px-3 py-2 text-sm bg-gray-100 dark:bg-gray-700 hover:bg-[#FFD24C] hover:text-[#825700] rounded-lg transition-colors">
                                        Besok
                                    </button>
                                    <button
                                        wire:click="setDateRange('{{ now()->startOfWeek()->format('Y-m-d') }}', '{{ now()->endOfWeek()->format('Y-m-d') }}')"
                                        class="px-3 py-2 text-sm bg-gray-100 dark:bg-gray-700 hover:bg-[#FFD24C] hover:text-[#825700] rounded-lg transition-colors">
                                        Minggu Ini
                                    </button>
                                    <button
                                        wire:click="setDateRange('{{ now()->addWeek()->startOfWeek()->format('Y-m-d') }}', '{{ now()->addWeek()->endOfWeek()->format('Y-m-d') }}')"
                                        class="px-3 py-2 text-sm bg-gray-100 dark:bg-gray-700 hover:bg-[#FFD24C] hover:text-[#825700] rounded-lg transition-colors">
                                        Minggu Depan
                                    </button>
                                </div>
                            </div>

                            {{-- Custom date range --}}
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3">Tanggal Kustom
                                </h4>
                                <div class="space-y-3">
                                    <div>
                                        <label
                                            class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Mulai</label>
                                        <input wire:model.live="startDate" type="date"
                                            class="w-full border-gray-300 dark:border-gray-600 rounded-lg focus:ring-[#FFD24C] focus:border-[#FFD24C] dark:bg-gray-700">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Selesai</label>
                                        <input wire:model.live="endDate" type="date"
                                            class="w-full border-gray-300 dark:border-gray-600 rounded-lg focus:ring-[#FFD24C] focus:border-[#FFD24C] dark:bg-gray-700">
                                    </div>
                                </div>
                            </div>

                            {{-- Search --}}
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3">Pencarian</h4>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <x-heroicon-s-magnifying-glass class="h-5 w-5 text-gray-400" />
                                    </div>
                                    <input wire:model.live.debounce.300ms="search" type="text"
                                        class="w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-[#FFD24C] focus:border-[#FFD24C] dark:bg-gray-700"
                                        placeholder="Cari nama aset...">
                                </div>
                            </div>

                            {{-- Category --}}
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3">Kategori</h4>
                                <select wire:model.live="selectedCategory"
                                    class="w-full border-gray-300 dark:border-gray-600 rounded-lg focus:ring-[#FFD24C] focus:border-[#FFD24C] dark:bg-gray-700">
                                    <option value="">Semua Kategori</option>
                                    @foreach ($assetCategories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Sort --}}
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3">Urutkan</h4>
                                <select wire:model.live="sortBy"
                                    class="w-full border-gray-300 dark:border-gray-600 rounded-lg focus:ring-[#FFD24C] focus:border-[#FFD24C] dark:bg-gray-700">
                                    <option value="latest">Terbaru Ditambahkan</option>
                                    <option value="name_asc">Nama (A-Z)</option>
                                    <option value="name_desc">Nama (Z-A)</option>
                                </select>
                            </div>
                        </div>

                        {{-- Bottom action buttons --}}
                        <div
                            class="absolute bottom-0 left-0 right-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-600 p-4">
                            <div class="flex space-x-3">
                                <button wire:click="resetFilters"
                                    class="flex-1 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                                    Reset
                                </button>
                                <button @click="filterOpen = false"
                                    class="flex-1 px-4 py-2 text-sm bg-[#FFD24C] hover:bg-[#FEC006] text-[#825700] font-medium rounded-lg transition-colors">
                                    Terapkan Filter
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Indikator Loading --}}
            <div wire:loading.flex class="justify-center items-center py-8">
                <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
                    <x-heroicon-s-arrow-path class="h-6 w-6 animate-spin" />
                    <span class="text-lg">Mencari ketersediaan aset...</span>
                </div>
            </div>

            {{-- Daftar Aset Grid --}}
            <div wire:loading.remove>
                @if ($assets->isNotEmpty())
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        @foreach ($assets as $asset)
                            <div wire:key="asset-{{ $asset->id }}"
                                class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden transition-transform transform hover:-translate-y-1 duration-300 flex flex-col">
                                <div class="p-5 flex-grow">
                                    <div class="flex justify-between items-start mb-2">
                                        <h3 class="font-bold text-lg text-gray-900 dark:text-gray-100 line-clamp-2">
                                            {{ $asset->name }}
                                        </h3>
                                        {{-- Logika Badge Ketersediaan --}}
                                        @if ($asset->available_stock > 0)
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                                                Tersedia
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200">
                                                Habis
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                        {{ $asset->assetCategory->name }}
                                    </p>
                                </div>
                                <div class="px-5 py-3 bg-gray-50 dark:bg-gray-700/50">
                                    <p class="text-sm text-gray-600 dark:text-gray-300">
                                        Stok Tersedia: <span
                                            class="font-semibold text-gray-800 dark:text-gray-100">{{ $asset->available_stock }}</span>
                                        / {{ $asset->quantity }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-16 px-6 bg-white dark:bg-gray-800 rounded-lg shadow-md">
                        <x-heroicon-o-x-circle class="mx-auto h-12 w-12 text-gray-400" />
                        <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-gray-200">Aset Tidak Ditemukan
                        </h3>
                        <p class="mt-2 text-sm text-gray-500">Tidak ada aset yang tersedia pada rentang tanggal yang
                            dipilih atau sesuai filter Anda.</p>
                    </div>
                @endif
            </div>

            {{-- Jadwal (Tidak berubah) --}}
            <div class="mt-20">
                <div class="text-center mb-10">
                    <h2 class="text-3xl font-bold text-gray-800 dark:text-gray-200">Jadwal Peminjaman</h2>
                    <p class="mt-2 text-md text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                        Jadwal yang sudah disetujui akan ditampilkan di sini.
                    </p>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 space-y-5">
                    @forelse ($borrowings as $borrowing)
                        <div
                            class="flex items-start space-x-4 p-4 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-200">
                            {{-- Tanggal --}}
                            <div class="flex-shrink-0 text-center bg-[#FFD24C] text-[#825700] rounded-lg p-3 w-20">
                                <p class="font-bold text-2xl">{{ $borrowing->start_datetime->format('d') }}</p>
                                <p class="text-sm uppercase tracking-wider">
                                    {{ $borrowing->start_datetime->format('M') }}</p>
                            </div>
                            {{-- Detail Peminjaman --}}
                            <div class="flex-grow">
                                <div class="flex justify-between items-center flex-wrap">
                                    <h4 class="font-bold text-gray-900 dark:text-gray-100">
                                        {{ $borrowing->event->name ?? 'N/A' }}</h4>
                                    <x-badge :color="$borrowing->status->color()">{{ $borrowing->status->label() }}</x-badge>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    Oleh: <span class="font-medium">{{ $borrowing->creator->name }}</span>
                                </p>
                                <div
                                    class="mt-2 text-xs text-gray-500 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 inline-block px-2 py-1 rounded">
                                    <span>{{ $borrowing->start_datetime->format('d M, H:i') }}</span>
                                    <span class="mx-1">&rarr;</span>
                                    <span>{{ $borrowing->end_datetime->format('d M, H:i') }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div
                            class="text-center py-12 px-6 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg">
                            <x-heroicon-o-calendar-days class="mx-auto h-12 w-12 text-gray-400" />
                            <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-gray-200">Belum Ada Jadwal
                            </h3>
                            <p class="mt-2 text-sm text-gray-500">Saat ini tidak ada jadwal peminjaman yang akan
                                datang.
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </main>

    <x-modal name="proposal-modal" maxWidth="6xl" focusable>
        <div class="p-4 sm:p-6 bg-gray-50 dark:bg-gray-900">
            <livewire:pages.borrowing.create-form-component />
        </div>
    </x-modal>
</div>
