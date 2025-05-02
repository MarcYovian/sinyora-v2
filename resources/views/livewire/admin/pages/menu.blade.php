<div>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Data Menu') }}
        </h2>
    </header>

    <div class="mt-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 my-4 px-4 md:px-0 md:flex md:justify-between">
            @can('create menu')
                <x-button type="button" variant="primary" wire:click="create" class="items-center max-w-xs gap-2">
                    <x-heroicon-s-plus class="w-5 h-5" />

                    <span>{{ __('Create') }}</span>
                </x-button>
            @endcan

            <div class="w-full md:w-1/2">
                <x-search placeholder="Search menu by main menu, name, route name.." />
            </div>
        </div>

        <div class="p-6 text-gray-900 dark:text-gray-100">
            <x-table title="Data Menu" :heads="$table_heads">
                @forelse ($menus as $key => $menu)
                    <tr wire:key="menu-{{ $menu->id }}"
                        class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            {{ $key + $menus->firstItem() }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            {{ $menu->main_menu }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            {{ $menu->menu }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            {{ $menu->route_name }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            {{ $menu->icon }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            {{ $menu->sort }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            <div class="flex flex-col items-center gap-2">
                                @can('edit menu')
                                    <x-button size="sm" variant="warning" type="button"
                                        wire:click="edit({{ $menu->id }})">
                                        {{ __('Edit') }}
                                    </x-button>
                                @endcan
                                @can('delete menu')
                                    <x-button size="sm" variant="danger" type="button"
                                        wire:click="confirmDelete({{ $menu->id }})">
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
            {{ $menus->links() }}
        </div>
    </div>

    <x-modal name="menu-modal" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="save" class="p-6">

            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ $editId ? __('Edit Menu') : __('Create Menu') }}
            </h2>

            <div class="mt-6">
                <x-input-label for="main-menu" value="{{ __('Main Menu') }}" />

                <x-text-input wire:model="form.main_menu" id="main-menu" name="main-menu" type="text"
                    class="mt-1 block w-3/4" placeholder="{{ __('Main Menu') }}" />

                <x-input-error :messages="$errors->get('form.main-menu')" class="mt-2" />
            </div>

            <div class="mt-6">
                <x-input-label for="menu" value="{{ __('Menu') }}" />

                <x-text-input wire:model="form.menu" id="menu" name="menu" type="text"
                    class="mt-1 block w-3/4" placeholder="{{ __('Menu') }}" />

                <x-input-error :messages="$errors->get('form.menu')" class="mt-2" />
            </div>
            <div class="mt-6">
                <x-input-label for="route_name" value="{{ __('Route Name') }}" />

                <select
                    class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm mt-1 block w-3/4"
                    wire:model="form.route_name" id="route_name" name="route_name">
                    <option selected>{{ __('Select Route Name') }}</option>
                    @foreach ($routes as $route)
                        <option value="{{ $route }}">{{ $route }}</option>
                    @endforeach
                </select>

                <x-input-error :messages="$errors->get('form.route_name')" class="mt-2" />
            </div>
            <div class="mt-6">
                <x-input-label for="icon" value="{{ __('Icon') }}" />

                <select
                    class="select2 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm mt-1 block w-3/4"
                    id="icon" name="icon" wire:model="form.icon">
                    <option selected>{{ __('Select Icon') }}</option>
                    @foreach ($icons as $iconSvg => $iconName)
                        <option value="{{ $iconSvg }}">
                            {{ $iconName }}
                        </option>
                    @endforeach
                </select>

                <x-input-error :messages="$errors->get('form.route_name')" class="mt-2" />
            </div>
            <div class="mt-6">
                <x-input-label for="sort" value="{{ __('Sort') }}" />

                <x-text-input wire:model="form.sort" id="sort" name="sort" type="number"
                    class="mt-1 block w-3/4" placeholder="{{ __('Sort') }}" />

                <x-input-error :messages="$errors->get('form.sort')" class="mt-2" />
            </div>

            <div
                class="mt-8 pt-5 border-t border-gray-200 dark:border-gray-700 flex flex-col-reverse sm:flex-row justify-end gap-3">
                <x-secondary-button type="button" @click="$dispatch('close')"
                    class="w-full sm:w-auto justify-center px-6 py-3">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button type="submit" class="w-full sm:w-auto justify-center">
                    <span wire:loading.remove wire:target="save">
                        {{ $editId ? __('Update Menu') : __('Create Menu') }}
                    </span>
                    <span wire:loading wire:target="save" class="flex items-center gap-2">
                        {{ __('Saving...') }}
                    </span>
                    <x-heroicon-s-arrow-path wire:loading wire:target="save" class="ml-2 h-4 w-4 animate-spin" />
                </x-danger-button>
            </div>
        </form>
    </x-modal>

    <x-modal name="delete-menu-confirmation" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="delete" class="p-6">

            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Are you sure you want to delete this menu?') }}
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
