<div>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Data Categories') }}
        </h2>
    </header>

    <div class="mt-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 my-4 px-4 md:px-0 md:flex md:justify-between">
            @can('create asset category')
                <x-button type="button" variant="primary" wire:click="create" class="items-center max-w-xs gap-2">
                    <x-heroicon-s-plus class="w-5 h-5" />

                    <span>{{ __('Create') }}</span>
                </x-button>
            @endcan

            <div class="w-full md:w-1/2">
                <x-search placeholder="Search asset categories by name.." />
            </div>
        </div>

        <div class="p-6 text-gray-900 dark:text-gray-100">
            <x-table title="Data Asset Categories" :heads="$table_heads">
                @forelse ($categories as $key => $category)
                    <tr wire:key="category-{{ $category->id }}"
                        class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            {{ $key + $categories->firstItem() }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            {{ $category->name }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            {{ $category->slug }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            <span class="w-2 h-2 rounded-full bg-red-500"></span>
                            {{ $category->is_active ? 'Active' : 'Inactive' }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            <div class="flex flex-col items-center gap-2">
                                @can('edit asset category')
                                    <x-button size="sm" variant="warning" type="button"
                                        wire:click="edit({{ $category->id }})">
                                        {{ __('Edit') }}
                                    </x-button>
                                @endcan

                                @can('delete asset category')
                                    <x-button size="sm" variant="danger" type="button"
                                        wire:click="confirmDelete({{ $category->id }})">
                                        {{ __('Delete') }}
                                    </x-button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="bg-white dark:bg-gray-800">
                        <td colspan="{{ count($table_heads) }}"
                            class="whitespace-nowrap px-6 py-4 text-rose-700 dark:text-rose-400 text-sm text-center">
                            {{ __('No data available') }}
                        </td>
                    </tr>
                @endforelse
            </x-table>
        </div>
        <div class="px-6 py-4">
            {{ $categories->links() }}
        </div>
    </div>

    <x-modal name="category-modal" :show="$errors->isNotEmpty()" maxWidth="md" focusable>
        <form wire:submit="save" class="p-6">
            <!-- Header with close button -->
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                    {{ $editId ? __('Edit Category') : __('Create New Category') }}
                </h2>
                <button type="button" @click="$dispatch('close')"
                    class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                    <x-heroicon-s-x-circle class="h-6 w-6" />
                </button>
            </div>

            <div class="space-y-6">
                <!-- Name Field -->
                <div>
                    <x-input-label for="name" value="{{ __('Name') }}" class="mb-1" />
                    <x-text-input wire:model.lazy="form.name" id="name" name="name" type="text"
                        class="block w-full mt-1" placeholder="{{ __('e.g. Misa, Meeting') }}" />
                    <x-input-error :messages="$errors->get('form.name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="slug" value="{{ __('Slug') }}" class="mb-1" />
                    <x-text-input wire:model="form.slug" id="slug" name="slug" type="text"
                        class="block w-full mt-1" placeholder="{{ __('e.g. misa, meeting') }}" />
                    <x-input-error :messages="$errors->get('form.slug')" class="mt-2" />
                </div>

                <!-- Status Field -->
                <div>
                    <x-input-label for="is_active" value="{{ __('Status') }}" class="mb-1" />
                    <div class="mt-1 relative">
                        <select wire:model="form.is_active" id="is_active" name="is_active"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:focus:border-indigo-600 dark:focus:ring-indigo-600 py-2 pl-3 pr-10">
                            <option value='1'>{{ __('Active') }}</option>
                            <option value='0'>{{ __('Inactive') }}</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                            <x-heroicon-s-chevron-down class="h-5 w-5 text-gray-400" />
                        </div>
                    </div>
                    <x-input-error :messages="$errors->get('form.is_active')" class="mt-2" />
                </div>
            </div>

            <!-- Footer Buttons -->
            <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 border-t pt-4 dark:border-gray-700">
                <x-secondary-button x-on:click="$dispatch('close')" class="w-full sm:w-auto justify-center">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-primary-button type="submit" class="w-full sm:w-auto justify-center" wire:loading.attr="disabled"
                    wire:target="save">
                    <x-heroicon-s-check class="w-5 h-5 mr-2" wire:loading.remove wire:target="save" />
                    <div wire:loading wire:target="save"
                        class="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2 dark:border-gray-400">
                    </div>
                    <span wire:loading.remove
                        wire:target="save">{{ $editId ? __('Update Category') : __('Create Category') }}
                    </span>
                    <span wire:loading wire:target="save">{{ __('Saving...') }}</span>
                </x-primary-button>
            </div>
        </form>
    </x-modal>

    <x-modal name="delete-category-confirmation" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="delete" class="p-6">

            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Are you sure you want to delete this category?') }}
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('This action cannot be undone.') }}
            </p>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Please confirm that you want to delete this category by clicking the button below.') }}
            </p>

            <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 border-t pt-4 dark:border-gray-700">
                <x-secondary-button x-on:click="$dispatch('close')" class="w-full sm:w-auto justify-center">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-primary-button type="submit" class="w-full sm:w-auto justify-center" wire:loading.attr="disabled"
                    wire:target="delete">
                    <x-heroicon-s-check class="w-5 h-5 mr-2" wire:loading.remove wire:target="delete" />
                    <div wire:loading wire:target="delete"
                        class="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2 dark:border-gray-400">
                    </div>
                    <span wire:loading.remove wire:target="delete">{{ __('Delete Category') }}
                    </span>
                    <span wire:loading wire:target="delete">{{ __('Deleting...') }}</span>
                </x-primary-button>
            </div>
        </form>
    </x-modal>
</div>
