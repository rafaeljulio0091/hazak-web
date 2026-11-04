<?php

namespace App\Policies;

use App\Repositories\DeviceSharingRepository;
use App\Support\DeviceId;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Devices have no Eloquent model (they only exist as Firebase nodes), so
 * this policy is registered as a plain gate ability ('view-device') that
 * receives the raw deviceId.
 */
class DevicePolicy
{
    public function __construct(private readonly DeviceSharingRepository $sharing) {}

    public function view(Authenticatable $user, string $deviceId): bool
    {
        if (! DeviceId::isValid($deviceId)) {
            return false;
        }

        return (method_exists($user, 'isAdmin') && $user->isAdmin())
            || $this->sharing->isSharedWith($user, $deviceId);
    }
}
