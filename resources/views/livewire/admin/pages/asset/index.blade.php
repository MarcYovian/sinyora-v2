<div>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Data Assets') }}
        </h2>
    </header>

    <div class="mt-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 my-4 px-4 md:px-0 md:flex md:justify-between">
            <x-button type="button" variant="primary" wire:click="create" class="items-center max-w-xs gap-2">
                <x-heroicon-s-plus class="w-5 h-5" />
                <span>{{ __('Create') }}</span>
            </x-button>

            <div class="w-full md:w-1/2">
                <x-search placeholder="Search asset by name.." wire:model.debounce.500ms="search" />
            </div>
        </div>

        <div class="p-6 text-gray-900 dark:text-gray-100">
            <x-table title="Data Assets" :heads="$table_heads">
                @forelse ($assets as $key => $asset)
                    <tr wire:key="asset-{{ $asset->id }}"
                        class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                            {{ $key + $assets->firstItem() }}
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @if ($asset->image)
                                <img src="{{ Storage::url($asset->image) }}" alt="{{ $asset->name }}"
                                    class="w-10 h-10 rounded object-cover">
                            @else
                                <span class="text-gray-400">No image</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm">{{ $asset->name }}</td>
                        <td class="px-6 py-4 text-sm">{{ $asset->code }}</td>
                        <td class="px-6 py-4 text-sm">{{ $asset->quantity }}</td>
                        <td class="px-6 py-4 text-sm">{{ $asset->storage_location }}</td>
                        <td class="px-6 py-4 text-sm">
                            <span
                                class="w-2 h-2 rounded-full inline-block {{ $asset->is_active ? 'bg-green-500' : 'bg-red-500' }}">
                            </span>
                            {{ $asset->is_active ? 'Active' : 'Inactive' }}
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <div class="flex flex-col items-center gap-2">
                                <x-button size="sm" variant="warning" type="button"
                                    wire:click="edit({{ $asset->id }})">
                                    {{ __('Edit') }}
                                </x-button>
                                <x-button size="sm" variant="danger" type="button"
                                    wire:click="confirmDelete({{ $asset->id }})">
                                    {{ __('Delete') }}
                                </x-button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($table_heads) }}" class="text-center text-sm text-gray-500 py-4">
                            No assets found.
                        </td>
                    </tr>
                @endforelse
            </x-table>

            <div class="mt-4">
                {{ $assets->links() }}
            </div>
        </div>
    </div>

    <x-modal name="asset-modal" :show="$errors->isNotEmpty()" maxWidth="5xl" focusable>
        <form wire:submit="save" class="p-6" enctype="multipart/form-data">
            <!-- Header with close button -->
            <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                    {{ $editId ? __('Edit Asset') : __('Create New Asset') }}
                </h2>
                <button type="button" @click="$dispatch('close')"
                    class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 transition-colors">
                    <x-heroicon-s-x-mark class="h-6 w-6" />
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Left Column - Image Upload -->
                <div class="md:col-span-1">
                    <x-input-label value="{{ __('Asset Image') }}" />
                    <div x-data="{ isUploading: false, progress: 0 }" x-on:livewire-upload-start="isUploading = true"
                        x-on:livewire-upload-finish="isUploading = false"
                        x-on:livewire-upload-error="isUploading = false"
                        x-on:livewire-upload-progress="progress = $event.detail.progress" class="mt-2">

                        <!-- Image Preview -->
                        <div class="relative group">
                            <div
                                class="w-full h-48 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 flex items-center justify-center overflow-hidden transition-all duration-300 hover:border-indigo-500 dark:hover:border-indigo-400">
                                @if ($form->image || $form->existingImage)
                                    <img src="{{ $form->image ? $form->image->temporaryUrl() : Storage::url($form->existingImage) }}"
                                        alt="Asset preview"
                                        class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
                                @else
                                    <div class="text-center p-4">
                                        <x-heroicon-s-photo
                                            class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" />
                                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                            {{ __('Upload an asset photo') }}
                                        </p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                            PNG, JPG up to 2MB
                                        </p>
                                    </div>
                                @endif
                            </div>

                            <!-- Remove Image Button -->
                            @if ($form->image || $form->existingImage)
                                <button type="button" wire:click="removeImage"
                                    class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 transition-colors">
                                    <x-heroicon-s-trash class="h-4 w-4" />
                                </button>
                            @endif

                            <!-- Upload Progress -->
                            <div x-show="isUploading"
                                class="absolute inset-x-0 bottom-0 bg-gray-200 dark:bg-gray-700 h-1.5">
                                <div class="bg-indigo-600 h-full transition-all duration-300 ease-out"
                                    x-bind:style="`width: ${progress}%`"></div>
                            </div>

                            <!-- Upload Button -->
                            <label for="image-upload" class="absolute inset-0 cursor-pointer"></label>
                            <input id="image-upload" type="file" wire:model="form.image" class="hidden">
                        </div>

                        <x-input-error :messages="$errors->get('form.image')" class="mt-2" />
                    </div>
                </div>

                <!-- Right Column - Form Fields -->
                <div class="md:col-span-2 space-y-4">
                    <!-- Category -->
                    <div>
                        <x-input-label for="asset_category_id" value="{{ __('Category') }}" />
                        <x-select wire:model="form.asset_category_id" id="asset_category_id" class="w-full mt-1">
                            <option value="">{{ __('Select Category') }}</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </x-select>
                        <x-input-error :messages="$errors->get('form.asset_category_id')" class="mt-2" />
                    </div>

                    <!-- Name and Code -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="name" value="{{ __('Asset Name') }}" />
                            <x-text-input wire:model="form.name" id="name" type="text" class="w-full mt-1"
                                placeholder="{{ __('e.g. Projector, Laptop') }}" />
                            <x-input-error :messages="$errors->get('form.name')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="code" value="{{ __('Asset Code') }}" />
                            <x-text-input wire:model="form.code" id="code" type="text" class="w-full mt-1"
                                placeholder="{{ __('e.g. AST-001') }}" />
                            <x-input-error :messages="$errors->get('form.code')" class="mt-2" />
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <x-input-label for="description" value="{{ __('Description') }}" />
                        <textarea wire:model="form.description" id="description" rows="3"
                            class="w-full mt-1 border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="{{ __('Detailed description of the asset...') }}"></textarea>
                        <x-input-error :messages="$errors->get('form.description')" class="mt-2" />
                    </div>

                    <!-- Quantity and Storage -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="quantity" value="{{ __('Quantity') }}" />
                            <x-text-input wire:model="form.quantity" id="quantity" type="number" min="1"
                                class="w-full mt-1" placeholder="1" />
                            <x-input-error :messages="$errors->get('form.quantity')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="storage_location" value="{{ __('Storage Location') }}" />
                            <x-text-input wire:model="form.storage_location" id="storage_location" type="text"
                                class="w-full mt-1" placeholder="{{ __('e.g. Room 101, Shelf A3') }}" />
                            <x-input-error :messages="$errors->get('form.storage_location')" class="mt-2" />
                        </div>
                    </div>

                    <!-- Status -->
                    <div>
                        <x-input-label value="{{ __('Status') }}" />
                        <div class="mt-2 flex items-center gap-4">
                            <label class="inline-flex items-center">
                                <input type="radio" wire:model="form.is_active" value="1"
                                    class="h-5 w-5 text-indigo-600 dark:text-indigo-500 focus:ring-indigo-500 border-gray-300 dark:border-gray-600">
                                <span class="ml-2 text-gray-700 dark:text-gray-300">{{ __('Active') }}</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" wire:model="form.is_active" value="0"
                                    class="h-5 w-5 text-indigo-600 dark:text-indigo-500 focus:ring-indigo-500 border-gray-300 dark:border-gray-600">
                                <span class="ml-2 text-gray-700 dark:text-gray-300">{{ __('Inactive') }}</span>
                            </label>
                        </div>
                        <x-input-error :messages="$errors->get('form.is_active')" class="mt-2" />
                    </div>
                </div>
            </div>

            <!-- Footer Buttons -->
            <div
                class="mt-8 pt-5 border-t border-gray-200 dark:border-gray-700 flex flex-col-reverse sm:flex-row justify-end gap-3">
                <x-secondary-button type="button" @click="$dispatch('close')"
                    class="w-full sm:w-auto justify-center px-6 py-3">
                    {{ __('Cancel') }}
                </x-secondary-button>
                <x-primary-button type="submit"
                    class="w-full sm:w-auto justify-center px-6 py-3 shadow-lg hover:shadow-xl transition-shadow">
                    <span wire:loading.remove wire:target="save">
                        {{ $editId ? __('Update Asset') : __('Create Asset') }}
                    </span>
                    <span wire:loading wire:target="save" class="flex items-center gap-2">
                        <x-heroicon-s-arrow-path class="h-5 w-5 animate-spin" />
                        {{ __('Saving...') }}
                    </span>
                </x-primary-button>
            </div>
        </form>
    </x-modal>


    {{-- Modal Delete Confirmation --}}
    <x-modal name="delete-asset-confirmation" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="delete" class="p-6">

            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Are you sure you want to delete this asset?') }}
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('This action cannot be undone.') }}
            </p>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Please confirm that you want to delete this asset by clicking the button below.') }}
            </p>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    {{ __('Delete') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</div>
