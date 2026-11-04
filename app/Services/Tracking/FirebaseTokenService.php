<?php

namespace App\Services\Tracking;

use Illuminate\Contracts\Auth\Authenticatable;
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
    public function __construct(private readonly Auth $auth) {}

    /**
     * @param  array<string, mixed>  $claims  Extra custom claims for security rules (e.g. role-based access).
     */
    public function mintCustomTokenFor(Authenticatable $user, array $claims = []): string
    {
        $token = $this->auth->createCustomToken((string) $user->getAuthIdentifier(), $claims);

        return $token->toString();
    }

    /**
     * Token for the live tracking screens. Only administrators receive the
     * `dashboard` claim (read access to every device in the security
     * rules); regular users get a plain token, so Firebase itself only lets
     * them read devices listed under users/{uid}/sharing.
     */
    public function mintTrackingTokenFor(Authenticatable $user): string
    {
        return $this->mintCustomTokenFor($user, $this->isAdmin($user) ? ['role' => 'dashboard'] : []);
    }

    private function isAdmin(Authenticatable $user): bool
    {
        return method_exists($user, 'isAdmin') && $user->isAdmin();
    }
}
