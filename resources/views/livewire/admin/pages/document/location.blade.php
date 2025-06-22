<div>
    <x-modal name="location-modal" maxWidth="6xl" focusable>
        <div class="p-6 bg-white dark:bg-gray-800">
            <header class="mb-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                    Konfirmasi dan Lengkapi Data Lokasi
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Terdapat data lokasi yang tidak dikenali. Mohon perbaiki data sebelum melanjutkan.
                </p>
            </header>

            @if (empty($this->data))
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
                    </div>

                    {{-- Skeleton untuk Footer --}}
                    <footer class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                        <div class="h-9 w-24 rounded bg-gray-200 dark:bg-gray-700"></div>
                        <div class="h-9 w-48 rounded bg-gray-300 dark:bg-gray-600"></div>
                    </footer>
                </div>
            @else
                @foreach ($this->data['detail_kegiatan'] ?? [] as $kegiatanIndex => $kegiatan)
                    <fieldset class="border-t border-gray-200 dark:border-gray-700 pt-6"
                        wire:key="kegiatan-{{ $kegiatanIndex }}">
                        <legend class="text-base font-semibold text-gray-900 dark:text-gray-200">
                            Data Kegiatan #{{ $loop->iteration }}
                        </legend>
                        <div
                            class="mt-4 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-lg border border-slate-200 dark:border-slate-700">
                            {{-- Tampilkan Nama dan Waktu Kegiatan --}}
                            <dl class="mb-4 space-y-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nama Kegiatan</dt>
                                    <dd class="text-sm font-semibold text-gray-900 dark:text-gray-200">
                                        {{ $kegiatan['nama_kegiatan_utama'] ?? 'N/A' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Waktu Pelaksanaan
                                    </dt>
                                    <dd class="text-sm font-semibold text-gray-900 dark:text-gray-200">
                                        @if (!empty($kegiatan['start_date']))
                                            {{ \Carbon\Carbon::parse($kegiatan['start_date'])->translatedFormat('l, d M Y, H:i') }}
                                            -
                                            {{ \Carbon\Carbon::parse($kegiatan['end_date'])->translatedFormat('H:i') }}
                                        @endif
                                    </dd>
                                </div>
                            </dl>

                            {{-- Form Konfirmasi Lokasi --}}
                            <div class="border-t border-gray-200 dark:border-gray-600 pt-4">
                                @if ($kegiatan['location_data']['match_status'] === 'unmatched')
                                    {{-- Tampilkan jika lokasi TIDAK COCOK --}}
                                    <div class="p-3 bg-yellow-50 dark:bg-yellow-500/10 rounded-lg mb-3">
                                        <p class="text-sm text-yellow-800 dark:text-yellow-300">
                                            Lokasi dari Dokumen: <strong
                                                class="font-semibold">{{ $kegiatan['location_data']['original_name'] }}</strong>
                                        </p>
                                    </div>

                                    <x-input-label for="location-{{ $kegiatanIndex }}"
                                        value="Pilih Lokasi yang Sesuai" />
                                    <x-select id="location-{{ $kegiatanIndex }}" class="mt-1 w-full"
                                        wire:model.live="selectedLocationIds.{{ $kegiatanIndex }}">
                                        <option value="">-- Pilih Lokasi --</option>
                                        @foreach ($allLocations as $loc)
                                            <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                                        @endforeach
                                    </x-select>

                                    @if (empty($showCreateForms[$kegiatanIndex]))
                                        <button wire:click="toggleCreateForm({{ $kegiatanIndex }})" type="button"
                                            class="mt-2 text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">+
                                            Buat Lokasi Baru</button>
                                    @else
                                        <div
                                            class="mt-3 p-3 border rounded-md border-sky-300 dark:border-sky-700 bg-sky-50 dark:bg-sky-900/20">
                                            <x-input-label for="new-location-{{ $kegiatanIndex }}"
                                                value="Nama Lokasi Baru" />
                                            <div class="mt-2 flex items-center gap-x-2">
                                                <x-text-input wire:model="newLocationNames.{{ $kegiatanIndex }}"
                                                    id="new-location-{{ $kegiatanIndex }}" class="flex-grow" />
                                                <x-button wire:click="createNewLocation({{ $kegiatanIndex }})"
                                                    variant="primary" size="sm">Simpan</x-button>
                                                <x-button wire:click="toggleCreateForm({{ $kegiatanIndex }})"
                                                    variant="secondary" size="sm">Batal</x-button>
                                            </div>
                                            <x-input-error :messages="$errors->get('newLocationNames.' . $kegiatanIndex)" class="mt-2" />
                                        </div>
                                    @endif
                                @else
                                    {{-- Tampilkan jika lokasi SUDAH COCOK --}}
                                    <div class="flex items-center gap-2">
                                        <div class="flex-shrink-0 text-green-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                                fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Lokasi
                                                Terkonfirmasi</dt>
                                            <dd class="text-sm font-semibold text-gray-900 dark:text-gray-200">
                                                {{ $kegiatan['location_data']['name'] }}</dd>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </fieldset>
                @endforeach

                <footer class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                    <x-secondary-button x-on:click="$dispatch('close')">
                        Batal
                    </x-secondary-button>
                    <x-primary-button wire:click="save" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="save">
                            Simpan data organisasi
                        </span>
                        <span wire:loading wire:target="save">
                            Menyimpan...
                        </span>
                    </x-primary-button>
                </footer>
            @endif
        </div>
    </x-modal>
</div>
