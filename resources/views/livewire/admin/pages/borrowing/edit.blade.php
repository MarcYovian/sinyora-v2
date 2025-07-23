<div>
    <header class="mb-8">
        <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">
            {{ __('Edit Peminjaman') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Perbarui detail peminjaman aset di bawah ini.
        </p>
    </header>

    <form wire:submit.prevent="save" class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
        <div class="p-6 md:p-8 space-y-10"> {{-- Sedikit menambah spasi antar bagian --}}

            {{-- Informasi Event Terkait (sekarang di dalam kartu) --}}
            @if ($form->borrowing && $form->borrowing->event)
                <div class="space-y-6"> {{-- Wrapper untuk bagian event --}}
                    <div>
                        <h3 class="text-base font-semibold leading-7 text-gray-900 dark:text-gray-100">Informasi Event
                            Terkait</h3>
                        <p class="mt-1 text-sm leading-6 text-gray-600 dark:text-gray-400">
                            Peminjaman ini terhubung dengan event berikut (read-only).
                        </p>
                    </div>

                    <div
                        class="grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-6 border p-4 rounded-lg dark:border-gray-700 bg-gray-50 dark:bg-white/5">
                        {{-- Nama Event --}}
                        <div class="sm:col-span-4">
                            <label class="block text-sm font-medium leading-6 text-gray-500 dark:text-gray-400">Nama
                                Event</label>
                            <p class="mt-1 text-base font-semibold text-gray-900 dark:text-gray-100">
                                {{ $form->borrowing->event->name }}
                            </p>
                        </div>

                        {{-- Status Event --}}
                        <div class="sm:col-span-2">
                            <label
                                class="block text-sm font-medium leading-6 text-gray-500 dark:text-gray-400">Status</label>
                            <p class="mt-1 text-base font-semibold text-gray-900 dark:text-gray-100">
                                {{ Str::ucfirst($form->borrowing->event->status->label()) }}
                            </p>
                        </div>

                        {{-- Tanggal Mulai & Selesai Event --}}
                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium leading-6 text-gray-500 dark:text-gray-400">Tanggal
                                Mulai Event</label>
                            <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">
                                {{ $form->borrowing->event->start_recurring ? $form->borrowing->event->start_recurring->format('d F Y') : '-' }}
                            </p>
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium leading-6 text-gray-500 dark:text-gray-400">Tanggal
                                Selesai Event</label>
                            <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">
                                {{ $form->borrowing->event->end_recurring ? $form->borrowing->event->end_recurring->format('d F Y') : '-' }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Semua bagian form sekarang dipisahkan oleh garis border untuk kejelasan --}}
            <hr class="border-gray-900/10 dark:border-gray-700">

            {{-- Form Peminjaman --}}
            <div class="space-y-6">
                <div>
                    <h3 class="text-base font-semibold leading-7 text-gray-900 dark:text-gray-100">Informasi Peminjam
                    </h3>
                    <p class="mt-1 text-sm leading-6 text-gray-600 dark:text-gray-400">Masukkan nama dan nomor kontak
                        yang bisa dihubungi.</p>
                </div>
                <div class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6">
                    <div class="sm:col-span-3">
                        <x-input-label for="borrower" :value="__('Nama Peminjam')" />
                        <x-text-input wire:model="form.borrower" id="borrower" type="text"
                            class="mt-1 block w-full" />
                        <x-input-error :messages="$errors->get('form.borrower')" class="mt-2" />
                    </div>
                    <div class="sm:col-span-3">
                        <x-input-label for="borrower_phone" :value="__('Nomor Telepon')" />
                        <x-text-input wire:model="form.borrower_phone" id="borrower_phone" type="tel"
                            class="mt-1 block w-full" />
                        <x-input-error :messages="$errors->get('form.borrower_phone')" class="mt-2" />
                    </div>
                </div>
            </div>

            <hr class="border-gray-900/10 dark:border-gray-700">

            <div class="space-y-6">
                <div>
                    <h3 class="text-base font-semibold leading-7 text-gray-900 dark:text-gray-100">Periode Peminjaman
                    </h3>
                    <p class="mt-1 text-sm leading-6 text-gray-600 dark:text-gray-400">Tentukan rentang waktu peminjaman
                        aset.</p>
                </div>
                <div class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6">
                    <div class="sm:col-span-3">
                        <x-input-label for="start_datetime" :value="__('Tanggal & Waktu Mulai')" />
                        <x-text-input wire:model="form.start_datetime" id="start_datetime" type="datetime-local"
                            class="mt-1 block w-full" />
                        <x-input-error :messages="$errors->get('form.start_datetime')" class="mt-2" />
                    </div>
                    <div class="sm:col-span-3">
                        <x-input-label for="end_datetime" :value="__('Tanggal & Waktu Selesai')" />
                        <x-text-input wire:model="form.end_datetime" id="end_datetime" type="datetime-local"
                            class="mt-1 block w-full" />
                        <x-input-error :messages="$errors->get('form.end_datetime')" class="mt-2" />
                    </div>
                </div>
            </div>

            <hr class="border-gray-900/10 dark:border-gray-700">

            <div class="space-y-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-base font-semibold leading-7 text-gray-900 dark:text-gray-100">Aset yang
                            Dipinjam</h3>
                        <p class="mt-1 text-sm leading-6 text-gray-600 dark:text-gray-400">Pilih satu atau lebih aset
                            yang akan dipinjam.</p>
                    </div>
                    <x-button type="button" wire:click="addAsset" variant="secondary">
                        <x-heroicon-s-plus class="h-4 w-4 mr-2" />
                        Tambah Aset
                    </x-button>
                </div>

                <div class="space-y-4">
                    @forelse ($form->assets as $index => $asset)
                        <div wire:key="asset-{{ $index }}"
                            class="flex items-start gap-4 p-4 border rounded-lg dark:border-gray-700 bg-gray-50 dark:bg-white/5">
                            <div class="flex-grow grid grid-cols-1 sm:grid-cols-8 gap-4">
                                <div class="sm:col-span-5">
                                    <x-input-label for="asset_id_{{ $index }}" :value="__('Pilih Aset')"
                                        class="sr-only" />
                                    <x-select wire:model.live="form.assets.{{ $index }}.asset_id"
                                        id="asset_id_{{ $index }}" class="w-full">
                                        <option value="">-- Pilih Aset --</option>
                                        @foreach ($this->availableAssets as $assetOption)
                                            <option value="{{ $assetOption->id }}"
                                                @if (in_array($assetOption->id, $this->selectedAssetIds) && $assetOption->id != $asset['asset_id']) disabled @endif>
                                                {{ $assetOption->name }} ({{ $assetOption->code }}) - Stok:
                                                {{ $assetOption->quantity - $assetOption->borrowed_quantity }}
                                            </option>
                                        @endforeach
                                    </x-select>
                                    <x-input-error :messages="$errors->get('form.assets.' . $index . '.asset_id')" class="mt-2" />
                                </div>

                                <div class="sm:col-span-3">
                                    <x-input-label for="quantity_{{ $index }}" :value="__('Jumlah')"
                                        class="sr-only" />
                                    <x-text-input wire:model="form.assets.{{ $index }}.quantity"
                                        id="quantity_{{ $index }}" type="number" min="1"
                                        placeholder="Jumlah" class="w-full" />
                                    <x-input-error :messages="$errors->get('form.assets.' . $index . '.quantity')" class="mt-2" />
                                </div>
                            </div>

                            <div class="flex-shrink-0 pt-1">
                                <x-button type="button" wire:click="removeAsset({{ $index }})" variant="danger"
                                    size="icon">
                                    <span class="sr-only">Hapus Aset</span>
                                    <x-heroicon-s-trash class="h-5 w-5" />
                                </x-button>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 px-4 border-2 border-dashed rounded-lg dark:border-gray-700">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" aria-hidden="true">
                                <path vector-effect="non-scaling-stroke" stroke-linecap="round"
                                    stroke-linejoin="round" stroke-width="2"
                                    d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-100">Belum ada aset</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Klik tombol "Tambah Aset" untuk
                                memulai.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <hr class="border-gray-900/10 dark:border-gray-700">

            <div class="space-y-6">
                <div>
                    <h3 class="text-base font-semibold leading-7 text-gray-900 dark:text-gray-100">Catatan
                        (Opsional)</h3>
                    <p class="mt-1 text-sm leading-6 text-gray-600 dark:text-gray-400">Tambahkan catatan atau
                        keterangan lain jika diperlukan.</p>
                </div>
                <div class="mt-6">
                    <textarea wire:model="form.notes" id="notes" rows="4"
                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 dark:bg-white/5"></textarea>
                    <x-input-error :messages="$errors->get('form.notes')" class="mt-2" />
                </div>
            </div>

        </div>

        <div class="flex items-center justify-end gap-x-6 border-t border-gray-900/10 dark:border-gray-700 px-6 py-4">
            <x-button type="button" variant="secondary" wire:click="cancel" tag="a"
                href="{{ route('admin.asset-borrowings.index') }}">
                Batal
            </x-button>
            <x-button type="submit" variant="primary">
                <span wire:loading.remove wire:target="save">
                    Simpan Perubahan
                </span>
                <span wire:loading wire:target="save" class="flex items-center gap-2">
                    <x-heroicon-s-arrow-path class="h-4 w-4 animate-spin" />
                    Menyimpan...
                </span>
            </x-button>
        </div>
    </form>
</div>
