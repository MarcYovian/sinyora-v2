<form wire:submit.prevent="save" class="space-y-6" enctype="multipart/form-data">
    {{-- 1. Bagian Informasi Penanggung Jawab / Kontak --}}
    <div>
        <h3 class="text-md font-medium text-gray-800">Informasi Penanggung Jawab / Kontak</h3>
        <div class="mt-4 grid grid-cols-1 gap-y-4 gap-x-6 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <x-input-label for="guest_name_upload" value="Nama Lengkap Anda" />
                <x-text-input wire:model="name" id="guest_name_upload" type="text" class="block w-full mt-1"
                    placeholder="Masukkan nama lengkap" />
                <x-input-error :messages="$errors->get('name')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="guest_email_upload" value="Alamat Email" />
                <x-text-input wire:model="email" id="guest_email_upload" type="email" class="block w-full mt-1"
                    placeholder="cth: emailanda@example.com" />
                <x-input-error :messages="$errors->get('email')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="guest_phone_upload" value="Nomor Telepon (WA)" />
                <x-text-input wire:model="phone" id="guest_phone_upload" type="text" class="block w-full mt-1"
                    placeholder="cth: 081234567890" />
                <x-input-error :messages="$errors->get('phone')" class="mt-1" />
            </div>
        </div>
    </div>

    {{-- 2. Bagian Upload Dokumen (UI/UX Baru) --}}
    <div>
        <x-input-label for="proposal_document" value="Unggah Dokumen Proposal" />

        {{-- Awal dari Alpine.js Component --}}
        <div x-data="{ isUploading: false, progress: 0, fileName: '', isDragging: false }" x-on:livewire-upload-start="isUploading = true"
            x-on:livewire-upload-finish="isUploading = false" x-on:livewire-upload-error="isUploading = false"
            x-on:livewire-upload-progress="progress = $event.detail.progress" class="mt-2">

            {{-- Hidden File Input --}}
            <input wire:model="attachment" id="proposal_document" type="file" class="hidden"
                accept=".pdf, image/png, image/jpeg, image/jpg" x-ref="fileInput"
                @change="fileName = $refs.fileInput.files[0] ? $refs.fileInput.files[0].name : ''">

            {{-- Dropzone Area --}}
            <div x-show="!fileName" @click="$refs.fileInput.click()" @dragover.prevent="isDragging = true"
                @dragleave.prevent="isDragging = false"
                @drop.prevent="isDragging = false; $refs.fileInput.files = $event.dataTransfer.files; $refs.fileInput.dispatchEvent(new Event('change'))"
                class="relative flex flex-col items-center justify-center w-full p-8 border-2 border-dashed rounded-lg cursor-pointer transition-colors"
                :class="isDragging ? 'border-[#825700] bg-[#FFD24C]/20' : 'border-gray-300 hover:border-gray-400'">

                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-gray-400" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M7 16a4 4 0 01-4-4V7a4 4 0 014-4h4l2 2m0 0l2-2h4a4 4 0 014 4v5a4 4 0 01-4 4H7z" />
                </svg>
                <p class="mt-4 text-sm font-medium text-gray-700">
                    <span class="text-[#825700]">Klik untuk memilih</span> atau seret & lepas file
                </p>
                <p class="mt-1 text-xs text-gray-500">
                    PDF, PNG, JPG (maks. 5MB)
                </p>
            </div>

            {{-- File Preview & Progress --}}
            <div x-show="fileName" style="display: none;"
                class="relative w-full p-4 border border-gray-200 rounded-lg flex items-center space-x-3">
                <div class="flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-[#825700]" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-800 truncate" x-text="fileName"></p>
                    <div x-show="isUploading" class="mt-1">
                        <div class="w-full bg-gray-200 rounded-full h-1.5">
                            <div class="bg-[#825700] h-1.5 rounded-full" :style="`width: ${progress}%`"></div>
                        </div>
                    </div>
                    <p x-show="!isUploading" class="text-xs text-gray-500">File siap untuk diunggah</p>
                </div>
                <div class="flex-shrink-0">
                    {{-- TOMBOL HAPUS YANG DIPERBAIKI --}}
                    <button wire:click.prevent="removeDocument" @click="fileName = ''; $refs.fileInput.value = null;"
                        type="button" class="text-gray-400 hover:text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <x-input-error :messages="$errors->get('attachment')" class="mt-1" />
        </div>
    </div>

    <div class="mt-8 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 border-t pt-4 dark:border-gray-700">
        <x-secondary-button x-on:click="$dispatch('close')" class="w-full sm:w-auto justify-center">
            Batal
        </x-secondary-button>

        <x-primary-button type="submit" class="w-full sm:w-auto justify-center" wire:loading.attr="disabled"
            wire:target="save">
            <x-heroicon-s-check class="w-5 h-5 mr-2" wire:loading.remove wire:target="save" />
            <div wire:loading wire:target="save"
                class="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2 dark:border-gray-400">
            </div>
            <span wire:loading.remove wire:target="save">{{ __('Ajukan Proposal') }}</span>
            <span wire:loading wire:target="save">{{ __('Diajukan...') }}</span>
        </x-primary-button>
    </div>
</form>
