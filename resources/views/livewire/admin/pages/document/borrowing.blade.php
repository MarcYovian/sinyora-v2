<div>
    <x-modal name="borrowing-modal" maxWidth="6xl" focusable>
        <div class="p-6 bg-white dark:bg-gray-800">
            <header class="mb-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                    Konfirmasi dan Lengkapi Data Peminjaman
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Beberapa aset dari dokumen tidak dikenali. Mohon perbaiki data sebelum melanjutkan.
                </p>
            </header>
            @if (empty($this->borrowingData))
                <div class="animate-pulse">
                    <div class="space-y-6">
                        {{-- Skeleton untuk Detail Peminjam --}}
                        <fieldset class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <div class="h-5 rounded bg-gray-200 dark:bg-gray-700 w-1/3 mb-4"></div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="h-10 rounded bg-gray-200 dark:bg-gray-700"></div>
                                <div class="h-10 rounded bg-gray-200 dark:bg-gray-700"></div>
                                <div class="h-10 rounded bg-gray-200 dark:bg-gray-700"></div>
                                <div class="h-10 rounded bg-gray-200 dark:bg-gray-700"></div>
                                <div class="sm:col-span-2 h-20 rounded bg-gray-200 dark:bg-gray-700"></div>
                            </div>
                        </fieldset>

                        {{-- Skeleton untuk Daftar Aset --}}
                        <fieldset class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <div class="h-5 rounded bg-gray-200 dark:bg-gray-700 w-1/3 mb-4"></div>
                            <div class="mt-4 space-y-4">
                                {{-- Placeholder untuk 3 item aset --}}
                                @for ($i = 0; $i < 3; $i++)
                                    <div class="p-4 rounded-lg bg-gray-100 dark:bg-gray-700/50">
                                        <div class="flex justify-between items-center">
                                            <div class="w-3/4 space-y-2">
                                                <div class="h-5 rounded bg-gray-300 dark:bg-gray-600 w-1/2"></div>
                                                <div class="h-3 rounded bg-gray-300 dark:bg-gray-600 w-1/4"></div>
                                            </div>
                                            <div class="h-8 w-20 rounded bg-gray-300 dark:bg-gray-600"></div>
                                        </div>
                                    </div>
                                @endfor
                            </div>
                        </fieldset>
                    </div>

                    {{-- Skeleton untuk Footer --}}
                    <footer class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                        <div class="h-9 w-24 rounded bg-gray-200 dark:bg-gray-700"></div>
                        <div class="h-9 w-48 rounded bg-gray-300 dark:bg-gray-600"></div>
                    </footer>
                </div>
            @else
                @foreach ($this->borrowingData['detail_kegiatan'] ?? [] as $kegiatanIndex => $kegiatan)
                    <div class="space-y-6">
                        <fieldset>
                            {{-- Tidak perlu border di atas untuk fieldset pertama --}}
                            <legend class="text-base font-semibold text-gray-900 dark:text-gray-200">Data Kegiatan
                            </legend>
                            <div
                                class="mt-4 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-lg border border-slate-200 dark:border-slate-700">
                                <dl class="space-y-3">
                                    {{-- Nama Kegiatan --}}
                                    <div class="flex items-start gap-3">
                                        <div class="flex-shrink-0 text-slate-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                                fill="currentColor">
                                                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                                                <path fill-rule="evenodd"
                                                    d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h.01a1 1 0 100-2H10zm3 0a1 1 0 000 2h.01a1 1 0 100-2H13zM7 12a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h.01a1 1 0 100-2H10zm3 0a1 1 0 000 2h.01a1 1 0 100-2H13z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                                Nama Kegiatan
                                            </dt>
                                            <dd class="text-sm font-semibold text-gray-900 dark:text-gray-200">
                                                {{ $kegiatan['nama_kegiatan_utama'] ?? 'Tidak ada data' }}
                                            </dd>
                                        </div>
                                    </div>
                                    {{-- Lokasi Kegiatan --}}
                                    <div class="flex items-start gap-3">
                                        <div class="flex-shrink-0 text-slate-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                                fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                                Lokasi
                                            </dt>
                                            <dd class="text-sm font-semibold text-gray-900 dark:text-gray-200">
                                                {{ $kegiatan['lokasi_kegiatan'] ?? 'Tidak ada data' }}
                                            </dd>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-3">
                                        <div class="flex-shrink-0 text-slate-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                                fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.414-1.414L11 9.586V6z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Waktu
                                                Pelaksanaan</dt>
                                            <dd class="text-sm font-semibold text-gray-900 dark:text-gray-200">
                                                {{-- Menampilkan tanggal dan waktu yang sudah diproses --}}
                                                {{ $kegiatan['tanggal_kegiatan'] ?? 'Tidak ada data' }}
                                                {{ $kegiatan['jam_kegiatan'] ? ', ' . $kegiatan['jam_kegiatan'] : '' }}
                                            </dd>
                                        </div>
                                    </div>
                                </dl>
                            </div>
                        </fieldset>
                        <fieldset class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <legend class="text-base font-semibold text-gray-900 dark:text-gray-200">Detail Peminjam
                            </legend>
                            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="borrower_name" value="Nama Peminjam" />
                                    @if ($kegiatan['penanggung_jawab'])
                                        <x-text-input id="borrower_name" type="text" class="mt-1 block w-full"
                                            wire:model.defer="borrowingData.detail_kegiatan.{{ $kegiatanIndex }}.penanggung_jawab" />
                                    @else
                                        <x-select
                                            wire:model="borrowingData.detail_kegiatan.{{ $kegiatanIndex }}.penanggung_jawab"
                                            id="borrower_name" class="w-full">
                                            <option value="">{{ __('Pilih Penanggung Jawab') }}</option>
                                            @foreach ($this->borrowingData['blok_penanda_tangan'] ?? [] as $penanggungJawab)
                                                <option value="{{ $penanggungJawab['nama'] }}">
                                                    {{ $penanggungJawab['nama'] }} | {{ $penanggungJawab['jabatan'] }}
                                                </option>
                                            @endforeach
                                        </x-select>
                                    @endif
                                    <x-input-error :messages="$errors->get(
                                        'borrowingData.detail_kegiatan.{{ $kegiatanIndex }}.penanggung_jawab',
                                    )" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="borrower_phone" value="Kontak Peminjam (WA)" />
                                    <x-text-input id="borrower_phone" type="text" class="mt-1 block w-full"
                                        wire:model.defer="borrowingData.detail_kegiatan.{{ $kegiatanIndex }}.kontak_pj" />
                                    <x-input-error :messages="$errors->get(
                                        'borrowingData.detail_kegiatan.{{ $kegiatanIndex }}.kontak_pj',
                                    )" class="mt-2" />
                                </div>
                                <div class="sm:col-span-2">
                                    <x-input-label for="notes" value="Catatan Tambahan" />
                                    <textarea id="notes" wire:model.defer="borrowingData.detail_kegiatan.{{ $kegiatanIndex }}.catatan_tambahan"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"></textarea>
                                    <x-input-error :messages="$errors->get(
                                        'borrowingData.detail_kegiatan.{{ $kegiatanIndex }}.catatan_tambahan',
                                    )" class="mt-2" />
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <legend class="text-base font-semibold text-gray-900 dark:text-gray-200">Aset yang Dipinjam
                            </legend>
                            <div class="mt-4 space-y-4">
                                @foreach ($kegiatan['barang_dipinjam'] ?? [] as $assetIndex => $asset)
                                    <div wire:key="asset-{{ $kegiatanIndex }}-{{ $assetIndex }}"
                                        class="p-4 rounded-lg
                                            {{ ($asset['match_status'] ?? 'unmatched') === 'matched' ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800' : 'bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800' }}">
                                        @if (($asset['match_status'] ?? 'unmatched') === 'matched')
                                            <div class="flex justify-between items-center">
                                                <div>
                                                    <p class="font-bold text-gray-800 dark:text-gray-200">
                                                        {{ $asset['item'] }} | {{ $asset['original_name'] }}
                                                    </p>
                                                    <p class="text-sm text-green-600 dark:text-green-400">Terverifikasi
                                                    </p>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <label for="qty-{{ $assetIndex }}"
                                                        class="text-sm">Jumlah:</label>
                                                    <x-text-input id="qty-{{ $assetIndex }}" type="number"
                                                        class="w-20 text-center" min="1"
                                                        wire:model.defer="borrowingData.detail_kegiatan.{{ $kegiatanIndex }}.barang_dipinjam.{{ $assetIndex }}.jumlah" />
                                                </div>
                                            </div>
                                        @else
                                            <div wire:loading.class="opacity-50"
                                                wire:target="linkAsset, removeUnmatchedAsset">
                                                <div class="flex items-center gap-2 mb-3">
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                        class="h-6 w-6 text-amber-500" viewBox="0 0 20 20"
                                                        fill="currentColor">
                                                        <path fill-rule="evenodd"
                                                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.21 3.03-1.742 3.03H4.42c-1.532 0-2.492-1.696-1.742-3.03l5.58-9.92zM10 13a1 1 0 110-2 1 1 0 010 2zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                    <p class="font-semibold text-amber-800 dark:text-amber-300">
                                                        Aset tidak dikenali: <span
                                                            class="font-bold text-red-600 dark:text-red-400">"{{ $asset['original_name'] ?? $asset['item'] }}"</span>
                                                    </p>
                                                </div>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    {{-- Opsi 1: Tautkan ke yang ada --}}
                                                    <div>
                                                        <x-input-label value="Opsi 1: Tautkan ke Aset yang Ada" />
                                                        <select
                                                            wire:change="linkAsset({{ $kegiatanIndex }}, {{ $assetIndex }}, $event.target.value)"
                                                            class="mt-1 w-full text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                                            <option value="">Pilih aset yang benar...</option>

                                                            @php
                                                                $availableAssets = $this->getAvailableAssetsForEvent(
                                                                    $kegiatan['dates'][0]['start'],
                                                                    $kegiatan['dates'][0]['end'],
                                                                );
                                                                $selectedAssetIds = $this->getSelectedAssetIds(
                                                                    $kegiatanIndex,
                                                                );
                                                            @endphp

                                                            @foreach ($availableAssets as $availableAsset)
                                                                <option value="{{ $availableAsset->id }}"
                                                                    @if (in_array($availableAsset->id, $selectedAssetIds) || $availableAsset->available_stock <= 0) disabled @endif>
                                                                    {{ $availableAsset->name }}
                                                                    @if ($availableAsset->available_stock > 0)
                                                                        - Tersedia:
                                                                        {{ $availableAsset->available_stock }}
                                                                    @else
                                                                        - (Stok Habis)
                                                                    @endif
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <x-input-label value="Opsi 2: Aset Tidak Tersedia" />
                                                        <x-button type="button" variant="danger" size="sm"
                                                            class="w-full mt-1 justify-center"
                                                            wire:click="removeUnmatchedAsset({{ $kegiatanIndex }}, {{ $assetIndex }})">
                                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                                class="h-4 w-4 mr-2" viewBox="0 0 20 20"
                                                                fill="currentColor">
                                                                <path fill-rule="evenodd"
                                                                    d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                                                    clip-rule="evenodd" />
                                                            </svg>
                                                            Hapus dari Daftar
                                                        </x-button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </fieldset>
                    </div>
                @endforeach

                <footer class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                    <x-secondary-button x-on:click="$dispatch('close')">
                        Batal
                    </x-secondary-button>
                    <x-primary-button wire:click="saveBorrowing" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="saveBorrowing">
                            Buat Permintaan Peminjaman
                        </span>
                        <span wire:loading wire:target="saveBorrowing">
                            Menyimpan...
                        </span>
                    </x-primary-button>
                </footer>
            @endif

        </div>
    </x-modal>
</div>
