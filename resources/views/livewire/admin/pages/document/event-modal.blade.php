<div>
    {{-- Modal utama dengan nama 'event-modal' --}}
    <x-modal name="event-modal" maxWidth="4xl" focusable>
        @if (!empty($data))
            <div class="p-6 bg-white dark:bg-gray-800">

                {{-- Header Modal --}}
                <div class="flex justify-between items-start pb-4 border-b border-gray-200 dark:border-gray-700 mb-6">
                    {{-- Added bottom border --}}
                    <div>
                        <h2 class="text-2xl font-extrabold text-gray-900 dark:text-gray-100"> {{-- Larger and bolder title --}}
                            Ringkasan Dokumen
                        </h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            Jenis Dokumen: <span
                                class="font-bold text-gray-800 dark:text-gray-200">{{ ucfirst($data['type'] ?? 'N/A') }}</span>
                            {{-- Bolder document type --}}
                        </p>
                    </div>
                    <button x-on:click="$dispatch('close')"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition duration-150 ease-in-out rounded-md p-1 -mt-1 -mr-1">
                        {{-- Better hover effect and padding --}}
                        <x-heroicon-s-x-mark class="h-6 w-6" /> {{-- Replaced with Heroicon --}}
                    </button>
                </div>

                <div class="space-y-8"> {{-- Increased space between main sections --}}

                    {{-- Informasi Umum --}}
                    <section>
                        <h3 class="text-xl font-bold leading-6 text-gray-900 dark:text-gray-100 mb-4">
                            {{-- Larger, bolder title --}}
                            <x-heroicon-s-document-text class="inline-block h-5 w-5 mr-2 text-gray-500" /> Informasi
                            Umum
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-base"> {{-- Increased gaps, larger text --}}
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">Nomor Surat</dt>
                                <dd class="text-gray-800 dark:text-gray-300 font-semibold"> {{-- Bolder text --}}
                                    {{ $data['document_information']['document_number'] ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">Tanggal Surat</dt>
                                <dd class="text-gray-800 dark:text-gray-300 font-semibold"> {{-- Bolder text --}}
                                    {{ Carbon\Carbon::parse($data['document_information']['document_date']['date'])->translatedFormat('d F Y') ?? '-' }}
                                </dd>
                            </div>
                            <div class="col-span-1 sm:col-span-2"> {{-- Full width on small screens, too --}}
                                <dt class="text-gray-500 dark:text-gray-400">Perihal</dt>
                                <dd class="text-gray-800 dark:text-gray-300 font-semibold"> {{-- Bolder text --}}
                                    {{ implode(', ', $data['document_information']['subjects'] ?? []) ?: '-' }}</dd>
                            </div>
                            <div class="col-span-1 sm:col-span-2"> {{-- Full width on small screens, too --}}
                                <dt class="text-gray-500 dark:text-gray-400 mb-1">Penerima</dt> {{-- Added margin-bottom --}}
                                <dd class="text-gray-800 dark:text-gray-300">
                                    @forelse (array_column($data['document_information']['recipients'] ?? [], 'name') as $recipient)
                                        <span
                                            class="inline-flex items-center px-3 py-1 mr-2 mb-2 rounded-full text-sm font-medium bg-blue-50 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300">
                                            <x-heroicon-s-user class="h-4 w-4 mr-1.5" /> {{ $recipient }}
                                        </span>
                                    @empty
                                        <span class="text-gray-500">-</span>
                                    @endforelse
                                </dd>
                            </div>
                        </div>
                    </section>

                    {{-- Detail Kegiatan --}}
                    <section class="pt-6 border-t border-gray-200 dark:border-gray-700">
                        <h3 class="text-xl font-bold leading-6 text-gray-900 dark:text-gray-100 mb-4">
                            <x-heroicon-s-calendar-days class="inline-block h-5 w-5 mr-2 text-gray-500" /> Detail
                            Kegiatan
                        </h3>
                        <div class="mt-4 space-y-6"> {{-- Adjusted spacing --}}
                            @forelse ($data['events'] ?? [] as $kegiatan)
                                @php
                                    // Logika untuk menentukan warna badge kategori
                                    $kategoriNama = $kegiatan['fivetask_categories']['nama'] ?? '';
                                    $badgeClass = 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200'; // Default
                                    if (str_contains($kategoriNama, 'Liturgia')) {
                                        $badgeClass =
                                            'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200';
                                    } elseif (str_contains($kategoriNama, 'Kerygma')) {
                                        $badgeClass = 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
                                    } elseif (str_contains($kategoriNama, 'Koinonia')) {
                                        $badgeClass =
                                            'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
                                    } elseif (str_contains($kategoriNama, 'Diakonia')) {
                                        $badgeClass =
                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
                                    } elseif (str_contains($kategoriNama, 'Martyria')) {
                                        $badgeClass = 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
                                    }
                                @endphp
                                <div
                                    class="p-5 border border-gray-200 rounded-xl dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 shadow-sm">
                                    {{-- Larger padding, slightly more prominent card --}}
                                    <div
                                        class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-x-4 gap-y-2 mb-4">
                                        <h4 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                                            {{ $kegiatan['eventName'] }}
                                        </h4>
                                        <span
                                            class="text-sm font-medium px-3 py-1 rounded-full {{ $badgeClass }} whitespace-nowrap">
                                            {{ $kategoriNama }}
                                        </span>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-base">
                                        {{-- Consistent text size and spacing --}}
                                        {{-- Kolom Kiri --}}
                                        <div class="space-y-3">
                                            <div class="flex items-start">
                                                <x-heroicon-s-calendar
                                                    class="h-5 w-5 text-gray-500 mr-3 flex-shrink-0" />
                                                <div>
                                                    <p class="text-gray-500 dark:text-gray-400">Tanggal</p>
                                                    <p class="font-semibold text-gray-800 dark:text-gray-200">
                                                        {{ $kegiatan['formatted_date'] ?? '-' }}</p>
                                                </div>
                                            </div>
                                            <div class="flex items-start">
                                                <x-heroicon-s-clock class="h-5 w-5 text-gray-500 mr-3 flex-shrink-0" />
                                                <div>
                                                    <p class="text-gray-500 dark:text-gray-400">Waktu</p>
                                                    <p class="font-semibold text-gray-800 dark:text-gray-200">
                                                        {{ $kegiatan['formatted_time'] ?? '-' }}</p>
                                                </div>
                                            </div>
                                            <div class="flex items-start">
                                                <x-heroicon-s-map-pin
                                                    class="h-5 w-5 text-gray-500 mr-3 flex-shrink-0" />
                                                <div>
                                                    <p class="text-gray-500 dark:text-gray-400">Lokasi</p>
                                                    <p class="font-semibold text-gray-800 dark:text-gray-200">
                                                        {{ $kegiatan['location'] ?? '-' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- Kolom Kanan --}}
                                        <div class="space-y-3">
                                            <div class="flex items-start">
                                                <x-heroicon-s-user-group
                                                    class="h-5 w-5 text-gray-500 mr-3 flex-shrink-0" />
                                                {{-- Changed to user-group for clarity --}}
                                                <div>
                                                    <p class="text-gray-500 dark:text-gray-400">Penanggung Jawab</p>
                                                    <div class="font-semibold text-gray-800 dark:text-gray-200">
                                                        @forelse ($kegiatan['organizers'] ?? [] as $organizer)
                                                            <p>{{ $organizer['name'] }}</p>
                                                        @empty
                                                            <p>-</p>
                                                        @endforelse
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex items-start">
                                                <x-heroicon-s-phone class="h-5 w-5 text-gray-500 mr-3 flex-shrink-0" />
                                                <div>
                                                    <p class="text-gray-500 dark:text-gray-400">Kontak PJ</p>
                                                    <div class="font-semibold text-gray-800 dark:text-gray-200">
                                                        @forelse ($kegiatan['organizers'] ?? [] as $organizer)
                                                            <p>{{ $organizer['contact'] }}</p>
                                                        @empty
                                                            <p>-</p>
                                                        @endforelse
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex items-start">
                                                <x-heroicon-s-users class="h-5 w-5 text-gray-500 mr-3 flex-shrink-0" />
                                                <div>
                                                    <p class="text-gray-500 dark:text-gray-400">Peserta</p>
                                                    <p class="font-semibold text-gray-800 dark:text-gray-200">
                                                        {{ $kegiatan['attendees'] ?? '-' }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Daftar Barang Dipinjam --}}
                                    @if (!empty($kegiatan['equipment']))
                                        <div
                                            class="mt-6 pt-4 border-t border-dashed border-gray-300 dark:border-gray-600">
                                            {{-- Increased top margin --}}
                                            <h5 class="font-bold text-gray-800 dark:text-gray-200 mb-3">
                                                {{-- Bolder, darker heading --}}
                                                <x-heroicon-s-cube class="inline-block h-5 w-5 mr-2 text-gray-500" />
                                                Barang Dipinjam
                                            </h5>
                                            <ul class="list-none space-y-2 text-gray-700 dark:text-gray-300">
                                                {{-- Removed disc, increased space, darker text --}}
                                                @foreach ($kegiatan['equipment'] as $item)
                                                    <li class="flex items-center gap-x-2">
                                                        <x-heroicon-s-check
                                                            class="h-4 w-4 text-green-500 flex-shrink-0" />
                                                        {{-- Checkmark icon --}}
                                                        <span><span class="font-medium">{{ $item['item'] }}</span> -
                                                            {{ $item['quantity'] }} unit</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div
                                    class="text-center text-gray-500 dark:text-gray-400 p-4 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-700/50">
                                    <p class="font-medium">Tidak ada detail kegiatan yang ditemukan.</p>
                                </div>
                            @endforelse
                        </div>
                    </section>


                    {{-- Penanda Tangan --}}
                    <section class="pt-6 border-t border-gray-200 dark:border-gray-700">
                        <h3 class="text-xl font-bold leading-6 text-gray-900 dark:text-gray-100 mb-4">
                            <x-heroicon-s-pencil-square class="inline-block h-5 w-5 mr-2 text-gray-500" /> Penanda
                            Tangan
                        </h3>
                        <div class="mt-4 space-y-4 text-base"> {{-- Increased spacing, larger text --}}
                            @forelse ($data['signature_blocks'] ?? [] as $signer)
                                <div
                                    class="p-3 rounded-lg bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600">
                                    {{-- Small card for each signer --}}
                                    <p class="font-semibold text-gray-800 dark:text-gray-200">{{ $signer['name'] }}</p>
                                    <p class="text-gray-600 dark:text-gray-400">{{ $signer['position'] }}</p>
                                </div>
                            @empty
                                <div
                                    class="text-gray-500 dark:text-gray-400 p-4 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-700/50">
                                    <p class="font-medium">Tidak ada data penanda tangan yang ditemukan.</p>
                                </div>
                            @endforelse
                        </div>
                    </section>
                </div>

                {{-- Footer Modal --}}
                <div
                    class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                    {{-- Added top border --}}
                    <x-secondary-button x-on:click="$dispatch('close')">
                        Tutup
                    </x-secondary-button>

                    <x-primary-button wire:click="save" wire:loading.attr="disabled"
                        class="w-full sm:w-auto justify-center">
                        <div wire:loading wire:target="save"
                            class="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2"></div>
                        @if ($data['type'] === 'peminjaman')
                            {{ __('Simpan & Buat Peminjaman') }}
                        @elseif ($data['type'] === 'perizinan')
                            {{ __('Simpan & Buat Perizinan') }}
                        @elseif ($data['type'] === 'undangan')
                            {{ __('Simpan & Buat Undangan') }}
                        @else
                            {{ __('Simpan Data Dokumen') }}
                        @endif
                    </x-primary-button>
                </div>
            </div>
        @else
            <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                <p>Memuat data...</p>
            </div>
        @endif
    </x-modal>
</div>
