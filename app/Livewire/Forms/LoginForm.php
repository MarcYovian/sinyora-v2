<?php

namespace App\Livewire\Forms;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Form;

class LoginForm extends Form
{
    public string $id_user = '';
    public string $password = '';
    public bool $remember = false;
    public $id_type;

    public function rules()
    {
        return [
            'id_user' => [
                'required',
                'string',
                $this->id_type === 'email' ? 'email' : 'min:3'
            ],
            'password' => 'required|string',
            'remember' => 'boolean',
        ];
    }

    public function messages()
    {
        return [
            'id_user.email' => 'Format email tidak valid',
            'id_user.min' => 'Username minimal 3 karakter',
            'password.required' => 'Password wajib diisi',
        ];
    }

    protected function beforeValidation()
    {
        if (filter_var($this->id_user, FILTER_VALIDATE_EMAIL)) {
            $this->id_type = 'email';
        } else {
            $this->id_type = 'username';
        }
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->beforeValidation();
        try {
            $this->validate();

            $this->ensureIsNotRateLimited();

            if (! Auth::attempt($this->getCredentials(), $this->remember)) {
                RateLimiter::hit($this->throttleKey());

                throw ValidationException::withMessages([
                    'form.id_user' => trans('auth.failed'),
                ]);
            }

            RateLimiter::clear($this->throttleKey());
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Authentication error', [
                'error' => $e->getMessage(),
                'user' => $this->id_user,
                'ip' => request()->ip()
            ]);

            throw $e;
        }
    }

    protected function getCredentials(): array
    {
        return [
            $this->id_type => $this->id_user,
            'password' => $this->password
        ];
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'form.id_user' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->id_user) . '|' . request()->ip());
    }
}
