<div>
    <header class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 leading-tight">
            {{ __('Manajemen Jadwal Misa Rutin') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Kelola jadwal misa rutin mingguan yang akan ditampilkan di halaman utama.
        </p>
    </header>

    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
        <div class="p-4 sm:p-6 space-y-4">
            <div class="flex justify-end">
                <x-button type="button" variant="primary" wire:click="clear" class="items-center gap-2">
                    <x-heroicon-s-plus class="w-5 h-5" />
                    <span>{{ __('Tambah Jadwal') }}</span>
                </x-button>
            </div>

            {{-- Tampilan Mobile (Card) --}}
            <div class="grid grid-cols-1 gap-4 md:hidden">
                @forelse ($schedules as $schedule)
                    <div wire:key="schedule-card-{{ $schedule->id }}"
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden ring-1 ring-black ring-opacity-5">
                        <div class="p-4">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h3 class="font-bold text-lg text-gray-800 dark:text-gray-200">
                                        {{ $schedule->label }}</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $schedule->description }}</p>
                                </div>
                                <x-dropdown align="right" width="48">
                                    <x-slot name="trigger">
                                        <button
                                            class="p-1 text-gray-500 dark:text-gray-400 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none">
                                            <x-heroicon-s-ellipsis-vertical class="w-5 h-5" />
                                        </button>
                                    </x-slot>
                                    <x-slot name="content">
                                        <x-dropdown-link wire:click="edit({{ $schedule->id }})">
                                            Edit
                                        </x-dropdown-link>
                                        <div class="border-t border-gray-100 dark:border-gray-600"></div>
                                        <x-dropdown-link wire:click="confirmDelete({{ $schedule->id }})"
                                            class="text-red-600 dark:text-red-500">
                                            Delete
                                        </x-dropdown-link>
                                    </x-slot>
                                </x-dropdown>
                            </div>
                            <div
                                class="mt-4 flex items-center text-gray-600 dark:text-gray-300 border-t pt-4 dark:border-gray-700">
                                <x-heroicon-s-calendar-days class="w-5 h-5 mr-2" />
                                <span class="font-semibold">{{ $schedule->dayName }}</span>
                                <span class="mx-2 text-gray-400">|</span>
                                <x-heroicon-s-clock class="w-5 h-5 mr-2" />
                                <span>{{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} WIB</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                        {{ __('Tidak ada jadwal misa rutin.') }}
                    </div>
                @endforelse
            </div>

            {{-- Tampilan Desktop (Tabel) --}}
            <div class="hidden md:block">
                <x-table title="Jadwal Misa" :heads="$table_heads">
                    @forelse ($schedules as $schedule)
                        <tr wire:key="schedule-table-{{ $schedule->id }}"
                            class="hover:bg-gray-50 dark:hover:bg-white/5">
                            <td class="px-6 py-4 text-gray-800 dark:text-gray-200 font-medium">{{ $schedule->label }}
                            </td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-300">{{ $schedule->dayName }}</td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                                {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} WIB</td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-300">{{ $schedule->description }}</td>
                            <td class="px-6 py-4 text-right">
                                <x-button wire:click="edit({{ $schedule->id }})" variant="warning"
                                    size="sm">Edit</x-button>
                                <x-button wire:click="confirmDelete({{ $schedule->id }})" variant="danger"
                                    size="sm">Hapus</x-button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                {{ __('Tidak ada data yang tersedia.') }}
                            </td>
                        </tr>
                    @endforelse
                </x-table>
            </div>
        </div>
    </div>

    <x-modal name="mass-schedule-modal" :show="$errors->isNotEmpty()" maxWidth="2xl" focusable>
        <form wire:submit="save" class="p-4 sm:p-6">
            <div class="flex items-start justify-between pb-4 mb-6 border-b border-gray-200 dark:border-gray-700">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{ $form->massSchedule ? 'Edit' : 'Tambah' }} Jadwal Misa
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Isi detail jadwal misa rutin di bawah ini.
                    </p>
                </div>
                <button type="button" @click="$dispatch('close')"
                    class="p-2 -m-2 text-gray-400 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700">
                    <x-heroicon-s-x-mark class="h-6 w-6" />
                </button>
            </div>

            <div class="space-y-6">
                <div>
                    <x-input-label for="label" value="Label Jadwal (cth: Misa Pagi)" />
                    <x-text-input wire:model="form.label" id="label" type="text" class="block w-full mt-2"
                        placeholder="Misa Pagi" />
                    <x-input-error :messages="$errors->get('form.label')" class="mt-2" />
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="day_of_week" value="Hari" />
                        <x-select wire:model="form.day_of_week" id="day_of_week" class="mt-2 w-full">
                            <option value="0">Minggu</option>
                            <option value="1">Senin</option>
                            <option value="2">Selasa</option>
                            <option value="3">Rabu</option>
                            <option value="4">Kamis</option>
                            <option value="5">Jumat</option>
                            <option value="6">Sabtu</option>
                        </x-select>
                        <x-input-error :messages="$errors->get('form.day_of_week')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="start_time" value="Waktu Mulai" />
                        <x-text-input wire:model="form.start_time" id="start_time" type="time"
                            class="block w-full mt-2" />
                        <x-input-error :messages="$errors->get('form.start_time')" class="mt-2" />
                    </div>
                </div>
                <div>
                    <x-input-label for="description" value="Deskripsi (Opsional)" />
                    <textarea wire:model="form.description" id="description" rows="3"
                        class="block w-full mt-2 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600"
                        placeholder="Misa Umum, Misa Anak, dll..."></textarea>
                    <x-input-error :messages="$errors->get('form.description')" class="mt-2" />
                </div>
            </div>

            <div class="mt-8 pt-5 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                <x-button type="button" variant="secondary" @click="$dispatch('close')">
                    Batal
                </x-button>
                <x-button type="submit" variant="primary">
                    Simpan
                </x-button>
            </div>
        </form>
    </x-modal>

    <x-modal name="delete-schedule-confirmation" focusable>
        <div class="p-6">
            <div class="flex items-start gap-4">
                <div
                    class="flex-shrink-0 bg-red-100 dark:bg-red-900/30 rounded-full w-12 h-12 flex items-center justify-center">
                    <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-red-600 dark:text-red-400" />
                </div>
                <div>
                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        Hapus Jadwal Misa?
                    </h2>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Apakah Anda yakin? Tindakan ini tidak dapat dibatalkan dan akan menghapus jadwal secara
                        permanen.
                    </p>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-button type="button" variant="secondary" x-on:click="$dispatch('close')">
                    Batal
                </x-button>
                <x-button type="button" wire:click="delete" variant="danger">
                    Ya, Hapus
                </x-button>
            </div>
        </div>
    </x-modal>
</div>
