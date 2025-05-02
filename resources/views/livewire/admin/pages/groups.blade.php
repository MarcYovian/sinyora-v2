<div>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Data Groups') }}
        </h2>
    </header>

    <div class="mt-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 my-4 px-4 md:px-0 md:flex md:justify-between">
            @can('create group')
                <x-button type="button" variant="primary" wire:click="create" class="items-center max-w-xs gap-2">
                    <x-heroicon-s-plus class="w-5 h-5" />

                    <span>{{ __('Create') }}</span>
                </x-button>
            @endcan


            <div class="w-full md:w-1/2">
                <x-search placeholder="Search groups by name.." />
            </div>
        </div>

        <div class="p-6 text-gray-900 dark:text-gray-100">
            <x-table title="Data Groups" :heads="$table_heads">
                @forelse ($groups as $key => $group)
                    <tr wire:key="user-{{ $group->id }}"
                        class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            {{ $key + $groups->firstItem() }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            {{ $group->name }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            <div class="flex flex-col items-center gap-2">
                                @can('edit group')
                                    <x-button size="sm" variant="warning" type="button"
                                        wire:click="edit({{ $group->id }})">
                                        {{ __('Edit') }}
                                    </x-button>
                                @endcan
                                @can('delete group')
                                    <x-button size="sm" variant="danger" type="button"
                                        wire:click="confirmDelete({{ $group->id }})">
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
            {{ $groups->links() }}
        </div>
    </div>

    <x-modal name="group-modal" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="save" class="p-6">

            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ $editId ? __('Edit Group') : __('Create New Group') }}
            </h2>

            <div class="mt-6">
                <x-input-label for="name" value="{{ __('Name') }}" />

                <x-text-input wire:model="form.name" id="name" name="name" type="text"
                    class="mt-1 block w-3/4" placeholder="{{ __('name') }}" />

                <x-input-error :messages="$errors->get('form.name')" class="mt-2" />
            </div>

            <div
                class="mt-8 pt-5 border-t border-gray-200 dark:border-gray-700 flex flex-col-reverse sm:flex-row justify-end gap-3"">
                <x-secondary-button type="button" @click="$dispatch('close')"
                    class="w-full sm:w-auto justify-center px-6 py-3">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button type="submit" class="w-full sm:w-auto justify-center">
                    <span wire:loading.remove wire:target="save">
                        {{ $editId ? __('Update Group') : __('Create Group') }}
                    </span>
                    <span wire:loading wire:target="save" class="flex items-center gap-2">
                        {{ __('Saving...') }}
                    </span>
                    <x-heroicon-s-arrow-path wire:loading wire:target="save" class="ml-2 h-4 w-4 animate-spin" />
                </x-danger-button>
            </div>
        </form>
    </x-modal>

    <x-modal name="delete-group-confirmation" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="delete" class="p-6">

            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Are you sure you want to delete this group?') }}
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('This action cannot be undone.') }}
            </p>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Please confirm that you want to delete this group by clicking the button below.') }}
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
