<div>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Data Documents') }}
        </h2>
    </header>

    <div class="mt-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 my-4 px-4 md:px-0 md:flex md:justify-between">
            @can('add document')
                <x-button type="button" variant="primary" wire:click="add" class="items-center max-w-xs gap-2">
                    <x-heroicon-s-plus class="w-5 h-5" />

                    <span>{{ __('Add Document') }}</span>
                </x-button>
            @endcan


            <div class="w-full md:w-1/2">
                <x-search placeholder="Search Document by submitters.." />
            </div>
        </div>

        <div class="p-6 text-gray-900 dark:text-gray-100">
            <x-table title="Data Documents" :heads="$table_heads">
                @forelse ($documents as $key => $document)
                    <tr wire:key="document-{{ $document->id }}"
                        class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            {{ $key + $documents->firstItem() }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            {{ $document->submitter->name }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            {{ $document->original_file_name }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            {{ $document->mime_type }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            {{ $document->status }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            <div class="flex flex-col items-center gap-2">
                                @can('view document details')
                                    <x-button size="sm" variant="primary" type="button"
                                        wire:click="viewDetails({{ $document->id }})">
                                        {{ __('Detail') }}
                                    </x-button>
                                @endcan
                                @can('delete document')
                                    <x-button size="sm" variant="danger" type="button"
                                        wire:click="confirmDelete({{ $document->id }})">
                                        {{ __('Delete') }}
                                    </x-button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="bg-white dark:bg-gray-800">
                        <td colspan="{{ count($table_heads) }}"
                            class="whitespace-nowrap px-6 py-4 text-rose-700 dark:text-rose-400 text-sm text-center">
                            {{ __('No data available') }}
                        </td>
                    </tr>
                @endforelse
            </x-table>
        </div>
        <div class="px-6 py-4">
            {{ $documents->links() }}
        </div>
    </div>

    <x-modal name="add-document-modal" focusable>
        <div class="p-4 sm:p-6 bg-white dark:bg-gray-800">

            {{-- Header Modal --}}
            <header class="mb-4 pb-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                    Formulir Pengajuan Dokumen Baru
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Unggah proposal kegiatan Anda dalam format PDF atau gambar.
                </p>
            </header>

            <form wire:submit.prevent="save" class="space-y-6" enctype="multipart/form-data">
                <div>
                    <x-input-label for="proposal_document" value="Dokumen Proposal" class="mb-2" />

                    {{-- Komponen Alpine.js untuk Uploader --}}
                    <div x-data="{ isUploading: false, progress: 0, fileName: '', isDragging: false }" x-on:livewire-upload-start="isUploading = true"
                        x-on:livewire-upload-finish="isUploading = false; progress = 0;"
                        x-on:livewire-upload-error="isUploading = false; progress = 0;"
                        x-on:livewire-upload-progress="progress = $event.detail.progress"
                        x-on:reset-file-input.window="fileName = ''; $refs.fileInput.value = null;">

                        {{-- Input File Tersembunyi --}}
                        <input wire:model="attachment" id="proposal_document" type="file" class="hidden"
                            accept=".pdf, image/png, image/jpeg, image/jpg" x-ref="fileInput"
                            @change="fileName = $refs.fileInput.files[0] ? $refs.fileInput.files[0].name : ''">

                        {{-- Area Dropzone --}}
                        {{-- [IMPROVEMENT] Menggunakan x-show.transition untuk animasi yang lebih halus --}}
                        <div x-show="!fileName" x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0" @click="$refs.fileInput.click()"
                            @dragover.prevent="isDragging = true" @dragleave.prevent="isDragging = false"
                            @drop.prevent="isDragging = false; $refs.fileInput.files = $event.dataTransfer.files; $refs.fileInput.dispatchEvent(new Event('change'))"
                            class="relative flex flex-col items-center justify-center w-full p-8 border-2 border-dashed rounded-lg cursor-pointer transition-colors"
                            {{-- [IMPROVEMENT] Menggunakan warna tema dari Tailwind untuk dark/light mode --}}
                            :class="isDragging ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' :
                                'border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500'">

                            {{-- Ikon --}}
                            <div
                                class="mb-4 flex items-center justify-center h-16 w-16 rounded-full bg-gray-100 dark:bg-gray-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-gray-500 dark:text-gray-400"
                                    fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 16.5V9.75m0 0l-3 3m3-3l3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
                                </svg>
                            </div>

                            {{-- Teks Panduan --}}
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                <span class="font-bold text-primary-600 dark:text-primary-400">Klik untuk memilih</span>
                                atau seret & lepas file
                            </p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                PDF, PNG, JPG (maks. 5MB)
                            </p>
                        </div>

                        {{-- Pratinjau File & Progress Bar --}}
                        {{-- [IMPROVEMENT] Tampilan pratinjau yang lebih bersih dan terstruktur --}}
                        <div x-show="fileName" style="display: none;"
                            class="relative w-full p-4 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 rounded-lg flex items-center space-x-4">

                            <div class="flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="w-10 h-10 text-primary-600 dark:text-primary-400" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>

                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-200 truncate"
                                    x-text="fileName"></p>

                                {{-- Progress Bar --}}
                                <div x-show="isUploading" class="mt-2" x-transition>
                                    <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2">
                                        <div class="bg-primary-600 h-2 rounded-full transition-all duration-300"
                                            :style="`width: ${progress}%`"></div>
                                    </div>
                                </div>

                                <p x-show="!isUploading" class="text-xs text-gray-500 dark:text-gray-400 mt-1">File siap
                                    diunggah.</p>
                            </div>

                            <div class="flex-shrink-0">
                                <button wire:click.prevent="removeAttachment"
                                    @click="fileName = ''; $refs.fileInput.value = null;" type="button"
                                    class="p-1 text-gray-400 dark:text-gray-500 rounded-full hover:bg-gray-200 dark:hover:bg-gray-600 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                                    <span class="sr-only">Hapus file</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <x-input-error :messages="$errors->get('attachment')" class="mt-2" />
                    </div>
                </div>

                {{-- Footer dengan Tombol Aksi --}}
                <footer
                    class="mt-8 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 border-t pt-4 dark:border-gray-700">
                    <x-secondary-button x-on:click="$dispatch('close')" class="w-full sm:w-auto justify-center">
                        Batal
                    </x-secondary-button>

                    <x-primary-button type="submit" class="w-full sm:w-auto justify-center"
                        wire:loading.attr="disabled" wire:target="save">
                        <div wire:loading wire:target="save"
                            class="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2"></div>
                        <span>{{ __('Ajukan Proposal') }}</span>
                    </x-primary-button>
                </footer>
            </form>
        </div>
    </x-modal>

    <livewire:admin.pages.document.document-modal />
    <livewire:admin.pages.document.event-modal />

    <livewire:admin.pages.document.data-document-modal />
    <livewire:admin.pages.document.borrowing />
    <livewire:admin.pages.document.organization />
    <livewire:admin.pages.document.location />

</div>
