<div>
    <x-modal name="organization-modal" maxWidth="6xl" focusable>
        <div class="p-6 bg-white dark:bg-gray-800">
            <header class="mb-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                    Konfirmasi dan Lengkapi Data Organisasi
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Terdapat data organisasi yang tidak dikenali. Mohon perbaiki data sebelum melanjutkan.
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
                <fieldset class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <legend class="text-base font-semibold text-gray-900 dark:text-gray-200">
                        Konfirmasi Organisasi
                    </legend>
                    <div class="mt-4 grid grid-cols-1 gap-y-6 sm:grid-cols-6 sm:gap-x-6">
                        <div class="sm:col-span-6 p-4 bg-yellow-50 dark:bg-yellow-500/10 rounded-lg">
                            <p class="text-sm text-yellow-800 dark:text-yellow-300">
                                Nama Organisasi dari Dokumen:
                                <strong class="font-semibold">{{ $unmatchedOrganizationName }}</strong>
                            </p>
                        </div>

                        <div class="sm:col-span-6">
                            <x-input-label for="select-organization" value="Pilih Organisasi yang Sesuai" />
                            <x-select id="select-organization" class="mt-1 w-full"
                                wire:model.live="selectedOrganizationId" :disabled="$showCreateForm">
                                <option value="">-- Pilih Organisasi --</option>
                                @foreach ($allOrganizations as $org)
                                    <option value="{{ $org->id }}">{{ $org->name }}</option>
                                @endforeach
                            </x-select>
                        </div>

                        <div class="sm:col-span-6">
                            <div class="relative">
                                <div class="absolute inset-0 flex items-center" aria-hidden="true">
                                    <div class="w-full border-t border-gray-300 dark:border-gray-600"></div>
                                </div>
                                <div class="relative flex justify-center">
                                    <span class="bg-white dark:bg-gray-800 px-2 text-sm text-gray-500">atau</span>
                                </div>
                            </div>
                        </div>

                        <div class="sm:col-span-6">
                            @if ($showCreateForm)
                                <div
                                    class="p-4 border rounded-md border-sky-300 dark:border-sky-700 bg-sky-50 dark:bg-sky-900/20">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <x-input-label for="new-organization-name" value="Nama Organisasi Baru" />
                                            <x-text-input wire:model="newOrganizationName" id="new-organization-name"
                                                class="mt-1 w-full" placeholder="Contoh: BEM Fakultas Teknik" />
                                            <x-input-error :messages="$errors->get('newOrganizationName')" class="mt-2" />
                                        </div>
                                        <div>
                                            <x-input-label for="new-organization-code" value="Kode Organisasi" />
                                            <x-text-input wire:model="newOrganizationCode" id="new-organization-code"
                                                class="mt-1 w-full" placeholder="Contoh: BEM-FT" />
                                            <x-input-error :messages="$errors->get('newOrganizationCode')" class="mt-2" />
                                        </div>
                                    </div>

                                    <div class="mt-4 flex items-center justify-end gap-x-3">
                                        <x-button wire:click="createNewOrganization" variant="primary">Simpan</x-button>
                                        <x-button wire:click="toggleCreateForm" variant="secondary">Batal</x-button>
                                    </div>
                                </div>
                            @else
                                <x-button type="button" wire:click="toggleCreateForm" variant="outline"
                                    class="w-full justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    Buat Organisasi Baru
                                </x-button>
                            @endif
                        </div>
                    </div>
                </fieldset>

                @foreach ($this->data['detail_kegiatan'] ?? [] as $kegiatanIndex => $kegiatan)
                    <div class="space-y-6 mt-6">
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
                    </div>
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
