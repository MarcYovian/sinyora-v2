<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component {
    use Livewire\WithFileUploads;

    public string $name = '';
    public string $username = '';
    public string $email = '';
    public $avatar;
    public $currentAvatar;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->username = $user->username;
        $this->email = $user->email;
        $this->currentAvatar = $user->avatar;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'avatar' => ['nullable', 'image', 'max:2048'], // 2MB Max
        ]);

        // Handle avatar upload
        if ($this->avatar) {
            // Delete old avatar if exists
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            $path = $this->avatar->store('avatars', 'public');
            $validated['avatar'] = $path;
        }

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    public function deleteAvatar(): void
    {
        $user = Auth::user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $user->avatar = null;
            $user->save();
            $this->currentAvatar = null;
        }
        if ($this->avatar) {
            $this->avatar = null;
        }
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function sendVerification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section>
    <form wire:submit="updateProfileInformation" class="space-y-6">
        <!-- Name -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4">
            <div class="md:col-span-2">
                <x-input-label for="name" class="text-base font-semibold" :value="__('Name')" />
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ __('Will appear on receipts, invoices, and other communication.') }}
                </p>
            </div>
            <div class="md:col-span-1">
                <x-text-input wire:model="name" id="name" name="name" type="text"
                    class="block w-full rounded-md shadow-sm" required autofocus autocomplete="name" />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>
        </div>

        <div class="hidden border-t border-gray-100 dark:border-gray-700 md:block"></div>

        <!-- Email -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4">
            <div class="md:col-span-2">
                <x-input-label for="email" class="text-base font-semibold" :value="__('Email')" />
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ __('Used to sign in, for email receipts and product updates.') }}
                </p>
            </div>
            <div class="md:col-span-1">
                <x-text-input wire:model="email" id="email" name="email" type="email"
                    class="block w-full rounded-md shadow-sm" required autocomplete="email" />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />

                @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !auth()->user()->hasVerifiedEmail())
                    <div class="mt-3 p-3 bg-yellow-50 rounded-md">
                        <div class="flex items-center">
                            <svg class="h-5 w-5 text-yellow-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd" />
                            </svg>
                            <p class="text-sm text-yellow-700">
                                {{ __('Your email address is unverified.') }}
                                <button wire:click.prevent="sendVerification"
                                    class="ml-1 font-medium text-yellow-700 hover:text-yellow-600 underline">
                                    {{ __('Click to resend verification email.') }}
                                </button>
                            </p>
                        </div>

                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-2 text-sm text-green-600">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </p>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <div class="hidden border-t border-gray-100 dark:border-gray-700 md:block"></div>

        <!-- Username -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4">
            <div class="md:col-span-2">
                <x-input-label for="username" class="text-base font-semibold" :value="__('Username')" />
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ __('Your unique username for logging in and your profile URL.') }}
                </p>
            </div>
            <div class="md:col-span-1">
                 <div class="relative">
                    <x-text-input wire:model="username" id="username" name="username" type="text"
                        class="block w-full rounded-md shadow-sm bg-gray-100 cursor-not-allowed text-gray-500" disabled
                        autocomplete="username" />
                </div>
                <x-input-error class="mt-2" :messages="$errors->get('username')" />
            </div>
        </div>

        <div class="hidden border-t border-gray-100 dark:border-gray-700 md:block"></div>

        <!-- Avatar Upload -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4">
             <div class="md:col-span-2">
                <x-input-label for="avatar" class="text-base font-semibold" :value="__('Avatar')" />
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                   {{ __('JPG, GIF or PNG. 1MB Max.') }}
                </p>
            </div>

            <div class="md:col-span-1 flex items-center gap-6">
                <div x-data="{ isUploading: false, progress: 0 }" x-on:livewire-upload-start="isUploading = true"
                    x-on:livewire-upload-finish="isUploading = false" x-on:livewire-upload-error="isUploading = false"
                    x-on:livewire-upload-progress="progress = $event.detail.progress" class="flex items-center gap-6 w-full">
                    <div class="relative flex-shrink-0">
                        @if ($currentAvatar || $avatar)
                            <img src="{{ $avatar ? $avatar->temporaryUrl() : asset("storage/$currentAvatar") }}"
                                alt="Current Avatar" class="h-16 w-16 rounded-full object-cover border-2 border-gray-200">
                                
                            @if($currentAvatar)
                            <button type="button" wire:click="deleteAvatar"
                                class="absolute -top-2 -right-2 p-1 bg-white dark:bg-gray-800 text-red-500 rounded-full shadow border hover:bg-gray-50 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span class="sr-only">{{ __('Remove Photo') }}</span>
                            </button>
                            @endif
                        @else
                            <div class="h-16 w-16 rounded-full bg-gray-200 flex items-center justify-center">
                                <span class="text-gray-500 text-xl font-bold">{{ substr($name, 0, 1) }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="flex-1 space-y-2">
                        <label for="avatar" class="cursor-pointer inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                            {{ __('Choose') }}
                            <input wire:model="avatar" id="avatar" name="avatar" type="file" class="sr-only"
                                accept="image/*" />
                        </label>

                        <!-- Upload Progress Indicator -->
                        <div wire:loading wire:target="avatar" class="w-full max-w-xs">
                            <div class="h-1 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full bg-indigo-600 rounded-full transition-all duration-300"
                                    x-bind:style="`width: ${progress}%`">
                                </div>
                            </div>
                        </div>

                        <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end pt-6 border-t border-gray-200 dark:border-gray-700">
            <x-action-message on="profile-updated" class="mr-3 text-sm text-green-600 font-medium">
                {{ __('Saved.') }}
            </x-action-message>
            <x-primary-button class="px-6 py-2">
                {{ __('Save Changes') }}
            </x-primary-button>
        </div>
    </form>
</section>
