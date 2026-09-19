<?php

namespace App\Services\Tracking;

use App\Models\User;
use Kreait\Firebase\Contract\Auth;

/**
 * Bridges Laravel's own session/auth guard to Firebase Auth: the web
 * dashboard user is never a "real" Firebase user, but a short-lived
 * custom token minted here lets the browser sign in to Firebase so that
 * Realtime Database security rules (auth != null, auth.uid == ...) can be
 * enforced instead of leaving the database open to anonymous reads.
 */
class FirebaseTokenService
{
    public function __construct(private readonly Auth $auth)
    {
    }

    /**
     * @param  array<string, mixed>  $claims  Extra custom claims for security rules (e.g. role-based access).
     */
    public function mintCustomTokenFor(User $user, array $claims = []): string
    {
        $token = $this->auth->createCustomToken((string) $user->getAuthIdentifier(), $claims);

        return (string) $token;
    }
}
