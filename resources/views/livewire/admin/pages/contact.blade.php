<div>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Pesan Kontak') }}
        </h2>
    </header>

    <div class="mt-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 my-4 px-4 md:px-0 md:flex md:justify-between gap-4">
            {{-- Filter Status --}}
            <div class="flex items-center gap-2">
                <select wire:model.live="statusFilter"
                    class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm text-sm">
                    <option value="">Semua Status</option>
                    <option value="new">Baru</option>
                    <option value="read">Dibaca</option>
                    <option value="replied">Dibalas</option>
                </select>
            </div>

            {{-- Search --}}
            <div class="w-full md:w-1/2">
                <x-search placeholder="Cari berdasarkan nama, email, atau pesan..." />
            </div>
        </div>

        <div class="p-6 text-gray-900 dark:text-gray-100">
            {{-- Desktop Table --}}
            <div class="hidden md:block">
                <x-table title="Data Pesan Kontak" :heads="$table_heads">
                    @forelse ($contacts as $key => $contact)
                        <tr wire:key="contact-{{ $contact->id }}"
                            class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                                {{ $key + $contacts->firstItem() }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm font-medium">
                                {{ $contact->name }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                                <a href="mailto:{{ $contact->email }}" class="text-blue-600 hover:underline">
                                    {{ $contact->email }}
                                </a>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                                {{ $contact->phone ?? '-' }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                @switch($contact->status)
                                    @case('new')
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                            <x-heroicon-s-envelope class="w-3 h-3 mr-1" />
                                            Baru
                                        </span>
                                    @break

                                    @case('read')
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                            <x-heroicon-s-envelope-open class="w-3 h-3 mr-1" />
                                            Dibaca
                                        </span>
                                    @break

                                    @case('replied')
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            <x-heroicon-s-check-circle class="w-3 h-3 mr-1" />
                                            Dibalas
                                        </span>
                                    @break
                                @endswitch
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                                {{ $contact->created_at->format('d M Y, H:i') }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <div class="flex items-center gap-2">
                                    <x-button size="sm" variant="info" type="button"
                                        wire:click="show({{ $contact->id }})" title="Lihat Detail">
                                        <x-heroicon-s-eye class="w-4 h-4" />
                                    </x-button>
                                    @if ($contact->status !== 'replied')
                                        <x-button size="sm" variant="success" type="button"
                                            wire:click="updateStatus({{ $contact->id }}, 'replied')"
                                            title="Tandai Dibalas">
                                            <x-heroicon-s-check class="w-4 h-4" />
                                        </x-button>
                                    @endif
                                    <x-button size="sm" variant="danger" type="button"
                                        wire:click="confirmDelete({{ $contact->id }})" title="Hapus">
                                        <x-heroicon-s-trash class="w-4 h-4" />
                                    </x-button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="bg-white dark:bg-gray-800">
                            <td colspan="{{ count($table_heads) }}"
                                class="whitespace-nowrap px-6 py-4 text-rose-700 dark:text-rose-400 text-sm text-center">
                                {{ __('Tidak ada pesan kontak') }}
                            </td>
                        </tr>
                    @endforelse
                </x-table>
            </div>

            {{-- Mobile Cards --}}
            <div class="md:hidden space-y-4">
                @forelse ($contacts as $contact)
                    <div wire:key="contact-card-{{ $contact->id }}"
                        class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-gray-100">{{ $contact->name }}</h3>
                                <a href="mailto:{{ $contact->email }}"
                                    class="text-sm text-blue-600 hover:underline">{{ $contact->email }}</a>
                            </div>
                            @switch($contact->status)
                                @case('new')
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                        Baru
                                    </span>
                                @break

                                @case('read')
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                        Dibaca
                                    </span>
                                @break

                                @case('replied')
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        Dibalas
                                    </span>
                                @break
                            @endswitch
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3 line-clamp-2">{{ $contact->message }}
                        </p>
                        <div class="flex justify-between items-center">
                            <span
                                class="text-xs text-gray-500 dark:text-gray-400">{{ $contact->created_at->format('d M Y, H:i') }}</span>
                            <div class="flex items-center gap-2">
                                <x-button size="sm" variant="info" type="button"
                                    wire:click="show({{ $contact->id }})">
                                    <x-heroicon-s-eye class="w-4 h-4" />
                                </x-button>
                                <x-button size="sm" variant="danger" type="button"
                                    wire:click="confirmDelete({{ $contact->id }})">
                                    <x-heroicon-s-trash class="w-4 h-4" />
                                </x-button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                        Tidak ada pesan kontak
                    </div>
                @endforelse
            </div>
        </div>

        <div class="px-6 py-4">
            {{ $contacts->links() }}
        </div>
    </div>

    {{-- View Contact Modal --}}
    <x-modal name="view-contact-modal" focusable>
        <div class="p-6">
            @if ($selectedContact)
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                    Detail Pesan Kontak
                </h2>

                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Nama</label>
                        <p class="text-gray-900 dark:text-gray-100 mt-1">{{ $selectedContact->name }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</label>
                        <p class="mt-1">
                            <a href="mailto:{{ $selectedContact->email }}"
                                class="text-blue-600 hover:underline">{{ $selectedContact->email }}</a>
                        </p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Telepon</label>
                        <p class="text-gray-900 dark:text-gray-100 mt-1">{{ $selectedContact->phone ?? '-' }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Tanggal</label>
                        <p class="text-gray-900 dark:text-gray-100 mt-1">
                            {{ $selectedContact->created_at->format('d M Y, H:i') }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</label>
                        <div class="mt-2 flex gap-2">
                            <x-button size="sm"
                                variant="{{ $selectedContact->status === 'new' ? 'danger' : 'secondary' }}"
                                type="button" wire:click="updateStatus({{ $selectedContact->id }}, 'new')">
                                Baru
                            </x-button>
                            <x-button size="sm"
                                variant="{{ $selectedContact->status === 'read' ? 'warning' : 'secondary' }}"
                                type="button" wire:click="updateStatus({{ $selectedContact->id }}, 'read')">
                                Dibaca
                            </x-button>
                            <x-button size="sm"
                                variant="{{ $selectedContact->status === 'replied' ? 'success' : 'secondary' }}"
                                type="button" wire:click="updateStatus({{ $selectedContact->id }}, 'replied')">
                                Dibalas
                            </x-button>
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Pesan</label>
                        <div
                            class="mt-2 p-4 bg-gray-100 dark:bg-gray-700 rounded-lg text-gray-900 dark:text-gray-100">
                            {{ $selectedContact->message }}
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <x-secondary-button type="button" @click="$dispatch('close')">
                        {{ __('Tutup') }}
                    </x-secondary-button>
                    <x-button variant="primary"
                        onclick="window.location.href='mailto:{{ $selectedContact->email }}?subject=Re: Pesan dari Website'">
                        <x-heroicon-s-envelope class="w-4 h-4 mr-2" />
                        Balas via Email
                    </x-button>
                </div>
            @endif
        </div>
    </x-modal>

    {{-- Delete Confirmation Modal --}}
    <x-modal name="delete-contact-confirmation" focusable>
        <form wire:submit="delete" class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Hapus Pesan Kontak?') }}
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Apakah Anda yakin ingin menghapus pesan ini? Tindakan ini tidak dapat dibatalkan.') }}
            </p>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button type="button" @click="$dispatch('close')">
                    {{ __('Batal') }}
                </x-secondary-button>
                <x-danger-button type="submit">
                    {{ __('Hapus') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</div>
