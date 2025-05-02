<div>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Data Locations') }}
        </h2>
    </header>

    <div class="mt-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 my-4 px-4 md:px-0 md:flex md:justify-between">
            @can('create location')
                <x-button type="button" variant="primary" wire:click="create" class="items-center max-w-xs gap-2">
                    <x-heroicon-s-plus class="w-5 h-5" />

                    <span>{{ __('Create') }}</span>
                </x-button>
            @endcan

            <div class="w-full md:w-1/2">
                <x-search placeholder="Search location by name.." />
            </div>
        </div>

        <div class="p-6 text-gray-900 dark:text-gray-100">
            <x-table title="Data Locations" :heads="$table_heads">
                @forelse ($locations as $key => $location)
                    <tr wire:key="user-{{ $location->id }}"
                        class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            {{ $key + $locations->firstItem() }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            @if ($location->image)
                                <img src="{{ Storage::url($location->image) }}" alt="{{ $location->name }}"
                                    loading="lazy" class="h-10 w-10 rounded object-cover">
                            @else
                                <span class="text-gray-400">No image</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            {{ $location->name }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            {{ $location->description }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            <span
                                class="w-2 h-2 rounded-full {{ $location->is_active ? 'bg-green-500' : 'bg-red-500' }}"></span>
                            {{ $location->is_active ? 'Active' : 'Inactive' }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            <div class="flex flex-col items-center gap-2">
                                @can('edit location')
                                    <x-button size="sm" variant="warning" type="button"
                                        wire:click="edit({{ $location->id }})">
                                        {{ __('Edit') }}
                                    </x-button>
                                @endcan
                                @can('delete location')
                                    <x-button size="sm" variant="danger" type="button"
                                        wire:click="confirmDelete({{ $location->id }})">
                                        {{ __('Delete') }}
                                    </x-button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="bg-white dark:bg-gray-800">
                        <td colspan="6"
                            class="whitespace-nowrap px-6 py-4 text-rose-700 dark:text-rose-400 text-sm text-center">
                            {{ __('No data available') }}
                        </td>
                    </tr>
                @endforelse
            </x-table>
        </div>
        <div class="px-6 py-4">
            {{ $locations->links() }}
        </div>
    </div>

    <x-modal name="location-modal" :show="$errors->isNotEmpty()" maxWidth="lg" focusable>
        <form wire:submit="save" class="p-6" enctype="multipart/form-data">
            <!-- Header with close button -->
            <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
                    {{ $editId ? __('Edit Location') : __('Create Location') }}
                </h2>
                <button type="button" @click="$dispatch('close')"
                    class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 transition-colors">
                    <x-heroicon-s-x-mark class="h-6 w-6" />
                </button>
            </div>

            <div class="space-y-6">
                <!-- Image Upload Section -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <x-input-label for="image" value="{{ __('Location Image') }}" class="text-base" />
                        @if ($form->image || $form->existingImage)
                            <button type="button" wire:click="removeImage"
                                class="text-sm text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 flex items-center gap-1">
                                <x-heroicon-s-trash class="h-4 w-4" />
                                {{ __('Remove Image') }}
                            </button>
                        @endif
                    </div>

                    <div class="flex flex-col sm:flex-row gap-6 items-start">
                        <!-- Image Preview -->
                        <div x-data="{ isUploading: false, progress: 0 }" x-on:livewire-upload-start="isUploading = true"
                            x-on:livewire-upload-finish="isUploading = false"
                            x-on:livewire-upload-error="isUploading = false"
                            x-on:livewire-upload-progress="progress = $event.detail.progress" class="flex-1 w-full">

                            <div class="relative group">
                                <div
                                    class="w-full h-48 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 flex items-center justify-center overflow-hidden transition-all duration-300 hover:border-indigo-500 dark:hover:border-indigo-400">
                                    @if ($form->image || $form->existingImage)
                                        <img src="{{ $form->image ? $form->image->temporaryUrl() : Storage::url($form->existingImage) }}"
                                            alt="Location preview"
                                            class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
                                    @else
                                        <div class="text-center p-4">
                                            <x-heroicon-s-photo
                                                class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" />
                                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                                {{ __('Upload a location photo') }}
                                            </p>
                                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                                PNG, JPG, GIF up to 2MB
                                            </p>
                                        </div>
                                    @endif
                                </div>

                                <!-- Upload Progress -->
                                <div x-show="isUploading"
                                    class="absolute inset-x-0 bottom-0 bg-gray-200 dark:bg-gray-700 h-1.5">
                                    <div class="bg-indigo-600 h-full transition-all duration-300 ease-out"
                                        x-bind:style="`width: ${progress}%`"></div>
                                </div>

                                <!-- Upload Button -->
                                <label for="image-upload" class="absolute inset-0 cursor-pointer"></label>
                                <input id="image-upload" type="file" wire:model="form.image"
                                    accept="image/jpeg,image/png" class="hidden">
                            </div>

                            <x-input-error :messages="$errors->get('form.image')" class="mt-2" />
                        </div>

                        <!-- Form Fields -->
                        <div class="flex-1 w-full space-y-5">
                            <!-- Name Field -->
                            <div>
                                <x-input-label for="name" value="{{ __('Location Name') }}" />
                                <x-text-input wire:model="form.name" id="name" type="text"
                                    class="block w-full mt-2 px-4 py-2.5"
                                    placeholder="{{ __('e.g. Main Hall, Conference Room') }}" />
                                <x-input-error :messages="$errors->get('form.name')" class="mt-2" />
                            </div>

                            <!-- Description Field -->
                            <div>
                                <x-input-label for="description" value="{{ __('Description') }}" />
                                <textarea wire:model="form.description" id="description" rows="3"
                                    class="block w-full mt-2 px-4 py-2.5 border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                <x-input-error :messages="$errors->get('form.description')" class="mt-2" />
                            </div>

                            <!-- Status Field -->
                            <div>
                                <x-input-label for="is_active" value="{{ __('Status') }}" />
                                <div class="mt-1">
                                    <select wire:model.live="form.is_active" id="is_active"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:focus:border-indigo-600 dark:focus:ring-indigo-600 py-2 pl-3 pr-10">
                                        <option value='1'>{{ __('Active') }}</option>
                                        <option value='0'>{{ __('Inactive') }}</option>
                                    </select>
                                </div>
                                <x-input-error :messages="$errors->get('form.is_active')" class="mt-2" />
                            </div>
                        </div>
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
                <x-primary-button type="submit" class="w-full sm:w-auto justify-center">
                    <span wire:loading.remove wire:target="save">
                        {{ $editId ? __('Update Location') : __('Create Location') }}
                    </span>
                    <span wire:loading wire:target="save" class="flex items-center gap-2">
                        {{ __('Saving...') }}
                    </span>
                    <x-heroicon-s-arrow-path wire:loading wire:target="save" class="ml-2 h-4 w-4 animate-spin" />
                </x-primary-button>
            </div>
        </form>
    </x-modal>

    <x-modal name="delete-location-confirmation" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="delete" class="p-6">

            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Are you sure you want to delete this location?') }}
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('This action cannot be undone.') }}
            </p>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Please confirm that you want to delete this location by clicking the button below.') }}
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
