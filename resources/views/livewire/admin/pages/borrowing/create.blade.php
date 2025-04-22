<div>
    <header>
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Buat Peminjaman Baru') }}
        </h2>
    </header>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form wire:submit.prevent="store">
                        <!-- Informasi Peminjam -->
                        <div class="mb-8 border-b border-gray-200 dark:border-gray-700 pb-6">
                            <h3 class="text-lg font-medium mb-4">Informasi Peminjam</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="borrower" :value="__('Nama Peminjam')" />
                                    <x-text-input wire:model="form.borrower" id="borrower" type="text"
                                        class="mt-1 block w-full" />
                                    <x-input-error :messages="$errors->get('form.borrower')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="borrower_phone" :value="__('Nomor Telepon')" />
                                    <x-text-input wire:model="form.borrower_phone" id="borrower_phone" type="tel"
                                        class="mt-1 block w-full" />
                                    <x-input-error :messages="$errors->get('form.borrower_phone')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Periode Peminjaman -->
                        <div class="mb-8 border-b border-gray-200 dark:border-gray-700 pb-6">
                            <h3 class="text-lg font-medium mb-4">Periode Peminjaman</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="start_datetime" :value="__('Tanggal Mulai')" />
                                    <x-text-input wire:model="form.start_datetime" id="start_datetime"
                                        type="datetime-local" class="mt-1 block w-full" />
                                    <x-input-error :messages="$errors->get('form.start_datetime')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="end_datetime" :value="__('Tanggal Selesai')" />
                                    <x-text-input wire:model="form.end_datetime" id="end_datetime" type="datetime-local"
                                        class="mt-1 block w-full" />
                                    <x-input-error :messages="$errors->get('form.end_datetime')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Daftar Aset -->
                        <div class="mb-8 border-b border-gray-200 dark:border-gray-700 pb-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium">Daftar Aset Dipinjam</h3>
                                <x-button type="button" wire:click="addAsset" variant="secondary" size="sm">
                                    <x-heroicon-s-plus class="h-4 w-4 mr-1" />
                                    Tambah Aset
                                </x-button>
                            </div>

                            <div class="space-y-4">
                                @foreach ($form->assets as $index => $asset)
                                    <div class="border rounded-lg p-4 dark:border-gray-700"
                                        wire:key="asset-{{ $index }}">
                                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                            <!-- Pilih Aset -->
                                            <div class="md:col-span-5">
                                                <x-input-label :value="__('Pilih Aset')" />
                                                <x-select wire:model="form.assets.{{ $index }}.asset_id"
                                                    class="w-full mt-1">
                                                    <option value="">-- Pilih Aset --</option>
                                                    @foreach ($this->availableAssets as $assetOption)
                                                        <option value="{{ $assetOption->id }}"
                                                            @if (in_array($assetOption->id, $this->selectedAssetIds) ||
                                                                    $assetOption->quantity - $assetOption->borrowed_quantity <= 0) disabled @endif>
                                                            {{ $assetOption->name }} ({{ $assetOption->code }})
                                                            @if ($assetOption->quantity - $assetOption->borrowed_quantity > 0)
                                                                - Tersedia:
                                                                {{ $assetOption->quantity - $assetOption->borrowed_quantity }}
                                                            @else
                                                                - Habis
                                                            @endif
                                                        </option>
                                                    @endforeach
                                                </x-select>
                                                <x-input-error :messages="$errors->get('form.assets.' . $index . '.asset_id')" class="mt-2" />
                                            </div>

                                            <!-- Jumlah -->
                                            <div class="md:col-span-3">
                                                <x-input-label :value="__('Jumlah')" />
                                                <x-text-input wire:model="form.assets.{{ $index }}.quantity"
                                                    type="number" min="1" class="w-full mt-1" />
                                                <x-input-error :messages="$errors->get('form.assets.' . $index . '.quantity')" class="mt-2" />

                                                @if (isset($form->assets[$index]['asset_id']))
                                                    @php
                                                        $selectedAsset = $this->availableAssets->firstWhere(
                                                            'id',
                                                            $form->assets[$index]['asset_id'],
                                                        );
                                                    @endphp
                                                    @if ($selectedAsset)
                                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                            Stok tersedia:
                                                            {{ $selectedAsset->quantity - $selectedAsset->borrowed_quantity }}
                                                        </p>
                                                    @endif
                                                @endif
                                            </div>

                                            <!-- Tombol Hapus -->
                                            <div class="md:col-span-2 flex items-end">
                                                <x-button type="button" wire:click="removeAsset({{ $index }})"
                                                    variant="danger" size="sm">
                                                    <x-heroicon-s-trash class="h-4 w-4" />
                                                </x-button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Catatan -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium mb-4">Catatan</h3>
                            <div>
                                <x-input-label for="notes" :value="__('Catatan Tambahan (Opsional)')" />
                                <textarea wire:model="form.notes" id="notes" rows="3"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"></textarea>
                                <x-input-error :messages="$errors->get('form.notes')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Tombol Aksi -->
                        <div class="flex justify-end gap-3">
                            <x-button type="button" variant="secondary" wire:click="cancel" tag="a"
                                href="{{ route('admin.asset-borrowings.index') }}">
                                Batal
                            </x-button>
                            <x-button type="submit" variant="primary">
                                <span wire:loading.remove wire:target="store">Simpan Peminjaman</span>
                                <span wire:loading wire:target="store" class="flex items-center gap-2">
                                    <x-heroicon-s-arrow-path class="h-4 w-4 animate-spin" />
                                    Menyimpan...
                                </span>
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
