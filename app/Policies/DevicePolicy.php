<?php

namespace App\Policies;

use App\Models\User;
use App\Repositories\DeviceSharingRepository;
use App\Support\DeviceId;

/**
 * Devices have no Eloquent model (they only exist as Firebase nodes), so
 * this policy is registered as a plain gate ability ('view-device') that
 * receives the raw deviceId.
 */
class DevicePolicy
{
    public function __construct(private readonly DeviceSharingRepository $sharing) {}

    public function view(User $user, string $deviceId): bool
    {
        if (! DeviceId::isValid($deviceId)) {
            return false;
        }

        return $user->isAdmin() || $this->sharing->isSharedWith($user, $deviceId);
    }
}
