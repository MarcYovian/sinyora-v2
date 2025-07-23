{{-- Inisialisasi Alpine.js untuk Tab --}}
<form wire:submit.prevent="save">
    <div x-data="{ currentTab: 'dataDiri' }">
        {{-- Navigasi Tab --}}
        <div class="border-b border-gray-200 mb-6">
            <nav class="-mb-px flex space-x-6" aria-label="Tabs">
                {{-- Tombol Tab 1: Data Diri --}}
                <a href="#" @click.prevent="currentTab = 'dataDiri'"
                    :class="{
                        'border-[#FFD24C] text-[#825700]': currentTab === 'dataDiri',
                        'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': currentTab !== 'dataDiri'
                    }"
                    class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                    Langkah 1: Data Diri
                </a>

                {{-- Tombol Tab 2: Data Event --}}
                <a href="#" @click.prevent="currentTab = 'dataBorrowing'"
                    :class="{
                        'border-[#FFD24C] text-[#825700]': currentTab === 'dataBorrowing',
                        'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': currentTab !== 'dataBorrowing'
                    }"
                    class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                    Langkah 2: Detail Peminjaman
                </a>
            </nav>
        </div>

        {{-- Konten Tab 1: Data Diri Guest --}}
        <div x-show="currentTab === 'dataDiri'" class="space-y-4">
            <h3 class="text-md font-medium text-gray-800">Informasi Penanggung Jawab / Kontak</h3>
            <div class="space-y-2">
                <x-input-label for="guest_name" value="Nama Lengkap Anda" />
                <x-text-input wire:model.blur="guest_name" id="guest_name" type="text" class="block w-full"
                    placeholder="Masukkan nama lengkap" />
                <x-input-error :messages="$errors->get('guest_name')" />
            </div>
            <div class="space-y-2">
                <x-input-label for="guest_email" value="Alamat Email" />
                <x-text-input wire:model.blur="guest_email" id="guest_email" type="email" class="block w-full"
                    placeholder="cth: emailanda@example.com" />
                <x-input-error :messages="$errors->get('guest_email')" />
            </div>
            <div class="space-y-2">
                <x-input-label for="guest_phone" value="Nomor Telepon (WA)" />
                <x-text-input wire:model.blur="guest_phone" id="guest_phone" type="text" class="block w-full"
                    placeholder="cth: 081234567890" />
                <x-input-error :messages="$errors->get('guest_phone')" />
            </div>

            {{-- Tombol Navigasi ke Tab Berikutnya --}}
            <div class="flex justify-end pt-4">
                <button type="button" @click="currentTab = 'dataBorrowing'"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-[#825700] hover:bg-[#6b4900] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#825700]">
                    Selanjutnya
                    <svg xmlns="http://www.w3.org/2000/svg" class="ml-2 h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Konten Tab 2: Data Event --}}
        {{-- Konten Tab 2: Data Event (UI/UX Baru) --}}
        <div x-show="currentTab === 'dataBorrowing'" class="space-y-8">
            <!-- Periode Peminjaman -->
            <div class="mb-8 border-b border-gray-200 dark:border-gray-700 pb-6">
                <h3 class="text-lg font-medium mb-4">Periode Peminjaman</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="start_datetime" :value="__('Tanggal Mulai')" />
                        <x-text-input wire:model="start_datetime" id="start_datetime" type="datetime-local"
                            class="mt-1 block w-full" />
                        <x-input-error :messages="$errors->get('start_datetime')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="end_datetime" :value="__('Tanggal Selesai')" />
                        <x-text-input wire:model="end_datetime" id="end_datetime" type="datetime-local"
                            class="mt-1 block w-full" />
                        <x-input-error :messages="$errors->get('end_datetime')" class="mt-2" />
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
                    @foreach ($assets as $index => $asset)
                        <div class="border rounded-lg p-4 dark:border-gray-700" wire:key="asset-{{ $index }}">
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                <!-- Pilih Aset -->
                                <div class="md:col-span-5">
                                    <x-input-label :value="__('Pilih Aset')" />
                                    <x-select wire:model="assets.{{ $index }}.asset_id" class="w-full mt-1">
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
                                    <x-input-error :messages="$errors->get('assets.' . $index . '.asset_id')" class="mt-2" />
                                </div>

                                <!-- Jumlah -->
                                <div class="md:col-span-3">
                                    <x-input-label :value="__('Jumlah')" />
                                    <x-text-input wire:model="assets.{{ $index }}.quantity" type="number"
                                        min="1" class="w-full mt-1" />
                                    <x-input-error :messages="$errors->get('assets.' . $index . '.quantity')" class="mt-2" />

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
                    <textarea wire:model="notes" id="notes" rows="3"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"></textarea>
                    <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                </div>
            </div>

            {{-- Tombol Navigasi Kembali --}}
            <div class="flex justify-start pt-2">
                <button type="button" @click="currentTab = 'dataDiri'"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                    </svg>
                    Kembali
                </button>
            </div>
        </div>

    </div>

    <div class="mt-8 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 border-t pt-4 dark:border-gray-700">
        <x-secondary-button x-on:click="$dispatch('close')" class="w-full sm:w-auto justify-center">
            Batal
        </x-secondary-button>

        <x-primary-button type="submit" class="w-full sm:w-auto justify-center" wire:loading.attr="disabled"
            wire:target="syncPermission">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor"
                wire:loading.remove>
                <path
                    d="M2.003 5.884L10 2l7.997 3.884A2 2 0 0019 7.616l-7.5 3.232a3 3 0 01-3 0L1 7.616a2 2 0 00-1.997-1.732z" />
                <path d="M1 9.616l7.5 3.232a3 3 0 003 0L19 9.616V14a2 2 0 01-2 2H3a2 2 0 01-2-2V9.616z" />
            </svg>
            <div wire:loading wire:target="save"
                class="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2"></div>
            <span wire:loading.remove>Ajukan Proposal</span>
            <span wire:loading>Mengajukan...</span>
        </x-primary-button>
    </div>
</form>
