<?php

namespace App\Auth;

use App\Services\FirebaseAccountService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use JsonSerializable;

class FirebaseUser implements Authenticatable, JsonSerializable, MustVerifyEmail
{
    public function __construct(
        private readonly string $uid,
        private string $name,
        private string $email,
        private string $role,
        private bool $emailVerified,
        private ?string $rememberToken = null,
    ) {}

    public function getAuthIdentifierName(): string
    {
        return 'uid';
    }

    public function getAuthIdentifier(): string
    {
        return $this->uid;
    }

    public function getAuthPasswordName(): string
    {
        return 'password';
    }

    public function getAuthPassword(): string
    {
        return '';
    }

    public function getRememberToken(): ?string
    {
        return $this->rememberToken;
    }

    public function setRememberToken($value): void
    {
        $this->rememberToken = $value;
    }

    public function getRememberTokenName(): string
    {
        return 'remember_token';
    }

    public function getEmailForPasswordReset(): string
    {
        return $this->email;
    }

    public function getEmailForVerification(): string
    {
        return $this->email;
    }

    public function hasVerifiedEmail(): bool
    {
        return $this->emailVerified;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function getKey(): string
    {
        return $this->uid;
    }

    public function getAttribute(string $name): mixed
    {
        return match ($name) {
            'id', 'uid' => $this->uid,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'email_verified_at' => $this->emailVerified ? true : null,
            'remember_token' => $this->rememberToken,
            default => null,
        };
    }

    public function markEmailAsVerified(): bool
    {
        app(FirebaseAccountService::class)->markEmailAsVerified($this);
        $this->emailVerified = true;

        return true;
    }

    public function sendEmailVerificationNotification(): void
    {
        app(FirebaseAccountService::class)->sendEmailVerification($this);
    }

    public function refreshProfile(array $profile, bool $emailVerified): void
    {
        $this->name = (string) ($profile['name'] ?? $this->name);
        $this->email = (string) ($profile['email'] ?? $this->email);
        $this->role = (string) ($profile['role'] ?? 'user');
        $this->emailVerified = $emailVerified;
        $this->rememberToken = isset($profile['remember_token']) ? (string) $profile['remember_token'] : null;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->uid,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->emailVerified ? true : null,
            'role' => $this->role,
        ];
    }
}
