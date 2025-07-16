<div>
    <x-modal name="document-data-modal" maxWidth="6xl" focusable>
        @if ($this->data->isNotEmpty())
            <form wire:submit="saveCorrections" class="p-6 bg-white dark:bg-gray-800">
                <header class="mb-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                        {{-- Judul dinamis berdasarkan tipe dokumen --}}
                        Konfirmasi dan Perbaiki Data {{ ucfirst($this->data['type']) }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Beberapa data dari dokumen tidak dikenali atau butuh konfirmasi. Mohon perbaiki data yang
                        ditandai sebelum melanjutkan.
                    </p>
                </header>

                <div class="space-y-6">
                    <section
                        class="p-6 rounded-xl shadow-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                        <h3
                            class="text-xl font-bold mb-4 pb-2 border-b border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-100">
                            Informasi Dokumen
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal
                                    Dokumen</label>
                                @php $docDate = $data['document_information']['document_date']; @endphp

                                @if ($docDate['status'] === 'error')
                                    {{-- Error state: Editable input with error message --}}
                                    <input type="date" wire:model="data.document_information.document_date.date"
                                        class="mt-1 block w-full px-3 py-2 border border-red-500 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm
                                               bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500">
                                    <p class="text-sm text-red-600 dark:text-red-400 mt-2 flex items-center">
                                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z">
                                            </path>
                                        </svg>
                                        {{ $docDate['messages'] }}
                                    </p>
                                @else
                                    {{-- Success state: Read-only display --}}
                                    <div
                                        class="mt-1 block w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 rounded-md shadow-sm text-gray-900 dark:text-gray-100
                                                border border-gray-300 dark:border-gray-600 cursor-not-allowed">
                                        {{ \Carbon\Carbon::parse($docDate['date'])->isoFormat('D MMMM YYYY') }}
                                    </div>
                                @endif
                            </div>

                            {{-- Organisasi Penerbit --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Organisasi Penerbit
                                </label>
                                @php
                                    $organizations = $data['document_information']['emitter_organizations'];
                                    $matchedOrgs = $organizations
                                        ->filter(fn($value) => $value['match_status'] === 'matched')
                                        ->values()
                                        ->all();
                                    $matchedCount = count($matchedOrgs);
                                @endphp

                                @if ($matchedCount === 1)
                                    {{-- Case 1: Exactly one organization matched --}}
                                    <div class="mt-1">
                                        @php $theOnlyMatch = reset($matchedOrgs); @endphp
                                        <div
                                            class="block w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 rounded-md shadow-sm text-gray-900 dark:text-gray-100
                                                    border border-gray-300 dark:border-gray-600 cursor-not-allowed">
                                            {{ $theOnlyMatch['nama_organisasi'] }}
                                        </div>
                                        <p class="text-sm text-green-600 dark:text-green-400 mt-2 flex items-center">
                                            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            Organisasi berhasil dicocokkan.
                                        </p>
                                    </div>
                                @elseif ($matchedCount > 1)
                                    {{-- Case 2: More than one organization matched --}}
                                    <div class="mt-1">
                                        <select wire:model.defer="data.document_information.final_organization_id"
                                            class="block w-full px-3 py-2 border border-blue-500 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm
                                                   bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                            <option value="">Pilih salah satu dari hasil yang cocok...</option>
                                            @foreach ($matchedOrgs as $org)
                                                <option value="{{ $org['nama_organisasi_id'] }}">
                                                    {{ $org['nama_organisasi'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <p class="text-sm text-blue-600 dark:text-blue-400 mt-2 flex items-center">
                                            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                </path>
                                            </svg>
                                            Ditemukan beberapa kemungkinan. Mohon pilih organisasi yang paling tepat.
                                        </p>
                                    </div>
                                @else
                                    {{-- Case 3: No organizations matched --}}
                                    <div class="mt-1">
                                        @if ($organizations->isNotEmpty())
                                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Teks asli yang
                                                tidak dikenali:</p>
                                            <div class="flex flex-wrap gap-2 mb-3">
                                                @foreach ($organizations as $org)
                                                    <span
                                                        class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-full text-xs font-medium">
                                                        "{{ $org['original_name'] }}"
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif

                                        <select wire:model.defer="data.document_information.final_organization_id"
                                            class="block w-full px-3 py-2 border border-yellow-500 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm
                                                   bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                            <option value="">Pilih Organisasi...</option>
                                            {{-- Make sure $allOrganizations is passed to your Livewire component's render method --}}
                                            @if (isset($allOrganizations))
                                                @foreach ($allOrganizations as $orgOption)
                                                    <option value="{{ $orgOption->id }}">{{ $orgOption->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <p class="text-sm text-yellow-600 dark:text-yellow-400 mt-2 flex items-center">
                                            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                                </path>
                                            </svg>
                                            Organisasi tidak dikenali. Mohon pilih secara manual dari daftar.
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </section>

                    @if (!empty($data['events']))
                        <section
                            class="p-6 rounded-xl shadow-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                            <h3
                                class="text-xl font-bold mb-4 pb-2 border-b border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-100">
                                Detail Acara
                            </h3>

                            @foreach ($data['events'] as $eventIndex => $event)
                                <div class="py-6 {{ !$loop->first ? 'border-t border-gray-200 dark:border-gray-700 mt-6' : '' }}"
                                    wire:key="event-{{ $eventIndex }}">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 gap-2">
                                        <p class="font-bold text-xl text-gray-900 dark:text-white">
                                            {{ $event['eventName'] }}</p>
                                        <span
                                            class="text-xs font-semibold px-3 py-1 bg-blue-100 text-blue-800 rounded-full dark:bg-blue-900 dark:text-blue-200">
                                            {{ $event['fivetask_categories']['nama'] }}
                                        </span>
                                    </div>

                                    {{-- - - --}}
                                    <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-3 mt-6">
                                        <x-heroicon-s-calendar class="inline-block h-5 w-5 mr-2 text-gray-500" /> Hari
                                        dan Tanggal Kegiatan
                                    </h4>
                                    <div class="grid grid-cols-1 gap-4"> {{-- Changed from mt-6 directly to a grid for better structure --}}
                                        @if (($event['dates'][0]['status'] ?? 'success') === 'error')
                                            {{-- ERROR PARSING DATES --}}
                                            <div
                                                class="p-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-300 dark:border-red-700">
                                                <div
                                                    class="flex items-center text-red-700 dark:text-red-300 font-medium mb-3">
                                                    <x-heroicon-s-exclamation-circle class="h-5 w-5 mr-2" />
                                                    <p>{{ $event['dates'][0]['messages'] }}</p>
                                                </div>

                                                <div class="text-sm text-gray-600 dark:text-gray-400 mb-4 space-y-1">
                                                    <p><strong>Teks Asli:</strong></p>
                                                    <ul class="list-disc list-inside ml-2">
                                                        <li>Tanggal: "{{ $event['date'] }}"</li>
                                                        <li>Waktu: "{{ $event['time'] }}"</li>
                                                    </ul>
                                                </div>

                                                <p class="text-sm text-gray-700 dark:text-gray-300 mb-3">
                                                    Mohon masukkan tanggal dan waktu mulai/selesai secara manual:
                                                </p>

                                                <div class="space-y-3">
                                                    @foreach ($event['dates'] as $dateIndex => $dateRange)
                                                        <div class="flex flex-col sm:flex-row items-center gap-2"
                                                            wire:key="manual-date-{{ $eventIndex }}-{{ $dateIndex }}">
                                                            <div
                                                                class="flex-grow grid grid-cols-1 sm:grid-cols-2 gap-2 w-full">
                                                                <input type="datetime-local"
                                                                    wire:model.live="data.events.{{ $eventIndex }}.dates.{{ $dateIndex }}.start"
                                                                    class="block w-full px-3 py-2 border border-red-400 rounded-md shadow-sm text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                                                <input type="datetime-local"
                                                                    wire:model.live="data.events.{{ $eventIndex }}.dates.{{ $dateIndex }}.end"
                                                                    class="block w-full px-3 py-2 border border-red-400 rounded-md shadow-sm text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                                            </div>
                                                            @if (count($event['dates']) > 1)
                                                                {{-- Only show remove button if there's more than one date range --}}
                                                                <button type="button"
                                                                    wire:click="removeDateRange({{ $eventIndex }}, {{ $dateIndex }})"
                                                                    class="p-2 text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition duration-150 ease-in-out self-end sm:self-center">
                                                                    <x-heroicon-s-trash class="w-5 h-5" />
                                                                </button>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>

                                                <button type="button" wire:click="addDateRange({{ $eventIndex }})"
                                                    class="mt-4 text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-semibold flex items-center">
                                                    <x-heroicon-s-plus-circle class="h-4 w-4 mr-1" /> Tambah Rentang
                                                    Tanggal
                                                </button>
                                            </div>
                                        @else
                                            {{-- SUCCESSFUL DATES DISPLAY --}}
                                            @foreach ($event['dates'] as $dateRange)
                                                @if (!is_null($dateRange['start']))
                                                    @php
                                                        $startDate = \Carbon\Carbon::parse($dateRange['start']);
                                                        $endDate = \Carbon\Carbon::parse($dateRange['end']);
                                                    @endphp
                                                    <div
                                                        class="p-4 rounded-lg shadow-sm bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 mb-3">
                                                        <div
                                                            class="flex items-center text-gray-900 dark:text-gray-100 mb-1">
                                                            <x-heroicon-s-calendar
                                                                class="h-5 w-5 mr-2 text-blue-500" />
                                                            @if ($startDate->isSameDay($endDate))
                                                                <p class="text-lg font-medium">
                                                                    {{ $startDate->isoFormat('dddd, D MMMM YYYY') }}
                                                                </p>
                                                            @else
                                                                <p class="text-lg font-medium">
                                                                    {{ $startDate->isoFormat('dddd, D MMMM YYYY') }}
                                                                    &mdash;
                                                                    {{ $endDate->isoFormat('dddd, D MMMM YYYY') }}
                                                                </p>
                                                            @endif
                                                        </div>
                                                        <div
                                                            class="flex items-center text-gray-600 dark:text-gray-400">
                                                            <x-heroicon-s-clock class="h-5 w-5 mr-2 text-purple-500" />
                                                            <p class="text-sm">Pukul
                                                                {{ $startDate->isoFormat('HH:mm') }} -
                                                                {{ $endDate->isoFormat('HH:mm') }} WIB</p>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        @endif
                                    </div>

                                    <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-3 mt-6">
                                        <x-heroicon-s-map-pin class="inline-block h-5 w-5 mr-2 text-gray-500" /> Lokasi
                                        Acara
                                    </h4>
                                    <div class="grid grid-cols-1 gap-4">
                                        @php
                                            // Cek apakah ada lokasi yang sudah cocok di dalam location_data
                                            $matchedLocation = collect($event['location_data'])->firstWhere(
                                                'match_status',
                                                'matched',
                                            );
                                        @endphp
                                        @if ($matchedLocation)
                                            <div
                                                class="p-4 rounded-lg bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 space-y-3">
                                                @foreach ($event['location_data'] as $locIndex => $loc)
                                                    <div class="p-3 rounded-md {{ $editingLocationIndex[$eventIndex] === $locIndex ? 'bg-blue-50 dark:bg-blue-900/30 ring-2 ring-blue-500' : 'bg-white dark:bg-gray-800 shadow-sm' }}"
                                                        wire:key="location-item-{{ $eventIndex }}-{{ $locIndex }}">

                                                        @if ($editingLocationIndex[$eventIndex] === $locIndex)
                                                            {{-- Tampilan Mode Edit --}}
                                                            <div>
                                                                <select
                                                                    wire:change="updateLocation({{ $eventIndex }}, {{ $locIndex }}, $event.target.value)"
                                                                    class="block w-full px-3 py-2 border border-blue-500 rounded-md ...">
                                                                    <option value="">Pilih Lokasi yang Benar...
                                                                    </option>
                                                                    @foreach ($allLocations as $locOption)
                                                                        <option value="{{ $locOption->id }}">
                                                                            {{ $locOption->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                                <button type="button"
                                                                    wire:click="cancelEditLocation({{ $eventIndex }})"
                                                                    class="text-xs text-gray-500 hover:underline mt-1">Batal</button>
                                                            </div>
                                                        @else
                                                            {{-- Tampilan Mode Baca --}}
                                                            <div class="flex items-center justify-between">
                                                                <div class="flex items-center">
                                                                    @if ($loc['match_status'] === 'matched')
                                                                        <x-heroicon-s-check-circle
                                                                            class="h-5 w-5 mr-2 text-green-500" />
                                                                    @else
                                                                        <x-heroicon-s-exclamation-triangle
                                                                            class="h-5 w-5 mr-2 text-yellow-500" />
                                                                    @endif
                                                                    <p
                                                                        class="font-medium text-gray-900 dark:text-gray-100">
                                                                        {{ $loc['name'] ?: $loc['original_name'] }}</p>
                                                                </div>
                                                                <div class="flex items-center space-x-2">
                                                                    <button type="button"
                                                                        wire:click="editLocation({{ $eventIndex }}, {{ $locIndex }})"
                                                                        class="text-sm text-blue-600 dark:text-blue-400 hover:underline">Ubah</button>
                                                                    <button type="button"
                                                                        wire:click="removeLocation({{ $eventIndex }}, {{ $locIndex }})"
                                                                        class="text-gray-400 hover:text-red-500"><x-heroicon-s-x-mark
                                                                            class="h-4 w-4" /></button>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                                {{-- Tombol untuk menambah lokasi baru --}}
                                                <button type="button" wire:click="addLocation({{ $eventIndex }})"
                                                    class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-semibold flex items-center mt-2">
                                                    <x-heroicon-s-plus-circle class="h-4 w-4 mr-1" /> Tambah Lokasi
                                                    Lain
                                                </button>
                                            </div>
                                        @else
                                            <div
                                                class="p-4 rounded-lg bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-300 dark:border-yellow-700">
                                                <div
                                                    class="flex items-center mb-2 text-yellow-800 dark:text-yellow-300">
                                                    <x-heroicon-s-exclamation-triangle class="h-5 w-5 mr-2 shrink-0" />
                                                    <p class="text-sm font-medium">Lokasi tidak dikenali, mohon
                                                        perbaiki atau pilih dari daftar.</p>
                                                </div>
                                                <div>
                                                    <label for="raw-location-{{ $eventIndex }}"
                                                        class="sr-only">Lokasi Acara</label>
                                                    <input type="text" id="raw-location-{{ $eventIndex }}"
                                                        wire:model.defer="data.events.{{ $eventIndex }}.location"
                                                        class="block w-full px-3 py-2 border border-yellow-500 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 sm:text-sm"
                                                        placeholder="Contoh: Gedung Serbaguna Sasana Krida">
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- - - --}}
                                    <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-3 mt-6">
                                        <x-heroicon-s-list-bullet class="inline-block h-5 w-5 mr-2 text-gray-500" />
                                        Jadwal Acara / Rundown
                                    </h4>
                                    <div class="space-y-4">
                                        @forelse ($event['schedule'] as $scheduleIndex => $schedule)
                                            @php
                                                $hasTimeData =
                                                    !empty($schedule['startTime']) || !empty($schedule['endTime']);
                                            @endphp
                                            <div class="p-4 rounded-lg border bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700"
                                                wire:key="schedule-{{ $eventIndex }}-{{ $scheduleIndex }}">
                                                <div class="flex items-start space-x-3">
                                                    <div
                                                        class="flex-grow grid grid-cols-1 {{ $hasTimeData ? 'md:grid-cols-2' : '' }} gap-4 items-start">
                                                        <div>
                                                            <label
                                                                class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Deskripsi</label>
                                                            <div
                                                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 text-sm rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                                                {{ $schedule['description'] }}
                                                            </div>
                                                        </div>

                                                        @if ($hasTimeData)
                                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                                <x-schedule-time-input :eventIndex="$eventIndex"
                                                                    :scheduleIndex="$scheduleIndex" :scheduleData="$schedule"
                                                                    field="startTime_processed" label="Mulai" />

                                                                <x-schedule-time-input :eventIndex="$eventIndex"
                                                                    :scheduleIndex="$scheduleIndex" :scheduleData="$schedule"
                                                                    field="endTime_processed" label="Selesai" />
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div
                                                class="p-4 text-center text-sm text-gray-500 dark:text-gray-400 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600">
                                                Tidak ada jadwal/rundown untuk kegiatan ini.
                                            </div>
                                        @endforelse
                                    </div>

                                    {{-- Equipment --}}
                                    <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-3 mt-6">
                                        <x-heroicon-s-wrench-screwdriver
                                            class="inline-block h-5 w-5 mr-2 text-gray-500" />
                                        Peralatan yang Dibutuhkan
                                    </h4>
                                    <div class="space-y-4">
                                        @forelse ($event['equipment'] ?? [] as $itemIndex => $item)
                                            <div wire:key="item-{{ $eventIndex }}-{{ $itemIndex }}"
                                                class="p-4 rounded-lg shadow-sm transition-all duration-200 ease-in-out
                                            {{ $item['match_status'] === 'matched' ? 'bg-green-50 dark:bg-green-900/20 border border-green-300 dark:border-green-700' : 'bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-300 dark:border-yellow-700' }}">

                                                @if ($item['match_status'] === 'matched')
                                                    <div
                                                        class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2">
                                                        <div class="flex items-center">
                                                            <x-heroicon-s-check-circle
                                                                class="h-6 w-6 mr-2 text-green-600" />
                                                            <div>
                                                                <p class="font-bold text-gray-800 dark:text-gray-200">
                                                                    {{ $item['item'] }}</p>
                                                                <p class="text-sm text-green-600 dark:text-green-400">
                                                                    Terverifikasi</p>
                                                            </div>
                                                        </div>
                                                        <div class="flex items-center gap-2 mt-2 sm:mt-0">
                                                            <label for="qty-{{ $eventIndex }}-{{ $itemIndex }}"
                                                                class="text-sm text-gray-700 dark:text-gray-300">Jumlah:</label>
                                                            <input id="qty-{{ $eventIndex }}-{{ $itemIndex }}"
                                                                type="number" min="1"
                                                                wire:model.defer="data.events.{{ $eventIndex }}.equipment.{{ $itemIndex }}.quantity"
                                                                class="w-20 text-center px-2 py-1 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                                        </div>
                                                    </div>
                                                @else
                                                    <div wire:loading.class="opacity-50"
                                                        wire:target="linkItem, removeItem">
                                                        <div class="flex items-start gap-2 mb-3">
                                                            <x-heroicon-s-exclamation-triangle
                                                                class="h-6 w-6 text-yellow-500 shrink-0 mt-0.5" />
                                                            <p
                                                                class="font-semibold text-yellow-800 dark:text-yellow-300">
                                                                Peralatan tidak dikenali: <span
                                                                    class="font-bold text-red-600 dark:text-red-400">"{{ $item['original_name'] }}"</span>
                                                            </p>
                                                        </div>
                                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                            <div>
                                                                <label
                                                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                                    Opsi 1: Tautkan ke Aset yang Ada
                                                                </label>
                                                                @php
                                                                    $selectedAssetIds = $this->getSelectedAssetIds(
                                                                        $eventIndex,
                                                                    );
                                                                @endphp
                                                                <select
                                                                    wire:change="linkItem('equipment', $event.target.value, {{ $eventIndex }}, {{ $itemIndex }})"
                                                                    class="mt-1 w-full px-3 py-2 text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                                                    <option value="">Pilih aset yang benar...
                                                                    </option>
                                                                    @foreach ($this->availableAssets($eventIndex) as $availableAsset)
                                                                        <option value="{{ $availableAsset->id }}"
                                                                            @if (in_array($availableAsset->id, $selectedAssetIds) || $availableAsset->available_stock <= 0) disabled @endif>
                                                                            {{ $availableAsset->name }} (Tersedia:
                                                                            {{ $availableAsset->available_stock }})
                                                                            @if (in_array($availableAsset->id, $selectedAssetIds))
                                                                                (sudah dipilih)
                                                                            @endif
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div>
                                                                <label
                                                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                                    Opsi 2: Hapus Jika Tidak Diperlukan
                                                                </label>
                                                                <button type="button" variant="danger"
                                                                    class="w-full mt-1 justify-center inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150"
                                                                    wire:click="removeItem('equipment', {{ $eventIndex }}, {{ $itemIndex }})">
                                                                    <x-heroicon-s-trash class="h-4 w-4 mr-2" />
                                                                    Hapus dari Daftar
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @empty
                                            <div
                                                class="p-4 text-center text-sm text-gray-500 dark:text-gray-400 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600">
                                                Tidak ada data peralatan untuk kegiatan ini.
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            @endforeach
                        </section>
                    @endif
                </div>

                <footer class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                    <x-secondary-button x-on:click="$dispatch('close')">Batal</x-secondary-button>
                    <x-primary-button type="submit">Simpan</x-primary-button>
                </footer>
            </form>
        @else
            <div class="p-6 text-center text-gray-500">
                <p>Memuat data...</p>
            </div>
        @endif
    </x-modal>
</div>
