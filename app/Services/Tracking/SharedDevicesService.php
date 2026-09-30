<?php

namespace App\Services\Tracking;

use App\Models\User;
use App\Repositories\DeviceSharingRepository;

/**
 * Builds the regular user's dashboard: the devices explicitly shared with
 * them plus each device's last known position, used for the first paint
 * before the realtime listeners connect.
 */
class SharedDevicesService
{
    public function __construct(
        private readonly DeviceSharingRepository $sharing,
        private readonly DeviceTrackingService $tracking,
    ) {}

    /**
     * @return list<array{id: string, label: ?string, position: array<string, mixed>|null}>
     */
    public function overviewFor(User $user): array
    {
        $devices = [];

        foreach ($this->sharing->sharedWith($user) as $deviceId => $meta) {
            $devices[] = [
                'id' => $deviceId,
                'label' => $meta['label'],
                'position' => $this->tracking->latestPosition($deviceId),
            ];
        }

        return $devices;
    }
}
