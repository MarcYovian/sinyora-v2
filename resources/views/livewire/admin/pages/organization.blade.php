<div>
    <header class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 leading-tight">
            {{ __('Manajemen Organisasi') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Kelola struktur organisasi, wilayah, dan unit pelayanan.') }}
        </p>
    </header>

    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
        <div class="p-4 sm:p-6 space-y-4">
            {{-- Top Actions Bar --}}
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                @can('create organization')
                    <x-button type="button" variant="primary" wire:click="create" class="items-center max-w-xs gap-2">
                        <x-heroicon-s-plus class="w-5 h-5" />
                        <span>{{ __('Tambah Organisasi') }}</span>
                    </x-button>
                @endcan

                <div class="flex-grow flex flex-col sm:flex-row items-center gap-3">
                    <div class="w-full sm:w-auto sm:flex-grow">
                        <x-text-input wire:model.live.debounce.300ms="search" type="text" class="w-full"
                            placeholder="{{ __('Cari nama, kode...') }}" />
                    </div>
                    @if ($search)
                        <x-button type="button" wire:click="resetFilters" variant="secondary" class="w-full sm:w-auto">
                            {{ __('Reset') }}
                        </x-button>
                    @endif
                </div>
            </div>

            {{-- Indikator Loading --}}
            <div wire:loading.flex wire:target="search" class="items-center justify-center w-full py-4">
                <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
                    <x-heroicon-s-arrow-path class="h-5 w-5 animate-spin" />
                    <span>Memuat data...</span>
                </div>
            </div>

            <div wire:loading.remove wire:target="search">
                {{-- Tampilan Mobile (Card) --}}
                <div class="grid grid-cols-1 gap-4 md:hidden">
                    @forelse ($organizations as $organization)
                        <div wire:key="org-card-{{ $organization->id }}"
                            class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden ring-1 ring-black ring-opacity-5">
                            <div class="p-4 border-b dark:border-gray-700">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-center gap-3">
                                        <div>
                                            <h3 class="font-bold text-lg text-gray-800 dark:text-gray-200">
                                                {{ $organization->name }}
                                            </h3>
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $organization->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                                {{ $organization->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </div>
                                    </div>

                                    <x-dropdown align="right" width="48">
                                        <x-slot name="trigger">
                                            <button
                                                class="p-1 text-gray-500 dark:text-gray-400 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500">
                                                <x-heroicon-s-ellipsis-vertical class="w-5 h-5" />
                                            </button>
                                        </x-slot>
                                        <x-slot name="content">
                                            @can('edit organization')
                                                <x-dropdown-link wire:click="edit({{ $organization->id }})">
                                                    Edit
                                                </x-dropdown-link>
                                            @endcan
                                            <div class="border-t border-gray-100 dark:border-gray-600"></div>
                                            @can('delete organization')
                                                <x-dropdown-link wire:click="confirmDelete({{ $organization->id }})"
                                                    class="text-red-600 dark:text-red-500">Delete
                                                </x-dropdown-link>
                                            @endcan
                                        </x-slot>
                                    </x-dropdown>
                                </div>
                            </div>
                            <div class="p-4 space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">{{ __('Code') }}:</span>
                                    <span
                                        class="font-mono bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded text-gray-600 dark:text-gray-300">{{ $organization->code }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">{{ __('Description') }}:</span>
                                    <span
                                        class="font-medium text-gray-700 dark:text-gray-300">{{ \Illuminate\Support\Str::limit($organization->description, 30) }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                            {{ __('Tidak ada data organisasi.') }}
                        </div>
                    @endforelse
                </div>

                {{-- Tampilan Desktop (Tabel) --}}
                <div class="hidden md:block">
                    <x-table title="Data Organisasi" :heads="$table_heads">
                        @forelse ($organizations as $key => $organization)
                            <tr wire:key="org-table-{{ $organization->id }}"
                                class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-gray-700 dark:text-gray-300">
                                    {{ $key + $organizations->firstItem() }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-semibold text-gray-900 dark:text-gray-200">{{ $organization->name }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-300 font-mono text-xs">
                                    {{ $organization->code }}
                                </td>
                                <td class="px-6 py-4 whitespace-normal text-gray-600 dark:text-gray-300 max-w-xs">
                                    {{ $organization->description }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $organization->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                        {{ $organization->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center justify-end space-x-1">
                                        @can('edit organization')
                                            <x-button type="button" variant="warning" size="sm" class="!p-2"
                                                wire:click="edit({{ $organization->id }})" title="Edit Organization">
                                                <x-heroicon-o-pencil-square class="w-4 h-4" />
                                                <span class="sr-only">Edit</span>
                                            </x-button>
                                        @endcan

                                        @can('delete organization')
                                            <x-button type="button" variant="danger" size="sm" class="!p-2"
                                                wire:click="confirmDelete({{ $organization->id }})" title="Hapus Organization">
                                                <x-heroicon-o-trash class="w-4 h-4" />
                                                <span class="sr-only">Delete</span>
                                            </x-button>
                                        @endcan
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
            {{ $organizations->links() }}
        </div>
    </div>

    {{-- Create/Edit Modal --}}
    <x-modal name="organization-modal" :show="$errors->isNotEmpty()" maxWidth="lg" focusable>
        <form wire:submit="save" class="p-4 sm:p-6 bg-gray-50 dark:bg-gray-900">
            <div class="flex items-start justify-between pb-4 mb-6 border-b border-gray-200 dark:border-gray-700">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{ $editId ? __('Edit Organisasi') : __('Tambah Organisasi Baru') }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        {{ __('Silakan lengkapi form di bawah ini.') }}
                    </p>
                </div>
                <button type="button" @click="$dispatch('close')"
                    class="p-2 -m-2 text-gray-400 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 hover:text-gray-600 dark:hover:text-gray-200 transition-all">
                    <x-heroicon-s-x-mark class="h-6 w-6" />
                </button>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 space-y-5">
                <!-- Name Field -->
                <div>
                    <x-input-label for="name" value="{{ __('Name') }}" />
                    <x-text-input wire:model="form.name" id="name" name="name" type="text"
                        class="mt-1 block w-full" placeholder="{{ __('e.g. Wilayah D, OMK D') }}" />
                    <x-input-error :messages="$errors->get('form.name')" class="mt-2" />
                </div>

                <!-- Code Field -->
                <div>
                    <x-input-label for="code" value="{{ __('Code') }}" />
                    <x-text-input wire:model="form.code" id="code" name="code" type="text"
                        class="mt-1 block w-full" placeholder="{{ __('e.g. WD, OMK') }}" />
                    <x-input-error :messages="$errors->get('form.code')" class="mt-2" />
                </div>

                <!-- Description Field -->
                <div>
                    <x-input-label for="description" value="{{ __('Description') }}" />
                    <x-text-input wire:model="form.description" id="description" name="description" type="text"
                        class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('form.description')" class="mt-2" />
                </div>

                <!-- Status Field -->
                <div>
                    <x-input-label for="is_active" value="{{ __('Status') }}" />
                    <div class="mt-1 relative">
                        <select wire:model="form.is_active" id="is_active" name="is_active"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:focus:border-indigo-600 dark:focus:ring-indigo-600 py-2 pl-3 pr-10">
                            <option value='1'>{{ __('Active') }}</option>
                            <option value='0'>{{ __('Inactive') }}</option>
                        </select>
                    </div>
                    <x-input-error :messages="$errors->get('form.is_active')" class="mt-2" />
                </div>
            </div>

            <div class="mt-8 pt-5 border-t border-gray-200 dark:border-gray-700 flex flex-col-reverse sm:flex-row justify-end gap-3">
                <x-secondary-button type="button" @click="$dispatch('close')" class="justify-center">
                    {{ __('Cancel') }}
                </x-secondary-button>
                <x-primary-button type="submit" class="justify-center">
                    <span wire:loading.remove wire:target="save">
                        {{ $editId ? __('Update Organization') : __('Create Organization') }}
                    </span>
                    <span wire:loading wire:target="save" class="flex items-center gap-2">
                        <x-heroicon-s-arrow-path class="h-4 w-4 animate-spin" />
                        <span>{{ __('Saving...') }}</span>
                    </span>
                </x-primary-button>
            </div>
        </form>
    </x-modal>

    {{-- Delete Confirmation Modal --}}
    <x-modal name="delete-organization-confirmation" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="delete" class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Are you sure you want to delete this organization?') }}
            </h2>

            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('This action cannot be undone.') }}
            </p>

            <div class="mt-4 p-4 bg-gray-100 dark:bg-gray-800 rounded-lg">
                <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Name') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $form->name }}</dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Code') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $form->code }}</dd>
                    </div>
                </dl>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button type="button" x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button>
                    {{ __('Delete') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</div>
