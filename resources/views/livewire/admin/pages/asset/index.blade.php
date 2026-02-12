<div>
    <header class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 leading-tight">
            {{ __('Manajemen Asset') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Kelola data asset fisik, inventaris, dan status ketersediaan.
        </p>
    </header>

    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
        <div class="p-4 sm:p-6 space-y-4">
            {{-- Header Kontrol: Tombol, Filter, dan Pencarian --}}
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                @can('access', 'admin.assets.create')
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
                    <div class="w-full sm:w-48">
                        <x-select wire:model.live="filterStatus" class="w-full">
                            <option value="">{{ __('Semua Status') }}</option>
                            <option value="1">{{ __('Active') }}</option>
                            <option value="0">{{ __('Inactive') }}</option>
                        </x-select>
                    </div>
                    @if ($search || $filterStatus !== '')
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
                    @forelse ($assets as $asset)
                        <div wire:key="asset-card-{{ $asset->id }}"
                            class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden ring-1 ring-black ring-opacity-5">
                            <div class="p-4 border-b dark:border-gray-700 flex justify-between items-start">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-gray-100 dark:bg-gray-700 overflow-hidden">
                                        @if ($asset->image)
                                            <img src="{{ Storage::url($asset->image) }}" alt="{{ $asset->name }}"
                                                class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-gray-400">
                                                <x-heroicon-o-photo class="w-6 h-6" />
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-lg text-gray-800 dark:text-gray-200">{{ $asset->name }}</h3>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $asset->code ?? '-' }}</div>
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
                                        @can('access', 'admin.assets.edit')
                                            <x-dropdown-link wire:click="edit({{ $asset->id }})">
                                                Edit
                                            </x-dropdown-link>
                                        @endcan
                                        @can('access', 'admin.assets.destroy')
                                            <x-dropdown-link wire:click="confirmDelete({{ $asset->id }})"
                                                class="text-red-600 dark:text-red-500">Delete
                                            </x-dropdown-link>
                                        @endcan
                                    </x-slot>
                                </x-dropdown>
                            </div>
                            <div class="p-4 space-y-3 text-sm">
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center text-gray-600 dark:text-gray-300">
                                        <x-heroicon-o-cube class="w-4 h-4 mr-2 flex-shrink-0" />
                                        <span>Stok: {{ $asset->quantity }}</span>
                                    </div>
                                    <div>
                                        @if ($asset->is_active)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                Active
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                Inactive
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center text-gray-600 dark:text-gray-300">
                                    <x-heroicon-o-tag class="w-4 h-4 mr-2 flex-shrink-0" />
                                    <span>{{ $asset->assetCategory->name ?? '-' }}</span>
                                </div>
                                <div class="flex items-center text-gray-600 dark:text-gray-300">
                                    <x-heroicon-o-map-pin class="w-4 h-4 mr-2 flex-shrink-0" />
                                    <span>{{ $asset->storage_location ?? '-' }}</span>
                                </div>

                                {{-- Mobile Card Actions --}}
                                <div class="pt-3 border-t border-gray-100 dark:border-gray-700 flex flex-wrap gap-4">
                                    @can('access', 'admin.assets.edit')
                                        <button wire:click="edit({{ $asset->id }})" class="text-amber-500 dark:text-amber-400 font-medium hover:underline flex items-center gap-1.5 transition-colors">
                                            <x-heroicon-o-pencil-square class="w-4 h-4" />
                                            <span>{{ __('Edit') }}</span>
                                        </button>
                                    @endcan
                                    @can('access', 'admin.assets.destroy')
                                        <button wire:click="confirmDelete({{ $asset->id }})" class="text-red-600 dark:text-red-400 font-medium hover:underline flex items-center gap-1.5 transition-colors">
                                            <x-heroicon-o-trash class="w-4 h-4" />
                                            <span>{{ __('Delete') }}</span>
                                        </button>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                            {{ __('Tidak ada data asset') }}
                        </div>
                    @endforelse
                </div>

                {{-- Tampilan Desktop (Tabel) --}}
                <div class="hidden md:block">
                    <x-table title="Data Asset" :heads="$table_heads">
                        @forelse ($assets as $index => $asset)
                            <tr wire:key="asset-row-{{ $asset->id }}"
                                class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-300">
                                    {{ $assets->firstItem() + $index }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-700 overflow-hidden">
                                        @if ($asset->image)
                                            <img src="{{ Storage::url($asset->image) }}" alt="{{ $asset->name }}"
                                                class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-gray-400">
                                                <x-heroicon-o-photo class="w-5 h-5" />
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-gray-900 dark:text-gray-200">{{ $asset->name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $asset->assetCategory->name ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-300 font-mono text-xs">
                                    {{ $asset->code ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-300">
                                    {{ $asset->quantity }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-300">
                                    {{ $asset->storage_location ?? '-' }}
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
                                        @can('access', 'admin.assets.edit')
                                            <x-button type="button" variant="warning" size="sm" class="!p-2"
                                                wire:click="edit({{ $asset->id }})" title="Edit">
                                                <x-heroicon-o-pencil-square class="w-4 h-4" />
                                                <span class="sr-only">Edit</span>
                                            </x-button>
                                        @endcan
                                        @can('access', 'admin.assets.destroy')
                                            <x-button type="button" variant="danger" size="sm" class="!p-2"
                                                wire:click="confirmDelete({{ $asset->id }})" title="Hapus">
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
                                    <x-heroicon-o-inbox class="mx-auto h-12 w-12" />
                                    <h4 class="mt-2 text-sm font-semibold">{{ __('Tidak ada data asset') }}</h4>
                                    <p class="mt-1 text-sm">{{ __('Coba ubah filter Anda atau tambah asset baru.') }}</p>
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

    {{-- Asset Modal (Create/Edit) --}}
    <x-modal name="asset-modal" :show="$errors->isNotEmpty()" maxWidth="4xl" focusable>
        <form wire:submit="save" class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">
                {{ $editId ? __('Edit Asset') : __('Tambah Asset Baru') }}
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Nama Asset --}}
                <div class="col-span-1 md:col-span-2">
                    <x-input-label for="name" value="{{ __('Nama Asset') }}" />
                    <x-text-input wire:model.live="form.name" id="name" type="text" class="mt-1 block w-full"
                        placeholder="Contoh: Kursi Lipat, Proyektor EPSON..." />
                    <x-input-error :messages="$errors->get('form.name')" class="mt-2" />
                </div>

                {{-- Kategori --}}
                <div>
                    <x-input-label for="asset_category_id" value="{{ __('Kategori') }}" />
                    <x-select wire:model="form.asset_category_id" id="asset_category_id" class="mt-1 block w-full">
                        <option value="">{{ __('Pilih Kategori') }}</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </x-select>
                    <x-input-error :messages="$errors->get('form.asset_category_id')" class="mt-2" />
                </div>

                {{-- Kode Asset --}}
                <div>
                    <x-input-label for="code" value="{{ __('Kode Asset') }}" />
                    <x-text-input wire:model="form.code" id="code" type="text" class="mt-1 block w-full"
                        placeholder="Contoh: AST-001" />
                    <x-input-error :messages="$errors->get('form.code')" class="mt-2" />
                </div>

                {{-- Jumlah --}}
                <div>
                    <x-input-label for="quantity" value="{{ __('Jumlah (Qty)') }}" />
                    <x-text-input wire:model="form.quantity" id="quantity" type="number" min="0"
                        class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('form.quantity')" class="mt-2" />
                </div>

                {{-- Lokasi Penyimpanan --}}
                <div>
                    <x-input-label for="storage_location" value="{{ __('Lokasi Penyimpanan') }}" />
                    <x-text-input wire:model="form.storage_location" id="storage_location" type="text"
                        class="mt-1 block w-full" placeholder="Contoh: Gudang A, Lemari 2" />
                    <x-input-error :messages="$errors->get('form.storage_location')" class="mt-2" />
                </div>

                {{-- Status Aktif --}}
                <div class="flex items-center mt-4">
                    <label for="is_active" class="inline-flex items-center cursor-pointer">
                        <input wire:model="form.is_active" id="is_active" type="checkbox"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700">
                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Asset Aktif / Dapat Digunakan') }}</span>
                    </label>
                    <x-input-error :messages="$errors->get('form.is_active')" class="mt-2" />
                </div>

                {{-- Deskripsi --}}
                <div class="col-span-1 md:col-span-2">
                    <x-input-label for="description" value="{{ __('Deskripsi') }}" />
                    <textarea wire:model="form.description" id="description" rows="3"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"></textarea>
                    <x-input-error :messages="$errors->get('form.description')" class="mt-2" />
                </div>

                {{-- Gambar --}}
                <div class="col-span-1 md:col-span-2">
                    <x-input-label for="image" value="{{ __('Gambar Asset') }}" />
                    
                    @if ($form->image && !is_string($form->image))
                        <div class="mt-2 mb-2">
                            <img src="{{ $form->image->temporaryUrl() }}" alt="Preview" class="h-32 w-32 object-cover rounded-lg border dark:border-gray-700">
                        </div>
                    @elseif ($form->existingImage)
                        <div class="mt-2 mb-2 relative inline-block group">
                            <img src="{{ Storage::url($form->existingImage) }}" alt="Existing Image" class="h-32 w-32 object-cover rounded-lg border dark:border-gray-700">
                            <button type="button" wire:click="removeImage" 
                                class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 shadow-md hover:bg-red-600 focus:outline-none transition-transform hover:scale-110"
                                title="{{ __('Hapus Gambar') }}">
                                <x-heroicon-s-x-mark class="w-4 h-4" />
                            </button>
                        </div>
                    @endif
                    <input wire:model="form.image" id="image" type="file" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" accept="image/*">
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-300">Format: JPG, PNG, max 2MB.</p>
                    <x-input-error :messages="$errors->get('form.image')" class="mt-2" />
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button type="button" x-on:click="$dispatch('close')">
                    {{ __('Batal') }}
                </x-secondary-button>

                <x-button variant="primary">
                    <span wire:loading.remove wire:target="save">{{ $editId ? __('Simpan Perubahan') : __('Simpan Asset') }}</span>
                    <span wire:loading wire:target="save" class="flex items-center gap-2">
                        <x-heroicon-s-arrow-path class="h-4 w-4 animate-spin" />
                        {{ __('Menyimpan...') }}
                    </span>
                </x-button>
            </div>
        </form>
    </x-modal>

    {{-- Delete Confirmation --}}
    <x-modal name="delete-asset-confirmation" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="delete" class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Hapus Data Asset?') }}
            </h2>

            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Tindakan ini tidak dapat dibatalkan. Data asset beserta riwayatnya mungkin akan terpengaruh.') }}
            </p>

            @if ($form->name)
                <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700">
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Nama Asset') }}</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $form->name }}</dd>
                        </div>
                    </dl>
                </div>
            @endif

            <div class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-100 dark:border-red-800">
                <div class="flex items-center gap-3">
                    <x-heroicon-o-trash class="w-5 h-5 text-red-600 dark:text-red-400" />
                    <span class="text-sm font-medium text-red-900 dark:text-red-200">
                        {{ __('Data yang dihapus tidak dapat dipulihkan.') }}
                    </span>
                </div>
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
