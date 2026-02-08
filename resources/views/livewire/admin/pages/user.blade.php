<div>
    <header class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 leading-tight">
            {{ __('Manajemen User') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Kelola data pengguna, role, dan hak akses dalam sistem.') }}
        </p>
    </header>

    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
        <div class="p-4 sm:p-6 space-y-4">
            {{-- Top Actions Bar --}}
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                @can('create user')
                    <x-button type="button" variant="primary" wire:click="create" class="items-center max-w-xs gap-2">
                        <x-heroicon-s-plus class="w-5 h-5" />
                        <span>{{ __('Tambah User') }}</span>
                    </x-button>
                @endcan

                <div class="flex-grow flex flex-col sm:flex-row items-center gap-3">
                    <div class="w-full sm:w-auto sm:flex-grow">
                        <x-text-input wire:model.live.debounce.300ms="search" type="text" class="w-full"
                            placeholder="{{ __('Cari nama, username, email...') }}" />
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
                    @forelse ($users as $user)
                        <div wire:key="user-card-{{ $user->id }}"
                            class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden ring-1 ring-black ring-opacity-5">
                            <div class="p-4 border-b dark:border-gray-700 flex justify-between items-start">
                                <div>
                                    <h3 class="font-bold text-lg text-gray-800 dark:text-gray-200">
                                        {{ $user->name }}
                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ '@' . $user->username }}
                                    </p>
                                </div>
                                <x-dropdown align="right" width="48">
                                    <x-slot name="trigger">
                                        <button
                                            class="p-1 text-gray-500 dark:text-gray-400 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500">
                                            <x-heroicon-s-ellipsis-vertical class="w-5 h-5" />
                                        </button>
                                    </x-slot>
                                    <x-slot name="content">
                                        @can('edit user')
                                            <x-dropdown-link wire:click="edit({{ $user->id }})">
                                                Edit Data
                                            </x-dropdown-link>
                                        @endcan
                                        @can('reset password user')
                                            <x-dropdown-link wire:click="confirmResetPassword({{ $user->id }})">
                                                Reset Password
                                            </x-dropdown-link>
                                        @endcan
                                        <x-dropdown-link wire:click="permission({{ $user->id }})">
                                            Role Permission
                                        </x-dropdown-link>
                                        <div class="border-t border-gray-100 dark:border-gray-600"></div>
                                        @can('delete user')
                                            <x-dropdown-link wire:click="confirmDelete({{ $user->id }})"
                                                class="text-red-600 dark:text-red-500">Delete User
                                            </x-dropdown-link>
                                        @endcan
                                    </x-slot>
                                </x-dropdown>
                            </div>
                            <div class="p-4 space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">Email:</span>
                                    <span class="text-gray-700 dark:text-gray-300 font-medium">{{ $user->email }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">Verified:</span>
                                    @if ($user->email_verified_at)
                                        <span class="text-green-600 dark:text-green-400 font-medium">
                                            {{ \Carbon\Carbon::parse($user->email_verified_at)->diffForHumans() }}
                                        </span>
                                    @else
                                        <span class="text-rose-500 dark:text-rose-400 font-medium">{{ __('Not Verified') }}</span>
                                    @endif
                                </div>
                                <div class="pt-2">
                                    <button wire:click="permission({{ $user->id }})" class="text-indigo-600 dark:text-indigo-400 font-medium hover:underline flex items-center gap-1">
                                        <x-heroicon-o-shield-check class="w-4 h-4" />
                                        <span>Manage Access</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                            {{ __('Tidak ada data user.') }}
                        </div>
                    @endforelse
                </div>

                {{-- Tampilan Desktop (Tabel) --}}
                <div class="hidden md:block">
                    <x-table title="Data User" :heads="$table_heads">
                        @forelse ($users as $key => $user)
                            <tr wire:key="user-table-{{ $user->id }}"
                                class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-gray-700 dark:text-gray-300">
                                    {{ $key + $users->firstItem() }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-semibold text-gray-900 dark:text-gray-200">{{ $user->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-300">
                                    {{ $user->username }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-300">
                                    {{ $user->email }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($user->email_verified_at)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200" title="{{ $user->email_verified_at }}">
                                            Verified
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                            Not Verified
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="flex items-center justify-end space-x-1">
                                        <x-button type="button" variant="success" size="sm" class="!p-2"
                                            wire:click="permission({{ $user->id }})" title="Role & Permission">
                                            <x-heroicon-o-shield-check class="w-4 h-4" />
                                            <span class="sr-only">Permissions</span>
                                        </x-button>

                                        @can('edit user')
                                            <x-button type="button" variant="warning" size="sm" class="!p-2"
                                                wire:click="edit({{ $user->id }})" title="Edit User">
                                                <x-heroicon-o-pencil-square class="w-4 h-4" />
                                                <span class="sr-only">Edit</span>
                                            </x-button>
                                        @endcan

                                        @can('reset password user')
                                            <x-button type="button" variant="primary" size="sm" class="!p-2"
                                                wire:click="confirmResetPassword({{ $user->id }})" title="Reset Password">
                                                <x-heroicon-o-key class="w-4 h-4" />
                                                <span class="sr-only">Reset Password</span>
                                            </x-button>
                                        @endcan

                                        @can('delete user')
                                            <x-button type="button" variant="danger" size="sm" class="!p-2"
                                                wire:click="confirmDelete({{ $user->id }})" title="Delete User">
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
            {{ $users->links() }}
        </div>
    </div>

    {{-- Create/Edit Modal --}}
    <x-modal name="user-modal" :show="$errors->isNotEmpty()" maxWidth="lg" focusable>
        <form wire:submit="save" class="p-4 sm:p-6 bg-gray-50 dark:bg-gray-900">
            <div class="flex items-start justify-between pb-4 mb-6 border-b border-gray-200 dark:border-gray-700">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{ $editId ? __('Edit User') : __('Tambah User Baru') }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        {{ __('Silakan lengkapi informasi pengguna di bawah ini.') }}
                    </p>
                </div>
                <button type="button" @click="$dispatch('close')"
                    class="p-2 -m-2 text-gray-400 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 hover:text-gray-600 dark:hover:text-gray-200 transition-all">
                    <x-heroicon-s-x-mark class="h-6 w-6" />
                </button>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 space-y-5">
                <!-- Name Field -->
                <div>
                    <x-input-label for="name" value="{{ __('Name') }}" />
                    <x-text-input wire:model="form.name" id="name" name="name" type="text"
                        class="mt-1 block w-full" placeholder="{{ __('e.g. John Doe') }}" />
                    <x-input-error :messages="$errors->get('form.name')" class="mt-2" />
                </div>

                <!-- Username Field -->
                <div>
                    <x-input-label for="username" value="{{ __('Username') }}" />
                    <x-text-input wire:model="form.username" id="username" name="username" type="text"
                        class="mt-1 block w-full" placeholder="{{ __('e.g. johndoe') }}" />
                    <x-input-error :messages="$errors->get('form.username')" class="mt-2" />
                </div>

                <!-- Email Field -->
                <div>
                    <x-input-label for="email" value="{{ __('Email') }}" />
                    <x-text-input wire:model="form.email" id="email" name="email" type="email"
                        class="mt-1 block w-full" placeholder="{{ __('e.g. john@example.com') }}" />
                    <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
                </div>

                <!-- Role Selection -->
                <div>
                    <x-input-label for="role" value="{{ __('Role') }}" />
                    <select
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-indigo-600 dark:focus:ring-indigo-600"
                        wire:model="form.role" id="role" name="role">
                        <option value="">{{ __('Select Role') }}</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('form.role')" class="mt-2" />
                </div>

                @if (!$editId)
                    <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
                        <div class="flex gap-3">
                            <x-heroicon-o-information-circle class="w-5 h-5 text-yellow-600 dark:text-yellow-400 shrink-0" />
                            <div>
                                <h4 class="text-sm font-medium text-yellow-800 dark:text-yellow-300">Default Password</h4>
                                <p class="text-sm text-yellow-700 dark:text-yellow-400 mt-1">
                                    {{ __('Password awal user baru adalah') }} <span class="font-mono font-bold bg-yellow-100 dark:bg-yellow-900 px-1 rounded">password</span>.
                                    {{ __('User wajib menggantinya saat login pertama.') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Assign Default Permissions Checkbox -->
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input wire:model="form.assignDefaultPermissions"
                                id="assignDefaultPermissionsUser"
                                type="checkbox"
                                class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:bg-gray-700 dark:border-gray-600">
                        </div>
                        <div class="ml-3">
                            <label for="assignDefaultPermissionsUser" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('Assign default permissions') }}
                            </label>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ __('Automatically assign permissions marked as "Default" to this user.') }}
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
                        {{ $editId ? __('Update User') : __('Create User') }}
                    </span>
                    <span wire:loading wire:target="save" class="flex items-center gap-2">
                        <x-heroicon-s-arrow-path class="h-4 w-4 animate-spin" />
                        <span>{{ __('Saving...') }}</span>
                    </span>
                </x-primary-button>
            </div>
        </form>
    </x-modal>

    {{-- Reset Password Confirmation Modal --}}
    <x-modal name="reset-password-confirmation" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="resetPassword" class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Reset Password User?') }}
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Password akan dikembalikan ke default password sistem.') }}
            </p>
            
            <div class="mt-4 p-4 bg-gray-100 dark:bg-gray-800 rounded-lg flex items-center gap-3">
                <x-heroicon-o-key class="w-5 h-5 text-gray-500" />
                <div>
                    <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider block">Default Password</span>
                    <span class="font-mono font-bold text-lg text-gray-800 dark:text-gray-200">password</span>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button type="button" x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-primary-button type="submit" class="bg-indigo-600 hover:bg-indigo-700">
                    <span wire:loading.remove wire:target="resetPassword">{{ __('Reset Password') }}</span>
                    <span wire:loading wire:target="resetPassword">{{ __('Processing...') }}</span>
                </x-primary-button>
            </div>
        </form>
    </x-modal>

    {{-- Delete Confirmation Modal --}}
    <x-modal name="delete-user-confirmation" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="delete" class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Delete User Account?') }}
            </h2>

            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('This action cannot be undone. All data associated with this user will be permanently deleted.') }}
            </p>

            <div class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-100 dark:border-red-800">
                <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                    <div class="sm:col-span-1">
                        <dt class="text-xs font-medium text-red-500 dark:text-red-400 uppercase">{{ __('Name') }}</dt>
                        <dd class="mt-1 text-sm font-semibold text-red-900 dark:text-red-200">{{ $form->name }}</dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-xs font-medium text-red-500 dark:text-red-400 uppercase">{{ __('Email') }}</dt>
                        <dd class="mt-1 text-sm font-mono text-red-900 dark:text-red-200">{{ $form->email }}</dd>
                    </div>
                </dl>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button type="button" x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button>
                    {{ __('Delete User') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>

    {{-- Manage Permissions Modal --}}
    <x-modal name="permission-modal" :show="$errors->isNotEmpty()" maxWidth="5xl" focusable>
        @if ($form->user)
            <form wire:submit="syncPermission" class="p-4 sm:p-6 bg-gray-50 dark:bg-gray-900 h-[85vh] flex flex-col">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-200 dark:border-gray-700 shrink-0">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            {{ __('Manage Permissions') }}
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            User: <span class="font-semibold text-indigo-600 dark:text-indigo-400">{{ $form->user->name }}</span>
                        </p>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="bg-indigo-100 dark:bg-indigo-900 px-3 py-1 rounded-full text-xs font-semibold text-indigo-700 dark:text-indigo-300">
                            {{ count($form->directPermissions) }} direct permissions
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
                                            <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                                {{ $filteredPermissions->count() }}
                                            </span>
                                        </div>

                                        <div class="p-3 space-y-1">
                                            @foreach ($filteredPermissions as $permission)
                                                <div class="p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                                    <div class="flex items-start justify-between gap-2">
                                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300 break-all" title="{{ $permission->name }}">
                                                            {{ $permission->name }}
                                                        </span>
                                                        <div class="flex gap-1 shrink-0">
                                                            {{-- Role Permission Status --}}
                                                            <div class="relative group/tooltip">
                                                                <div class="w-5 h-5 flex items-center justify-center rounded {{ in_array($permission->name, $form->rolePermissions) ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-600 dark:text-indigo-400' : 'bg-gray-100 dark:bg-gray-800 text-gray-300 dark:text-gray-600' }}">
                                                                    <x-heroicon-s-user-group class="w-3.5 h-3.5" />
                                                                </div>
                                                                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 rounded opacity-0 group-hover/tooltip:opacity-100 transition-opacity whitespace-nowrap pointer-events-none z-10">
                                                                    {{ in_array($permission->name, $form->rolePermissions) ? 'Inherited from Role' : 'Not in Role' }}
                                                                </div>
                                                            </div>

                                                            {{-- Direct Permission Checkbox --}}
                                                            <div class="relative group/tooltip">
                                                                <input wire:model="form.directPermissions"
                                                                    type="checkbox"
                                                                    value="{{ $permission->name }}"
                                                                    class="w-5 h-5 text-green-600 border-gray-300 rounded focus:ring-green-500 dark:focus:ring-green-600 dark:bg-gray-700 dark:border-gray-600 transition duration-150 ease-in-out cursor-pointer">
                                                                <div class="absolute bottom-full right-0 mb-2 px-2 py-1 text-xs text-white bg-gray-900 rounded opacity-0 group-hover/tooltip:opacity-100 transition-opacity whitespace-nowrap pointer-events-none z-10">
                                                                    Direct Access
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
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
        @endif
    </x-modal>
</div>
