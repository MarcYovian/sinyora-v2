<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        try {
            $this->form->authenticate();

            // Log successful login
            Log::info('User logged in', [
                'request_id' => Str::uuid()->toString(),
                'user_id' => auth()->id(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            Session::regenerate();

            $this->redirectIntended(default: route('admin.dashboard.index', absolute: false), navigate: true);
        } catch (Illuminate\Validation\ValidationException $e) {
            // Let Livewire handle validation errors automatically
            throw $e;
        } catch (\Illuminate\Auth\AuthenticationException $e) {
            // Already handled in LoginForm, but we can add additional logging
            Log::warning('Authentication failed', [
                'email' => $this->form->email, // Only log non-sensitive identifier
                'ip_address' => request()->ip(),
                'error' => $e->getMessage(),
            ]);
        } catch (PDOException $e) {
            Log::error('Database connection error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->addError('form.id_user', __('auth.database_error'));
        } catch (\Exception $e) {
            Log::error('Login system error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->dispatch('login-error', message: __('auth.login_error'));
        }
    }
}; ?>

<div>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login">
        <!-- Email Address -->
        <div>
            <x-input-label for="id_user" :value="__('Email or Username')" />
            <x-text-input wire:model="form.id_user" id="id_user" class="block mt-1 w-full" type="text" name="id_user"
                required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('form.id_user')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input wire:model="form.password" id="password" class="block mt-1 w-full" type="password"
                name="password" required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember" class="inline-flex items-center">
                <input wire:model="form.remember" id="remember" type="checkbox"
                    class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800"
                    name="remember">
                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                    href="{{ route('password.request') }}" wire:navigate>
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-button variant="warning" size="sm" class="ms-3" type="submit" wire:loading.attr="disabled"
                wire:target="login">
                <x-heroicon-s-check class="w-5 h-5" wire:loading.remove wire:target="login" />
                <div wire:loading wire:target="login"
                    class="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2 dark:border-gray-400">
                </div>
                <span wire:loading.remove wire:target="login">
                    {{ __('Log in') }}
                </span>
            </x-button>
        </div>
    </form>
</div>
