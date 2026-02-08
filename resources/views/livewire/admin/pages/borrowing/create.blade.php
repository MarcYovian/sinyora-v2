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
                        <x-button type="button" wire:click="openAssetModal" variant="secondary" size="sm"
                            class="flex-shrink-0 disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="!$form->start_datetime || !$form->end_datetime"
                            wire:loading.attr="disabled"
                            title="{{ !$form->start_datetime || !$form->end_datetime ? 'Pilih waktu pinjam terlebih dahulu' : 'Tambah Aset' }}">
                            <x-heroicon-s-plus class="h-4 w-4 mr-1" />
                            <p class="text-sm font-semibold">{{ __('Tambah Aset') }}</p>
                        </x-button>
                    </div>
                    <div class="space-y-4">
                        @forelse ($form->assets as $index => $assetItem)
                            @php
                                $assetDetail = $this->selectedAssetsDetails[$assetItem['asset_id']] ?? null;
                            @endphp
                            @if($assetDetail)
                            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-4 transition-all hover:shadow-md"
                                wire:key="asset-row-{{ $index }}">
                                <div class="flex items-start gap-4">
                                    {{-- Asset Image/Icon --}}
                                    <div class="flex-shrink-0 w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-lg flex items-center justify-center overflow-hidden">
                                        @if($assetDetail->image)
                                            <img src="{{ Storage::url($assetDetail->image) }}" alt="{{ $assetDetail->name }}" class="w-full h-full object-cover">
                                        @else
                                            <x-heroicon-o-cube class="w-8 h-8 text-gray-400" />
                                        @endif
                                    </div>

                                    {{-- Asset Info --}}
                                    <div class="flex-grow min-w-0">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">
                                                    {{ $assetDetail->name }}
                                                </h4>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                    Kode: <span class="font-mono">{{ $assetDetail->code }}</span>
                                                </p>
                                            </div>
                                            <button type="button" wire:click="removeAsset({{ $index }})" 
                                                wire:loading.attr="disabled"
                                                wire:target="removeAsset({{ $index }})"
                                                class="text-gray-400 hover:text-red-500 transition-colors p-1 disabled:opacity-50">
                                                <span wire:loading.remove wire:target="removeAsset({{ $index }})">
                                                    <x-heroicon-o-trash class="w-5 h-5" />
                                                </span>
                                                <span wire:loading wire:target="removeAsset({{ $index }})">
                                                    <x-heroicon-s-arrow-path class="w-5 h-5 animate-spin" />
                                                </span>
                                            </button>
                                        </div>

                                        {{-- Quantity Input --}}
                                        <div class="mt-3 flex items-center gap-3">
                                            <div class="w-32">
                                                <x-input-label :value="__('Jumlah')" for="quantity_{{ $index }}" class="sr-only" />
                                                <div class="relative rounded-md shadow-sm">
                                                    <x-text-input wire:model.lazy="form.assets.{{ $index }}.quantity"
                                                        id="quantity_{{ $index }}" type="number" min="1" max="{{ $assetDetail->quantity - ($assetDetail->borrowed_quantity ?? 0) }}"
                                                        class="block w-full sm:text-sm rounded-md" placeholder="Qty" />
                                                </div>
                                                <p class="text-xs text-gray-500 mt-1">
                                                    Max: {{ $assetDetail->quantity - ($assetDetail->borrowed_quantity ?? 0) }}
                                                </p>
                                            </div>
                                            <x-input-error :messages="$errors->get('form.assets.' . $index . '.quantity')" class="mt-0" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        @empty
                            <div class="text-center py-10 px-4 border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800/30">
                                <x-heroicon-o-cube class="mx-auto h-12 w-12 text-gray-400" />
                                <h4 class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-200">Belum ada aset dipilih</h4>
                                <p class="mt-1 text-sm text-gray-500">
                                    @if(!$form->start_datetime || !$form->end_datetime)
                                        Silakan pilih waktu pinjam terlebih dahulu.
                                    @else
                                        Klik tombol "Tambah Aset" untuk mulai memilih.
                                    @endif
                                </p>
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

    {{-- Modal Pilih Aset --}}
    <x-modal name="asset-selection-modal" :show="$isAssetModalOpen" maxWidth="4xl" focusable>
        <div class="p-6 bg-white dark:bg-gray-800">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    {{ __('Pilih Aset untuk Dipinjam') }}
                </h2>
                <button type="button" @click="$dispatch('close')"
                    class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                    <span class="sr-only">Close</span>
                    <x-heroicon-s-x-mark class="h-6 w-6" />
                </button>
            </div>

            {{-- Search Bar --}}
            <div class="mb-6">
                <x-search wire:model.live.debounce.300ms="assetSearch" placeholder="Cari aset berdasarkan nama atau kode..." />
            </div>

            {{-- Asset Grid --}}
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 max-h-[60vh] overflow-y-auto p-1">
                @forelse ($this->availableAssets as $asset)
                    @php
                        $availableStock = $asset->quantity - $asset->borrowed_quantity;
                        $isSelected = in_array($asset->id, array_column($form->assets, 'asset_id'));
                    @endphp
                    <button type="button" 
                        wire:click="selectAsset({{ $asset->id }})"
                        class="group relative flex flex-col bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden hover:shadow-lg transition-all text-left focus:outline-none focus:ring-2 focus:ring-indigo-500 {{ $availableStock <= 0 ? 'opacity-60 cursor-not-allowed' : '' }}"
                        @if($availableStock <= 0) disabled @endif>
                        
                        {{-- Image --}}
                        <div class="aspect-square bg-gray-100 dark:bg-gray-800 w-full relative overflow-hidden">
                            @if($asset->image)
                                <img src="{{ Storage::url($asset->image) }}" alt="{{ $asset->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <x-heroicon-o-cube class="w-12 h-12 text-gray-400" />
                                </div>
                            @endif
                            
                            @if($isSelected)
                                <div class="absolute inset-0 bg-indigo-500/20 flex items-center justify-center">
                                    <div class="bg-indigo-600 text-white rounded-full p-1 shadow-sm">
                                        <x-heroicon-s-check class="w-6 h-6" />
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Info --}}
                        <div class="p-3 flex flex-col flex-grow w-full">
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate w-full" title="{{ $asset->name }}">
                                {{ $asset->name }}
                            </h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 font-mono mt-0.5 truncate">
                                {{ $asset->code }}
                            </p>
                            
                            <div class="mt-auto pt-3 flex flex-col gap-1 text-xs">
                                <span class="{{ $availableStock > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }} font-medium">
                                    {{ $availableStock > 0 ? $availableStock . ' Tersedia' : 'Habis' }}
                                </span>
                            </div>
                        </div>
                    </button>
                @empty
                    <div class="col-span-full py-10 text-center text-gray-500 dark:text-gray-400">
                        <x-heroicon-o-magnifying-glass class="w-10 h-10 mx-auto mb-2 text-gray-300 dark:text-gray-600" />
                        <p>Tidak ada aset yang ditemukan.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </x-modal>
</div>
