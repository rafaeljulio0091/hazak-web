<?php

namespace App\Rules;

use App\Auth\FirebaseUser;
use App\Services\FirebaseAccountService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CurrentFirebasePassword implements ValidationRule
{
    public function __construct(private readonly FirebaseUser $user) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! app(FirebaseAccountService::class)->verifyPassword($this->user, $value)) {
            $fail('auth.password');
        }
    }
}
