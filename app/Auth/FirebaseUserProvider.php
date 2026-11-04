<?php

namespace App\Auth;

use App\Enums\UserRole;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Kreait\Firebase\Auth\SignIn\FailedToSignIn;
use Kreait\Firebase\Auth\UserRecord;
use Kreait\Firebase\Contract\Auth as FirebaseAuth;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Exception\Auth\AuthError;
use Kreait\Firebase\Exception\Auth\UserNotFound;

class FirebaseUserProvider implements UserProvider
{
    public function __construct(
        private readonly FirebaseAuth $auth,
        private readonly Database $database,
    ) {}

    public function retrieveById($identifier): ?FirebaseUser
    {
        if (! is_string($identifier) || $identifier === '') {
            return null;
        }

        try {
            return $this->fromFirebase($this->auth->getUser($identifier));
        } catch (UserNotFound) {
            return null;
        }
    }

    public function retrieveByToken($identifier, #[\SensitiveParameter] $token): ?FirebaseUser
    {
        if (! is_string($token) || $token === '') {
            return null;
        }

        $user = $this->retrieveById($identifier);
        $rememberToken = $user?->getRememberToken();

        return $rememberToken !== null && $rememberToken !== '' && hash_equals($rememberToken, $token) ? $user : null;
    }

    public function updateRememberToken(Authenticatable $user, #[\SensitiveParameter] $token): void
    {
        $this->database->getReference($this->profilePath($user->getAuthIdentifier()))
            ->update(['remember_token' => $token]);
        $user->setRememberToken($token);
    }

    public function retrieveByCredentials(#[\SensitiveParameter] array $credentials): ?FirebaseUser
    {
        $email = $credentials['email'] ?? null;

        if (! is_string($email) || $email === '') {
            return null;
        }

        try {
            return $this->fromFirebase($this->auth->getUserByEmail($email));
        } catch (UserNotFound) {
            return null;
        }
    }

    public function validateCredentials(Authenticatable $user, #[\SensitiveParameter] array $credentials): bool
    {
        try {
            $result = $this->auth->signInWithEmailAndPassword(
                (string) ($credentials['email'] ?? ''),
                (string) ($credentials['password'] ?? ''),
            );

            return $result->firebaseUserId() === $user->getAuthIdentifier();
        } catch (FailedToSignIn $exception) {
            if (in_array($exception->getMessage(), [
                'INVALID_LOGIN_CREDENTIALS',
                'INVALID_PASSWORD',
                'EMAIL_NOT_FOUND',
                'USER_DISABLED',
                'INVALID_EMAIL',
            ], true)) {
                return false;
            }

            // Do not propagate SDK exceptions that may retain a sign-in request.
            throw new AuthError('Firebase authentication service is unavailable.');
        }
    }

    public function rehashPasswordIfRequired(Authenticatable $user, #[\SensitiveParameter] array $credentials, bool $force = false): void
    {
        // Firebase Authentication owns password hashing and rotation.
    }

    private function fromFirebase(UserRecord $record): ?FirebaseUser
    {
        if ($record->disabled) {
            return null;
        }

        $uid = (string) $record->uid;
        $profile = $this->database->getReference($this->profilePath($uid))->getValue();
        $profile = is_array($profile) ? $profile : [];
        $role = $profile['role'] ?? null;
        $role = is_string($role) ? UserRole::tryFrom($role) : null;

        return new FirebaseUser(
            uid: $uid,
            name: (string) ($profile['name'] ?? $record->displayName ?? ''),
            email: (string) ($profile['email'] ?? $record->email ?? ''),
            role: ($role ?? UserRole::User)->value,
            emailVerified: (bool) ($record->emailVerified ?? false),
            rememberToken: isset($profile['remember_token']) ? (string) $profile['remember_token'] : null,
        );
    }

    private function profilePath(string $uid): string
    {
        return "users/{$uid}/profile";
    }
}
