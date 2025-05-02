<div>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Data Permissions') }}
        </h2>
    </header>

    <div class="mt-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 my-4 px-4 md:px-0 md:flex md:justify-between">
            @can('create permission')
                <x-button type="button" variant="primary" wire:click="create" class="items-center max-w-xs gap-2">
                    <x-heroicon-s-plus class="w-5 h-5" />

                    <span>{{ __('Create') }}</span>
                </x-button>
            @endcan

            <div class="w-full md:w-1/2">
                <x-search placeholder="Search permissions by group, name, route name, and default.." />
            </div>
        </div>

        <div class="p-6 text-gray-900 dark:text-gray-100">
            <x-table title="Data Permissions" :heads="$table_heads">
                @forelse ($permissions as $key => $permission)
                    <tr wire:key="permission-{{ $permission->id }}"
                        class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            {{ $key + $permissions->firstItem() }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            {{ $permission->groupPermission->name }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            {{ $permission->name }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            {{ $permission->route_name }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            {{ $permission->default }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            <div class="flex flex-col items-center gap-2">
                                @can('edit permission')
                                    <x-button size="sm" variant="warning" type="button"
                                        wire:click="edit({{ $permission->id }})">
                                        {{ __('Edit') }}
                                    </x-button>
                                @endcan
                                @can('delete permission')
                                    <x-button size="sm" variant="danger" type="button"
                                        wire:click="confirmDelete({{ $permission->id }})">
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
            {{ $permissions->links() }}
        </div>
    </div>

    <x-modal name="permission-modal" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="save" class="p-4 sm:p-6">

            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">
                {{ $editId ? __('Edit Permission') : __('Create Permission') }}
            </h2>

            <div class="space-y-4 sm:space-y-0 sm:grid sm:grid-cols-2 sm:gap-4">
                <!-- Route Name Field -->
                <div class="space-y-2">
                    <x-input-label for="route_name" value="{{ __('Route Name') }}" />
                    <x-text-input wire:model="form.route_name" id="route_name" name="route_name" type="text"
                        class="block w-full" placeholder="{{ __('route_name') }}" />
                    <x-input-error :messages="$errors->get('form.route_name')" class="mt-1" />
                </div>

                <!-- Name Field -->
                <div class="space-y-2">
                    <x-input-label for="name" value="{{ __('Name') }}" />
                    <x-text-input wire:model="form.name" id="name" name="name" type="text"
                        class="block w-full" placeholder="{{ __('name') }}" />
                    <x-input-error :messages="$errors->get('form.name')" class="mt-1" />
                </div>
            </div>

            <div class="mt-4 space-y-4 sm:space-y-0 sm:grid sm:grid-cols-2 sm:gap-4">
                <!-- Group Field -->
                <div class="space-y-2">
                    <x-input-label for="group" value="{{ __('Group') }}" />
                    <select wire:model="form.group" id="group" name="group"
                        class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm">
                        <option value="">{{ __('Select Group') }}</option>
                        @foreach ($groups as $group)
                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('form.group')" class="mt-1" />
                </div>

                <!-- Default Field -->
                <div class="space-y-2">
                    <x-input-label for="default" value="{{ __('Default') }}" />
                    <select wire:model="form.default" id="default" name="default"
                        class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm">
                        <option value="">{{ __('Select Default') }}</option>
                        <option value="Default">{{ __('Default') }}</option>
                        <option value="Non-Default">{{ __('Non-Default') }}</option>
                    </select>
                    <x-input-error :messages="$errors->get('form.default')" class="mt-1" />
                </div>
            </div>

            <div
                class="mt-8 pt-5 border-t border-gray-200 dark:border-gray-700 flex flex-col-reverse sm:flex-row justify-end gap-3">
                <x-secondary-button type="button" @click="$dispatch('close')"
                    class="w-full sm:w-auto justify-center px-6 py-3">
                    {{ __('Cancel') }}
                </x-secondary-button>
                <x-primary-button type="submit" class="w-full sm:w-auto justify-center">
                    <span wire:loading.remove wire:target="save">
                        {{ $editId ? __('Update Permission') : __('Create Permission') }}
                    </span>
                    <span wire:loading wire:target="save" class="flex items-center gap-2">
                        {{ __('Saving...') }}
                    </span>
                    <x-heroicon-s-arrow-path wire:loading wire:target="save" class="ml-2 h-4 w-4 animate-spin" />
                </x-primary-button>
            </div>
        </form>
    </x-modal>

    <x-modal name="delete-permission-confirmation" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="delete" class="p-6">

            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Are you sure you want to delete this permissio?') }}
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('This action cannot be undone.') }}
            </p>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Please confirm that you want to delete this menu by clicking the button below.') }}
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
