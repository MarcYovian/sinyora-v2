<div>
    <header class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 leading-tight">
            {{ __('Manajemen Role') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Kelola peran pengguna dan hak akses terkait.') }}
        </p>
    </header>

    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
        <div class="p-4 sm:p-6 space-y-4">
            {{-- Top Actions Bar --}}
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                @can('create role')
                    <x-button type="button" variant="primary" wire:click="create" class="items-center max-w-xs gap-2">
                        <x-heroicon-s-plus class="w-5 h-5" />
                        <span>{{ __('Tambah Role') }}</span>
                    </x-button>
                @endcan

                <div class="flex-grow flex flex-col sm:flex-row items-center gap-3">
                    <div class="w-full sm:w-auto sm:flex-grow">
                        <x-text-input wire:model.live.debounce.300ms="search" type="text" class="w-full"
                            placeholder="{{ __('Cari nama role...') }}" />
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
                    @forelse ($roles as $role)
                        <div wire:key="role-card-{{ $role->id }}"
                            class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden ring-1 ring-black ring-opacity-5">
                            <div class="p-4 border-b dark:border-gray-700 flex justify-between items-center">
                                <h3 class="font-bold text-lg text-gray-800 dark:text-gray-200">
                                    {{ $role->name }}
                                </h3>
                                <x-dropdown align="right" width="48">
                                    <x-slot name="trigger">
                                        <button
                                            class="p-1 text-gray-500 dark:text-gray-400 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500">
                                            <x-heroicon-s-ellipsis-vertical class="w-5 h-5" />
                                        </button>
                                    </x-slot>
                                    <x-slot name="content">
                                        @can('edit role')
                                            <x-dropdown-link wire:click="edit({{ $role->id }})">
                                                Edit
                                            </x-dropdown-link>
                                        @endcan
                                        <x-dropdown-link wire:click="permission({{ $role->id }})">
                                            Manage Permissions
                                        </x-dropdown-link>
                                        <div class="border-t border-gray-100 dark:border-gray-600"></div>
                                        @can('delete role')
                                            <x-dropdown-link wire:click="confirmDelete({{ $role->id }})"
                                                class="text-red-600 dark:text-red-500">Delete
                                            </x-dropdown-link>
                                        @endcan
                                    </x-slot>
                                </x-dropdown>
                            </div>
                            <div class="p-4 bg-gray-50 dark:bg-gray-700/30">
                                <button wire:click="permission({{ $role->id }})" class="text-sm text-indigo-600 dark:text-indigo-400 font-medium hover:underline">
                                    {{ __('Manage Permissions') }} &rarr;
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                            {{ __('Tidak ada data role.') }}
                        </div>
                    @endforelse
                </div>

                {{-- Tampilan Desktop (Tabel) --}}
                <div class="hidden md:block">
                    <x-table title="Data Role" :heads="$table_heads">
                        @forelse ($roles as $key => $role)
                            <tr wire:key="role-table-{{ $role->id }}"
                                class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-gray-700 dark:text-gray-300">
                                    {{ $key + $roles->firstItem() }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-semibold text-gray-900 dark:text-gray-200">{{ $role->name }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center justify-end space-x-2">
                                        <x-button type="button" variant="success" size="sm" class="!px-3 !py-1.5 gap-1.5"
                                            wire:click="permission({{ $role->id }})" title="Manage Permissions">
                                            <x-heroicon-o-key class="w-4 h-4" />
                                            <span>Permissions</span>
                                        </x-button>

                                        @can('edit role')
                                            <x-button type="button" variant="warning" size="sm" class="!p-2"
                                                wire:click="edit({{ $role->id }})" title="Edit Role">
                                                <x-heroicon-o-pencil-square class="w-4 h-4" />
                                                <span class="sr-only">Edit</span>
                                            </x-button>
                                        @endcan

                                        @can('delete role')
                                            <x-button type="button" variant="danger" size="sm" class="!p-2"
                                                wire:click="confirmDelete({{ $role->id }})" title="Delete Role">
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
            {{ $roles->links() }}
        </div>
    </div>

    {{-- Create/Edit Modal --}}
    <x-modal name="role-modal" :show="$errors->isNotEmpty()" maxWidth="lg" focusable>
        <form wire:submit="save" class="p-4 sm:p-6 bg-gray-50 dark:bg-gray-900">
            <div class="flex items-start justify-between pb-4 mb-6 border-b border-gray-200 dark:border-gray-700">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{ $editId ? __('Edit Role') : __('Tambah Role Baru') }}
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

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 space-y-4">
                <!-- Name Field -->
                <div>
                    <x-input-label for="name" value="{{ __('Name') }}" />
                    <x-text-input wire:model="form.name" id="name" name="name" type="text"
                        class="mt-1 block w-full" placeholder="{{ __('e.g. Administrator') }}" />
                    <x-input-error :messages="$errors->get('form.name')" class="mt-2" />
                </div>

                <!-- Assign Default Permissions Checkbox (only for create) -->
                @if (!$editId)
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input wire:model="form.assignDefaultPermissions"
                                id="assignDefaultPermissions"
                                type="checkbox"
                                class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:bg-gray-700 dark:border-gray-600">
                        </div>
                        <div class="ml-3">
                            <label for="assignDefaultPermissions" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('Assign default permissions') }}
                            </label>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ __('Automatically assign permissions marked as "Default" to this role.') }}
                            </p>
                        </div>
                    </div>
                @endif
            </div>

            <div class="mt-8 pt-5 border-t border-gray-200 dark:border-gray-700 flex flex-col-reverse sm:flex-row justify-end gap-3">
                <x-secondary-button type="button" @click="$dispatch('close')" class="justify-center">
                    {{ __('Cancel') }}
                </x-secondary-button>
                <x-primary-button type="submit" class="justify-center">
                    <span wire:loading.remove wire:target="save">
                        {{ $editId ? __('Update Role') : __('Create Role') }}
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
    <x-modal name="delete-role-confirmation" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="delete" class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Are you sure you want to delete this role?') }}
            </h2>

            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('This action cannot be undone.') }}
            </p>

            <div class="mt-4 p-4 bg-gray-100 dark:bg-gray-800 rounded-lg">
                <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Name') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $form->name }}</dd>
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

    {{-- Manage Permissions Modal --}}
    <x-modal name="permission-modal" :show="$errors->isNotEmpty()" maxWidth="5xl" focusable>
        <form wire:submit="syncPermission" class="p-4 sm:p-6 bg-gray-50 dark:bg-gray-900 h-[85vh] flex flex-col">
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-200 dark:border-gray-700 shrink-0">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{ __('Manage Permissions') }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Role: <span class="font-semibold text-indigo-600 dark:text-indigo-400">{{ $form->role?->name }}</span>
                    </p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="bg-indigo-100 dark:bg-indigo-900 px-3 py-1 rounded-full text-xs font-semibold text-indigo-700 dark:text-indigo-300">
                        {{ count($form->selectedPermissions) }} selected
                    </div>
                    <button type="button" @click="$dispatch('close')"
                        class="p-2 -m-2 text-gray-400 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 hover:text-gray-600 dark:hover:text-gray-200 transition-all">
                        <x-heroicon-s-x-mark class="h-6 w-6" />
                    </button>
                </div>
            </div>

            <div class="flex-1 min-h-0 flex flex-col gap-4">
                 <!-- Search Filter -->
                 <div class="relative shrink-0">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <x-heroicon-s-magnifying-glass class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                    </div>
                    <input wire:model.live.debounce.300ms="searchPermission" type="text"
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white dark:bg-gray-800 dark:border-gray-700 placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:focus:ring-indigo-400 dark:focus:border-indigo-400 dark:text-gray-300 transition-colors"
                        placeholder="Search permissions...">
                </div>

                <!-- Loading State -->
                <div wire:loading.flex wire:target="searchPermission" class="items-center justify-center flex-1">
                     <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
                        <x-heroicon-s-arrow-path class="h-8 w-8 animate-spin text-indigo-500" />
                        <span class="text-lg">Searching permissions...</span>
                    </div>
                </div>

                <!-- Permissions Grid -->
                <div wire:loading.remove wire:target="searchPermission" class="overflow-y-auto pr-2 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 auto-rows-min">
                    @if ($groups)
                        @foreach ($groups as $group)
                            @php
                                $filteredPermissions = $group->permissions->filter(
                                    fn($permission) => str_contains(
                                        strtolower($permission->name),
                                        strtolower($searchPermission ?? ''),
                                    ),
                                );
                            @endphp

                            @if ($filteredPermissions->count() > 0)
                                <div wire:key="group-{{ $group->id }}"
                                    class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm hover:shadow-md transition-shadow duration-200 flex flex-col h-full">
                                    <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-800/50 rounded-t-xl flex justify-between items-center">
                                        <h3 class="font-semibold text-gray-800 dark:text-gray-200 truncate pr-2">
                                            {{ $group->name }}
                                        </h3>
                                        <button type="button" wire:click="toggleGroup({{ $group->id }})"
                                            class="text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition-colors">
                                            {{ $this->isAllSelected($group) ? 'Deselect All' : 'Select All' }}
                                        </button>
                                    </div>

                                    <div class="p-3 space-y-1">
                                        @foreach ($filteredPermissions as $permission)
                                            <label wire:key="perm-{{ $group->id }}-{{ $permission->id }}"
                                                for="permission-{{ $permission->id }}"
                                                class="flex items-start p-2 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group">
                                                <div class="flex items-center h-5">
                                                    <input wire:model="form.selectedPermissions"
                                                        type="checkbox"
                                                        id="permission-{{ $permission->id }}"
                                                        value="{{ $permission->name }}"
                                                        class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:bg-gray-700 dark:border-gray-600 transition duration-150 ease-in-out cursor-pointer">
                                                </div>
                                                <div class="ml-3 text-sm">
                                                    <span class="font-medium text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-white transition-colors">
                                                        {{ $permission->name }}
                                                    </span>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endforeach

                        @if ($this->hasNoResults())
                            <div class="col-span-full flex flex-col items-center justify-center py-12 text-center text-gray-500 dark:text-gray-400">
                                <x-heroicon-o-magnifying-glass class="h-12 w-12 mb-3 text-gray-300 dark:text-gray-600" />
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">No permissions found</h3>
                                <p class="mt-1">Try adjusting your search terms.</p>
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            <div class="mt-6 pt-5 border-t border-gray-200 dark:border-gray-700 flex flex-col-reverse sm:flex-row justify-end gap-3 shrink-0">
                <x-secondary-button type="button" @click="$dispatch('close')" class="justify-center">
                    {{ __('Cancel') }}
                </x-secondary-button>
                <x-primary-button type="submit" class="justify-center" wire:loading.attr="disabled" wire:target="syncPermission">
                    <span wire:loading.remove wire:target="syncPermission">{{ __('Save Permissions') }}</span>
                    <span wire:loading wire:target="syncPermission" class="flex items-center gap-2">
                         <x-heroicon-s-arrow-path class="h-4 w-4 animate-spin" />
                        {{ __('Saving...') }}
                    </span>
                </x-primary-button>
            </div>
        </form>
    </x-modal>
</div>
