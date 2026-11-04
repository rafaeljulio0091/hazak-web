<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Kreait\Firebase\Exception\AuthException;
use Kreait\Firebase\Exception\DatabaseException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('email'))) {
            $this->merge(['email' => Str::lower(trim($this->input('email')))]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:254'],
            'password' => ['required', 'string', 'min:6', 'max:4096'],
            'remember' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'password.min' => __('E-mail ou senha inválidos.'),
            'password.max' => __('E-mail ou senha inválidos.'),
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        try {
            $authenticated = Auth::attempt($this->only('email', 'password'), $this->boolean('remember'));
        } catch (AuthException|DatabaseException $exception) {
            RateLimiter::hit($this->throttleKey());
            Auth::guard()->forgetUser();
            $this->session()->invalidate();
            $this->session()->regenerateToken();

            // SDK exceptions may retain HTTP requests containing passwords and tokens.
            Log::warning('Firebase login unavailable.', ['exception_type' => $exception::class]);

            throw ValidationException::withMessages([
                'authentication' => __('Não foi possível entrar agora. Tente novamente em instantes.'),
            ]);
        }

        if (! $authenticated) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('E-mail ou senha inválidos.'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('Muitas tentativas de acesso. Tente novamente em :seconds segundos.', [
                'seconds' => $seconds,
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return 'login:'.hash('sha256', Str::lower((string) $this->input('email')).'|'.$this->ip());
    }
}
