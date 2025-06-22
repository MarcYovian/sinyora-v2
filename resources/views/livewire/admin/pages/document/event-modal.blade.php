<div>
    {{-- Modal utama dengan nama 'event-modal' --}}
    <x-modal name="event-modal" maxWidth="4xl" focusable>
        <div class="p-6 dark:bg-gray-800">

            {{-- Header Modal --}}
            <div class="flex justify-between items-start">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        Ringkasan Dokumen
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Jenis Dokumen: <span
                            class="font-semibold text-gray-700 dark:text-gray-300">{{ ucfirst($data['type'] ?? 'N/A') }}</span>
                    </p>
                </div>
                <button x-on:click="$dispatch('close')" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="mt-6 space-y-6">

                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-semibold leading-6 text-gray-900 dark:text-gray-200">Informasi Umum</h3>
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-2 text-sm">
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Nomor Surat</dt>
                            <dd class="text-gray-800 dark:text-gray-300 font-medium">
                                {{ $data['informasi_umum_dokumen']['nomor_surat'] ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Tanggal Surat</dt>
                            <dd class="text-gray-800 dark:text-gray-300 font-medium">
                                {{ $data['informasi_umum_dokumen']['tanggal_surat_dokumen'] ?? '-' }}</dd>
                        </div>
                        <div class="col-span-1 md:col-span-2">
                            <dt class="text-gray-500 dark:text-gray-400">Perihal</dt>
                            <dd class="text-gray-800 dark:text-gray-300 font-medium">
                                {{ $data['informasi_umum_dokumen']['perihal_surat'] ?? '-' }}</dd>
                        </div>
                        <div class="col-span-1 md:col-span-2">
                            <dt class="text-gray-500 dark:text-gray-400">Penerima</dt>
                            <dd class="text-gray-800 dark:text-gray-300 font-medium">
                                {{-- Menggabungkan array penerima menjadi satu string --}}
                                {{ !empty($data['informasi_umum_dokumen']['penerima_surat']) ? implode(', ', array_column($data['informasi_umum_dokumen']['penerima_surat'], 'name')) : '-' }}
                            </dd>
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-semibold leading-6 text-gray-900 dark:text-gray-200">Detail Kegiatan</h3>
                    <div class="mt-4 space-y-8">
                        @forelse ($data['detail_kegiatan'] ?? [] as $kegiatan)
                            @php
                                // Logika untuk menentukan warna badge kategori
                                $kategoriNama = $kegiatan['kategori_pancatugas']['nama'] ?? '';
                                $badgeColor = 'bg-gray-100 text-gray-800'; // Default
                                if (str_contains($kategoriNama, 'Liturgia')) {
                                    $badgeColor = 'bg-purple-100 text-purple-800';
                                }
                                if (str_contains($kategoriNama, 'Kerygma')) {
                                    $badgeColor = 'bg-blue-100 text-blue-800';
                                }
                                if (str_contains($kategoriNama, 'Koinonia')) {
                                    $badgeColor = 'bg-green-100 text-green-800';
                                }
                                if (str_contains($kategoriNama, 'Diakonia')) {
                                    $badgeColor = 'bg-yellow-100 text-yellow-800';
                                }
                                if (str_contains($kategoriNama, 'Martyria')) {
                                    $badgeColor = 'bg-red-100 text-red-800';
                                }
                            @endphp
                            <div
                                class="p-4 border border-gray-200 rounded-lg dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50">
                                <div class="flex items-center gap-x-3">
                                    <h4 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                                        {{ $kegiatan['nama_kegiatan_utama'] }}</h4>
                                    <span
                                        class="text-xs font-medium px-2.5 py-0.5 rounded-full {{ $badgeColor }}">{{ $kategoriNama }}</span>
                                </div>

                                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                                    {{-- Kolom Kiri --}}
                                    <div class="space-y-3">
                                        <div class="flex items-start">
                                            <svg class="h-5 w-5 text-gray-400 mr-3 flex-shrink-0"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                            </svg>
                                            <div>
                                                <p class="text-gray-500 dark:text-gray-400">Tanggal</p>
                                                <p class="font-medium text-gray-800 dark:text-gray-200">
                                                    {{ $kegiatan['tanggal_kegiatan'] }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-start">
                                            <svg class="h-5 w-5 text-gray-400 mr-3 flex-shrink-0"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <div>
                                                <p class="text-gray-500 dark:text-gray-400">Waktu</p>
                                                <p class="font-medium text-gray-800 dark:text-gray-200">
                                                    {{ $kegiatan['jam_kegiatan'] }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-start">
                                            <svg class="h-5 w-5 text-gray-400 mr-3 flex-shrink-0"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                            </svg>
                                            <div>
                                                <p class="text-gray-500 dark:text-gray-400">Lokasi</p>
                                                <p class="font-medium text-gray-800 dark:text-gray-200">
                                                    {{ $kegiatan['lokasi_kegiatan'] }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- Kolom Kanan --}}
                                    <div class="space-y-3">
                                        <div class="flex items-start">
                                            <svg class="h-5 w-5 text-gray-400 mr-3 flex-shrink-0"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                            </svg>
                                            <div>
                                                <p class="text-gray-500 dark:text-gray-400">Penanggung Jawab</p>
                                                <p class="font-medium text-gray-800 dark:text-gray-200">
                                                    {{ $kegiatan['penanggung_jawab'] ?: '-' }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-start">
                                            <svg class="h-5 w-5 text-gray-400 mr-3 flex-shrink-0"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                                            </svg>
                                            <div>
                                                <p class="text-gray-500 dark:text-gray-400">Kontak PJ</p>
                                                <p class="font-medium text-gray-800 dark:text-gray-200">
                                                    {{ $kegiatan['kontak_pj'] ?: '-' }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-start">
                                            <svg class="h-5 w-5 text-gray-400 mr-3 flex-shrink-0"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m-7.5-2.964A4.5 4.5 0 0112 10.5c2.485 0 4.5 2.015 4.5 4.5v.75m-9-3.75c-2.485 0-4.5 2.015-4.5 4.5v.75A4.5 4.5 0 0112 10.5a4.5 4.5 0 01-4.5 4.5m9-3.75h-.75a.375.375 0 00-.375.375v.75c0 .207.168.375.375.375h.75a.375.375 0 00.375-.375v-.75a.375.375 0 00-.375-.375z" />
                                            </svg>
                                            <div>
                                                <p class="text-gray-500 dark:text-gray-400">Jumlah Peserta</g>
                                                <p class="font-medium text-gray-800 dark:text-gray-200">
                                                    {{ $kegiatan['jumlah_peserta'] ? $kegiatan['jumlah_peserta'] . ' orang' : '-' }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Daftar Barang Dipinjam --}}
                                @if (!empty($kegiatan['barang_dipinjam']))
                                    <div class="mt-4 pt-4 border-t border-dashed border-gray-300 dark:border-gray-600">
                                        <h5 class="font-semibold text-gray-700 dark:text-gray-300">Barang Dipinjam</h5>
                                        <ul
                                            class="list-disc list-inside mt-2 space-y-1 text-gray-600 dark:text-gray-400">
                                            @foreach ($kegiatan['barang_dipinjam'] as $item)
                                                <li>{{ $item['item'] }} - {{ $item['jumlah'] }} unit</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <p class="text-center text-gray-500 dark:text-gray-400">Tidak ada detail kegiatan yang
                                ditemukan.</p>
                        @endforelse
                    </div>
                </div>


                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-semibold leading-6 text-gray-900 dark:text-gray-200">Penanda Tangan</h3>
                    <div class="mt-4 space-y-2 text-sm">
                        @forelse ($data['blok_penanda_tangan'] ?? [] as $signer)
                            <div>
                                <p class="font-medium text-gray-800 dark:text-gray-200">{{ $signer['nama'] }}</p>
                                <p class="text-gray-500 dark:text-gray-400">{{ $signer['jabatan'] }}</p>
                            </div>
                        @empty
                            <p class="text-gray-500 dark:text-gray-400">Tidak ada data penanda tangan.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Footer Modal --}}
            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 mt-8">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Tutup
                </x-secondary-button>

                <x-primary-button wire:click="save" wire:loading.attr="disabled"
                    class="w-full sm:w-auto justify-center">
                    <div wire:loading wire:target="save"
                        class="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2"></div>
                    @if ($data)
                        @if ($data['type'] === 'peminjaman')
                            {{ __('Simpan & Buat Peminjaman') }}
                        @elseif ($data['type'] === 'perizinan')
                            {{ __('Simpan & Buat Perizinan') }}
                        @elseif ($data['type'] === 'undangan')
                            {{ __('Simpan & Buat Undangan') }}
                        @endif
                    @else
                        {{ __('Simpan Data Dokumen') }}
                    @endif
                </x-primary-button>
            </div>
        </div>
    </x-modal>
</div>
