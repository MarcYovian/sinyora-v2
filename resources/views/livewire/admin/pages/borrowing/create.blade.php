<div>
    <header class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 leading-tight">
            {{ __('Buat Peminjaman Baru') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Isi detail peminjaman aset langkah demi langkah.
        </p>
    </header>

    <form wire:submit.prevent="store" class="space-y-8">
        {{-- Notifikasi Error Global --}}
        @if ($errors->isNotEmpty())
            <div class="bg-red-50 dark:bg-red-900/30 rounded-lg border border-red-200 dark:border-red-700/50 p-4">
                <div class="flex items-center gap-3 text-red-600 dark:text-red-400">
                    <x-heroicon-s-exclamation-triangle class="h-6 w-6 flex-shrink-0" />
                    <div>
                        <h3 class="font-semibold">{{ __('Oops, terjadi kesalahan!') }}</h3>
                        <p class="text-sm">{{ $errors->first() }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Layout Utama: Grid Responsif --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- Kolom Kiri: Input Utama --}}
            <div class="lg:col-span-2 space-y-8">

                {{-- LANGKAH 1: INFORMASI DASAR --}}
                <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm ring-1 ring-gray-900/5">
                    <div class="flex items-center gap-4 border-b dark:border-gray-700 pb-4 mb-6">
                        <div
                            class="bg-indigo-100 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 rounded-lg p-2">
                            <x-heroicon-o-user-circle class="h-6 w-6" />
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Informasi Peminjam &
                                Jadwal</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Siapa dan kapan aset akan digunakan.</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="borrower" :value="__('Nama Peminjam')" />
                            <x-text-input wire:model.lazy="form.borrower" id="borrower" type="text"
                                class="mt-1 block w-full" placeholder="Nama lengkap" />
                            <x-input-error :messages="$errors->get('form.borrower')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="borrower_phone" :value="__('Nomor Telepon')" />
                            <x-text-input wire:model.lazy="form.borrower_phone" id="borrower_phone" type="tel"
                                class="mt-1 block w-full" placeholder="0812..." />
                            <x-input-error :messages="$errors->get('form.borrower_phone')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="start_datetime" :value="__('Waktu Mulai Pinjam')" />
                            <x-text-input wire:model.live="form.start_datetime" id="start_datetime"
                                type="datetime-local" class="mt-1 block w-full" />
                            <x-input-error :messages="$errors->get('form.start_datetime')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="end_datetime" :value="__('Waktu Selesai Pinjam')" />
                            <x-text-input wire:model.live="form.end_datetime" id="end_datetime" type="datetime-local"
                                class="mt-1 block w-full" />
                            <x-input-error :messages="$errors->get('form.end_datetime')" class="mt-2" />
                        </div>
                    </div>
                </div>

                {{-- LANGKAH 2: DAFTAR ASET --}}
                <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm ring-1 ring-gray-900/5">
                    <div
                        class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 border-b dark:border-gray-700 pb-4 mb-6">
                        <div class="flex items-center gap-4">
                            <div
                                class="bg-teal-100 dark:bg-teal-500/20 text-teal-600 dark:text-teal-400 rounded-lg p-2">
                                <x-heroicon-o-cube class="h-6 w-6" />
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Aset yang Dipinjam
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Pilih satu atau lebih aset.</p>
                            </div>
                        </div>
                        <x-button type="button" wire:click="addAsset" variant="secondary" size="sm"
                            class="flex-shrink-0">
                            <x-heroicon-s-plus class="h-4 w-4 mr-1" />
                            <p class="text-sm font-semibold">{{ __('Tambah Aset') }}</p>
                        </x-button>
                    </div>
                    <div class="space-y-4">
                        @forelse ($form->assets as $index => $asset)
                            <div class="bg-gray-50 dark:bg-gray-800/50 border rounded-lg p-4 dark:border-gray-700 transition-all"
                                wire:key="asset-{{ $index }}">
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                                    <div class="md:col-span-7">
                                        <x-input-label :value="__('Pilih Aset')" for="asset_id_{{ $index }}" />
                                        <x-select wire:model.live="form.assets.{{ $index }}.asset_id"
                                            id="asset_id_{{ $index }}" class="w-full mt-1">
                                            <option value="">-- Pilih Aset --</option>
                                            @foreach ($this->availableAssets as $assetOption)
                                                @php
                                                    // Hitung sisa stok yang tersedia
                                                    $availableStock =
                                                        $assetOption->quantity - $assetOption->borrowed_quantity;
                                                @endphp
                                                <option value="{{ $assetOption->id }}"
                                                    @if (in_array($assetOption->id, $this->selectedAssetIds) && $assetOption->id != $asset['asset_id']) disabled @endif>
                                                    {{ $assetOption->name }} ({{ $assetOption->code }})
                                                    @if ($availableStock > 0)
                                                        - Tersedia: {{ $availableStock }}
                                                    @else
                                                        - Habis
                                                    @endif
                                                </option>
                                            @endforeach
                                        </x-select>
                                        <x-input-error :messages="$errors->get('form.assets.' . $index . '.asset_id')" class="mt-2" />
                                    </div>
                                    <div class="md:col-span-3">
                                        <x-input-label :value="__('Jumlah')" for="quantity_{{ $index }}" />
                                        <x-text-input wire:model.lazy="form.assets.{{ $index }}.quantity"
                                            id="quantity_{{ $index }}" type="number" min="1"
                                            class="w-full mt-1" />
                                        <x-input-error :messages="$errors->get('form.assets.' . $index . '.quantity')" class="mt-2" />
                                    </div>
                                    <div class="md:col-span-2">
                                        <x-button type="button" wire:click="removeAsset({{ $index }})"
                                            variant="danger" class="w-full justify-center">
                                            <x-heroicon-s-trash class="h-5 w-5" />
                                        </x-button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-10 px-4 border-2 border-dashed rounded-lg dark:border-gray-700">
                                <x-heroicon-o-cube class="mx-auto h-12 w-12 text-gray-400" />
                                <h4 class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-200">Belum ada aset
                                    dipilih</h4>
                                <p class="mt-1 text-sm text-gray-500">Klik tombol "Tambah Aset" untuk memulai.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>

            {{-- Kolom Kanan: Sidebar Ringkasan & Aktivitas Terkait --}}
            <div class="lg:col-span-1 space-y-8">

                {{-- KARTU AKTIVITAS TERKAIT --}}
                <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm ring-1 ring-gray-900/5">
                    <div class="flex items-center gap-4 border-b dark:border-gray-700 pb-4 mb-6">
                        <div class="bg-sky-100 dark:bg-sky-500/20 text-sky-600 dark:text-sky-400 rounded-lg p-2">
                            <x-heroicon-o-link class="h-6 w-6" />
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Aktivitas Terkait</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">(Opsional)</p>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <x-radio-card wire:model.live="form.borrowable_type" value="event">
                            <div class="flex items-center gap-3">
                                <x-heroicon-o-calendar-days class="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
                                <span class="font-semibold text-indigo-600 dark:text-indigo-400">
                                    Gunakan Event Terjadwal
                                </span>
                            </div>
                        </x-radio-card>
                        <x-radio-card wire:model.live="form.borrowable_type" value="activity">
                            <div class="flex items-center gap-3">
                                <x-heroicon-o-clipboard-document-list
                                    class="h-6 w-6 text-teal-600 dark:text-teal-400" />
                                <span class="font-semibold text-teal-600 dark:text-teal-400">
                                    Aktivitas Internal Baru
                                </span>
                            </div>
                        </x-radio-card>
                    </div>

                    <div class="mt-4 animate-fade-in" x-data="{ open: @entangle('form.borrowable_type') }">
                        <div x-show="open === 'event'" x-transition>
                            <x-input-label for="borrowable_id_event" :value="__('Pilih Event Kapel')" />
                            <x-select wire:model="form.borrowable_id" id="borrowable_id_event"
                                class="mt-1 block w-full">
                                <option value="">-- Pilih Event --</option>
                                @foreach ($this->events as $event)
                                    <option value="{{ $event->id }}">{{ $event->name }}
                                        ({{ $event->start_recurring->translatedFormat('d M Y') }})
                                    </option>
                                @endforeach
                            </x-select>
                            <x-input-error :messages="$errors->get('form.borrowable_id')" class="mt-2" />
                        </div>
                        <div x-show="open === 'activity'" x-transition class="space-y-4">
                            <div>
                                <x-input-label for="activity_name" :value="__('Nama Aktivitas')" />
                                <x-text-input wire:model.lazy="form.activity_name" id="activity_name" type="text"
                                    class="mt-1 block w-full" placeholder="e.g., Kerja Bakti" />
                                <x-input-error :messages="$errors->get('form.activity_name')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="activity_location" :value="__('Lokasi Aktivitas')" />
                                <x-text-input wire:model.lazy="form.activity_location" id="activity_location"
                                    type="text" class="mt-1 block w-full" placeholder="e.g., Area Taman" />
                                <x-input-error :messages="$errors->get('form.activity_location')" class="mt-2" />
                            </div>
                        </div>
                    </div>
                </div>

                {{-- KARTU CATATAN & SUBMIT --}}
                <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm ring-1 ring-gray-900/5">
                    <div class="flex items-center gap-4 border-b dark:border-gray-700 pb-4 mb-6">
                        <div
                            class="bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 rounded-lg p-2">
                            <x-heroicon-o-pencil-square class="h-6 w-6" />
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Catatan & Finalisasi
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Informasi tambahan.</p>
                        </div>
                    </div>
                    <div>
                        <x-input-label for="notes" :value="__('Catatan Tambahan (Opsional)')" />
                        <textarea wire:model.lazy="form.notes" id="notes" rows="4"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"></textarea>
                        <x-input-error :messages="$errors->get('form.notes')" class="mt-2" />
                    </div>
                    <div class="flex justify-end gap-3 mt-6 pt-6 border-t dark:border-gray-700">
                        <x-button type="button" variant="secondary" tag="a"
                            href="{{ route('admin.asset-borrowings.index') }}">
                            Batal
                        </x-button>
                        <x-button type="submit" variant="primary">
                            <span wire:loading.remove wire:target="store">Simpan Peminjaman</span>
                            <span wire:loading wire:target="store" class="flex items-center gap-2">
                                <x-heroicon-s-arrow-path class="h-4 w-4 animate-spin" />
                                Memproses...
                            </span>
                        </x-button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
