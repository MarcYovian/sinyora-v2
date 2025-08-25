<div x-data="{ isOpen: true }"
    class="bg-white dark:bg-slate-800/50 rounded-lg border border-slate-200 dark:border-slate-700">
    <div class="p-4">
        <div class="flex justify-between items-center">
            <button @click="isOpen = !isOpen" class="flex-grow flex items-center text-left">
                <h3 class="font-semibold text-slate-900 dark:text-slate-200">Hasil Analisis AI</h3>
                <svg class="ml-2 w-5 h-5 text-slate-500 transform transition-transform" :class="{ 'rotate-180': isOpen }"
                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            {{-- Tombol Edit/Save/Cancel --}}
            @if ($form->analysisResult && !$isProcessing)
                <div class="flex items-center gap-2">
                    @if ($isEditing)
                        <x-button wire:click="saveAnalysis" size="sm" variant="primary">
                            {{ __('Simpan') }}
                        </x-button>
                        <x-button wire:click="cancelEdit" size="sm" variant="secondary">
                            {{ __('Batal') }}
                        </x-button>
                    @else
                        @if ($doc->status !== App\Enums\DocumentStatus::DONE)
                            <x-button wire:click="prepareReanalysis" size="sm" variant="primary"
                                class="flex items-center gap-1" wire:loading.attr="disabled"
                                wire:target="prepareReanalysis">
                                <div wire:loading wire:target="prepareReanalysis"
                                    class="animate-spin rounded-full h-4 w-4 border-b-2 border-current">
                                </div>
                                <svg wire:loading.remove wire:target="prepareReanalysis"
                                    xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.5L15.232 5.232z" />
                                </svg>

                                <span>{{ __('Analisa Ulang') }}</span>
                            </x-button>
                            <x-button wire:click="editAnalysis" size="sm" variant="warning"
                                class="flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.5L15.232 5.232z" />
                                </svg>
                                {{ __('Edit') }}
                            </x-button>
                        @endif
                    @endif
                </div>
            @endif
        </div>

        <div x-show="isOpen" x-collapse>
            <div class="pt-4 mt-4 border-t border-slate-200 dark:border-slate-600">
                @if (!$isProcessing && !$form->analysisResult && $this->doc->status !== App\Enums\DocumentStatus::PROCESSED)
                    <button wire:click="processDocument" wire:loading.attr="disabled" type="button"
                        class="mt-2 w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <div wire:loading wire:target="processDocument"
                            class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2">
                        </div>
                        Jalankan Analisis
                    </button>
                @elseif (!$isProcessing && !$form->analysisResult && $this->doc->status === App\Enums\DocumentStatus::PROCESSED)
                    <button wire:click="processDocument" wire:loading.attr="disabled" type="button"
                        class="mt-2 w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <div wire:loading wire:target="processDocument"
                            class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2">
                        </div>
                        Jalankan Analisis
                    </button>
                @endif

                @if ($isProcessing)
                    <div class="flex mt-2 items-center text-sm text-slate-600 dark:text-slate-400">
                        <div
                            class="animate-spin rounded-full h-4 w-4 border-b-2 border-slate-700 dark:border-white mr-2">
                        </div>
                        <span>{{ $processingStatus ?: 'Menganalisis dokumen...' }}</span>
                    </div>
                @elseif ($processingStatus && Str::contains($processingStatus, 'Error'))
                    <div
                        class="mt-4 p-3 flex items-start gap-3 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 rounded-lg text-sm text-red-700 dark:text-red-300">
                        {{-- Ikon Error --}}
                        <div class="flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.21 3.001-1.742 3.001H4.42c-1.532 0-2.492-1.667-1.742-3.001l5.58-9.92zM10 13a1 1 0 110-2 1 1 0 010 2zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        {{-- Pesan Error --}}
                        <p>
                            {{ $processingStatus }}
                        </p>
                    </div>
                @endif

                @if ($form->analysisResult)
                    {{-- Loop untuk setiap kegiatan --}}
                    <div class="mt-2 space-y-4">
                        @forelse ($form->analysisResult['events'] ?? [] as $index => $kegiatan)
                            @include('livewire.admin.pages.document.document-modal.event-card', [
                                'index' => $index,
                                'kegiatan' => $kegiatan,
                            ])
                        @empty
                            <p class="text-slate-500 text-center py-4">
                                Tidak ada detail kegiatan yang ditemukan.
                            </p>
                        @endforelse

                        {{-- Bagian Penanda Tangan (di luar loop kegiatan) --}}
                        @include('livewire.admin.pages.document.document-modal.signers')
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
