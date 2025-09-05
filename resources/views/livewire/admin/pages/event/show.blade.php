<div class="py-8">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <div class="lg:col-span-2 space-y-6">

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                    <div class="p-6">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $event->name }}</h1>
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $event->status->color() }}">
                                {{ $event->status->label() }}
                            </span>
                        </div>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Dibuat oleh {{ $event->creator->name }} &bull; {{ $event->created_at->diffForHumans() }}
                        </p>

                        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 text-sm">
                            <div class="flex items-start gap-3">
                                <x-heroicon-o-tag class="h-6 w-6 text-indigo-500 flex-shrink-0" />
                                <div>
                                    <p class="font-semibold text-gray-800 dark:text-gray-200">Kategori</p>
                                    <p class="text-gray-600 dark:text-gray-400">{{ $event->eventCategory->name ?? '-' }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <x-heroicon-o-calendar-days class="h-6 w-6 text-indigo-500 flex-shrink-0" />
                                <div>
                                    <p class="font-semibold text-gray-800 dark:text-gray-200">Rentang Acara</p>
                                    <p class="text-gray-600 dark:text-gray-400">
                                        {{ $event->start_recurring->format('d M Y') }} -
                                        {{ $event->end_recurring->format('d M Y') }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <x-heroicon-o-map-pin class="h-6 w-6 text-indigo-500 flex-shrink-0" />
                                <div>
                                    <p class="font-semibold text-gray-800 dark:text-gray-200">Lokasi</p>
                                    <p class="text-gray-600 dark:text-gray-400">
                                        {{ $event->locations->pluck('name')->join(', ') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4">Deskripsi Acara</h2>
                        <div class="prose dark:prose-invert max-w-none text-gray-600 dark:text-gray-300">
                            {!! nl2br(e($event->description)) !!}
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <div class="p-6">
                        <div class="border-b dark:border-gray-700 pb-4 mb-4">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Jadwal Pelaksanaan</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                Pola perulangan: <span
                                    class="font-semibold">{{ $event->recurrence_type->label() }}</span>
                            </p>
                        </div>
                        <div class="space-y-4">
                            @forelse ($mergedSchedules as $schedule)
                                @php
                                    // Cek apakah acara ini multi-hari atau tidak.
                                    // Metode isSameDay() dari Carbon akan mengembalikan true jika tanggal, bulan, dan tahunnya sama.
                                    $isMultiDay = !$schedule['start']->isSameDay($schedule['end']);
                                @endphp

                                <div
                                    class="flex items-start gap-4 p-4 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-200">

                                    @if ($isMultiDay)
                                        <div
                                            class="flex-shrink-0 text-center bg-gray-100 dark:bg-gray-700 rounded-lg p-3 w-24">
                                            <p class="text-sm font-bold text-red-600 dark:text-red-400">
                                                {{ $schedule['start']->format('M Y') }}
                                            </p>
                                            <div class="flex items-center justify-center gap-1 mt-1">
                                                <p class="text-2xl font-bold text-gray-800 dark:text-gray-200">
                                                    {{ $schedule['start']->format('d') }}
                                                </p>
                                                <x-heroicon-s-arrow-right class="h-4 w-4 text-gray-400" />
                                                <p class="text-2xl font-bold text-gray-800 dark:text-gray-200">
                                                    {{ $schedule['end']->format('d') }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex-grow pt-1">
                                            <p class="font-semibold text-gray-900 dark:text-gray-100">
                                                Acara Berlangsung Beberapa Hari
                                            </p>
                                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                                <span class="font-medium">Mulai:</span>
                                                {{ $schedule['start']->isoFormat('dddd, D MMM YYYY • HH:mm') }}
                                            </p>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                <span class="font-medium">Selesai:</span>
                                                {{ $schedule['end']->isoFormat('dddd, D MMM YYYY • HH:mm') }}
                                            </p>
                                            <p class="mt-2 text-xs text-gray-500">
                                                Total Durasi: {{ $schedule['duration'] }}
                                            </p>
                                        </div>
                                    @else
                                        <div
                                            class="flex-shrink-0 text-center bg-gray-100 dark:bg-gray-700 rounded-lg p-2 w-20">
                                            <p class="text-sm font-bold text-red-600 dark:text-red-400">
                                                {{ $schedule['start']->format('M') }}</p>
                                            <p class="text-2xl font-bold text-gray-800 dark:text-gray-200">
                                                {{ $schedule['start']->format('d') }}</p>
                                            <p class="text-xs text-gray-500">{{ $schedule['start']->format('Y') }}</p>
                                        </div>
                                        <div class="flex-grow">
                                            <p class="font-semibold text-gray-900 dark:text-gray-100">
                                                {{ $schedule['start']->isoFormat('dddd') }}</p>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                {{ $schedule['start']->format('H:i') }} -
                                                {{ $schedule['end']->format('H:i') }} WIB
                                                <span class="text-gray-400 dark:text-gray-500 mx-1">•</span>
                                                Durasi: {{ $schedule['duration'] }}
                                            </p>
                                        </div>
                                    @endif

                                </div>
                            @empty
                                <div
                                    class="text-center py-8 px-4 border-2 border-dashed rounded-lg dark:border-gray-700">
                                    <x-heroicon-o-x-mark class="mx-auto h-10 w-10 text-gray-400" />
                                    <p class="mt-2 text-sm font-semibold text-gray-700 dark:text-gray-300">Tidak ada
                                        data jadwal ditemukan.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4">Lokasi Acara</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach ($event->locations as $location)
                                <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($location->name) }}"
                                    target="_blank" rel="noopener noreferrer"
                                    class="block rounded-lg border dark:border-gray-700 overflow-hidden group hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                                    @if ($location->image && Storage::exists($location->image))
                                        <img class="h-40 w-full object-cover"
                                            src="{{ Storage::url($location->image) }}" alt="{{ $location->name }}">
                                    @else
                                        <div
                                            class="h-40 w-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                            <x-heroicon-o-photo class="h-12 w-12 text-gray-400" />
                                        </div>
                                    @endif
                                    <div class="p-4">
                                        <div class="flex justify-between items-start">
                                            <h3
                                                class="font-bold text-gray-900 dark:text-gray-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">
                                                {{ $location->name }}</h3>
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $location->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                                {{ $location->is_active ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                        </div>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 line-clamp-2">
                                            {{ $location->description }}</p>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:sticky top-20 space-y-6 self-start">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Tindakan</h2>
                        <div class="space-y-3">
                            <x-button variant="secondary" :href="route('admin.events.index')" class="w-full justify-center">
                                Kembali ke Daftar Acara
                            </x-button>
                            @if ($event->status === App\Enums\EventApprovalStatus::PENDING)
                                <x-button variant="primary" type="button"
                                    wire:click="confirmEdit({{ $event->id }})" class="w-full justify-center">
                                    Ubah Acara
                                </x-button>
                            @endif
                            <x-button variant="danger" class="w-full justify-center" type="button"
                                wire:click="confirmDelete({{ $event->id }})">
                                Hapus Acara
                            </x-button>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Informasi Tambahan</h2>
                        <div class="space-y-4 text-sm">
                            <div>
                                <p class="font-medium text-gray-500 dark:text-gray-400">Organisasi</p>
                                <p class="mt-1 font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $event->organization->name }}</p>
                            </div>
                            <div>
                                <p class="font-medium text-gray-500 dark:text-gray-400">Status Persetujuan</p>
                                <p class="mt-1">
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $event->status->color() }}">
                                        {{ $event->status->label() }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-modal name="event-modal" :show="$errors->isNotEmpty()" maxWidth="6xl" focusable>
        <form wire:submit="save" class="p-4 sm:p-6 bg-gray-50 dark:bg-gray-900">
            <!-- Header with close button -->
            <div class="flex items-start justify-between pb-4 mb-6 border-b border-gray-200 dark:border-gray-700">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{ __('Edit Event') }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        {{ __('Fill in the details below to schedule your event.') }}
                    </p>
                </div>
                <button type="button" @click="$dispatch('close')"
                    class="p-2 -m-2 text-gray-400 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 hover:text-gray-600 dark:hover:text-gray-200 transition-all">
                    <x-heroicon-s-x-mark class="h-6 w-6" />
                </button>
            </div>

            <!-- Notifikasi Error Global -->
            @if ($errors->isNotEmpty())
                <div
                    class="mb-6 p-4 bg-red-50 dark:bg-red-900/30 rounded-lg border border-red-200 dark:border-red-700/50">
                    <div class="flex items-center gap-3 text-red-600 dark:text-red-400">
                        <x-heroicon-s-exclamation-triangle class="h-6 w-6" />
                        <div>
                            <h3 class="font-semibold">{{ __('Oops, something went wrong!') }}</h3>
                            <p>{{ $errors->first() }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Konten Utama: Tata Letak Grid Dua Kolom -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <!-- Kolom Kiri: Detail Utama & Jadwal -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- Bagian Detail Acara -->
                    <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm">
                        <h3
                            class="text-lg font-semibold text-gray-900 dark:text-gray-100 border-b dark:border-gray-700 pb-3 mb-6">
                            {{ __('Event Details') }}
                        </h3>
                        <div class="space-y-6">
                            <!-- Nama Event -->
                            <div>
                                <x-input-label for="name" value="{{ __('Event Name') }}" />
                                <x-text-input wire:model="form.name" id="name" type="text"
                                    class="block w-full mt-2"
                                    placeholder="{{ __('e.g., Annual General Meeting') }}" />
                                <x-input-error :messages="$errors->get('form.name')" class="mt-2" />
                            </div>

                            <!-- Deskripsi -->
                            <div>
                                <x-input-label for="description" value="{{ __('Description') }}" />
                                <textarea wire:model="form.description" id="description" rows="4"
                                    class="block w-full mt-2 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-lg shadow-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600"
                                    placeholder="{{ __('Provide a brief and engaging description for the event...') }}"></textarea>
                                <x-input-error :messages="$errors->get('form.description')" class="mt-2" />
                            </div>

                            <!-- Kategori & Organisasi -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="event_category_id" value="{{ __('Category') }}" />
                                    <x-select wire:model="form.event_category_id" id="event_category_id"
                                        class="mt-2 w-full">
                                        <option value="">{{ __('Select Category') }}</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </x-select>
                                    <x-input-error :messages="$errors->get('form.event_category_id')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="organization_id" value="{{ __('Organization') }}" />
                                    <x-select wire:model="form.organization_id" id="organization_id"
                                        class="mt-2 w-full">
                                        <option value="">{{ __('Select Organization') }}</option>
                                        @foreach ($organizations as $org)
                                            <option value="{{ $org->id }}">{{ $org->name }}</option>
                                        @endforeach
                                    </x-select>
                                    <x-input-error :messages="$errors->get('form.organization_id')" class="mt-2" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bagian Penjadwalan -->
                    <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm">
                        <h3
                            class="text-lg font-semibold text-gray-900 dark:text-gray-100 border-b dark:border-gray-700 pb-3 mb-6">
                            {{ __('Schedule') }}
                        </h3>

                        @if ($form->recurrence_type !== App\Enums\EventRecurrenceType::CUSTOM)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="specific_datetime_start" value="Start Date & Time" />
                                    <x-text-input wire:model="form.datetime_start" id="specific_datetime_start"
                                        type="datetime-local" class="w-full mt-2" />
                                    <x-input-error :messages="$errors->get('form.datetime_start')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="specific_datetime_end" value="End Date & Time" />
                                    <x-text-input wire:model="form.datetime_end" id="specific_datetime_end"
                                        type="datetime-local" class="w-full mt-2" />
                                    <x-input-error :messages="$errors->get('form.datetime_end')" class="mt-2" />
                                </div>
                            </div>
                        @endif

                        @if ($form->recurrence_type === App\Enums\EventRecurrenceType::CUSTOM)
                            <div class="space-y-4 animate-fade-in" wire:key="custom-schedules">
                                <x-input-label value="{{ __('Custom Schedules') }}" />

                                @foreach ($form->custom_schedules as $index => $schedule)
                                    <div wire:key="schedule-{{ $index }}"
                                        class="grid grid-cols-1 md:grid-cols-[1fr_1fr_auto] items-end gap-4 p-4 border dark:border-gray-700 rounded-lg">
                                        {{-- Start Datetime --}}
                                        <div class="w-full">
                                            <x-input-label for="custom_start_{{ $index }}"
                                                value="Start Date & Time" class="text-xs" />
                                            <x-text-input
                                                wire:model="form.custom_schedules.{{ $index }}.datetime_start"
                                                id="custom_start_{{ $index }}" type="datetime-local"
                                                class="w-full mt-1" />
                                            <x-input-error :messages="$errors->get(
                                                'form.custom_schedules.' . $index . '.datetime_start',
                                            )" class="mt-2" />
                                        </div>

                                        {{-- End Datetime --}}
                                        <div class="w-full">
                                            <x-input-label for="custom_end_{{ $index }}"
                                                value="End Date & Time" class="text-xs" />
                                            <x-text-input
                                                wire:model="form.custom_schedules.{{ $index }}.datetime_end"
                                                id="custom_end_{{ $index }}" type="datetime-local"
                                                class="w-full mt-1" />
                                            <x-input-error :messages="$errors->get(
                                                'form.custom_schedules.' . $index . '.datetime_end',
                                            )" class="mt-2" />
                                        </div>

                                        {{-- Tombol Hapus --}}
                                        <div class="w-full md:w-auto">
                                            @if (count($form->custom_schedules) > 1)
                                                <x-danger-button type="button"
                                                    wire:click="removeCustomSchedule({{ $index }})"
                                                    wire:loading.attr="disabled"
                                                    class="w-full h-10 flex items-center justify-center">
                                                    <x-heroicon-o-trash class="w-5 h-5" />
                                                </x-danger-button>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach

                                <div class="pt-4">
                                    <x-secondary-button type="button" wire:click="addCustomSchedule"
                                        class="w-full sm:w-auto justify-center">
                                        <x-heroicon-s-plus class="w-5 h-5 mr-2" />
                                        {{ __('Tambah Jadwal Lain') }}
                                    </x-secondary-button>
                                </div>
                                <x-input-error :messages="$errors->get('form.custom_schedules')" class="mt-2" />
                            </div>
                        @endif
                    </div>

                </div>

                <!-- Kolom Kanan (Sidebar): Pengulangan & Lokasi -->
                <div class="lg:col-span-1 space-y-6">

                    <!-- Bagian Pola Pengulangan -->
                    <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm">
                        <h3
                            class="text-lg font-semibold text-gray-900 dark:text-gray-100 border-b dark:border-gray-700 pb-3 mb-6">
                            {{ __('Recurrence') }}
                        </h3>
                        <div class="space-y-5">
                            <x-input-label value="{{ __('Pattern') }}" />
                            <div class="grid grid-cols-2 gap-3">
                                @foreach (App\Enums\EventRecurrenceType::cases() as $type)
                                    <x-radio-card wire:model.live="form.recurrence_type" value="{{ $type->value }}"
                                        class="flex-col items-center justify-center text-center p-3 h-24">
                                        <div class="mb-1 text-indigo-600 dark:text-indigo-400">
                                            @switch($type)
                                                @case(App\Enums\EventRecurrenceType::DAILY)
                                                    <x-heroicon-o-calendar-days class="h-6 w-6" />
                                                @break

                                                @case(App\Enums\EventRecurrenceType::WEEKLY)
                                                    <x-heroicon-o-arrow-path-rounded-square class="h-6 w-6" />
                                                @break

                                                @case(App\Enums\EventRecurrenceType::BIWEEKLY)
                                                    <x-heroicon-o-arrows-right-left class="h-6 w-6" />
                                                @break

                                                @case(App\Enums\EventRecurrenceType::MONTHLY)
                                                    <x-heroicon-o-calendar class="h-6 w-6" />
                                                @break

                                                @case(App\Enums\EventRecurrenceType::CUSTOM)
                                                    <x-heroicon-o-cog-6-tooth class="h-6 w-6" />
                                                @break
                                            @endswitch
                                        </div>
                                        <span class="text-sm font-medium">{{ $type->label() }}</span>
                                    </x-radio-card>
                                @endforeach
                            </div>
                            <x-input-error :messages="$errors->get('form.recurrence_type')" class="mt-2" />

                            @if (
                                $this->form->recurrence_type &&
                                    ($this->form->recurrence_type !== App\Enums\EventRecurrenceType::DAILY &&
                                        $this->form->recurrence_type !== App\Enums\EventRecurrenceType::CUSTOM))
                                <div class="pt-5 mt-5 border-t dark:border-gray-700 space-y-5 animate-fade-in">
                                    <x-input-label value="{{ __('Recurrence Period') }}" />
                                    <div class="grid grid-cols-1 gap-6">
                                        <div>
                                            <x-input-label for="start_recurring" value="{{ __('Starts On') }}"
                                                class="text-sm" />
                                            <x-text-input wire:model="form.start_recurring" id="start_recurring"
                                                type="date" class="block w-full mt-1" />
                                            <x-input-error :messages="$errors->get('form.start_recurring')" class="mt-2" />
                                        </div>
                                        <div>
                                            <x-input-label for="end_recurring" value="{{ __('Ends On') }}"
                                                class="text-sm" />
                                            <x-text-input wire:model="form.end_recurring" id="end_recurring"
                                                type="date" class="block w-full mt-1" />
                                            <x-input-error :messages="$errors->get('form.end_recurring')" class="mt-2" />
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Bagian Lokasi -->
                    <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm">
                        <h3
                            class="text-lg font-semibold text-gray-900 dark:text-gray-100 border-b dark:border-gray-700 pb-3 mb-6">
                            {{ __('Available Locations') }}
                        </h3>
                        <div class="space-y-3 max-h-64 overflow-y-auto pr-2">
                            @forelse ($locations as $location)
                                <x-checkbox-card wire:model="form.locations" value="{{ $location->id }}"
                                    class="p-4">
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="flex-shrink-0 bg-indigo-100 dark:bg-indigo-500/20 rounded-lg w-10 h-10 flex items-center justify-center">
                                            <x-heroicon-o-map-pin
                                                class="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
                                        </div>
                                        <div class="flex-1">
                                            <h4 class="font-semibold text-gray-800 dark:text-gray-200">
                                                {{ $location->name }}</h4>
                                            <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-1">
                                                {{ $location->description }}
                                            </p>
                                        </div>
                                    </div>
                                </x-checkbox-card>
                            @empty
                                <div
                                    class="text-center py-8 px-4 border-2 border-dashed rounded-lg dark:border-gray-700">
                                    <x-heroicon-o-map-pin class="mx-auto h-10 w-10 text-gray-400" />
                                    <h4 class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-200">No
                                        Locations Found</h4>
                                    <p class="mt-1 text-sm text-gray-500">Please add locations first.</p>
                                </div>
                            @endforelse
                        </div>
                        <x-input-error :messages="$errors->get('form.locations')" class="mt-2" />
                    </div>
                </div>
            </div>

            <!-- Footer Tombol Aksi -->
            <div
                class="mt-8 pt-5 border-t border-gray-200 dark:border-gray-700 flex flex-col-reverse sm:flex-row justify-end gap-3">
                <x-secondary-button type="button" @click="$dispatch('close')" class="justify-center">
                    {{ __('Cancel') }}
                </x-secondary-button>
                <x-primary-button type="submit" class="justify-center">
                    <span wire:loading.remove wire:target="save">
                        {{ __('Update Event') }}
                    </span>
                    <span wire:loading wire:target="save" class="flex items-center gap-2">
                        <x-heroicon-s-arrow-path class="h-5 w-5 animate-spin" />
                        {{ __('Processing...') }}
                    </span>
                </x-primary-button>
            </div>
        </form>
    </x-modal>

    <x-modal name="update-event-confirmation" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="edit" class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Apakah Anda yakin ingin memperbarui acara ini?') }}
            </h2>

            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Setelah acara diperbarui, semua detail akan ikut diperbarui.') }}
            </p>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Batal') }}
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    {{ __('Perbarui') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>

    <x-modal name="delete-event-confirmation" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="delete" class="p-6">

            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Apakah Anda yakin ingin menghapus acara ini?') }}
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Tindakan ini tidak dapat dibatalkan.') }}
            </p>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Mohon konfirmasi bahwa Anda ingin menghapus acara ini dengan menekan tombol di bawah.') }}
            </p>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Batal') }}
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    {{ __('Hapus') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</div>
