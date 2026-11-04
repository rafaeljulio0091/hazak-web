<?php

namespace App\Services\Tracking;

use App\Repositories\DeviceSharingRepository;
use App\Support\DeviceId;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Builds the regular user's dashboard: the device linked to the logged
 * account (devices/{uid}) plus the devices explicitly shared with them
 * (users/{uid}/sharing), each with its last known position, used for the
 * first paint before the realtime listeners connect.
 */
class SharedDevicesService
{
    public const OWN_DEVICE_LABEL = 'Meu dispositivo';

    public function __construct(
        private readonly DeviceSharingRepository $sharing,
        private readonly DeviceTrackingService $tracking,
    ) {}

    /**
     * @return list<array{id: string, label: ?string, own: bool, position: array<string, mixed>|null}>
     */
    public function overviewFor(Authenticatable $user): array
    {
        $devices = [];
        $ownId = DeviceId::ownedBy($user);

        // The account's own device is only listed once it has reported a
        // position, so accounts without the mobile app keep the empty state.
        if ($ownId !== null && ($position = $this->tracking->latestPosition($ownId)) !== null) {
            $devices[] = [
                'id' => $ownId,
                'label' => self::OWN_DEVICE_LABEL,
                'own' => true,
                'position' => $position,
            ];
        }

        foreach ($this->sharing->sharedWith($user) as $deviceId => $meta) {
            // PHP turns numeric-string array keys into ints.
            $deviceId = (string) $deviceId;

            if ($deviceId === $ownId) {
                continue;
            }

            $devices[] = [
                'id' => $deviceId,
                'label' => $meta['label'],
                'own' => false,
                'position' => $this->tracking->latestPosition($deviceId),
            ];
        }

        return $devices;
    }
}
