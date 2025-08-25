<div class="mt-6 pt-6 border-t border-slate-200 dark:border-slate-700 flex-shrink-0">
    <div class="flex flex-col space-y-3">
        <a href="{{ Storage::url($this->doc->document_path) }}" download target="_blank"
            class="inline-flex items-center justify-center w-full px-4 py-2 text-sm font-medium text-blue-700 bg-blue-100 rounded-lg hover:bg-blue-200 dark:bg-blue-900/50 dark:text-blue-300 dark:hover:bg-blue-900">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                    clip-rule="evenodd" />
            </svg>
            Unduh Dokumen Asli
        </a>
        <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
            <x-secondary-button wire:click="closeModal"
                class="w-full sm:w-auto justify-center">Tutup</x-secondary-button>
            @if ($doc->status !== App\Enums\DocumentStatus::DONE)
                <x-primary-button wire:click="save" wire:loading.attr="disabled"
                    class="w-full sm:w-auto justify-center">
                    <div wire:loading wire:target="save"
                        class="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2">
                    </div>
                    @if ($form->analysisResult)
                        @if ($form->analysisResult['type'] === 'peminjaman')
                            {{ __('Simpan & Buat Peminjaman') }}
                        @elseif ($form->analysisResult['type'] === 'perizinan')
                            {{ __('Simpan & Buat Perizinan') }}
                        @elseif ($form->analysisResult['type'] === 'undangan')
                            {{ __('Simpan & Buat Undangan') }}
                        @endif
                    @else
                        {{ __('Simpan Data Dokumen') }}
                    @endif
                </x-primary-button>
            @endif
        </div>
    </div>
</div>
