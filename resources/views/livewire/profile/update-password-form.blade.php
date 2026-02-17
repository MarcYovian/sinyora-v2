<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component
{
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }
}; ?>

<section>
    <form wire:submit="updatePassword" class="space-y-6">
        <!-- Current Password -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4">
            <div class="md:col-span-1">
                <x-input-label for="update_password_current_password" class="text-base font-semibold" :value="__('Current Password')" />
            </div>
            <div class="md:col-span-2">
                <x-text-input wire:model="current_password" id="update_password_current_password" name="current_password" type="password" class="block w-full" autocomplete="current-password" />
                <x-input-error :messages="$errors->get('current_password')" class="mt-2" />
            </div>
        </div>

        <div class="hidden border-t border-gray-100 dark:border-gray-700 md:block"></div>

        <!-- New Password -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4">
            <div class="md:col-span-1">
                <x-input-label for="update_password_password" class="text-base font-semibold" :value="__('New Password')" />
            </div>
            <div class="md:col-span-2">
                <x-text-input wire:model="password" id="update_password_password" name="password" type="password" class="block w-full" autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>
        </div>

        <div class="hidden border-t border-gray-100 dark:border-gray-700 md:block"></div>

        <!-- Confirm Password -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4">
            <div class="md:col-span-1">
                <x-input-label for="update_password_password_confirmation" class="text-base font-semibold" :value="__('Confirm Password')" />
            </div>
            <div class="md:col-span-2">
                <x-text-input wire:model="password_confirmation" id="update_password_password_confirmation" name="password_confirmation" type="password" class="block w-full" autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>
        </div>

        <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200 dark:border-gray-700">
             <x-action-message class="mr-3" on="password-updated">
                {{ __('Saved.') }}
            </x-action-message>
            <x-primary-button>{{ __('Save') }}</x-primary-button>
        </div>
    </form>
</section>
