<div class="font-sans antialiased text-gray-900 dark:text-gray-100">
    <section class="relative bg-cover bg-center bg-no-repeat py-32 px-6"
        style="background-image: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('{{ asset('images/1.jpg') }}');">
        <div class="container mx-auto text-center text-white max-w-4xl">
            <h1 class="text-4xl sm:text-5xl md:text-6xl font-bold mb-6 leading-tight">
                Sistem Peminjaman Aset Kapel
            </h1>
            <p class="text-lg sm:text-xl md:text-2xl mb-8 opacity-90 max-w-3xl mx-auto">
                Lihat ketersediaan dan ajukan peminjaman aset untuk mendukung kegiatan Anda di Kapel St. Yohanes Rasul.
            </p>
            <div class="flex flex-wrap justify-center gap-4">
                {{-- Tombol ini bisa mengarah ke halaman form atau memunculkan modal --}}
                <button wire:click="createRequest"
                    class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-500 text-white font-semibold rounded-full shadow-lg transition-colors duration-300">
                    <x-heroicon-o-plus-circle class="h-6 w-6 mr-2" />
                    Ajukan Peminjaman
                </button>
                <a href="#asset-list"
                    class="inline-flex items-center px-6 py-3 border border-white text-white hover:bg-white/10 font-medium rounded-full transition-colors duration-300">
                    <x-heroicon-o-list-bullet class="h-6 w-6 mr-2" />
                    Lihat Daftar Aset
                </a>
            </div>
        </div>
    </section>

    <div class="py-12" id="asset-list">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                {{-- Kolom Kiri: Daftar Aset --}}
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-xl font-semibold mb-4 text-gray-800 dark:text-gray-200">Daftar Aset Kapel</h3>
                        <div class="space-y-4">
                            @forelse ($assets as $asset)
                                <div
                                    class="p-4 border dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h4 class="font-bold text-lg text-gray-900 dark:text-gray-100">
                                                {{ $asset->name }}</h4>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                {{ $asset->assetCategory->name }}</p>
                                        </div>
                                        {{-- Badge untuk status aset (contoh: Tersedia, Dipinjam) --}}
                                        {{-- <x-badge :color="$asset->status->getColor()">{{ $asset->status->getLabel() }}</x-badge> --}}
                                    </div>
                                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Jumlah:
                                        {{ $asset->quantity }}</p>
                                </div>
                            @empty
                                <div
                                    class="text-center p-4 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg">
                                    <p class="text-gray-500 dark:text-gray-400">Belum ada aset yang terdaftar.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Kolom Kanan: Jadwal Peminjaman --}}
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                                Jadwal Peminjaman Mendatang
                            </h3>
                        </div>

                        <div class="p-6 space-y-5">
                            @forelse ($borrowings as $borrowing)
                                <div class="flex items-start space-x-4">
                                    {{-- Tanggal --}}
                                    <div
                                        class="flex-shrink-0 text-center bg-gray-100 dark:bg-gray-700 rounded-lg p-3 w-24">
                                        <p class="text-gray-900 dark:text-gray-100 font-bold text-lg">
                                            {{ $borrowing->start_datetime->format('d') }}</p>
                                        <p class="text-gray-600 dark:text-gray-400 text-sm uppercase">
                                            {{ $borrowing->start_datetime->format('M') }}</p>
                                    </div>
                                    {{-- Detail Peminjaman --}}
                                    <div
                                        class="flex-grow p-4 border-l-4 rounded-r-lg {{ $borrowing->status->borderColor() }} bg-gray-50 dark:bg-gray-700/30">
                                        <div class="flex justify-between items-center">
                                            <h4 class="font-bold text-gray-900 dark:text-gray-100">
                                                {{ $borrowing->event->name ?? 'N/A' }}</h4>
                                            <x-badge :color="$borrowing->status->color()">{{ $borrowing->status->label() }}</x-badge>
                                        </div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            Oleh: <span class="font-medium">{{ $borrowing->creator->name }}</span>
                                        </p>
                                        <div class="mt-2 text-xs text-gray-500 dark:text-gray-300">
                                            <span>Mulai: {{ $borrowing->start_datetime->format('d M Y, H:i') }}</span>
                                            <span class="mx-1">|</span>
                                            <span>Selesai: {{ $borrowing->end_datetime->format('d M Y, H:i') }}</span>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div
                                    class="text-center py-8 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg">
                                    <x-heroicon-o-calendar-days class="mx-auto h-12 w-12 text-gray-400" />
                                    <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-200">Tidak Ada
                                        Jadwal Peminjaman</h3>
                                    <p class="mt-1 text-sm text-gray-500">Saat ini tidak ada jadwal peminjaman yang akan
                                        datang.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-modal name="proposal-modal" maxWidth="6xl" focusable>
        <div class="p-4 sm:p-6">
            <livewire:pages.borrowing.create-form-component />
        </div>
    </x-modal>
</div>
