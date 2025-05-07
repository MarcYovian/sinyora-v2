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
    <header class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-2 text-gray-600 dark:text-gray-400">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form wire:submit="updateProfileInformation" class="space-y-6">
        <!-- Avatar Upload -->
        <div class="space-y-4">
            <div>
                <x-input-label for="avatar" :value="__('Profile Photo')" />
                <p class="mt-1 text-sm text-gray-500">
                    {{ __('Recommended size: 200x200 pixels (JPEG, PNG, or JPG, max 2MB)') }}
                </p>
            </div>

            <div x-data="{ isUploading: false, progress: 0 }" x-on:livewire-upload-start="isUploading = true"
                x-on:livewire-upload-finish="isUploading = false" x-on:livewire-upload-error="isUploading = false"
                x-on:livewire-upload-progress="progress = $event.detail.progress" class="flex items-center gap-6">
                <div class="relative">
                    @if ($currentAvatar || $avatar)
                        <img src="{{ $avatar ? $avatar->temporaryUrl() : asset("storage/$currentAvatar") }}"
                            alt="Current Avatar" class="h-24 w-24 rounded-full object-cover border-2 border-gray-200">
                        <button type="button" wire:click="deleteAvatar"
                            class="absolute -top-2 -right-2 p-1 bg-red-500 text-white rounded-full hover:bg-red-600 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span class="sr-only">{{ __('Remove Photo') }}</span>
                        </button>
                    @else
                        <div class="h-24 w-24 rounded-full bg-gray-200 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                    @endif
                </div>

                <div class="flex-1 space-y-2">
                    <label for="avatar" class="cursor-pointer">
                        <div
                            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                            {{ $currentAvatar || $avatar ? __('Change Photo') : __('Upload Photo') }}
                        </div>
                        <input wire:model="avatar" id="avatar" name="avatar" type="file" class="sr-only"
                            accept="image/*" />
                    </label>

                    <!-- Upload Progress Indicator -->
                    <div wire:loading wire:target="avatar" class="w-full">
                        <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full bg-blue-500 rounded-full transition-all duration-300"
                                x-bind:style="`width: ${progress}%`">
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            Uploading... <span x-text="progress"></span>%
                        </p>
                    </div>

                    <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <x-input-label for="name" :value="__('Full Name')" />
                <x-text-input wire:model="name" id="name" name="name" type="text"
                    class="mt-1 block w-full rounded-md shadow-sm" required autofocus autocomplete="name" />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <div>
                <x-input-label for="username" :value="__('Username')" />
                <div class="relative">
                    <x-text-input wire:model="username" id="username" name="username" type="text"
                        class="mt-1 block w-full rounded-md shadow-sm bg-gray-100 cursor-not-allowed" disabled
                        autocomplete="username" />
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <span class="text-gray-500 text-sm">{{ __('Cannot be changed') }}</span>
                    </div>
                </div>
                <x-input-error class="mt-2" :messages="$errors->get('username')" />
            </div>

            <div class="md:col-span-2">
                <x-input-label for="email" :value="__('Email Address')" />
                <x-text-input wire:model="email" id="email" name="email" type="email"
                    class="mt-1 block w-full rounded-md shadow-sm" required autocomplete="email" />
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

        <div class="flex items-center justify-between pt-6 border-t border-gray-200">
            <x-primary-button class="px-6 py-2">
                {{ __('Save Changes') }}
            </x-primary-button>
            <x-action-message on="profile-updated" class="text-sm text-green-600 font-medium">
                {{ __('Profile saved successfully.') }}
            </x-action-message>
        </div>
    </form>
</section>
