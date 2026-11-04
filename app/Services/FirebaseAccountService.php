<?php

namespace App\Services;

use App\Auth\FirebaseUser;
use App\DTOs\Auth\RegisterUserData;
use App\Notifications\FirebaseActionLinkNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Kreait\Firebase\Auth\SignIn\FailedToSignIn;
use Kreait\Firebase\Contract\Auth as FirebaseAuth;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Exception\Auth\UserNotFound;

class FirebaseAccountService
{
    public function __construct(
        private readonly FirebaseAuth $auth,
        private readonly Database $database,
    ) {}

    public function register(RegisterUserData $data): FirebaseUser
    {
        try {
            $this->auth->getUserByEmail($data->email);
            throw ValidationException::withMessages(['email' => __('validation.unique', ['attribute' => 'email'])]);
        } catch (UserNotFound) {
            // Firebase Authentication is the uniqueness authority for email addresses.
        }

        try {
            $user = $this->auth->createUserWithEmailAndPassword($data->email, $data->password);
        } catch (EmailExists) {
            throw ValidationException::withMessages(['email' => __('validation.unique', ['attribute' => 'email'])]);
        }
        $profile = [
            'name' => $data->name,
            'email' => $data->email,
            'role' => 'user',
            'created_at' => now()->toIso8601String(),
        ];

        try {
            $this->auth->updateUser($user->uid, ['displayName' => $data->name]);
            $this->database->getReference("users/{$user->uid}/profile")->set($profile);
        } catch (\Throwable $exception) {
            $this->auth->deleteUser($user->uid);
            throw $exception;
        }

        return new FirebaseUser($user->uid, $data->name, $data->email, 'user', false);
    }

    public function updateProfile(FirebaseUser $user, string $name, string $email): void
    {
        if (strcasecmp($email, $user->getAttribute('email')) !== 0) {
            try {
                $existing = $this->auth->getUserByEmail($email);
                if ($existing->uid !== $user->getAuthIdentifier()) {
                    throw ValidationException::withMessages(['email' => __('validation.unique', ['attribute' => 'email'])]);
                }
            } catch (UserNotFound) {
                // The address can be safely assigned when no account owns it.
            }

            try {
                $this->auth->updateUser($user->getAuthIdentifier(), [
                    'email' => $email,
                    'emailVerified' => false,
                    'displayName' => $name,
                ]);
            } catch (EmailExists) {
                throw ValidationException::withMessages(['email' => __('validation.unique', ['attribute' => 'email'])]);
            }
        } else {
            $this->auth->updateUser($user->getAuthIdentifier(), ['displayName' => $name]);
        }

        $emailChanged = strcasecmp($email, $user->getAttribute('email')) !== 0;
        $this->database->getReference("users/{$user->getAuthIdentifier()}/profile")
            ->update([
                'name' => $name,
                'email' => $email,
                'email_verified_at' => $emailChanged ? null : $user->getAttribute('email_verified_at'),
            ]);
        $user->refreshProfile(
            ['name' => $name, 'email' => $email],
            ! $emailChanged && $user->hasVerifiedEmail(),
        );
    }

    public function verifyPassword(FirebaseUser $user, string $password): bool
    {
        try {
            $this->auth->signInWithEmailAndPassword($user->getAttribute('email'), $password);

            return true;
        } catch (FailedToSignIn) {
            return false;
        }
    }

    public function updatePassword(FirebaseUser $user, string $password): void
    {
        $this->auth->changeUserPassword($user->getAuthIdentifier(), $password);
        $this->auth->revokeRefreshTokens($user->getAuthIdentifier());
    }

    public function delete(FirebaseUser $user): void
    {
        $this->auth->deleteUser($user->getAuthIdentifier());
        $this->database->getReference("users/{$user->getAuthIdentifier()}")->remove();
    }

    public function sendPasswordResetLink(string $email): void
    {
        $link = $this->auth->getPasswordResetLink($email);
        parse_str((string) parse_url($link, PHP_URL_QUERY), $query);
        $code = $query['oobCode'] ?? null;

        if (! is_string($code) || $code === '') {
            throw new \RuntimeException('Firebase não retornou código válido de redefinição de senha.');
        }

        $url = route('password.reset', ['token' => $code, 'email' => $email]);
        Notification::route('mail', $email)->notify(new FirebaseActionLinkNotification(
            $url,
            'Redefinição de senha',
            'Use o link abaixo para cadastrar uma nova senha. Se você não solicitou esta alteração, ignore esta mensagem.',
            'Redefinir senha',
        ));
    }

    public function resetPassword(string $email, string $code, string $password): void
    {
        if (strcasecmp($this->auth->verifyPasswordResetCode($code), $email) !== 0) {
            throw ValidationException::withMessages(['email' => trans('password.user')]);
        }

        $this->auth->confirmPasswordReset($code, $password);
    }

    public function sendEmailVerification(FirebaseUser $user): void
    {
        $link = $this->auth->getEmailVerificationLink($user->getEmailForVerification(), [
            'continueUrl' => route('verification.notice'),
        ]);

        Notification::route('mail', $user->getEmailForVerification())->notify(new FirebaseActionLinkNotification(
            $link,
            'Confirme seu endereço de e-mail',
            'Confirme seu e-mail para liberar o acesso à sua conta.',
            'Confirmar e-mail',
        ));
    }

    public function markEmailAsVerified(FirebaseUser $user): void
    {
        $this->auth->updateUser($user->getAuthIdentifier(), ['emailVerified' => true]);
        $this->database->getReference("users/{$user->getAuthIdentifier()}/profile")
            ->update(['email_verified_at' => now()->toIso8601String()]);
    }
}
