<div>
    <header class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 leading-tight">
            {{ __('Manajemen Menu') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Kelola menu aplikasi, navigasi, dan konfigurasi rute.
        </p>
    </header>

    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
        <div class="p-4 sm:p-6 space-y-4">
            {{-- Top Actions Bar --}}
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                @can('create menu')
                    <x-button type="button" variant="primary" wire:click="create" class="items-center max-w-xs gap-2">
                        <x-heroicon-s-plus class="w-5 h-5" />
                        <span>{{ __('Tambah Menu') }}</span>
                    </x-button>
                @endcan

                <div class="flex-grow flex flex-col sm:flex-row items-center gap-3">
                    <div class="w-full sm:w-auto sm:flex-grow">
                        <x-text-input wire:model.live.debounce.300ms="search" type="text" class="w-full"
                            placeholder="{{ __('Cari menu, route, atau main menu...') }}" />
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
                    @forelse ($menus as $menu)
                        <div wire:key="menu-card-{{ $menu->id }}"
                            class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden ring-1 ring-black ring-opacity-5">
                            <div class="p-4 border-b dark:border-gray-700">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-center gap-3">
                                        @if ($menu->icon)
                                             <div class="text-indigo-600 dark:text-indigo-400">
                                                <x-dynamic-component :component="(\Illuminate\Support\Str::startsWith($menu->icon, 'c-') ? 'heroicon-' : 'heroicon-s-') . $menu->icon" class="w-6 h-6" />
                                            </div>
                                        @endif
                                        <h3 class="font-bold text-lg text-gray-800 dark:text-gray-200">{{ $menu->menu }}
                                        </h3>
                                    </div>

                                    <x-dropdown align="right" width="48">
                                        <x-slot name="trigger">
                                            <button
                                                class="p-1 text-gray-500 dark:text-gray-400 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500">
                                                <x-heroicon-s-ellipsis-vertical class="w-5 h-5" />
                                            </button>
                                        </x-slot>
                                        <x-slot name="content">
                                            @can('edit menu')
                                                <x-dropdown-link wire:click="edit({{ $menu->id }})">
                                                    Edit
                                                </x-dropdown-link>
                                            @endcan
                                            <div class="border-t border-gray-100 dark:border-gray-600"></div>
                                            @can('delete menu')
                                                <x-dropdown-link wire:click="confirmDelete({{ $menu->id }})"
                                                    class="text-red-600 dark:text-red-500">Delete
                                                </x-dropdown-link>
                                            @endcan
                                        </x-slot>
                                    </x-dropdown>
                                </div>
                            </div>
                            <div class="p-4 space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">Main Menu:</span>
                                    <span class="font-medium text-gray-700 dark:text-gray-300">{{ $menu->main_menu }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">Route:</span>
                                    <span class="font-mono text-xs bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded text-gray-600 dark:text-gray-300">{{ $menu->route_name }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">Sort Order:</span>
                                    <span class="font-medium text-gray-700 dark:text-gray-300">{{ $menu->sort }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                            {{ __('Tidak ada data menu.') }}
                        </div>
                    @endforelse
                </div>

                {{-- Tampilan Desktop (Tabel) --}}
                <div class="hidden md:block">
                    <x-table title="Data Menu" :heads="$table_heads">
                        @forelse ($menus as $key => $menu)
                            <tr wire:key="menu-table-{{ $menu->id }}"
                                class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-gray-700 dark:text-gray-300">
                                    {{ $key + $menus->firstItem() }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-300">
                                    {{ $menu->main_menu }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-semibold text-gray-900 dark:text-gray-200">{{ $menu->menu }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-300 font-mono text-xs">
                                    {{ $menu->route_name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-300">
                                    <div class="flex items-center gap-2">
                                        @if ($menu->icon)
                                            <div class="text-gray-500 dark:text-gray-400">
                                                 <x-dynamic-component :component="(\Illuminate\Support\Str::startsWith($menu->icon, 'c-') ? 'heroicon-' : 'heroicon-s-') . $menu->icon" class="w-5 h-5" />
                                            </div>
                                            <span class="text-xs">{{ $menu->icon }}</span>
                                        @else
                                            -
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-300">
                                    {{ $menu->sort }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center justify-end space-x-1">
                                        @can('edit menu')
                                            <x-button type="button" variant="warning" size="sm" class="!p-2"
                                                wire:click="edit({{ $menu->id }})" title="Edit Menu">
                                                <x-heroicon-o-pencil-square class="w-4 h-4" />
                                                <span class="sr-only">Edit</span>
                                            </x-button>
                                        @endcan

                                        @can('delete menu')
                                            <x-button type="button" variant="danger" size="sm" class="!p-2"
                                                wire:click="confirmDelete({{ $menu->id }})" title="Hapus Menu">
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
            {{ $menus->links() }}
        </div>
    </div>

    {{-- Create/Edit Modal --}}
    <x-modal name="menu-modal" :show="$errors->isNotEmpty()" maxWidth="2xl" focusable>
        <form wire:submit="save" class="p-4 sm:p-6 bg-gray-50 dark:bg-gray-900">
            <div class="flex items-start justify-between pb-4 mb-6 border-b border-gray-200 dark:border-gray-700">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{ $editId ? __('Edit Menu') : __('Tambah Menu Baru') }}
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

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 space-y-5">
                {{-- Main Menu --}}
                <div>
                    <x-input-label for="main-menu" value="{{ __('Main Menu') }}" />
                    <x-text-input wire:model="form.main_menu" id="main-menu" type="text"
                        class="mt-1 block w-full" placeholder="e.g. Master Data" />
                    <x-input-error :messages="$errors->get('form.main_menu')" class="mt-2" />
                </div>

                {{-- Menu Name --}}
                <div>
                    <x-input-label for="menu" value="{{ __('Menu Name') }}" />
                    <x-text-input wire:model="form.menu" id="menu" type="text"
                        class="mt-1 block w-full" placeholder="e.g. Users" />
                    <x-input-error :messages="$errors->get('form.menu')" class="mt-2" />
                </div>

                {{-- Route Name --}}
                <div>
                    <x-input-label for="route_name" value="{{ __('Route Name') }}" />
                    <select
                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                        wire:model="form.route_name" id="route_name">
                        <option value="">{{ __('Select Route') }}</option>
                        @foreach ($routes as $route)
                            <option value="{{ $route }}">{{ $route }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('form.route_name')" class="mt-2" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    {{-- Icon --}}
                    {{-- Icon Custom Select --}}
                    <div x-data="{
                        open: false,
                        search: '',
                        selected: @entangle('form.icon')
                    }" class="relative">
                        <x-input-label for="icon" value="{{ __('Icon') }}" />

                        <button type="button" @click="open = !open"
                            class="relative w-full cursor-default rounded-md bg-white dark:bg-gray-900 py-2 pl-3 pr-10 text-left text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 sm:text-sm sm:leading-6 mt-1 border border-gray-300 dark:border-gray-700 h-[42px]"
                            aria-haspopup="listbox" :aria-expanded="open" aria-labelledby="listbox-label">
                            <span class="flex items-center gap-2 truncate">
                                @if ($form->icon)
                                    <x-dynamic-component :component="(\Illuminate\Support\Str::startsWith($form->icon, 'c-') ? 'heroicon-' : 'heroicon-s-') . $form->icon"
                                        class="h-5 w-5 text-gray-500 dark:text-gray-400" />
                                    <span>{{ $icons[$form->icon] ?? $form->icon }}</span>
                                @else
                                    <span class="text-gray-500 dark:text-gray-400">{{ __('Select Icon') }}</span>
                                @endif
                            </span>
                            <span class="pointer-events-none absolute inset-y-0 right-0 ml-3 flex items-center pr-2">
                                <x-heroicon-m-chevron-up-down class="h-5 w-5 text-gray-400" />
                            </span>
                        </button>

                        <ul x-show="open" @click.away="open = false" x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                            class="absolute z-50 mt-1 max-h-56 w-full overflow-auto rounded-md bg-white dark:bg-gray-900 py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm"
                            style="display: none;">

                            <div class="sticky top-0 z-10 bg-white dark:bg-gray-900 px-2 py-1.5 border-b border-gray-200 dark:border-gray-700">
                                <input x-model="search" type="text"
                                    class="w-full border-gray-300 dark:border-gray-700 rounded-md p-1.5 text-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400"
                                    placeholder="Search icon...">
                            </div>

                            @foreach ($icons as $iconSvg => $iconName)
                                <li class="text-gray-900 dark:text-gray-100 relative cursor-default select-none py-2 pl-3 pr-9 hover:bg-indigo-600 hover:text-white group"
                                    role="option"
                                    @click="$wire.set('form.icon', '{{ $iconSvg }}'); open = false; search = ''"
                                    x-show="search === '' || '{{ strtolower($iconName) }}'.includes(search.toLowerCase())">
                                    <div class="flex items-center">
                                        <x-dynamic-component :component="(\Illuminate\Support\Str::startsWith($iconSvg, 'c-') ? 'heroicon-' : 'heroicon-s-') . $iconSvg"
                                            class="h-5 w-5 flex-shrink-0 mr-3 text-gray-400 group-hover:text-white" />
                                        <span class="font-normal block truncate"
                                            :class="{ 'font-semibold': selected === '{{ $iconSvg }}', 'font-normal': selected !== '{{ $iconSvg }}' }">
                                            {{ $iconName }}
                                        </span>
                                    </div>

                                    <span x-show="selected === '{{ $iconSvg }}'"
                                        class="text-indigo-600 group-hover:text-white absolute inset-y-0 right-0 flex items-center pr-4">
                                        <x-heroicon-s-check class="h-5 w-5" />
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                        <x-input-error :messages="$errors->get('form.icon')" class="mt-2" />
                    </div>

                    {{-- Sort --}}
                    <div>
                        <x-input-label for="sort" value="{{ __('Sort Order') }}" />
                        <x-text-input wire:model="form.sort" id="sort" type="number"
                            class="mt-1 block w-full" placeholder="e.g. 1" />
                        <x-input-error :messages="$errors->get('form.sort')" class="mt-2" />
                    </div>
                </div>
            </div>

            <div class="mt-8 pt-5 border-t border-gray-200 dark:border-gray-700 flex flex-col-reverse sm:flex-row justify-end gap-3">
                <x-secondary-button type="button" @click="$dispatch('close')" class="justify-center">
                    {{ __('Cancel') }}
                </x-secondary-button>
                <x-primary-button type="submit" class="justify-center">
                    <span wire:loading.remove wire:target="save">
                        {{ $editId ? __('Update Menu') : __('Create Menu') }}
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
    <x-modal name="delete-menu-confirmation" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="delete" class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Are you sure you want to delete this menu?') }}
            </h2>

            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('This action cannot be undone.') }}
            </p>

            <div class="mt-4 p-4 bg-gray-100 dark:bg-gray-800 rounded-lg">
                <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Menu') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $form->menu }}</dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Main Menu') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $form->main_menu }}</dd>
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
