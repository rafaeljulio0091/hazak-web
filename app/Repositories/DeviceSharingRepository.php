<?php

namespace App\Repositories;

use App\Models\User;

/**
 * Source of truth for which devices have shared their location with a
 * given user. Backed by the Firebase node users/{uid}/sharing/{deviceId},
 * written by the device app itself.
 */
interface DeviceSharingRepository
{
    /**
     * @return array<string, array{label: ?string}> Keyed by deviceId.
     */
    public function sharedWith(User $user): array;

    public function isSharedWith(User $user, string $deviceId): bool;
}
