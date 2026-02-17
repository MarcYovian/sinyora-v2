<div>
    <header class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 leading-tight">
            {{ __('Manajemen Data Dokumen') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Kelola semua data dokumen yang masuk.
        </p>
    </header>

    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
        <div class="p-4 sm:p-6 space-y-4">
            {{-- Header Kontrol: Tombol, Filter, dan Pencarian --}}
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                @can('access', 'admin.documents.create')
                    <x-button type="button" variant="primary" wire:click="add" class="items-center max-w-xs gap-2">
                        <x-heroicon-s-plus class="w-5 h-5" />
                        <span>{{ __('Tambah Dokumen') }}</span>
                    </x-button>
                @endcan

                <div class="flex-grow flex flex-col sm:flex-row items-center gap-3">
                    <div class="w-full sm:w-auto sm:flex-grow">
                        <x-text-input wire:model.live.debounce.300ms="search" type="text" class="w-full"
                            placeholder="{{ __('Cari subjek, no. dokumen, atau pengaju...') }}" />
                    </div>
                    <div class="w-full sm:w-48">
                        <x-select wire:model.live="filterStatus" class="w-full">
                            <option value="">{{ __('Semua Status') }}</option>
                            @foreach (App\Enums\DocumentStatus::cases() as $status)
                                <option value="{{ $status->value }}">{{ $status->label() }}</option>
                            @endforeach
                        </x-select>
                    </div>
                    @if ($search || $filterStatus)
                        <x-button type="button" wire:click="resetFilters" variant="secondary" class="w-full sm:w-auto">
                            {{ __('Reset') }}
                        </x-button>
                    @endif
                </div>
            </div>

            {{-- Indikator Loading --}}
            <div wire:loading.flex wire:target="search, filterStatus" class="items-center justify-center w-full py-4">
                <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
                    <x-heroicon-s-arrow-path class="h-5 w-5 animate-spin" />
                    <span>Memuat data...</span>
                </div>
            </div>

            <div wire:loading.remove wire:target="search, filterStatus">
                {{-- Tampilan Mobile (Card) --}}
                <div class="grid grid-cols-1 gap-4 md:hidden">
                    @forelse ($documents as $document)
                        <div wire:key="document-card-{{ $document->id }}"
                            class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden ring-1 ring-black ring-opacity-5">
                            <div class="p-4 border-b dark:border-gray-700 flex justify-between items-start">
                                <div>
                                    @if ($document->subject)
                                        <h3 class="font-bold text-lg text-gray-800 dark:text-gray-200">
                                            {{ $document->subject }}
                                        </h3>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            No: {{ $document->doc_num ?? '-' }}
                                        </p>
                                    @else
                                        <h3 class="font-bold text-lg text-gray-600 dark:text-gray-400 italic">
                                            {{ $document->original_file_name }}
                                        </h3>
                                        <p class="text-xs text-yellow-600 dark:text-yellow-500">
                                            [Menunggu Analisis Informasi...]
                                        </p>
                                    @endif
                                    <div class="mt-1">
                                        <x-status-badge :status="$document->status" />
                                    </div>
                                </div>
                                {{-- Dropdown Aksi Mobile --}}
                                <x-dropdown align="right" width="48">
                                    <x-slot name="trigger">
                                        <button
                                            class="p-1 text-gray-500 dark:text-gray-400 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500">
                                            <x-heroicon-s-ellipsis-vertical class="w-5 h-5" />
                                        </button>
                                    </x-slot>
                                    <x-slot name="content">
                                        @can('access', 'admin.documents.show')
                                            <x-dropdown-link wire:click="viewDetails({{ $document->id }})">
                                                Detail
                                            </x-dropdown-link>
                                        @endcan
                                        <div class="border-t border-gray-100 dark:border-gray-600"></div>
                                        @can('access', 'admin.documents.delete')
                                            @if ($document->status !== App\Enums\DocumentStatus::DONE)
                                                <x-dropdown-link wire:click="confirmDelete({{ $document->id }})"
                                                    class="text-red-600 dark:text-red-500">
                                                    Hapus
                                                </x-dropdown-link>
                                            @endif
                                        @endcan
                                    </x-slot>
                                </x-dropdown>
                            </div>
                            <div class="p-4 space-y-3 text-sm">
                                <div class="flex items-center text-gray-600 dark:text-gray-300">
                                    <x-heroicon-o-user class="w-4 h-4 mr-2 flex-shrink-0" />
                                    <span>{{ $document->submitter->name }}</span>
                                </div>
                                <div class="flex items-center text-gray-600 dark:text-gray-300">
                                    <x-heroicon-o-arrow-up-tray class="w-4 h-4 mr-2 flex-shrink-0" />
                                    <span>
                                        Diunggah:
                                        {{ $document->created_at->translatedFormat('d M Y, H:i') }}
                                    </span>
                                </div>
                                @if ($document->processor)
                                    <div class="flex items-center text-gray-600 dark:text-gray-300">
                                        <x-heroicon-o-check-circle class="w-4 h-4 mr-2 flex-shrink-0" />
                                        <span>Diproses oleh: {{ $document->processor->name }}</span>
                                    </div>
                                @endif

                                {{-- Mobile Card Actions --}}
                                <div class="pt-3 border-t border-gray-100 dark:border-gray-700 flex flex-wrap gap-4">
                                    @can('access', 'admin.documents.show')
                                        <button wire:click="viewDetails({{ $document->id }})"
                                            class="text-indigo-600 dark:text-indigo-400 font-medium hover:underline flex items-center gap-1.5 transition-colors">
                                            <x-heroicon-o-eye class="w-4 h-4" />
                                            <span>{{ __('Detail') }}</span>
                                        </button>
                                    @endcan

                                    @can('access', 'admin.documents.delete')
                                        @if ($document->status !== App\Enums\DocumentStatus::DONE)
                                            <button wire:click="confirmDelete({{ $document->id }})"
                                                class="text-red-500 dark:text-red-400 font-medium hover:underline flex items-center gap-1.5 transition-colors">
                                                <x-heroicon-o-trash class="w-4 h-4" />
                                                <span>{{ __('Hapus') }}</span>
                                            </button>
                                        @endif
                                    @endcan
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                            {{ __('Tidak ada data yang tersedia') }}
                        </div>
                    @endforelse
                </div>

                {{-- Tampilan Desktop (Tabel) --}}
                <div class="hidden md:block">
                    <x-table title="Data Dokumen" :heads="$table_heads">
                        @forelse ($documents as $key => $document)
                            <tr wire:key="document-row-{{ $document->id }}"
                                class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150 isolate">
                                <td class="px-6 py-4 whitespace-nowrap text-gray-700 dark:text-gray-300">
                                    {{ $key + $documents->firstItem() }}
                                </td>
                                {{-- Dokumen --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($document->subject)
                                        <div class="font-semibold text-gray-900 dark:text-gray-200">
                                            {{ $document->subject }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            No: {{ $document->doc_num ?? '-' }}
                                        </div>
                                    @else
                                        <div class="font-semibold text-gray-600 dark:text-gray-400 italic">
                                            {{ $document->original_file_name }}
                                        </div>
                                        <div class="text-xs text-yellow-600 dark:text-yellow-500">
                                            [Menunggu Analisis Informasi...]
                                        </div>
                                    @endif
                                </td>
                                {{-- Pengaju --}}
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                                    {{ $document->submitter->name }}
                                </td>
                                {{-- Tanggal Unggah --}}
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                                    {{ $document->created_at->translatedFormat('d M Y') }}
                                </td>
                                {{-- Status --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <x-status-badge :status="$document->status" />
                                </td>
                                {{-- Diproses Oleh --}}
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                                    @if ($document->processor)
                                        <div>{{ $document->processor->name }}</div>
                                        <div class="text-xs text-gray-400">
                                            ({{ $document->processed_at->translatedFormat('d M') }})
                                        </div>
                                    @else
                                        {{ __('-') }}
                                    @endif
                                </td>
                                {{-- Aksi --}}
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="flex items-center justify-end space-x-1">
                                        {{-- Detail Button - Blue/Primary --}}
                                        @can('access', 'admin.documents.show')
                                            <x-button type="button" variant="primary" size="sm" class="!p-2"
                                                wire:click="viewDetails({{ $document->id }})" title="Detail">
                                                <x-heroicon-o-eye class="w-4 h-4" />
                                                <span class="sr-only">Detail</span>
                                            </x-button>
                                        @endcan

                                        {{-- Delete Button - Red/Danger --}}
                                        @can('access', 'admin.documents.delete')
                                            @if ($document->status !== App\Enums\DocumentStatus::DONE)
                                                <x-button type="button" variant="danger" size="sm" class="!p-2"
                                                    wire:click="confirmDelete({{ $document->id }})" title="Hapus">
                                                    <x-heroicon-o-trash class="w-4 h-4" />
                                                    <span class="sr-only">Hapus</span>
                                                </x-button>
                                            @endif
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($table_heads) }}"
                                    class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    <x-heroicon-o-inbox class="mx-auto h-12 w-12" />
                                    <h4 class="mt-2 text-sm font-semibold">{{ __('Tidak ada data yang tersedia') }}</h4>
                                    <p class="mt-1 text-sm">{{ __('Coba ubah filter Anda atau unggah dokumen baru.') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </x-table>
                </div>
            </div>
        </div>

        <div class="px-4 md:px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $documents->links() }}
        </div>
    </div>

    {{-- Add Document Modal --}}
    <x-modal name="add-document-modal" focusable>
        <div class="p-4 sm:p-6 bg-white dark:bg-gray-800">

            {{-- Header Modal --}}
            <div class="flex items-start justify-between pb-4 mb-6 border-b border-gray-200 dark:border-gray-700">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                        Formulir Pengajuan Dokumen Baru
                    </h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Unggah proposal kegiatan Anda dalam format PDF atau gambar.
                    </p>
                </div>
                <button type="button" @click="$dispatch('close')"
                    class="p-2 -m-2 text-gray-400 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 hover:text-gray-600 dark:hover:text-gray-200 transition-all">
                    <x-heroicon-s-x-mark class="h-6 w-6" />
                </button>
            </div>

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
                        <div x-show="!fileName" x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0" @click="$refs.fileInput.click()"
                            @dragover.prevent="isDragging = true" @dragleave.prevent="isDragging = false"
                            @drop.prevent="isDragging = false; $refs.fileInput.files = $event.dataTransfer.files; $refs.fileInput.dispatchEvent(new Event('change'))"
                            class="relative flex flex-col items-center justify-center w-full p-8 border-2 border-dashed rounded-lg cursor-pointer transition-colors"
                            :class="isDragging ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' :
                                'border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500'">

                            {{-- Ikon --}}
                            <div
                                class="mb-4 flex items-center justify-center h-16 w-16 rounded-full bg-gray-100 dark:bg-gray-700">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="w-8 h-8 text-gray-500 dark:text-gray-400" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 16.5V9.75m0 0l-3 3m3-3l3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
                                </svg>
                            </div>

                            {{-- Teks Panduan --}}
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                <span class="font-bold text-primary-600 dark:text-primary-400">Klik untuk
                                    memilih</span>
                                atau seret & lepas file
                            </p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                PDF, PNG, JPG (maks. 5MB)
                            </p>
                        </div>

                        {{-- Pratinjau File & Progress Bar --}}
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

                                <p x-show="!isUploading" class="text-xs text-gray-500 dark:text-gray-400 mt-1">File
                                    siap
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
                <div
                    class="mt-8 pt-5 border-t border-gray-200 dark:border-gray-700 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                    <x-secondary-button type="button" x-on:click="$dispatch('close')" class="justify-center">
                        {{ __('Batal') }}
                    </x-secondary-button>

                    <x-primary-button type="submit" class="justify-center"
                        wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">
                            {{ __('Ajukan Proposal') }}
                        </span>
                        <span wire:loading wire:target="save" class="flex items-center gap-2">
                            <x-heroicon-s-arrow-path class="h-5 w-5 animate-spin" />
                            {{ __('Memproses...') }}
                        </span>
                    </x-primary-button>
                </div>
            </form>
        </div>
    </x-modal>

    {{-- Delete Document Confirmation Modal --}}
    <x-modal name="delete-document-confirmation" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="delete" class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Hapus Data Dokumen?') }}
            </h2>

            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Tindakan ini tidak dapat dibatalkan. Data dokumen akan dihapus permanen dari sistem.') }}
            </p>

            @if ($document)
                <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700">
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                {{ __('Nama Dokumen') }}</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">
                                {{ $document->subject ?? $document->original_file_name }}
                            </dd>
                        </div>
                    </dl>
                </div>
            @endif

            <div class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-100 dark:border-red-800">
                <div class="flex items-center gap-3">
                    <x-heroicon-o-trash class="w-5 h-5 text-red-600 dark:text-red-400" />
                    <span class="text-sm font-medium text-red-900 dark:text-red-200">
                        {{ __('Data yang dihapus tidak dapat dipulihkan.') }}
                    </span>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button type="button" x-on:click="$dispatch('close')">
                    {{ __('Batal') }}
                </x-secondary-button>

                <x-danger-button>
                    <span wire:loading.remove wire:target="delete">{{ __('Hapus Dokumen') }}</span>
                    <span wire:loading wire:target="delete" class="flex items-center gap-2">
                        <x-heroicon-s-arrow-path class="h-4 w-4 animate-spin" />
                        {{ __('Menghapus...') }}
                    </span>
                </x-danger-button>
            </div>
        </form>
    </x-modal>

    <livewire:admin.pages.document.document-modal />
    <livewire:admin.pages.document.event-modal />

    <livewire:admin.pages.document.data-document-modal />
    <livewire:admin.pages.document.borrowing />
    <livewire:admin.pages.document.organization />
    <livewire:admin.pages.document.location />

</div>
