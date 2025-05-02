<div>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Data Users') }}
        </h2>
    </header>

    <div class="mt-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 my-4 px-4 md:px-0 md:flex md:justify-between">
            @can('create user')
                <x-button type="button" variant="primary" wire:click="create" class="items-center max-w-xs gap-2">
                    <x-heroicon-s-plus class="w-5 h-5" />
                    <span>{{ __('Create') }}</span>
                </x-button>
            @endcan

            <div class="w-full md:w-1/2">
                <x-search placeholder="Search users by name, username, or email.." />
            </div>
        </div>

        <div class="p-6 text-gray-900 dark:text-gray-100">
            <x-table title="Data Users" :heads="$table_heads">
                @forelse ($users as $key => $user)
                    <tr wire:key="user-{{ $user->id }}"
                        class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            {{ $key + $users->firstItem() }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            {{ $user->name }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            {{ $user->username }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            {{ $user->email }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            @if ($user->email_verified_at)
                                {{ Carbon\Carbon::parse($user->email_verified_at)->translatedFormat('l, d F Y H:i:s') }}
                            @else
                                <span class="text-rose-500 dark:text-rose-400">{{ __('Not verified') }}</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            <div class="flex flex-col items-center gap-2">
                                @can('edit user')
                                    <x-button size="sm" variant="warning" type="button"
                                        wire:click="edit({{ $user->id }})">
                                        {{ __('Edit') }}
                                    </x-button>
                                @endcan
                                @can('delete user')
                                    <x-button size="sm" variant="danger" type="button"
                                        wire:click="confirmDelete({{ $user->id }})">
                                        {{ __('Delete') }}
                                    </x-button>
                                @endcan
                                @can('reset password user')
                                    <x-button size="sm" variant="primary" type="button"
                                        wire:click="confirmResetPassword({{ $user->id }})">
                                        {{ __('Reset Password') }}
                                    </x-button>
                                @endcan
                                <x-button size="sm" variant="success" type="button"
                                    wire:click="permission({{ $user->id }})">
                                    {{ __('Role Permission') }}
                                </x-button>
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
    </div>

    <x-modal name="user-modal" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="save" class="p-6">

            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ $editId ? __('Edit Data User') : __('Create Data User') }}
            </h2>

            <div class="mt-6">
                <x-input-label for="name" value="{{ __('Name') }}" />

                <x-text-input wire:model="form.name" id="name" name="name" type="text"
                    class="mt-1 block w-3/4" placeholder="{{ __('Name') }}" />

                <x-input-error :messages="$errors->get('form.name')" class="mt-2" />
            </div>

            <div class="mt-6">
                <x-input-label for="username" value="{{ __('Username') }}" />

                <x-text-input wire:model="form.username" id="username" name="username" type="text"
                    class="mt-1 block w-3/4" placeholder="{{ __('Name') }}" />

                <x-input-error :messages="$errors->get('form.username')" class="mt-2" />
            </div>

            <div class="mt-6">
                <x-input-label for="email" value="{{ __('Email') }}" />

                <x-text-input wire:model="form.email" id="email" name="email" type="email"
                    class="mt-1 block w-3/4" placeholder="{{ __('Email') }}" />

                <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
            </div>

            <div class="mt-6">
                <x-input-label for="role" value="{{ __('Role') }}" />

                <select
                    class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm mt-1 block w-3/4"
                    wire:model="form.role" id="role" name="role">
                    <option selected>{{ __('Select Role') }}</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->name }}">{{ $role->name }}</option>
                    @endforeach
                </select>

                <x-input-error :messages="$errors->get('form.role')" class="mt-2" />
            </div>

            @if (!$editId)
                <div class="mt-6">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Default Password: ') }}
                        <span class="font-bold text-red-600 dark:text-red-500">{{ __('password') }}</span><br>
                        {{ __('This password will be used for the first login, please change it after logging in.') }}
                    </p>
                </div>
            @endif

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
                    <span wire:loading.remove wire:target="save">{{ $editId ? __('Update User') : __('Create User') }}
                    </span>
                    <span wire:loading wire:target="save">{{ __('Saving...') }}</span>
                </x-primary-button>
            </div>
        </form>
    </x-modal>

    <x-modal name="delete-user-confirmation" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="delete" class="p-6">

            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Are you sure you want to delete this user?') }}
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('This action cannot be undone.') }}
            </p>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Please confirm that you want to delete this user by clicking the button below.') }}
            </p>

            <div
                class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 border-t pt-4 dark:border-gray-700">
                <x-secondary-button x-on:click="$dispatch('close')" class="w-full sm:w-auto justify-center">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-primary-button type="submit" class="w-full sm:w-auto justify-center" wire:loading.attr="disabled"
                    wire:target="delete">
                    <x-heroicon-s-check class="w-5 h-5 mr-2" wire:loading.remove wire:target="delete" />
                    <div wire:loading wire:target="delete"
                        class="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2 dark:border-gray-400">
                    </div>
                    <span wire:loading.remove wire:target="delete">{{ __('Delete User') }}
                    </span>
                    <span wire:loading wire:target="delete">{{ __('Saving...') }}</span>
                </x-primary-button>
            </div>
        </form>
    </x-modal>

    <x-modal name="reset-password-confirmation" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="resetPassword" class="p-6">

            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Are you sure you want to reset password?') }}
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('This action will reset the password to the default password.') }}
            </p>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Default Password: ') }}
                <span class="font-bold text-red-600 dark:text-red-500">{{ __('password') }}</span>
            </p>

            <div
                class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 border-t pt-4 dark:border-gray-700">
                <x-secondary-button x-on:click="$dispatch('close')" class="w-full sm:w-auto justify-center">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-primary-button type="submit" class="w-full sm:w-auto justify-center" wire:loading.attr="disabled"
                    wire:target="resetPassword">
                    <x-heroicon-s-check class="w-5 h-5 mr-2" wire:loading.remove wire:target="resetPassword" />
                    <div wire:loading wire:target="resetPassword"
                        class="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2 dark:border-gray-400">
                    </div>
                    <span wire:loading.remove wire:target="resetPassword">{{ __('Reset Password') }}
                    </span>
                    <span wire:loading wire:target="resetPassword">{{ __('Saving...') }}</span>
                </x-primary-button>
            </div>
        </form>
    </x-modal>

    <x-modal name="permission-modal" :show="$errors->isNotEmpty()" maxWidth="5xl" focusable>
        @if ($form->user)
            <form wire:submit="syncPermission" class="p-4 sm:p-6">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            {{ __('Manage Permissions') }}
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            User:
                            <span class="font-medium text-indigo-600 dark:text-indigo-400">
                                {{ $form->user->name }}
                            </span>
                        </p>
                    </div>
                    <div
                        class="bg-indigo-50 dark:bg-indigo-900/30 px-3 py-1 rounded-full text-xs text-indigo-800 dark:text-indigo-200">
                        {{ count($form->directPermissions) }} direct permissions
                    </div>
                </div>

                <!-- Search Filter -->
                <div class="mb-4">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <x-heroicon-s-magnifying-glass class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                        </div>
                        <input wire:model.live.debounce.300ms="searchPermission" type="text"
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white dark:bg-gray-800 dark:border-gray-700 placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            placeholder="Search permissions...">
                    </div>
                </div>

                <!-- Loading State -->
                <div wire:loading.flex wire:target="searchPermission" class="justify-center py-8">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 dark:border-indigo-400">
                    </div>
                </div>

                <!-- Content -->
                <div wire:loading.remove wire:target="searchPermission"
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-[65vh] overflow-y-auto pr-2">
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
                                    class="w-full p-4 bg-white border border-gray-200 rounded-lg shadow-lg dark:bg-gray-800 dark:border-gray-700">
                                    <div class="flex items-center justify-between mb-3">
                                        <h5 class="text-lg font-medium text-gray-900 dark:text-white">
                                            {{ $group->name }}
                                        </h5>
                                        <span
                                            class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                            {{ $filteredPermissions->count() }} permissions
                                        </span>
                                    </div>

                                    <div class="space-y-3 my-2 max-h-[250px] overflow-y-auto">
                                        <div
                                            class="grid grid-cols-12 gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 pb-2 border-b dark:border-gray-700">
                                            <div class="col-span-5">Permission</div>
                                            <div class="col-span-3 text-center">From Role</div>
                                            <div class="col-span-4 text-center">Direct Access</div>
                                        </div>

                                        @foreach ($filteredPermissions as $permission)
                                            <div wire:key="permission-{{ $permission->id }}"
                                                class="grid grid-cols-12 gap-2 items-center p-2 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-200">
                                                <label for="permission-{{ $permission->id }}"
                                                    class="col-span-5 text-sm font-medium text-gray-700 dark:text-gray-300 cursor-pointer truncate"
                                                    title="{{ $permission->name }}">
                                                    {{ $permission->name }}
                                                </label>

                                                <div class="col-span-3 flex justify-center">
                                                    <input disabled wire:model="form.rolePermissions"
                                                        id="role-permission-{{ $permission->id }}" type="checkbox"
                                                        value="{{ $permission->name }}" @checked(in_array($permission->name, $form->rolePermissions))
                                                        class="w-4 h-4 text-indigo-600 bg-gray-200 border-gray-300 rounded focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:bg-gray-700 dark:border-gray-600">
                                                </div>

                                                <div class="col-span-4 flex justify-center">
                                                    <input wire:model="form.directPermissions"
                                                        id="direct-permission-{{ $permission->id }}" type="checkbox"
                                                        value="{{ $permission->name }}" @checked(in_array($permission->name, $form->directPermissions))
                                                        class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:bg-gray-700 dark:border-gray-600">
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endforeach

                        @if ($this->hasNoResults())
                            <div class="col-span-full text-center py-8">
                                <x-heroicon-s-magnifying-glass
                                    class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" />
                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No permissions
                                    found
                                </h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Try adjusting your search
                                    query
                                </p>
                            </div>
                        @endif
                    @endif
                </div>

                <div
                    class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 border-t pt-4 dark:border-gray-700">
                    <x-secondary-button x-on:click="$dispatch('close')" class="w-full sm:w-auto justify-center">
                        {{ __('Cancel') }}
                    </x-secondary-button>

                    <x-primary-button type="submit" class="w-full sm:w-auto justify-center"
                        wire:loading.attr="disabled" wire:target="syncPermission">
                        <x-heroicon-s-check class="w-5 h-5 mr-2" wire:loading.remove wire:target="syncPermission" />
                        <div wire:loading wire:target="syncPermission"
                            class="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2 dark:border-gray-400">
                        </div>
                        <span wire:loading.remove wire:target="syncPermission">{{ __('Save Permissions') }}</span>
                        <span wire:loading wire:target="syncPermission">{{ __('Saving...') }}</span>
                    </x-primary-button>
                </div>
            </form>
        @endif
    </x-modal>
</div>
