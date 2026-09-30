<?php

namespace App\Repositories\Firebase;

use App\Models\User;
use App\Repositories\DeviceSharingRepository;
use App\Support\DeviceId;
use Kreait\Firebase\Contract\Database;

/**
 * Reads users/{uid}/sharing/{deviceId}. The {uid} is the Laravel user id,
 * the same uid used for the Firebase custom tokens minted by
 * FirebaseTokenService, so the backend check and the Realtime Database
 * security rules (database.rules.json) authorize against the same node.
 *
 * A device counts as shared when its entry is `true` or a non-empty
 * object with optional metadata (e.g. {"label": "..."}); any other value
 * (false, null, strings) is ignored.
 */
class FirebaseDeviceSharingRepository implements DeviceSharingRepository
{
    public function __construct(private readonly Database $database) {}

    public function sharedWith(User $user): array
    {
        $entries = $this->database
            ->getReference($this->sharingPath($user))
            ->getValue();

        if (! is_array($entries)) {
            return [];
        }

        $shared = [];

        foreach ($entries as $deviceId => $value) {
            $deviceId = (string) $deviceId;

            if (! DeviceId::isValid($deviceId) || ! $this->isActiveShare($value)) {
                continue;
            }

            $label = is_array($value) && is_string($value['label'] ?? null)
                ? mb_substr($value['label'], 0, 80)
                : null;

            $shared[$deviceId] = ['label' => $label];
        }

        return $shared;
    }

    public function isSharedWith(User $user, string $deviceId): bool
    {
        if (! DeviceId::isValid($deviceId)) {
            return false;
        }

        $value = $this->database
            ->getReference($this->sharingPath($user)."/{$deviceId}")
            ->getValue();

        return $this->isActiveShare($value);
    }

    private function isActiveShare(mixed $value): bool
    {
        return $value === true || (is_array($value) && $value !== []);
    }

    private function sharingPath(User $user): string
    {
        return 'users/'.$user->getAuthIdentifier().'/sharing';
    }
}
