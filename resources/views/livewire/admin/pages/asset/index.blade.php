<div>
    <header class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 leading-tight">
            {{ __('Manajemen Asset') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Kelola data asset fisik, lokasi penyimpanan, dan status inventaris.') }}
        </p>
    </header>

    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
        <div class="p-4 sm:p-6 space-y-4">
            {{-- Top Actions Bar --}}
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                @can('create asset')
                    <x-button type="button" variant="primary" wire:click="create" class="items-center max-w-xs gap-2">
                        <x-heroicon-s-plus class="w-5 h-5" />
                        <span>{{ __('Tambah Asset') }}</span>
                    </x-button>
                @endcan

                <div class="flex-grow flex flex-col sm:flex-row items-center gap-3">
                    <div class="w-full sm:w-auto sm:flex-grow">
                        <x-text-input wire:model.live.debounce.300ms="search" type="text" class="w-full"
                            placeholder="{{ __('Cari nama, kode, atau lokasi...') }}" />
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
                    @forelse ($assets as $asset)
                        <div wire:key="asset-card-{{ $asset->id }}"
                            class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden ring-1 ring-black ring-opacity-5">
                            <div class="relative h-48 w-full bg-gray-200 dark:bg-gray-700">
                                @if ($asset->image)
                                    <img src="{{ Storage::url($asset->image) }}" alt="{{ $asset->name }}"
                                        class="w-full h-full object-cover">
                                @else
                                    <div class="flex items-center justify-center h-full text-gray-400 dark:text-gray-500">
                                        <x-heroicon-o-photo class="w-12 h-12" />
                                    </div>
                                @endif
                                <div class="absolute top-2 right-2">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $asset->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                        {{ $asset->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            </div>
                            <div class="p-4 border-b dark:border-gray-700 flex justify-between items-start">
                                <div>
                                    <h3 class="font-bold text-lg text-gray-800 dark:text-gray-200">
                                        {{ $asset->name }}
                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 font-mono">
                                        {{ $asset->code }}
                                    </p>
                                </div>
                                <x-dropdown align="right" width="48">
                                    <x-slot name="trigger">
                                        <button
                                            class="p-1 text-gray-500 dark:text-gray-400 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500">
                                            <x-heroicon-s-ellipsis-vertical class="w-5 h-5" />
                                        </button>
                                    </x-slot>
                                    <x-slot name="content">
                                        @can('edit asset')
                                            <x-dropdown-link wire:click="edit({{ $asset->id }})">
                                                Edit Asset
                                            </x-dropdown-link>
                                        @endcan
                                        <div class="border-t border-gray-100 dark:border-gray-600"></div>
                                        @can('delete asset')
                                            <x-dropdown-link wire:click="confirmDelete({{ $asset->id }})"
                                                class="text-red-600 dark:text-red-500">Hapus Asset
                                            </x-dropdown-link>
                                        @endcan
                                    </x-slot>
                                </x-dropdown>
                            </div>
                            <div class="p-4 space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">Kategori:</span>
                                    <span class="text-gray-700 dark:text-gray-300 font-medium">
                                        {{ $asset->assetCategory ? $asset->assetCategory->name : '-' }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">Jumlah:</span>
                                    <span class="text-gray-700 dark:text-gray-300 font-medium">{{ $asset->quantity }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">Lokasi:</span>
                                    <span class="text-gray-700 dark:text-gray-300 font-medium">{{ $asset->storage_location }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                            {{ __('Tidak ada data asset.') }}
                        </div>
                    @endforelse
                </div>

                {{-- Tampilan Desktop (Tabel) --}}
                <div class="hidden md:block">
                    <x-table title="Data Asset" :heads="$table_heads">
                        @forelse ($assets as $key => $asset)
                            <tr wire:key="asset-table-{{ $asset->id }}"
                                class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-gray-700 dark:text-gray-300">
                                    {{ $key + $assets->firstItem() }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($asset->image)
                                        <div class="h-10 w-10 flex-shrink-0">
                                            <img class="h-10 w-10 rounded-lg object-cover ring-1 ring-gray-200 dark:ring-gray-700"
                                                 src="{{ Storage::url($asset->image) }}"
                                                 alt="{{ $asset->name }}">
                                        </div>
                                    @else
                                        <div class="h-10 w-10 flex-shrink-0 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center ring-1 ring-gray-200 dark:ring-gray-700">
                                            <x-heroicon-o-photo class="h-5 w-5 text-gray-400" />
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-semibold text-gray-900 dark:text-gray-200">{{ $asset->name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $asset->assetCategory ? $asset->assetCategory->name : '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-mono text-sm text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded">
                                        {{ $asset->code }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-300">
                                    {{ $asset->quantity }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-300">
                                    {{ $asset->storage_location }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($asset->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            <span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-green-500"></span>
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                            <span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-red-500"></span>
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="flex items-center justify-end space-x-1">
                                        @can('edit asset')
                                            <x-button type="button" variant="warning" size="sm" class="!p-2"
                                                wire:click="edit({{ $asset->id }})" title="Edit Asset">
                                                <x-heroicon-o-pencil-square class="w-4 h-4" />
                                                <span class="sr-only">Edit</span>
                                            </x-button>
                                        @endcan

                                        @can('delete asset')
                                            <x-button type="button" variant="danger" size="sm" class="!p-2"
                                                wire:click="confirmDelete({{ $asset->id }})" title="Hapus Asset">
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
            {{ $assets->links() }}
        </div>
    </div>

    {{-- Create/Edit Modal --}}
    <x-modal name="asset-modal" :show="$errors->isNotEmpty()" maxWidth="5xl" focusable>
        <form wire:submit="save" class="p-4 sm:p-6 bg-gray-50 dark:bg-gray-900" enctype="multipart/form-data">
            <div class="flex items-start justify-between pb-4 mb-6 border-b border-gray-200 dark:border-gray-700">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{ $editId ? __('Edit Asset') : __('Tambah Asset Baru') }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        {{ __('Silakan lengkapi informasi asset di bawah ini.') }}
                    </p>
                </div>
                <button type="button" @click="$dispatch('close')"
                    class="p-2 -m-2 text-gray-400 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 hover:text-gray-600 dark:hover:text-gray-200 transition-all">
                    <x-heroicon-s-x-mark class="h-6 w-6" />
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Left Column - Image Upload -->
                <div class="md:col-span-1">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4">
                        <x-input-label value="{{ __('Foto Asset') }}" class="mb-2" />
                        <div x-data="{ isUploading: false, progress: 0 }"
                             x-on:livewire-upload-start="isUploading = true"
                             x-on:livewire-upload-finish="isUploading = false"
                             x-on:livewire-upload-error="isUploading = false"
                             x-on:livewire-upload-progress="progress = $event.detail.progress">

                            <div class="relative group cursor-pointer">
                                <label for="image-upload" class="block w-full h-64 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600 hover:border-indigo-500 dark:hover:border-indigo-400 transition-all duration-200 overflow-hidden bg-gray-50 dark:bg-gray-900">
                                    @if ($form->image)
                                        <img src="{{ $form->image->temporaryUrl() }}" alt="Preview" class="w-full h-full object-cover">
                                    @elseif ($form->existingImage)
                                        <img src="{{ Storage::url($form->existingImage) }}" alt="Existing" class="w-full h-full object-cover">
                                    @else
                                        <div class="flex flex-col items-center justify-center h-full text-gray-400 dark:text-gray-500 pb-6 pt-5">
                                            <x-heroicon-o-cloud-arrow-up class="w-12 h-12 mb-3" />
                                            <p class="mb-2 text-sm"><span class="font-semibold text-indigo-600 dark:text-indigo-400">Click to upload</span></p>
                                            <p class="text-xs">PNG, JPG (MAX. 2MB)</p>
                                        </div>
                                    @endif

                                    <!-- Hover Overlay -->
                                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white">
                                        <div class="text-center">
                                            <x-heroicon-s-pencil-square class="w-8 h-8 mx-auto mb-2" />
                                            <span class="text-sm font-medium">Ubah Foto</span>
                                        </div>
                                    </div>
                                </label>
                                <input id="image-upload" type="file" wire:model="form.image" class="hidden" accept="image/png, image/jpeg, image/jpg">
                            </div>

                            <!-- Remove Button -->
                            @if ($form->image || $form->existingImage)
                                <button type="button" wire:click="removeImage"
                                    class="mt-2 w-full py-2 px-4 bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50 rounded-lg text-sm font-medium transition-colors flex items-center justify-center gap-2">
                                    <x-heroicon-s-trash class="w-4 h-4" />
                                    {{ __('Hapus Foto') }}
                                </button>
                            @endif

                            <!-- Progress Bar -->
                            <div x-show="isUploading" class="mt-4 w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                                <div class="bg-indigo-600 h-2.5 rounded-full transition-all duration-300" x-bind:style="`width: ${progress}%`"></div>
                            </div>
                        </div>
                        <x-input-error :messages="$errors->get('form.image')" class="mt-2" />
                    </div>
                </div>

                <!-- Right Column - Form Fields -->
                <div class="md:col-span-2 space-y-5">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 space-y-5">
                        <!-- Name & Category -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <x-input-label for="name" value="{{ __('Nama Asset') }}" />
                                <x-text-input wire:model.lazy="form.name" id="name" type="text"
                                    class="mt-1 block w-full" placeholder="{{ __('e.g. Laptop Lenovo Thinkpad') }}" />
                                <x-input-error :messages="$errors->get('form.name')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="asset_category_id" value="{{ __('Kategori') }}" />
                                <select wire:model="form.asset_category_id" id="asset_category_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-indigo-600 dark:focus:ring-indigo-600">
                                    <option value="">{{ __('Pilih Kategori') }}</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('form.asset_category_id')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Code & Slug -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <x-input-label for="code" value="{{ __('Kode Asset') }}" />
                                <x-text-input wire:model="form.code" id="code" type="text"
                                    class="mt-1 block w-full font-mono" placeholder="{{ __('e.g. AST-001') }}" />
                                <x-input-error :messages="$errors->get('form.code')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="slug" value="{{ __('Slug') }}" />
                                <x-text-input wire:model="form.slug" id="slug" type="text"
                                    class="mt-1 block w-full font-mono" placeholder="{{ __('e.g. laptop-lenovo-thinkpad') }}" />
                                <x-input-error :messages="$errors->get('form.slug')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <x-input-label for="description" value="{{ __('Deskripsi') }}" />
                            <textarea wire:model="form.description" id="description" rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-indigo-600 dark:focus:ring-indigo-600"
                                placeholder="{{ __('Deskripsi detail tentang aset ini...') }}"></textarea>
                            <x-input-error :messages="$errors->get('form.description')" class="mt-2" />
                        </div>

                        <!-- Quantity & Location -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <x-input-label for="quantity" value="{{ __('Jumlah') }}" />
                                <x-text-input wire:model="form.quantity" id="quantity" type="number" min="1"
                                    class="mt-1 block w-full" placeholder="1" />
                                <x-input-error :messages="$errors->get('form.quantity')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="storage_location" value="{{ __('Lokasi Penyimpanan') }}" />
                                <x-text-input wire:model="form.storage_location" id="storage_location" type="text"
                                    class="mt-1 block w-full" placeholder="{{ __('e.g. Gudang Utama, Rak A') }}" />
                                <x-input-error :messages="$errors->get('form.storage_location')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Status -->
                        <div>
                            <x-input-label for="is_active" value="{{ __('Status') }}" />
                            <select wire:model="form.is_active" id="is_active"
                                class="mt-1 block w-full md:w-1/2 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-indigo-600 dark:focus:ring-indigo-600">
                                <option value="1">{{ __('Active') }}</option>
                                <option value="0">{{ __('Inactive') }}</option>
                            </select>
                            <x-input-error :messages="$errors->get('form.is_active')" class="mt-2" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 pt-5 border-t border-gray-200 dark:border-gray-700 flex flex-col-reverse sm:flex-row justify-end gap-3">
                <x-secondary-button type="button" @click="$dispatch('close')" class="justify-center">
                    {{ __('Cancel') }}
                </x-secondary-button>
                <x-primary-button type="submit" class="justify-center">
                    <span wire:loading.remove wire:target="save">
                        {{ $editId ? __('Update Asset') : __('Simpan Asset') }}
                    </span>
                    <span wire:loading wire:target="save" class="flex items-center gap-2">
                        <x-heroicon-s-arrow-path class="h-4 w-4 animate-spin" />
                        <span>{{ __('Menyimpan...') }}</span>
                    </span>
                </x-primary-button>
            </div>
        </form>
    </x-modal>

    {{-- Delete Confirmation Modal --}}
    <x-modal name="delete-asset-confirmation" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="delete" class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Hapus Data Asset?') }}
            </h2>

            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Tindakan ini tidak dapat dibatalkan. Data asset akan dihapus dari sistem.') }}
            </p>

            <div class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-100 dark:border-red-800">
                <dl class="grid grid-cols-1 gap-x-4 gap-y-3 sm:grid-cols-2">
                    <div class="sm:col-span-1">
                        <dt class="text-xs font-medium text-red-500 dark:text-red-400 uppercase">{{ __('Nama Asset') }}</dt>
                        <dd class="mt-1 text-sm font-semibold text-red-900 dark:text-red-200">{{ $form->name }}</dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-xs font-medium text-red-500 dark:text-red-400 uppercase">{{ __('Kode') }}</dt>
                        <dd class="mt-1 text-sm font-mono text-red-900 dark:text-red-200">{{ $form->code }}</dd>
                    </div>
                </dl>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button type="button" x-on:click="$dispatch('close')">
                    {{ __('Batal') }}
                </x-secondary-button>

                <x-danger-button>
                    <span wire:loading.remove wire:target="delete">{{ __('Hapus Asset') }}</span>
                    <span wire:loading wire:target="delete" class="flex items-center gap-2">
                        <x-heroicon-s-arrow-path class="h-4 w-4 animate-spin" />
                        {{ __('Menghapus...') }}
                    </span>
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</div>
