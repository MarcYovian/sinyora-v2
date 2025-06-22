<div>
    <x-modal name="document-modal" maxWidth="7xl" focusable>
        @if ($this->doc)
            {{-- Layout utama modal dengan 2 kolom --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 h-[90vh] bg-white dark:bg-gray-900">

                <div class="lg:col-span-5 p-6 flex flex-col overflow-y-auto">
                    {{-- HEADER --}}
                    <div class="flex-shrink-0">
                        <div class="flex justify-between items-start">
                            <div>
                                <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">
                                    Detail Pengajuan Dokumen
                                </h2>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                                    Diajukan oleh: <span
                                        class="font-medium text-slate-700 dark:text-slate-300">{{ $this->doc->submitter->name }}</span>
                                </p>
                            </div>
                            {{-- Tombol close untuk mobile --}}
                            <button x-on:click="$dispatch('close')" class="text-gray-400 hover:text-gray-500 lg:hidden">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- KONTEN UTAMA (SCROLLABLE) --}}
                    <div class="mt-6 space-y-5 flex-grow pr-2">
                        {{-- KARTU INFORMASI PENGAJUAN --}}
                        <div
                            class="bg-slate-50 dark:bg-slate-800/50 rounded-lg border border-slate-200 dark:border-slate-700">
                            <div class="p-4">
                                <h3 class="font-semibold text-slate-900 dark:text-slate-200">Informasi Pengajuan</h3>
                                <dl class="mt-2 grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                                    <div class="col-span-2">
                                        <dt class="text-slate-500 dark:text-slate-400 mb-1">Organisasi</dt>
                                        @if ($isEditing)
                                            <div class="space-y-2">
                                                @foreach ($analysisResult['data']['informasi_umum_dokumen']['organisasi'] ?? [] as $organizationIndex => $organization)
                                                    <div class="flex items-center gap-2"
                                                        wire:key="Organization-{{ $organizationIndex }}">
                                                        <x-text-input type="text" class="w-full text-sm"
                                                            wire:model.defer="analysisResult.data.informasi_umum_dokumen.organisasi.{{ $organizationIndex }}.nama" />
                                                        <button type="button"
                                                            wire:click="removeOrganization({{ $organizationIndex }})"
                                                            class="text-red-500 hover:text-red-700 dark:hover:text-red-400 p-2 rounded-full">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                                viewBox="0 0 20 20" fill="currentColor">
                                                                <path fill-rule="evenodd"
                                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 000 2h6a1 1 0 100-2H7z"
                                                                    clip-rule="evenodd" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                    <x-input-error :messages="$errors->get(
                                                        'analysisResult.data.informasi_umum_dokumen.organisasi.{{ $organizationIndex }}.nama',
                                                    )" class="mt-2" />
                                                @endforeach
                                                <x-button type="button" variant="primary" size="sm"
                                                    wire:click="addOrganization()">
                                                    + Tambah Organisasi
                                                </x-button>
                                            </div>
                                        @else
                                            <dd class="font-medium text-slate-800 dark:text-slate-200">
                                                <ul class="list-disc list-inside">
                                                    @forelse ($analysisResult['data']['informasi_umum_dokumen']['organisasi'] ?? [] as $organization)
                                                        <li>{{ $organization['nama'] }}</li>
                                                    @empty
                                                        <li>N/A</li>
                                                    @endforelse
                                                </ul>
                                            </dd>
                                        @endif
                                    </div>
                                    <div class="col-span-2">
                                        @if ($isEditing)
                                            <dt class="text-slate-500 dark:text-slate-400">Perihal</dt>
                                            <dd class="font-medium text-slate-800 dark:text-slate-200">
                                                <x-text-input type="text" class="mt-1 block w-full"
                                                    wire:model.defer="analysisResult.data.informasi_umum_dokumen.perihal_surat" />
                                                <x-input-error :messages="$errors->get(
                                                    'analysisResult.data.informasi_umum_dokumen.perihal_surat',
                                                )" class="mt-2" />
                                            </dd>
                                        @else
                                            <dt class="text-slate-500 dark:text-slate-400">Perihal</dt>
                                            <dd class="font-medium text-slate-800 dark:text-slate-200">
                                                {{ $analysisResult['data']['informasi_umum_dokumen']['perihal_surat'] ?? 'N/A' }}
                                            </dd>
                                        @endif
                                    </div>
                                    <div>
                                        <dt class="text-slate-500 dark:text-slate-400">Nomor Surat</dt>
                                        @if ($isEditing)
                                            <dd class="font-medium text-slate-800 dark:text-slate-200">
                                                <x-text-input type="text" class="mt-1 block w-full"
                                                    wire:model.defer="analysisResult.data.informasi_umum_dokumen.nomor_surat" />
                                                <x-input-error :messages="$errors->get(
                                                    'analysisResult.data.informasi_umum_dokumen.nomor_surat',
                                                )" class="mt-2" />
                                            </dd>
                                        @else
                                            <dd class="text-slate-800 dark:text-slate-200">
                                                {{ $analysisResult['data']['informasi_umum_dokumen']['nomor_surat'] ?? 'N/A' }}
                                            </dd>
                                        @endif
                                    </div>
                                    <div>
                                        <dt class="text-slate-500 dark:text-slate-400">Tanggal Surat</dt>
                                        @if ($isEditing)
                                            <dd class="font-medium text-slate-800 dark:text-slate-200">
                                                <x-text-input type="date" class="mt-1 block w-full"
                                                    wire:model.defer="editableData.doc_date" />
                                                <x-input-error :messages="$errors->get('editableData.doc_date')" class="mt-2" />
                                            </dd>
                                        @else
                                            <dd class="text-slate-800 dark:text-slate-200">
                                                {{ $analysisResult['data']['informasi_umum_dokumen']['tanggal_surat_dokumen'] ?? 'N/A' }}
                                            </dd>
                                        @endif
                                    </div>
                                    <div class="col-span-2">
                                        <dt class="text-slate-500 dark:text-slate-400 mb-1">Penerima Surat</dt>
                                        @if ($isEditing)
                                            <div class="space-y-2">
                                                @foreach ($analysisResult['data']['informasi_umum_dokumen']['penerima_surat'] ?? [] as $recipientIndex => $penerima)
                                                    <div class="flex items-center gap-2"
                                                        wire:key="recipient-{{ $recipientIndex }}">
                                                        <x-text-input type="text" class="w-full text-sm"
                                                            wire:model.defer="analysisResult.data.informasi_umum_dokumen.penerima_surat.{{ $recipientIndex }}.name" />
                                                        <button type="button"
                                                            wire:click="removeRecipient({{ $recipientIndex }})"
                                                            class="text-red-500 hover:text-red-700 dark:hover:text-red-400 p-2 rounded-full">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                                viewBox="0 0 20 20" fill="currentColor">
                                                                <path fill-rule="evenodd"
                                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 000 2h6a1 1 0 100-2H7z"
                                                                    clip-rule="evenodd" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                    <x-input-error :messages="$errors->get(
                                                        'analysisResult.data.informasi_umum_dokumen.penerima_surat.{{ $recipientIndex }}.name',
                                                    )" class="mt-2" />
                                                @endforeach
                                                <x-button type="button" variant="primary" size="sm"
                                                    wire:click="addRecipient()">
                                                    + Tambah Penerima
                                                </x-button>
                                            </div>
                                        @else
                                            <dd class="font-medium text-slate-800 dark:text-slate-200">
                                                <ul class="list-disc list-inside">
                                                    @forelse ($analysisResult['data']['informasi_umum_dokumen']['penerima_surat'] ?? [] as $penerima)
                                                        <li>{{ $penerima['name'] }}</li>
                                                    @empty
                                                        <li>N/A</li>
                                                    @endforelse
                                                </ul>
                                            </dd>
                                        @endif
                                    </div>
                                    <div class="col-span-2">
                                        <dt class="text-slate-500 dark:text-slate-400">Jenis Surat</dt>
                                        @if ($isEditing)
                                            <dd class="font-medium text-slate-800 dark:text-slate-200">
                                                <x-text-input type="text" class="mt-1 block w-full"
                                                    wire:model.defer="analysisResult.data.type" />
                                                <x-input-error :messages="$errors->get('analysisResult.data.type')" class="mt-2" />
                                            </dd>
                                        @else
                                            <dd class="font-medium text-slate-800 dark:text-slate-200">
                                                {{ $analysisResult['data']['type'] ?? 'N/A' }}
                                            </dd>
                                        @endif
                                    </div>
                                </dl>
                            </div>
                        </div>

                        {{-- KARTU HASIL ANALISIS AI --}}
                        <div
                            class="bg-white dark:bg-slate-800/50 rounded-lg border border-slate-200 dark:border-slate-700">
                            <div class="p-4">
                                <div class="flex justify-between items-center">
                                    <h3 class="font-semibold text-slate-900 dark:text-slate-200">Hasil Analisis AI</h3>

                                    {{-- Tombol Edit/Save/Cancel --}}
                                    @if ($analysisResult && !$isProcessing)
                                        <div class="flex items-center gap-2">
                                            @if ($isEditing)
                                                <x-button wire:click="saveAnalysis" size="sm" variant="primary">
                                                    {{ __('Simpan') }}
                                                </x-button>
                                                <x-button wire:click="cancelEdit" size="sm" variant="secondary">
                                                    {{ __('Batal') }}
                                                </x-button>
                                            @else
                                                <x-button wire:click="prepareReanalysis" size="sm"
                                                    variant="primary" class="flex items-center gap-1"
                                                    wire:loading.attr="disabled" wire:target="prepareReanalysis">

                                                    {{-- Tampilkan spinner saat proses prepareReanalysis berjalan --}}
                                                    <div wire:loading wire:target="prepareReanalysis"
                                                        class="animate-spin rounded-full h-4 w-4 border-b-2 border-current">
                                                    </div>

                                                    {{-- Tampilkan ikon asli saat tidak loading --}}
                                                    <svg wire:loading.remove wire:target="prepareReanalysis"
                                                        xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.5L15.232 5.232z" />
                                                    </svg>

                                                    <span>{{ __('Analisa Ulang') }}</span>
                                                </x-button>
                                                <x-button wire:click="editAnalysis" size="sm" variant="warning"
                                                    class="flex items-center gap-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.5L15.232 5.232z" />
                                                    </svg>
                                                    {{ __('Edit') }}
                                                </x-button>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                @if (!$isProcessing && !$analysisResult && $this->doc->status !== 'processed')
                                    <button wire:click="processDocument" wire:loading.attr="disabled" type="button"
                                        class="mt-2 w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        <div wire:loading wire:target="processDocument"
                                            class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2">
                                        </div>
                                        Jalankan Analisis
                                    </button>
                                @elseif (!$isProcessing && !$analysisResult && $this->doc->status === 'processed')
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
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                viewBox="0 0 20 20" fill="currentColor">
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
                                @elseif ($analysisResult)
                                    {{-- Loop untuk setiap kegiatan --}}
                                    <div class="mt-2 space-y-4">
                                        @forelse ($analysisResult['data']['detail_kegiatan'] ?? [] as $index => $kegiatan)
                                            <div class="border border-slate-200 dark:border-slate-700 rounded-lg p-4"
                                                x-data="{ currentAiTab: 'detail' }">
                                                <div class="flex justify-between items-start mb-2">
                                                    <h4
                                                        class="font-bold text-md text-slate-800 dark:text-slate-200 pr-2">
                                                        Kegiatan #{{ $loop->iteration }}:
                                                        {{-- Saat edit, ambil dari editableKegiatan agar judul ikut terupdate --}}
                                                        @if ($isEditing)
                                                            {{ $editableKegiatan[$index]['nama_kegiatan_utama'] ?? 'Tanpa Nama' }}
                                                        @else
                                                            {{ $kegiatan['nama_kegiatan_utama'] ?? 'Tanpa Nama' }}
                                                        @endif
                                                    </h4>
                                                    @if ($isEditing)
                                                        <button type="button"
                                                            wire:click.prevent="removeKegiatan({{ $index }})"
                                                            class="text-red-500 hover:text-red-600 dark:hover:text-red-400 p-1 -mt-1 rounded-full transition-colors flex-shrink-0"
                                                            title="Hapus Kegiatan">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6"
                                                                fill="none" viewBox="0 0 24 24"
                                                                stroke="currentColor" stroke-width="2">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    @endif
                                                </div>

                                                {{-- Navigasi Tab untuk setiap kegiatan --}}
                                                <div class="border-b border-slate-200 dark:border-slate-700">
                                                    <nav class="-mb-px flex space-x-4" aria-label="Tabs">
                                                        <button @click="currentAiTab = 'detail'" type="button"
                                                            :class="currentAiTab === 'detail' ?
                                                                'border-blue-500 text-blue-600' :
                                                                'border-transparent text-slate-500 hover:text-slate-700'"
                                                            class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">Detail</button>
                                                        <button @click="currentAiTab = 'peminjaman'" type="button"
                                                            :class="currentAiTab === 'peminjaman' ?
                                                                'border-blue-500 text-blue-600' :
                                                                'border-transparent text-slate-500 hover:text-slate-700'"
                                                            class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">Peminjaman</button>
                                                    </nav>
                                                </div>

                                                {{-- Konten Tab --}}
                                                <div class="pt-4 text-sm text-slate-700 dark:text-slate-300">
                                                    {{-- Tab Detail --}}
                                                    <div x-show="currentAiTab === 'detail'">
                                                        @if ($isEditing)
                                                            <div class="space-y-4">
                                                                <div>
                                                                    <x-input-label
                                                                        for="kegiatan-nama-{{ $index }}"
                                                                        value="{{ __('Nama Kegiatan') }}" />
                                                                    <x-text-input
                                                                        id="kegiatan-nama-{{ $index }}"
                                                                        type="text" class="mt-1 block w-full"
                                                                        wire:model.defer="editableKegiatan.{{ $index }}.nama_kegiatan_utama" />
                                                                    <x-input-error :messages="$errors->get(
                                                                        'editableKegiatan.' .
                                                                            $index .
                                                                            '.nama_kegiatan_utama',
                                                                    )" class="mt-2" />
                                                                </div>
                                                                <div class="col-span-1 md:col-span-2 mt-4">
                                                                    <label
                                                                        class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                                                                        Jadwal Kegiatan
                                                                    </label>

                                                                    {{-- Container untuk semua baris jadwal, dengan jarak vertikal antar baris --}}
                                                                    <div class="mt-2 space-y-3">
                                                                        @forelse ($editableKegiatan[$index]['dates'] ?? [] as $dateIndex => $datePair)
                                                                            {{-- div pembungkus untuk satu baris jadwal --}}
                                                                            <div class="p-3 border rounded-md dark:border-gray-600 bg-gray-50 dark:bg-gray-900/50"
                                                                                wire:key="date-{{ $index }}-{{ $dateIndex }}">
                                                                                <div class="flex flex-col space-y-4">
                                                                                    {{-- Input Start DateTime --}}
                                                                                    <div>
                                                                                        <x-input-label
                                                                                            for="start-{{ $index }}-{{ $dateIndex }}"
                                                                                            value="{{ __('Mulai') }}"
                                                                                            class="text-xs" />
                                                                                        <x-text-input
                                                                                            id="start-{{ $index }}-{{ $dateIndex }}"
                                                                                            type="datetime-local"
                                                                                            class="mt-1 block w-full"
                                                                                            wire:model.defer="editableKegiatan.{{ $index }}.dates.{{ $dateIndex }}.start" />
                                                                                        <x-input-error :messages="$errors->get(
                                                                                            'editableKegiatan.' .
                                                                                                $index .
                                                                                                '.dates.' .
                                                                                                $dateIndex .
                                                                                                '.start',
                                                                                        )"
                                                                                            class="mt-2" />
                                                                                    </div>

                                                                                    {{-- Input End DateTime --}}
                                                                                    <div>
                                                                                        <x-input-label
                                                                                            for="end-{{ $index }}-{{ $dateIndex }}"
                                                                                            value="{{ __('Selesai') }}"
                                                                                            class="text-xs" />
                                                                                        <x-text-input
                                                                                            id="end-{{ $index }}-{{ $dateIndex }}"
                                                                                            type="datetime-local"
                                                                                            class="mt-1 block w-full"
                                                                                            wire:model.defer="editableKegiatan.{{ $index }}.dates.{{ $dateIndex }}.end" />
                                                                                        <x-input-error :messages="$errors->get(
                                                                                            'editableKegiatan.' .
                                                                                                $index .
                                                                                                '.dates.' .
                                                                                                $dateIndex .
                                                                                                '.end',
                                                                                        )"
                                                                                            class="mt-2" />
                                                                                    </div>

                                                                                    {{-- Tombol Hapus Jadwal --}}
                                                                                    <div class="self-end">
                                                                                        <button type="button"
                                                                                            wire:click.prevent="removeDate({{ $index }}, {{ $dateIndex }})"
                                                                                            class="text-red-500 hover:text-red-700 transition flex items-center text-sm">
                                                                                            <svg class="h-4 w-4 mr-1"
                                                                                                xmlns="http://www.w3.org/2000/svg"
                                                                                                fill="none"
                                                                                                viewBox="0 0 24 24"
                                                                                                stroke-width="2"
                                                                                                stroke="currentColor">
                                                                                                <path
                                                                                                    stroke-linecap="round"
                                                                                                    stroke-linejoin="round"
                                                                                                    d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                                            </svg>
                                                                                            <span>Hapus Jadwal</span>
                                                                                        </button>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        @empty
                                                                            {{-- [UI BARU] Tampilan ketika tidak ada jadwal --}}
                                                                            <div
                                                                                class="p-4 border border-dashed rounded-md dark:border-gray-600 text-center bg-gray-50 dark:bg-gray-900/50">
                                                                                <p
                                                                                    class="text-sm text-gray-500 dark:text-gray-400">
                                                                                    Belum ada jadwal untuk kegiatan ini.
                                                                                    <br> Silakan tambahkan jadwal baru.
                                                                                </p>
                                                                            </div>
                                                                        @endforelse
                                                                    </div>

                                                                    {{-- Tombol Tambah Jadwal Baru --}}
                                                                    <div class="mt-3">
                                                                        <x-secondary-button type="button"
                                                                            wire:click.prevent="addDate({{ $index }})">
                                                                            <svg class="h-5 w-5 mr-2"
                                                                                xmlns="http://www.w3.org/2000/svg"
                                                                                fill="none" viewBox="0 0 24 24"
                                                                                stroke-width="1.5"
                                                                                stroke="currentColor">
                                                                                <path stroke-linecap="round"
                                                                                    stroke-linejoin="round"
                                                                                    d="M12 6v12m6-6H6" />
                                                                            </svg>
                                                                            Tambah Jadwal
                                                                        </x-secondary-button>
                                                                    </div>
                                                                </div>
                                                                <div>
                                                                    <x-input-label for="lokasi-{{ $index }}"
                                                                        value="{{ __('Lokasi Kegiatan') }}" />
                                                                    <x-text-input id="lokasi-{{ $index }}"
                                                                        type="text" class="mt-1 block w-full"
                                                                        wire:model.defer="editableKegiatan.{{ $index }}.lokasi_kegiatan" />
                                                                    <x-input-error :messages="$errors->get(
                                                                        'editableKegiatan.' .
                                                                            $index .
                                                                            '.lokasi_kegiatan',
                                                                    )" class="mt-2" />
                                                                </div>
                                                                <div>
                                                                    <x-input-label
                                                                        for="kegiatan-pj-{{ $index }}"
                                                                        value="{{ __('Nama Penanggung Jawab') }}" />
                                                                    <x-text-input id="kegiatan-pj-{{ $index }}"
                                                                        type="text" class="mt-1 block w-full"
                                                                        wire:model.defer="analysisResult.data.detail_kegiatan.{{ $index }}.penanggung_jawab" />
                                                                    <x-input-error :messages="$errors->get(
                                                                        'editableKegiatan.' .
                                                                            $index .
                                                                            '.penanggung_jawab',
                                                                    )" class="mt-2" />
                                                                </div>
                                                                <div>
                                                                    <x-input-label
                                                                        for="kegiatan-kontak-{{ $index }}"
                                                                        value="{{ __('Kontak Penanggung Jawab') }}" />
                                                                    <x-text-input
                                                                        id="kegiatan-kontak-{{ $index }}"
                                                                        type="text" class="mt-1 block w-full"
                                                                        wire:model.defer="analysisResult.data.detail_kegiatan.{{ $index }}.kontak_pj" />
                                                                    <x-input-error :messages="$errors->get(
                                                                        'editableKegiatan.' . $index . '.kontak_pj',
                                                                    )" class="mt-2" />
                                                                </div>
                                                            </div>
                                                        @else
                                                            <div class="space-y-4">
                                                                {{-- Waktu & Tanggal --}}
                                                                <div
                                                                    class="flex items-start gap-4 p-3 bg-slate-50 dark:bg-slate-800/50 rounded-lg">
                                                                    <div
                                                                        class="flex-shrink-0 text-blue-500 dark:text-blue-400">
                                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                                            class="h-6 w-6" fill="none"
                                                                            viewBox="0 0 24 24" stroke="currentColor">
                                                                            <path stroke-linecap="round"
                                                                                stroke-linejoin="round"
                                                                                stroke-width="2"
                                                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                                        </svg>
                                                                    </div>
                                                                    <div>
                                                                        <p
                                                                            class="font-semibold text-slate-800 dark:text-slate-200">
                                                                            {{ $kegiatan['tanggal_kegiatan'] ?? 'N/A' }}
                                                                        </p>
                                                                        <p
                                                                            class="text-sm text-slate-500 dark:text-slate-400">
                                                                            {{ $kegiatan['jam_kegiatan'] ?? 'Jam tidak ditentukan' }}
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                                {{-- Lokasi --}}
                                                                <div
                                                                    class="flex items-start gap-4 p-3 bg-slate-50 dark:bg-slate-800/50 rounded-lg">
                                                                    <div
                                                                        class="flex-shrink-0 text-blue-500 dark:text-blue-400">
                                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                                            class="h-6 w-6" fill="none"
                                                                            viewBox="0 0 24 24" stroke="currentColor">
                                                                            <path stroke-linecap="round"
                                                                                stroke-linejoin="round"
                                                                                stroke-width="2"
                                                                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                                            <path stroke-linecap="round"
                                                                                stroke-linejoin="round"
                                                                                stroke-width="2"
                                                                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                        </svg>
                                                                    </div>
                                                                    <div>
                                                                        <p
                                                                            class="font-semibold text-slate-800 dark:text-slate-200">
                                                                            {{ $kegiatan['lokasi_kegiatan'] ?? 'N/A' }}
                                                                        </p>
                                                                        <p
                                                                            class="text-sm text-slate-500 dark:text-slate-400">
                                                                            Lokasi Acara</p>
                                                                    </div>
                                                                </div>
                                                                {{-- Penanggung Jawab --}}
                                                                <div
                                                                    class="flex items-start gap-4 p-3 bg-slate-50 dark:bg-slate-800/50 rounded-lg">
                                                                    <div
                                                                        class="flex-shrink-0 text-blue-500 dark:text-blue-400">
                                                                        {{-- MEMPERBAIKI IKON MENJADI IKON PENGGUNA --}}
                                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                                            class="h-6 w-6" fill="none"
                                                                            viewBox="0 0 24 24" stroke="currentColor"
                                                                            stroke-width="2">
                                                                            <path stroke-linecap="round"
                                                                                stroke-linejoin="round"
                                                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                                        </svg>
                                                                    </div>
                                                                    <div>
                                                                        <p
                                                                            class="font-semibold text-slate-800 dark:text-slate-200">
                                                                            {{ !empty($kegiatan['penanggung_jawab']) ? $kegiatan['penanggung_jawab'] : 'N/A' }}
                                                                        </p>
                                                                        <p
                                                                            class="text-sm text-slate-500 dark:text-slate-400">
                                                                            {{ !empty($kegiatan['kontak_pj']) ? $kegiatan['kontak_pj'] : 'Kontak tidak tersedia' }}
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    {{-- Tab Peminjaman --}}
                                                    <div x-show="currentAiTab === 'peminjaman'">
                                                        @if ($isEditing)
                                                            <div class="space-y-3">
                                                                <table class="min-w-full text-sm">
                                                                    <thead
                                                                        class="text-left text-slate-500 dark:text-slate-400">
                                                                        <tr>
                                                                            <th class="p-2">Nama Barang</th>
                                                                            <th class="p-2 w-24">Jumlah</th>
                                                                            <th class="p-2 w-16">Aksi</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody
                                                                        class="divide-y divide-slate-200 dark:divide-slate-700">
                                                                        {{-- Ganti $index menjadi $kegiatanIndex untuk kejelasan --}}
                                                                        @forelse ($analysisResult['data']['detail_kegiatan'][$index]['barang_dipinjam'] ?? [] as $itemIndex => $item)
                                                                            <tr
                                                                                wire:key="kegiatan-{{ $index }}-item-{{ $itemIndex }}">
                                                                                <td class="p-1">
                                                                                    <x-text-input type="text"
                                                                                        class="w-full text-sm"
                                                                                        wire:model.defer="analysisResult.data.detail_kegiatan.{{ $index }}.barang_dipinjam.{{ $itemIndex }}.item" />
                                                                                    <x-input-error :messages="$errors->get(
                                                                                        'analysisResult.data.detail_kegiatan.{{ $index }}.barang_dipinjam.{{ $itemIndex }}.item',
                                                                                    )"
                                                                                        class="mt-2" />
                                                                                </td>
                                                                                <td class="p-1">
                                                                                    <x-text-input type="number"
                                                                                        class="w-full text-sm"
                                                                                        min="1"
                                                                                        wire:model.defer="analysisResult.data.detail_kegiatan.{{ $index }}.barang_dipinjam.{{ $itemIndex }}.jumlah" />
                                                                                    <x-input-error :messages="$errors->get(
                                                                                        'analysisResult.data.detail_kegiatan.{{ $index }}.barang_dipinjam.{{ $itemIndex }}.jumlah',
                                                                                    )"
                                                                                        class="mt-2" />
                                                                                </td>
                                                                                <td class="p-1 text-center">
                                                                                    {{-- Tombol untuk menghapus item --}}
                                                                                    <button type="button"
                                                                                        wire:click="removeItem({{ $index }}, {{ $itemIndex }})"
                                                                                        class="text-red-500 hover:text-red-700 dark:hover:text-red-400 p-2 rounded-full">
                                                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                                                            class="h-5 w-5"
                                                                                            viewBox="0 0 20 20"
                                                                                            fill="currentColor">
                                                                                            <path fill-rule="evenodd"
                                                                                                d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                                                                                clip-rule="evenodd" />
                                                                                        </svg>
                                                                                    </button>
                                                                                </td>
                                                                            </tr>
                                                                        @empty
                                                                            <tr>
                                                                                <td colspan="3"
                                                                                    class="text-center p-4 text-slate-500">
                                                                                    Belum ada barang untuk dipinjam.
                                                                                </td>
                                                                            </tr>
                                                                        @endforelse
                                                                    </tbody>
                                                                </table>

                                                                {{-- Tombol untuk menambah item baru --}}
                                                                <div class="pt-2">
                                                                    <x-button type="button" variant="primary"
                                                                        size="sm"
                                                                        wire:click="addItem({{ $index }})">
                                                                        + Tambah Barang
                                                                    </x-button>
                                                                </div>
                                                            </div>
                                                        @else
                                                            <div
                                                                class="overflow-hidden rounded-md border border-slate-200 dark:border-slate-700">
                                                                <table
                                                                    class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                                                                    <thead class="bg-slate-50 dark:bg-slate-800">
                                                                        <tr>
                                                                            <th
                                                                                class="px-3 py-2 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">
                                                                                Barang
                                                                            </th>
                                                                            <th
                                                                                class="px-3 py-2 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">
                                                                                Jumlah
                                                                            </th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody
                                                                        class="divide-y divide-slate-200 dark:divide-slate-700">
                                                                        {{-- Ganti $index menjadi $kegiatanIndex juga di sini --}}
                                                                        @forelse ($analysisResult['data']['detail_kegiatan'][$index]['barang_dipinjam'] ?? [] as $item)
                                                                            <tr
                                                                                class="odd:bg-white dark:odd:bg-slate-800/50 even:bg-slate-50 dark:even:bg-slate-800">
                                                                                <td class="px-3 py-2 font-medium">
                                                                                    {{ $item['item'] }}</td>
                                                                                <td class="px-3 py-2">
                                                                                    {{ $item['jumlah'] }}</td>
                                                                            </tr>
                                                                        @empty
                                                                            <tr>
                                                                                <td class="p-3 text-center text-slate-500"
                                                                                    colspan="2">
                                                                                    Tidak ada barang yang dipinjam.
                                                                                </td>
                                                                            </tr>
                                                                        @endforelse
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        @endif

                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <p class="text-slate-500 text-center py-4">Tidak ada detail kegiatan yang
                                                ditemukan.</p>
                                        @endforelse

                                        {{-- Bagian Penanda Tangan (di luar loop kegiatan) --}}
                                        <div class="border-t border-slate-200 dark:border-slate-700 pt-4 mt-4">
                                            <h4 class="font-semibold text-slate-900 dark:text-slate-200 mb-2">Penanda
                                                Tangan</h4>

                                            @if ($isEditing)
                                                <div class="space-y-3">
                                                    @foreach ($analysisResult['data']['blok_penanda_tangan'] ?? [] as $signerIndex => $signer)
                                                        <div wire:key="signer-{{ $signerIndex }}"
                                                            class="p-2 rounded-md border border-slate-200 dark:border-slate-700">
                                                            <div class="flex items-center justify-end">
                                                                <button type="button"
                                                                    wire:click="removeSigner({{ $signerIndex }})"
                                                                    class="text-red-500 hover:text-red-700 -mr-2 -mt-2">
                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                        class="h-5 w-5" viewBox="0 0 20 20"
                                                                        fill="currentColor">
                                                                        <path fill-rule="evenodd"
                                                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                                                            clip-rule="evenodd" />
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                                <div>
                                                                    <x-input-label
                                                                        for="signer-nama-{{ $signerIndex }}"
                                                                        value="Nama" />
                                                                    <x-text-input id="signer-nama-{{ $signerIndex }}"
                                                                        type="text" class="mt-1 w-full text-sm"
                                                                        wire:model.defer="analysisResult.data.blok_penanda_tangan.{{ $signerIndex }}.nama" />
                                                                    <x-input-error :messages="$errors->get(
                                                                        'analysisResult.data.blok_penanda_tangan.{{ $signerIndex }}.nama',
                                                                    )" class="mt-2" />
                                                                </div>
                                                                <div>
                                                                    <x-input-label
                                                                        for="signer-jabatan-{{ $signerIndex }}"
                                                                        value="Jabatan" />
                                                                    <x-text-input
                                                                        id="signer-jabatan-{{ $signerIndex }}"
                                                                        type="text" class="mt-1 w-full text-sm"
                                                                        wire:model.defer="analysisResult.data.blok_penanda_tangan.{{ $signerIndex }}.jabatan" />
                                                                    <x-input-error :messages="$errors->get(
                                                                        'analysisResult.data.blok_penanda_tangan.{{ $signerIndex }}.jabatan',
                                                                    )" class="mt-2" />
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach

                                                    <div class="pt-2">
                                                        <x-button type="button" variant="outline-primary"
                                                            size="sm" wire:click="addSigner()">
                                                            + Tambah Penanda Tangan
                                                        </x-button>
                                                    </div>
                                                </div>
                                            @else
                                                <ul class="space-y-3">
                                                    @forelse ($analysisResult['data']['blok_penanda_tangan'] ?? [] as $signer)
                                                        <li>
                                                            <p
                                                                class="font-semibold text-slate-800 dark:text-slate-200">
                                                                {{ $signer['nama'] }}</p>
                                                            <p class="text-slate-500 dark:text-slate-400">
                                                                {{ Str::title($signer['jabatan']) }}</p>
                                                        </li>
                                                    @empty
                                                        <li>Tidak ada data penanda tangan.</li>
                                                    @endforelse
                                                </ul>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- FOOTER PANEL KIRI --}}
                    <div class="mt-6 pt-6 border-t border-slate-200 dark:border-slate-700 flex-shrink-0">
                        <div class="flex flex-col space-y-3">
                            <a href="{{ Storage::url($this->doc->document_path) }}" download target="_blank"
                                class="inline-flex items-center justify-center w-full px-4 py-2 text-sm font-medium text-blue-700 bg-blue-100 rounded-lg hover:bg-blue-200 dark:bg-blue-900/50 dark:text-blue-300 dark:hover:bg-blue-900">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                                Unduh Dokumen Asli
                            </a>
                            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                                <x-secondary-button wire:click="closeModal"
                                    class="w-full sm:w-auto justify-center">Tutup</x-secondary-button>
                                <x-primary-button wire:click="save" wire:loading.attr="disabled"
                                    class="w-full sm:w-auto justify-center">
                                    <div wire:loading wire:target="save"
                                        class="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2"></div>
                                    @if ($analysisResult)
                                        @if ($analysisResult['data']['type'] === 'peminjaman')
                                            {{ __('Simpan & Buat Peminjaman') }}
                                        @elseif ($analysisResult['data']['type'] === 'perizinan')
                                            {{ __('Simpan & Buat Perizinan') }}
                                        @elseif ($analysisResult['data']['type'] === 'undangan')
                                            {{ __('Simpan & Buat Undangan') }}
                                        @endif
                                    @else
                                        {{ __('Simpan Data Dokumen') }}
                                    @endif
                                </x-primary-button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-7 bg-slate-200 dark:bg-black rounded-r-lg flex items-center justify-center overflow-hidden"
                    style="background-image: linear-gradient(45deg, #e2e8f0 25%, transparent 25%), linear-gradient(-45deg, #e2e8f0 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #e2e8f0 75%), linear-gradient(-45deg, transparent 75%, #e2e8f0 75%); background-size: 20px 20px; background-position: 0 0, 0 10px, 10px -10px, -10px 0px;">
                    <div class="w-full h-full backdrop-blur-sm flex items-center justify-center p-4">
                        @if (Str::startsWith($this->doc->mime_type, 'image/'))
                            <img src="{{ Storage::url($this->doc->document_path) }}" alt="Pratinjau Dokumen"
                                class="max-w-full max-h-full object-contain rounded-lg shadow-2xl">
                        @elseif ($this->doc->mime_type === 'application/pdf')
                            <iframe src="{{ Storage::url($this->doc->document_path) }}" width="100%"
                                height="100%" class="border-0 rounded-lg shadow-2xl bg-white"></iframe>
                        @else
                            <iframe src="{{ Storage::url($this->doc->document_path) }}" width="100%"
                                height="100%" class="border-0 rounded-lg shadow-2xl bg-white"></iframe>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-12 h-[90vh]">
                {{-- SKELETON KOLOM KIRI --}}
                <div class="lg:col-span-5 p-6 flex flex-col animate-pulse">
                    {{-- SKELETON HEADER --}}
                    <div class="flex-shrink-0">
                        <div class="h-6 bg-gray-200 dark:bg-gray-700 rounded w-3/4"></div>
                        <div class="h-4 mt-2 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
                    </div>

                    {{-- SKELETON KONTEN --}}
                    <div class="mt-6 space-y-5 flex-grow">
                        {{-- SKELETON KARTU 1 --}}
                        <div class="p-4 rounded-lg border border-gray-200 dark:border-gray-700 space-y-4">
                            <div class="h-5 bg-gray-200 dark:bg-gray-700 rounded w-1/3"></div>
                            <div class="space-y-3">
                                <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-full"></div>
                                <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-5/6"></div>
                                <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-full"></div>
                            </div>
                        </div>

                        {{-- SKELETON KARTU 2 --}}
                        <div class="p-4 rounded-lg border border-gray-200 dark:border-gray-700 space-y-4">
                            <div class="flex justify-between items-center">
                                <div class="h-5 bg-gray-200 dark:bg-gray-700 rounded w-1/3"></div>
                                <div class="h-7 bg-gray-200 dark:bg-gray-700 rounded w-1/4"></div>
                            </div>
                            <div class="space-y-3 pt-4">
                                <div class="h-10 bg-gray-200 dark:bg-gray-700 rounded w-full"></div>
                                <div class="h-10 bg-gray-200 dark:bg-gray-700 rounded w-full"></div>
                            </div>
                        </div>
                    </div>

                    {{-- SKELETON FOOTER --}}
                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700 flex-shrink-0">
                        <div class="h-10 bg-gray-200 dark:bg-gray-700 rounded w-full mb-3"></div>
                        <div class="flex justify-end gap-3">
                            <div class="h-9 bg-gray-200 dark:bg-gray-700 rounded w-20"></div>
                            <div class="h-9 bg-gray-300 dark:bg-gray-600 rounded w-40"></div>
                        </div>
                    </div>
                </div>

                {{-- SKELETON KOLOM KANAN --}}
                <div class="hidden lg:block lg:col-span-7 bg-gray-200 dark:bg-gray-800 animate-pulse rounded-r-lg">
                </div>
            </div>
        @endif
    </x-modal>
</div>
