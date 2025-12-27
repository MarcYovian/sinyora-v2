<div>
    <header class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 leading-tight">
            {{ __('Manajemen Kegiatan Kapel') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Kelola semua kegiatan kapel yang masuk.
        </p>
    </header>

    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
        <div class="p-4 sm:p-6 space-y-4">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                <x-button type="button" variant="primary" wire:click="create" class="items-center max-w-xs gap-2">
                    <x-heroicon-s-plus class="w-5 h-5" />
                    <span>{{ __('Tambah Kegiatan') }}</span>
                </x-button>

                <div class="flex-grow flex flex-col sm:flex-row items-center gap-3">
                    <div class="w-full sm:w-auto sm:flex-grow">
                        <x-text-input wire:model.live.debounce.300ms="search" type="text" class="w-full"
                            placeholder="{{ __('Cari nama kegiatan...') }}" />
                    </div>
                    <div class="w-full sm:w-48">
                        <x-select wire:model.live="filterStatus" class="w-full">
                            <option value="">{{ __('Semua Status') }}</option>
                            @foreach (App\Enums\EventApprovalStatus::cases() as $status)
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
                    @forelse ($events as $event)
                        <div wire:key="event-card-{{ $event->id }}"
                            class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden ring-1 ring-black ring-opacity-5">
                            <div class="p-4 border-b dark:border-gray-700">
                                <div class="flex items-start justify-between">
                                    <h3 class="font-bold text-lg text-gray-800 dark:text-gray-200">{{ $event->name }}
                                    </h3>
                                    {{-- Dropdown di mobile relatif terhadap card, jadi tidak ada masalah z-index --}}
                                    <x-dropdown align="right" width="48">
                                        <x-slot name="trigger">
                                            <button
                                                class="p-1 text-gray-500 dark:text-gray-400 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500">
                                                <x-heroicon-s-ellipsis-vertical class="w-5 h-5" />
                                            </button>
                                        </x-slot>
                                        <x-slot name="content">
                                            <x-dropdown-link href="{{ route('admin.events.show', $event) }}">
                                                Detail
                                            </x-dropdown-link>
                                            <x-dropdown-link wire:click="edit({{ $event->id }})">
                                                Edit
                                            </x-dropdown-link>
                                            @if ($event->status === App\Enums\EventApprovalStatus::PENDING)
                                                <x-dropdown-link wire:click="confirmApprove({{ $event->id }})">
                                                    Approve
                                                </x-dropdown-link>
                                                <x-dropdown-link wire:click="confirmReject({{ $event->id }})">
                                                    Reject
                                                </x-dropdown-link>
                                            @endif
                                            <div class="border-t border-gray-100 dark:border-gray-600"></div>
                                            <x-dropdown-link wire:click="confirmDelete({{ $event->id }})"
                                                class="text-red-600 dark:text-red-500">Delete
                                            </x-dropdown-link>
                                        </x-slot>
                                    </x-dropdown>
                                </div>
                                <x-status-badge :status="$event->status" class="mt-2" />
                            </div>
                            <div class="p-4 space-y-3 text-sm">
                                <div class="flex items-center text-gray-600 dark:text-gray-300">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                    <span>
                                        {{ Carbon\Carbon::parse($event->start_recurring)->translatedFormat('d M Y') }}
                                        -
                                        {{ Carbon\Carbon::parse($event->end_recurring)->translatedFormat('d M Y') }}
                                    </span>
                                </div>
                                <div class="flex items-center text-gray-600 dark:text-gray-300">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2 0h2v2h-2V9zm2-4h-2v2h2V5z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span>{{ $event->organization->name ?? '-' }}</span>
                                </div>
                                {{-- Kategori --}}
                                <div class="flex items-center text-gray-600 dark:text-gray-300">
                                    <div class="w-3 h-3 rounded-full mr-2"
                                        style="background-color: {{ $event->eventCategory->color ?? '#9ca3af' }}">
                                    </div>
                                    <span>{{ $event->eventCategory->name ?? '-' }}</span>
                                </div>
                                {{-- Lokasi --}}
                                <div class="flex items-start text-gray-600 dark:text-gray-300">
                                    <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span>{{ $event->locations->pluck('name')->implode(', ') }}</span>
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
                    <x-table title="Data Kegiatan" :heads="$table_heads">
                        @forelse ($events as $key => $event)
                            <tr wire:key="event-table-{{ $event->id }}"
                                class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors duration-150 isolate">
                                <td class="px-6 py-4 whitespace-nowrap text-gray-700 dark:text-gray-300">
                                    {{ $key + $events->firstItem() }}
                                </td>
                                {{-- Event --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-semibold text-gray-900 dark:text-gray-200">{{ $event->name }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $event->organization->name ?? '-' }}
                                    </div>
                                </td>
                                {{-- Period --}}
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-300">
                                    <div>{{ Carbon\Carbon::parse($event->start_recurring)->translatedFormat('d M Y') }}
                                    </div>
                                    <div class="text-xs text-gray-400">sampai</div>
                                    <div>{{ Carbon\Carbon::parse($event->end_recurring)->translatedFormat('d M Y') }}
                                    </div>
                                </td>
                                {{-- Category --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center gap-x-1.5 rounded-md px-2 py-1 text-xs font-medium"
                                        style="background-color: {{ $event->eventCategory->color ?? '#9ca3af' }}20; color: {{ $event->eventCategory->color ?? '#9ca3af' }}">
                                        <svg class="h-1.5 w-1.5" viewBox="0 0 6 6" aria-hidden="true"
                                            fill="currentColor">
                                            <circle cx="3" cy="3" r="3" />
                                        </svg>
                                        {{ $event->eventCategory->name ?? '-' }}
                                    </span>
                                </td>
                                {{-- Locations --}}
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-300" style="min-width: 180px;">
                                    @php
                                        $locationsName = $event->locations->pluck('name');
                                        $locationCount = $locationsName->count();
                                    @endphp
                                    @if ($locationCount > 0)
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium text-gray-800 dark:text-gray-200 truncate"
                                                title="{{ $locationsName->first() }}">
                                                {{ $locationsName->first() }}
                                            </span>
                                            @if ($locationCount > 1)
                                                <span
                                                    class="bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 text-xs font-semibold px-2 py-0.5 rounded-full"
                                                    title="{{ $locationsName->slice(1)->implode(', ') }}">
                                                    +{{ $locationCount - 1 }}
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        -
                                    @endif
                                </td>
                                {{-- Status --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <x-status-badge :status="$event->status" />
                                </td>
                                {{-- Actions --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center justify-end space-x-1">
                                        {{-- Detail Button - Blue/Info --}}
                                        <x-button tag="a" href="{{ route('admin.events.show', $event) }}"
                                            variant="info" size="sm" class="!p-2" title="Lihat Detail">
                                            <x-heroicon-o-eye class="w-4 h-4" />
                                            <span class="sr-only">Detail</span>
                                        </x-button>

                                        {{-- Edit Button - Amber/Warning --}}
                                        @if ($event->status !== App\Enums\EventApprovalStatus::APPROVED && $event->status !== App\Enums\EventApprovalStatus::REJECTED)
                                            <x-button type="button" variant="warning" size="sm" class="!p-2"
                                                wire:click="edit({{ $event->id }})" title="Edit Kegiatan">
                                                <x-heroicon-o-pencil-square class="w-4 h-4" />
                                                <span class="sr-only">Edit</span>
                                            </x-button>
                                        @endif

                                        {{-- Approve/Reject Buttons (only for pending) --}}
                                        @if ($event->status === App\Enums\EventApprovalStatus::PENDING)
                                            <x-button type="button" variant="success" size="sm" class="!p-2"
                                                wire:click="confirmApprove({{ $event->id }})" title="Setujui Kegiatan">
                                                <x-heroicon-o-check-circle class="w-4 h-4" />
                                                <span class="sr-only">Approve</span>
                                            </x-button>
                                            <x-button type="button" variant="secondary" size="sm" class="!p-2 !bg-orange-100 !text-orange-600 hover:!bg-orange-200 dark:!bg-orange-900/30 dark:!text-orange-400"
                                                wire:click="confirmReject({{ $event->id }})" title="Tolak Kegiatan">
                                                <x-heroicon-o-x-circle class="w-4 h-4" />
                                                <span class="sr-only">Reject</span>
                                            </x-button>
                                        @endif

                                        {{-- Delete Button - Red/Danger --}}
                                        <x-button type="button" variant="danger" size="sm" class="!p-2"
                                            wire:click="confirmDelete({{ $event->id }})" title="Hapus Kegiatan">
                                            <x-heroicon-o-trash class="w-4 h-4" />
                                            <span class="sr-only">Delete</span>
                                        </x-button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($table_heads) }}"
                                    class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    {{ __('Tidak ada data yang tersedia.') }}
                                </td>
                            </tr>
                        @endforelse
                    </x-table>
                </div>
            </div>
        </div>

        <div class="px-4 md:px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $events->links() }}
        </div>
    </div>

    <x-modal name="event-modal" :show="$errors->isNotEmpty()" maxWidth="6xl" focusable>
        <form wire:submit="save" class="p-4 sm:p-6 bg-gray-50 dark:bg-gray-900">
            <!-- Header with close button -->
            <div class="flex items-start justify-between pb-4 mb-6 border-b border-gray-200 dark:border-gray-700">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{ $editId ? __('Edit Event') : __('Create New Event') }}
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
                        {{ $editId ? __('Update Event') : __('Create Event') }}
                    </span>
                    <span wire:loading wire:target="save" class="flex items-center gap-2">
                        <x-heroicon-s-arrow-path class="h-5 w-5 animate-spin" />
                        {{ __('Processing...') }}
                    </span>
                </x-primary-button>
            </div>
        </form>
    </x-modal>

    <x-modal name="delete-event-confirmation" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="delete" class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Are you sure you want to delete this event?') }}
            </h2>

            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('This action cannot be undone.') }}
            </p>

            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Please confirm that you want to delete this event by clicking the button below.') }}
            </p>

            <div class="mt-6 flex justify-end">
                <x-secondary-button type="button" x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    {{ __('Delete') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>

    <x-modal name="approve-event-confirmation" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="approve" class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Are you sure you want to approve this event?') }}
            </h2>

            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('This action cannot be undone.') }}
            </p>

            <div class="mt-6 flex justify-end">
                <x-secondary-button type="button" x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    {{ __('Approve') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>

    <x-modal name="reject-event-confirmation" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="reject" class="p-6">

            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Are you sure you want to reject this event?') }}
            </h2>

            <div class="mt-6">
                <x-input-label for="rejection_reason" value="{{ __('Alasan Penolakan') }}" class="sr-only" />

                <textarea wire:model="form.rejection_reason" id="rejection_reason" rows="4"
                    class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300"
                    placeholder="{{ __('Reason for rejection') }}"></textarea>

                <x-input-error :messages="$errors->get('form.rejection_reason')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button type="button" x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    {{ __('Reject') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
    <style>
        @keyframes fade-in {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fade-in 0.3s ease-out forwards;
        }
    </style>
</div>
