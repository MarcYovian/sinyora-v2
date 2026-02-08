<div>
    <header class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 leading-tight">
            {{ __('Manajemen Lokasi') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Kelola data lokasi fisik dan ruang pertemuan.') }}
        </p>
    </header>

    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
        <div class="p-4 sm:p-6 space-y-4">
            {{-- Top Actions Bar --}}
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                @can('create location')
                    <x-button type="button" variant="primary" wire:click="create" class="items-center max-w-xs gap-2">
                        <x-heroicon-s-plus class="w-5 h-5" />
                        <span>{{ __('Tambah Lokasi') }}</span>
                    </x-button>
                @endcan

                <div class="flex-grow flex flex-col sm:flex-row items-center gap-3">
                    <div class="w-full sm:w-auto sm:flex-grow">
                        <x-text-input wire:model.live.debounce.300ms="search" type="text" class="w-full"
                            placeholder="{{ __('Cari nama lokasi...') }}" />
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
                    @forelse ($locations as $location)
                        <div wire:key="location-card-{{ $location->id }}"
                            class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden ring-1 ring-black ring-opacity-5">
                            @if ($location->image)
                                <div class="h-48 w-full overflow-hidden">
                                    <img src="{{ Storage::url($location->image) }}" alt="{{ $location->name }}"
                                        loading="lazy" class="w-full h-full object-cover">
                                </div>
                            @endif
                            <div class="p-4 border-b dark:border-gray-700">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-center gap-3">
                                        <div>
                                            <h3 class="font-bold text-lg text-gray-800 dark:text-gray-200">
                                                {{ $location->name }}
                                            </h3>
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $location->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                                {{ $location->is_active ? 'Active' : 'Inactive' }}
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
                                            @can('edit location')
                                                <x-dropdown-link wire:click="edit({{ $location->id }})">
                                                    Edit
                                                </x-dropdown-link>
                                            @endcan
                                            <div class="border-t border-gray-100 dark:border-gray-600"></div>
                                            @can('delete location')
                                                <x-dropdown-link wire:click="confirmDelete({{ $location->id }})"
                                                    class="text-red-600 dark:text-red-500">Delete
                                                </x-dropdown-link>
                                            @endcan
                                        </x-slot>
                                    </x-dropdown>
                                </div>
                            </div>
                            <div class="p-4 text-sm text-gray-600 dark:text-gray-400">
                                {{ \Illuminate\Support\Str::limit($location->description, 100) }}
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                            {{ __('Tidak ada data lokasi.') }}
                        </div>
                    @endforelse
                </div>

                {{-- Tampilan Desktop (Tabel) --}}
                <div class="hidden md:block">
                    <x-table title="Data Lokasi" :heads="$table_heads">
                        @forelse ($locations as $key => $location)
                            <tr wire:key="location-table-{{ $location->id }}"
                                class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-gray-700 dark:text-gray-300">
                                    {{ $key + $locations->firstItem() }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($location->image)
                                        <div class="h-10 w-10 rounded overflow-hidden">
                                            <img src="{{ Storage::url($location->image) }}" alt="{{ $location->name }}"
                                                loading="lazy" class="h-full w-full object-cover">
                                        </div>
                                    @else
                                        <div class="h-10 w-10 rounded bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-400">
                                            <x-heroicon-s-photo class="w-6 h-6" />
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-semibold text-gray-900 dark:text-gray-200">{{ $location->name }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-normal text-gray-600 dark:text-gray-300 max-w-xs">
                                    {{ \Illuminate\Support\Str::limit($location->description, 50) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $location->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                        {{ $location->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center justify-end space-x-1">
                                        @can('edit location')
                                            <x-button type="button" variant="warning" size="sm" class="!p-2"
                                                wire:click="edit({{ $location->id }})" title="Edit Location">
                                                <x-heroicon-o-pencil-square class="w-4 h-4" />
                                                <span class="sr-only">Edit</span>
                                            </x-button>
                                        @endcan

                                        @can('delete location')
                                            <x-button type="button" variant="danger" size="sm" class="!p-2"
                                                wire:click="confirmDelete({{ $location->id }})" title="Hapus Location">
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
            {{ $locations->links() }}
        </div>
    </div>

    {{-- Create/Edit Modal --}}
    <x-modal name="location-modal" :show="$errors->isNotEmpty()" maxWidth="lg" focusable>
        <form wire:submit="save" class="p-4 sm:p-6 bg-gray-50 dark:bg-gray-900" enctype="multipart/form-data">
            <div class="flex items-start justify-between pb-4 mb-6 border-b border-gray-200 dark:border-gray-700">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{ $editId ? __('Edit Lokasi') : __('Tambah Lokasi Baru') }}
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

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 space-y-6">
                <!-- Image Upload Section -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <x-input-label for="image" value="{{ __('Location Image') }}" />
                        @if ($form->image || $form->existingImage)
                            <button type="button" wire:click="removeImage"
                                class="text-xs text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 flex items-center gap-1 transition-colors">
                                <x-heroicon-s-trash class="h-3 w-3" />
                                {{ __('Remove') }}
                            </button>
                        @endif
                    </div>

                    <div class="flex flex-col sm:flex-row gap-6 items-start">
                        <!-- Image Preview -->
                        <div x-data="{ isUploading: false, progress: 0 }" x-on:livewire-upload-start="isUploading = true"
                            x-on:livewire-upload-finish="isUploading = false"
                            x-on:livewire-upload-error="isUploading = false"
                            x-on:livewire-upload-progress="progress = $event.detail.progress" class="w-full sm:w-1/3">

                            <div class="relative group">
                                <div
                                    class="w-full h-32 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 flex items-center justify-center overflow-hidden transition-all duration-300 hover:border-indigo-500 dark:hover:border-indigo-400 bg-gray-50 dark:bg-gray-900/50">
                                    @if ($form->image)
                                        <img src="{{ $form->image->temporaryUrl() }}" alt="Preview" class="h-full w-full object-cover">
                                    @elseif ($form->existingImage)
                                        <img src="{{ Storage::url($form->existingImage) }}" alt="Current" class="h-full w-full object-cover">
                                    @else
                                        <div class="text-center p-2">
                                            <x-heroicon-s-photo class="mx-auto h-8 w-8 text-gray-400" />
                                            <span class="text-xs text-gray-500 dark:text-gray-400 mt-1 block">Click to upload</span>
                                        </div>
                                    @endif
                                </div>

                                <!-- Upload Progress -->
                                <div x-show="isUploading"
                                    class="absolute inset-x-0 bottom-0 bg-gray-200 dark:bg-gray-700 h-1">
                                    <div class="bg-indigo-600 h-full transition-all duration-300 ease-out"
                                        x-bind:style="`width: ${progress}%`"></div>
                                </div>

                                <label for="image-upload" class="absolute inset-0 cursor-pointer"></label>
                                <input id="image-upload" type="file" wire:model="form.image"
                                    accept="image/jpeg,image/png,image/webp" class="hidden">
                            </div>
                            <x-input-error :messages="$errors->get('form.image')" class="mt-2" />
                        </div>

                        <!-- Main Form Fields -->
                        <div class="flex-1 w-full space-y-4">
                            <!-- Name Field -->
                            <div>
                                <x-input-label for="name" value="{{ __('Name') }}" />
                                <x-text-input wire:model="form.name" id="name" type="text"
                                    class="mt-1 block w-full" placeholder="{{ __('e.g. Main Hall') }}" />
                                <x-input-error :messages="$errors->get('form.name')" class="mt-1" />
                            </div>

                            <!-- Status Field -->
                            <div>
                                <x-input-label for="is_active" value="{{ __('Status') }}" />
                                <select wire:model="form.is_active" id="is_active"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:focus:border-indigo-600 dark:focus:ring-indigo-600 py-2 pl-3 pr-10">
                                    <option value='1'>{{ __('Active') }}</option>
                                    <option value='0'>{{ __('Inactive') }}</option>
                                </select>
                                <x-input-error :messages="$errors->get('form.is_active')" class="mt-1" />
                            </div>
                        </div>
                    </div>

                    <!-- Description Field (Full Width) -->
                    <div class="mt-4">
                        <x-input-label for="description" value="{{ __('Description') }}" />
                        <textarea wire:model="form.description" id="description" rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:focus:border-indigo-600 dark:focus:ring-indigo-600"
                            placeholder="{{ __('Optional description...') }}"></textarea>
                        <x-input-error :messages="$errors->get('form.description')" class="mt-1" />
                    </div>
                </div>
            </div>

            <div class="mt-8 pt-5 border-t border-gray-200 dark:border-gray-700 flex flex-col-reverse sm:flex-row justify-end gap-3">
                <x-secondary-button type="button" @click="$dispatch('close')" class="justify-center">
                    {{ __('Cancel') }}
                </x-secondary-button>
                <x-primary-button type="submit" class="justify-center">
                    <span wire:loading.remove wire:target="save">
                        {{ $editId ? __('Update Location') : __('Create Location') }}
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
    <x-modal name="delete-location-confirmation" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="delete" class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Are you sure you want to delete this location?') }}
            </h2>

            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('This action cannot be undone.') }}
            </p>

            <div class="mt-4 p-4 bg-gray-100 dark:bg-gray-800 rounded-lg">
                <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Name') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $form->name }}</dd>
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
