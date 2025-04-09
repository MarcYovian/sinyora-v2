<div>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Data Menu') }}
        </h2>
    </header>

    <div class="mt-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 my-4 px-4 md:px-0 md:flex md:justify-between">
            <x-button type="button" variant="primary" wire:click="create" class="items-center max-w-xs gap-2">
                <x-heroicon-s-plus class="w-5 h-5" />

                <span>{{ __('Create') }}</span>
            </x-button>

            <div class="w-full md:w-1/2">
                <x-search placeholder="Search menu by name.." />
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
                            {{ $menu->link }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            {{ $menu->link_type }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            {{ $menu->open_in_new_tab }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            {{ $menu->icon }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            {{ $menu->sort }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            {{ $menu->is_active }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            <div class="flex flex-col items-center gap-2">
                                <x-button size="sm" variant="warning" type="button"
                                    wire:click="edit({{ $menu->id }})">
                                    {{ __('Edit') }}
                                </x-button>
                                <x-button size="sm" variant="danger" type="button"
                                    wire:click="confirmDelete({{ $menu->id }})">
                                    {{ __('Delete') }}
                                </x-button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="bg-white dark:bg-gray-800">
                        <td colspan="10"
                            class="whitespace-nowrap px-6 py-4 text-rose-700 dark:text-rose-400 text-sm text-center">
                            {{ __('No data available') }}
                        </td>
                    </tr>
                @endforelse
            </x-table>
        </div>
    </div>

    <x-modal name="menu-modal" :show="$errors->isNotEmpty()" maxWidth="2xl" focusable>
        <form wire:submit="save" class="p-4 sm:p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    {{ $editId ? __('Edit Menu') : __('Create Menu') }}
                </h2>
                <button type="button" x-on:click="$dispatch('close')" class="text-gray-400 hover:text-gray-500">
                    <x-heroicon-s-x-circle class="h-6 w-6" />
                </button>
            </div>

            <div class="mt-6 space-y-4">
                <!-- Main Menu and Menu Fields -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="main-menu" value="{{ __('Main Menu') }}" />
                        <x-text-input wire:model="form.main_menu" id="main-menu" name="main-menu" type="text"
                            class="mt-1 block w-full" placeholder="{{ __('e.g. Main Navigation') }}" />
                        <x-input-error :messages="$errors->get('form.main_menu')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="menu" value="{{ __('Menu Name') }}" />
                        <x-text-input wire:model="form.menu" id="menu" name="menu" type="text"
                            class="mt-1 block w-full" placeholder="{{ __('e.g. Home, About Us') }}" />
                        <x-input-error :messages="$errors->get('form.menu')" class="mt-2" />
                    </div>
                </div>

                <!-- Link Type and Dynamic Link Field -->
                <div>
                    <x-input-label for="link_type" value="{{ __('Link Type') }}" />
                    <select wire:model.live="linkType" id="link_type" name="link_type"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-indigo-600 dark:focus:ring-indigo-600">
                        <option value="">{{ __('Select Link Type') }}</option>
                        <option value="route">{{ __('Route Name') }}</option>
                        <option value="url">{{ __('External URL') }}</option>
                        <option value="anchor">{{ __('Page Anchor') }}</option>
                    </select>
                    <x-input-error :messages="$errors->get('linkType')" class="mt-2" />
                </div>

                <!-- Dynamic Link Input Based on Type -->
                @if ($linkType)
                    <div wire:key="link-field-{{ $form->link_type }}">
                        <x-input-label for="link" value="{{ __('Link') }}" />

                        @if ($linkType == 'route')
                            <select wire:model="form.link" id="link" name="link"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-indigo-600 dark:focus:ring-indigo-600">
                                <option value="">{{ __('Select Route') }}</option>
                                @foreach ($routes as $route)
                                    <option value="{{ $route }}">{{ $route }}</option>
                                @endforeach
                            </select>
                        @else
                            <x-text-input wire:model="form.link" id="link" name="link" type="text"
                                class="mt-1 block w-full"
                                placeholder="{{ $linkType == 'url' ? 'https://example.com' : '#section-id' }}" />
                        @endif
                        <x-input-error :messages="$errors->get('form.link')" class="mt-2" />
                    </div>
                @endif

                <!-- Anchor Field (Conditional) -->
                @if ($linkType && $linkType != 'anchor')
                    <div>
                        <div class="flex items-center">
                            <x-input-label for="link_anchor" value="{{ __('Add Anchor') }}" class="mr-2" />
                            <x-checkbox wire:model.live="useAnchor" id="use_anchor" name="use_anchor" />
                        </div>

                        @if ($useAnchor)
                            <x-text-input wire:model="form.link_anchor" id="link_anchor" name="link_anchor"
                                type="text" class="mt-1 block w-full" placeholder="#section-id" />
                            <x-input-error :messages="$errors->get('form.link_anchor')" class="mt-2" />
                        @endif
                    </div>
                @endif

                <!-- Icon Selector with Preview -->
                <div>
                    <x-input-label for="icon" value="{{ __('Icon') }}" />
                    <div class="relative mt-1">
                        <select wire:model="form.icon" id="icon" name="icon"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-indigo-600 dark:focus:ring-indigo-600">
                            <option value="">{{ __('Select Icon') }}</option>
                            @foreach ($icons as $iconSvg => $iconName)
                                <option value="{{ $iconSvg }}">{{ $iconName }}</option>
                            @endforeach
                        </select>
                        @if ($form->icon)
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                {{-- <x-dynamic-component :component="$form->icon" class="h-5 w-5 text-gray-400" /> --}}
                            </div>
                        @endif
                    </div>
                    <x-input-error :messages="$errors->get('form.icon')" class="mt-2" />
                </div>

                <!-- Toggle Options -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="open_in_new_tab" value="{{ __('Open Link In') }}" />
                        <select wire:model="form.open_in_new_tab" id="open_in_new_tab" name="open_in_new_tab"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-indigo-600 dark:focus:ring-indigo-600">
                            <option value="false">{{ __('Same Tab') }}</option>
                            <option value="true">{{ __('New Tab') }}</option>
                        </select>
                        <x-input-error :messages="$errors->get('form.open_in_new_tab')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="is_active" value="{{ __('Status') }}" />
                        <select wire:model="form.is_active" id="is_active" name="is_active"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-indigo-600 dark:focus:ring-indigo-600">
                            <option value="true">{{ __('Active') }}</option>
                            <option value="false">{{ __('Inactive') }}</option>
                        </select>
                        <x-input-error :messages="$errors->get('form.is_active')" class="mt-2" />
                    </div>
                </div>

                <!-- Sort Field -->
                <div>
                    <x-input-label for="sort" value="{{ __('Sort Order') }}" />
                    <x-text-input wire:model="form.sort" id="sort" name="sort" type="number"
                        class="mt-1 block w-full" placeholder="0" min="0" />
                    <x-input-error :messages="$errors->get('form.sort')" class="mt-2" />
                </div>
            </div>

            <div class="mt-6 sm:flex sm:justify-end space-x-3">
                <x-secondary-button x-on:click="$dispatch('close')" type="button" class="w-full sm:w-auto">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-primary-button type="submit" wire:loading.attr="disabled"
                    class="w-full sm:w-auto ml-0 mt-3 sm:ml-3 sm:mt-0">
                    <span wire:loading.remove wire:target="save">{{ $editId ? __('Update') : __('Create') }}</span>
                    <span wire:loading wire:target="save">{{ __('Processing...') }}</span>
                </x-primary-button>
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
