<div>
    {{--
        Redesain Modal Verifikasi Data Dokumen
        Fokus: Mobile-First, Best Practice UI/UX, Menampilkan Semua Data
    --}}
    <x-modal name="document-data-modal" maxWidth="5xl" focusable>
        @if (!empty($form->id))
            <form wire:submit="saveCorrections" class="p-4 sm:p-6 bg-gray-50 dark:bg-gray-900">
                <header class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        Verifikasi Data Dokumen
                    </h2>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Sistem telah menganalisis dokumen. Mohon periksa kembali semua data di bawah ini dan perbaiki
                        bagian yang ditandai sebelum melanjutkan.
                    </p>
                </header>

                @if ($errors->isNotEmpty())
                    <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400">
                        <ul role="list" class="mt-2 list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Container Utama dengan Jarak Antar Section --}}
                <div class="space-y-8">

                    <!-- =================================================================== -->
                    <!-- SECTION: INFORMASI DOKUMEN                                          -->
                    <!-- =================================================================== -->
                    <section
                        class="bg-white dark:bg-gray-800 p-4 sm:p-6 rounded-xl shadow-md border dark:border-gray-700">
                        <h3
                            class="text-lg font-semibold mb-4 pb-3 border-b border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-200 flex items-center">
                            <x-heroicon-s-document-text class="h-6 w-6 mr-3 text-indigo-500" />
                            Informasi Dokumen
                        </h3>

                        {{-- Grid Layout: 1 kolom di mobile, 2 di desktop --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">

                            {{-- Field: Nomor Dokumen --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Nomor
                                    Dokumen</label>
                                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $form->document_information['document_number'] ?? 'Tidak ada' }}</p>
                            </div>

                            {{-- Field: Perihal --}}
                            <div>
                                <label
                                    class="block text-xs font-medium text-gray-500 dark:text-gray-400">Perihal</label>
                                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    {{ implode(', ', $form->document_information['subjects'] ?? ['Tidak ada']) }}</p>
                            </div>

                            {{-- Field: Tanggal Dokumen (Dengan Logika Status) --}}
                            @php $docDate = $form->document_information['document_date']; @endphp
                            <div
                                class="p-3 rounded-lg {{ $docDate['status'] === 'error' ? 'bg-red-50 dark:bg-red-900/20 ring-1 ring-red-400' : 'bg-gray-50 dark:bg-gray-700/50' }}">
                                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Tanggal
                                    Dokumen</label>
                                @if ($docDate['status'] === 'error')
                                    <input type="date" wire:model="form.document_information.document_date.date"
                                        class="mt-1 block w-full text-sm border-red-400 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                    <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $docDate['messages'] }}
                                    </p>
                                @else
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                        {{ \Carbon\Carbon::parse($docDate['date'])->isoFormat('D MMMM YYYY') }}</p>
                                @endif
                            </div>

                            {{-- Field: Organisasi Penerbit (Dengan Logika Status) --}}
                            @php
                                $orgs = $form->document_information['emitter_organizations'] ?? [];
                                $unmatchedOrgs = collect($orgs)->where('match_status', 'unmatched')->all();
                                $isOrgUnmatched = !empty($unmatchedOrgs);
                            @endphp
                            <div
                                class="p-3 rounded-lg {{ $isOrgUnmatched ? 'bg-yellow-50 dark:bg-yellow-900/20 ring-1 ring-yellow-400' : 'bg-gray-50 dark:bg-gray-700/50' }}">
                                <label
                                    class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Organisasi
                                    Penerbit</label>
                                @if ($isOrgUnmatched)
                                    <p class="text-xs text-yellow-700 dark:text-yellow-300 mb-2">Teks asli:
                                        "{{ collect($unmatchedOrgs)->pluck('original_name')->join(', ') }}" tidak
                                        dikenali.</p>
                                    <select
                                        wire:model.live.debounce.300ms="form.document_information.final_organization_id"
                                        class="block w-full text-sm border-yellow-400 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                        <option value="">Pilih Organisasi yang Benar...</option>
                                        @foreach ($allOrganizations as $orgOption)
                                            <option value="{{ $orgOption->id }}">{{ $orgOption->name }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                        {{ data_get(collect($orgs)->firstWhere('match_status', 'matched'), 'name', 'Tidak Dikenali') }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </section>

                    <!-- =================================================================== -->
                    <!-- SECTION: DETAIL ACARA (LOOPING)                                     -->
                    <!-- =================================================================== -->
                    @foreach ($form->events as $eventIndex => $event)
                        <section wire:key="event-{{ $eventIndex }}"
                            class="bg-white dark:bg-gray-800 p-4 sm:p-6 rounded-xl shadow-md border dark:border-gray-700">
                            <header class="mb-4 pb-3 border-b border-gray-200 dark:border-gray-700">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                    <h3
                                        class="text-lg font-semibold text-gray-800 dark:text-gray-200 flex items-center">
                                        <x-heroicon-s-calendar-days class="h-6 w-6 mr-3 text-indigo-500" />
                                        Acara: {{ $event['eventName'] }}
                                    </h3>
                                    <span
                                        class="text-xs font-medium px-2 py-1 bg-blue-100 text-blue-800 rounded-full dark:bg-blue-900 dark:text-blue-200 self-start sm:self-center">
                                        {{ $event['fivetask_categories']['name'] ?? 'Umum' }}
                                    </span>
                                </div>
                            </header>

                            <div class="space-y-5">
                                {{-- Sub-Section: Waktu & Tanggal --}}
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-600 dark:text-gray-300 mb-2">Waktu &
                                        Tanggal</h4>
                                    @if (data_get($event, 'parsed_dates.status') === 'error')
                                        <div class="p-3 rounded-lg bg-red-50 dark:bg-red-900/20 ring-1 ring-red-400">
                                            <p class="text-sm font-medium text-red-800 dark:text-red-300">
                                                {{ data_get($event, 'parsed_dates.messages') }}</p>
                                            <div class="mt-2 text-xs text-red-700 dark:text-red-400">
                                                <strong>Teks asli:</strong> "{{ data_get($event, 'date') }}
                                                {{ data_get($event, 'time') }}"
                                            </div>
                                            <div
                                                class="mt-3 flex flex-col sm:flex-row sm:items-end sm:gap-2 space-y-2 sm:space-y-0">
                                                <div class="flex-1">
                                                    <label
                                                        class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Mulai</label>
                                                    <input type="datetime-local"
                                                        wire:model="form.events.{{ $eventIndex }}.parsed_dates.dates.0.start"
                                                        class="block w-full text-sm border-red-400 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                                </div>
                                                <div class="hidden sm:block text-gray-500 pb-2">&rarr;</div>
                                                <div class="flex-1">
                                                    <label
                                                        class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Selesai</label>
                                                    <input type="datetime-local"
                                                        wire:model="form.events.{{ $eventIndex }}.parsed_dates.dates.0.end"
                                                        class="block w-full text-sm border-red-400 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        @foreach (data_get($event, 'parsed_dates.dates', []) as $dateRange)
                                            <div
                                                class="p-3 rounded-lg bg-gray-50 dark:bg-gray-700/50 text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                @php
                                                    $startDate = \Carbon\Carbon::parse($dateRange['start']);
                                                    $endDate = \Carbon\Carbon::parse($dateRange['end']);
                                                @endphp
                                                {{ $startDate->isoFormat('dddd, D MMM YYYY') }} &bull;
                                                {{ $startDate->isoFormat('HH:mm') }} -
                                                {{ $endDate->isoFormat('HH:mm') }}
                                            </div>
                                        @endforeach
                                    @endif
                                </div>

                                {{-- Sub-Section: Lokasi --}}
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-600 dark:text-gray-300 mb-2">Lokasi</h4>
                                    <div class="space-y-3">

                                        {{-- Loop setiap item lokasi dan tangani statusnya secara individual --}}
                                        @forelse ($event['location_data'] as $locIndex => $loc)
                                            @php
                                                $isMatched = $loc['match_status'] === 'matched';
                                                $isEditing = $editingLocationIndex[$eventIndex] === $locIndex;
                                            @endphp
                                            <div wire:key="location-item-{{ $eventIndex }}-{{ $locIndex }}"
                                                class="p-3 rounded-lg transition-all
                                                {{ $isEditing ? 'bg-blue-50 dark:bg-blue-900/30 ring-2 ring-blue-500' : '' }}
                                                {{ !$isEditing && $isMatched ? 'bg-gray-50 dark:bg-gray-700/50' : '' }}
                                                {{ !$isEditing && !$isMatched ? 'bg-yellow-50 dark:bg-yellow-900/20 ring-1 ring-yellow-400' : '' }}
                                            ">
                                                @if (!$isEditing && $isMatched)
                                                    {{-- TAMPILAN MODE BACA (untuk item yang sudah 'matched') --}}
                                                    <div class="flex items-center justify-between">
                                                        <div class="flex items-center flex-grow min-w-0">
                                                            <x-heroicon-s-check-circle
                                                                class="h-5 w-5 mr-2 text-green-500 shrink-0" />
                                                            <p
                                                                class="font-semibold text-gray-900 dark:text-gray-100 text-sm truncate">
                                                                {{ $loc['name'] }}</p>
                                                        </div>
                                                        <div class="flex items-center space-x-3 shrink-0 ml-4">
                                                            <button type="button"
                                                                wire:click="editLocation({{ $eventIndex }}, {{ $locIndex }})"
                                                                class="text-xs font-medium text-blue-600 dark:text-blue-400 hover:underline">Ubah</button>
                                                            <button type="button"
                                                                wire:click="removeLocation({{ $eventIndex }}, {{ $locIndex }})"
                                                                class="text-gray-400 hover:text-red-500">
                                                                <x-heroicon-s-x-mark class="h-4 w-4" />
                                                            </button>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="flex-grow">
                                                        @if (!$isMatched && !empty($loc['original_name']))
                                                            <p
                                                                class="text-xs text-yellow-800 dark:text-yellow-300 mb-2">
                                                                Teks asli tidak dikenali:
                                                                <strong class="font-medium">
                                                                    "{{ $loc['original_name'] }}"
                                                                </strong>
                                                            </p>
                                                        @endif

                                                        <fieldset
                                                            class="flex flex-wrap items-center gap-x-4 gap-y-2 mb-3">
                                                            <legend class="sr-only">Pilih aksi untuk lokasi</legend>
                                                            <div>
                                                                <input
                                                                    wire:click="selectLocationFromDB({{ $eventIndex }}, {{ $locIndex }})"
                                                                    type="radio"
                                                                    name="location_mode_{{ $eventIndex }}_{{ $locIndex }}"
                                                                    id="mode_loc_{{ $eventIndex }}_{{ $locIndex }}"
                                                                    value="location"
                                                                    @if ($loc['source'] === 'location') checked @endif
                                                                    class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                                <label
                                                                    for="mode_loc_{{ $eventIndex }}_{{ $locIndex }}"
                                                                    class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                                                                    Pilih dari Daftar
                                                                </label>
                                                            </div>
                                                            <div>
                                                                <input
                                                                    wire:click="acceptAsExternalLocation({{ $eventIndex }}, {{ $locIndex }})"
                                                                    type="radio"
                                                                    name="location_mode_{{ $eventIndex }}_{{ $locIndex }}"
                                                                    id="mode_custom_{{ $eventIndex }}_{{ $locIndex }}"
                                                                    value="custom"
                                                                    @if ($loc['source'] === 'custom') checked @endif
                                                                    class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                                <label
                                                                    for="mode_custom_{{ $eventIndex }}_{{ $locIndex }}"
                                                                    class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                                                                    Simpan sebagai Lokasi Baru
                                                                </label>
                                                            </div>
                                                        </fieldset>

                                                        @if (data_get($loc, 'source') === 'custom')
                                                            <div>
                                                                <label
                                                                    class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Nama
                                                                    Lokasi Baru</label>
                                                                <input type="text"
                                                                    wire:model.live.debounce.300ms="form.events.{{ $eventIndex }}.location_data.{{ $locIndex }}.name"
                                                                    placeholder="Ketik nama lokasi..."
                                                                    class="block w-full text-sm border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500">
                                                                <p
                                                                    class="mt-2 text-xs text-blue-600 dark:text-blue-400 flex items-center">
                                                                    <x-heroicon-s-information-circle
                                                                        class="h-4 w-4 mr-1" />
                                                                    Lokasi ini akan disimpan sebagai data baru.
                                                                </p>
                                                            </div>
                                                        @else
                                                            <div>
                                                                <select
                                                                    wire:change="updateLocation({{ $eventIndex }}, {{ $locIndex }}, $event.target.value)"
                                                                    class="block w-full text-sm border-yellow-400 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-yellow-500">
                                                                    <option value="">Pilih Lokasi untuk
                                                                        menautkan...</option>
                                                                    @foreach ($allLocations as $locOption)
                                                                        <option value="{{ $locOption->id }}"
                                                                            @if ($locOption->id == $loc['location_id']) selected @endif>
                                                                            {{ $locOption->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        @endif

                                                        @if ($isEditing)
                                                            <button type="button"
                                                                wire:click="cancelEditLocation({{ $eventIndex }})"
                                                                class="text-xs text-gray-500 dark:text-gray-400 hover:underline mt-3">
                                                                Batal
                                                            </button>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        @empty
                                            <div
                                                class="p-4 text-center text-sm text-gray-500 dark:text-gray-400 rounded-lg bg-gray-50 dark:bg-gray-700/50 border border-dashed dark:border-gray-600">
                                                Tidak ada data lokasi untuk kegiatan ini.
                                            </div>
                                        @endforelse

                                        {{-- Tombol untuk menambah lokasi baru (selalu tersedia) --}}
                                        <div class="pt-2">
                                            <button type="button" wire:click="addLocation({{ $eventIndex }})"
                                                class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-semibold flex items-center">
                                                <x-heroicon-s-plus-circle class="h-4 w-4 mr-1" />
                                                Tambah Lokasi Lain
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                {{-- Sub-Section: Peralatan --}}
                                @if (!empty($event['equipment']))
                                    <div>
                                        <h4 class="text-sm font-semibold text-gray-600 dark:text-gray-300 mb-2">
                                            Peralatan</h4>
                                        <div class="space-y-2">
                                            @foreach ($event['equipment'] as $itemIndex => $item)
                                                @php $isUnmatched = $item['match_status'] === 'unmatched'; @endphp
                                                <div wire:key="item-{{ $eventIndex }}-{{ $itemIndex }}"
                                                    class="p-3 rounded-lg {{ $isUnmatched ? 'bg-yellow-50 dark:bg-yellow-900/20 ring-1 ring-yellow-400' : 'bg-gray-50 dark:bg-gray-700/50' }}">
                                                    @if ($isUnmatched)
                                                        <div class="flex-grow">
                                                            <p
                                                                class="text-xs text-yellow-700 dark:text-yellow-300 mb-1">
                                                                Teks asli: "{{ $item['original_name'] }}"</p>
                                                            <select
                                                                wire:change="linkItem('equipment', $event.target.value, {{ $eventIndex }}, {{ $itemIndex }})"
                                                                class="block w-full text-sm border-yellow-400 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                                                <option value="">Pilih Aset...</option>
                                                                @foreach ($this->availableAssets($eventIndex) as $asset)
                                                                    <option value="{{ $asset->id }}"
                                                                        @disabled($asset->available_stock <= 0)>
                                                                        {{ $asset->name }} (Stok:
                                                                        {{ $asset->available_stock }})
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    @else
                                                        <div class="flex items-center justify-between gap-4">
                                                            <p
                                                                class="text-sm font-semibold text-gray-900 dark:text-gray-100 flex-grow">
                                                                {{ $item['item'] }}</p>
                                                            <div class="flex items-center gap-2">
                                                                <label class="text-xs text-gray-500">Jumlah:</label>
                                                                <input type="number" min="1"
                                                                    wire:model="form.events.{{ $eventIndex }}.equipment.{{ $itemIndex }}.quantity"
                                                                    class="w-20 text-center text-sm border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </section>
                    @endforeach

                    <!-- =================================================================== -->
                    <!-- SECTION: PENANDA TANGAN                                             -->
                    <!-- =================================================================== -->
                    @if (!empty($form->signature_blocks))
                        <section
                            class="bg-white dark:bg-gray-800 p-4 sm:p-6 rounded-xl shadow-md border dark:border-gray-700">
                            <h3
                                class="text-lg font-semibold mb-4 pb-3 border-b border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-200 flex items-center">
                                <x-heroicon-s-user-group class="h-6 w-6 mr-3 text-indigo-500" />
                                Penanda Tangan
                            </h3>
                            <div class="flow-root">
                                <ul role="list" class="-my-4 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach ($form->signature_blocks as $signer)
                                        <li class="flex items-center py-4 space-x-4">
                                            <div class="flex-shrink-0">
                                                <span
                                                    class="flex items-center justify-center h-8 w-8 rounded-full bg-gray-200 dark:bg-gray-700">
                                                    <x-heroicon-s-user
                                                        class="h-5 w-5 text-gray-500 dark:text-gray-400" />
                                                </span>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 truncate dark:text-white">
                                                    {{ $signer['name'] }}</p>
                                                <p class="text-sm text-gray-500 truncate dark:text-gray-400">
                                                    {{ $signer['position'] }}</p>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </section>
                    @endif

                </div>

                {{-- Footer dengan Tombol Aksi --}}
                <footer class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                    <x-secondary-button type="button" x-on:click="$dispatch('close')">Batal</x-secondary-button>
                    <x-primary-button type="submit" wire:loading.attr="disabled">
                        <span wire:loading.remove>Simpan & Lanjutkan</span>
                        <span wire:loading>Menyimpan...</span>
                    </x-primary-button>
                </footer>
            </form>
        @else
            {{-- Tampilan Loading Awal --}}
            <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                <p>Memuat data verifikasi...</p>
            </div>
        @endif
    </x-modal>
</div>
