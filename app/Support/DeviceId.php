<?php

namespace App\Support;

use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Device ids are Firebase Auth uids used as Realtime Database keys. They
 * are interpolated into database paths, so anything outside this
 * whitelist ('/', '.', '#', '$', '[', ']', ...) is rejected to prevent
 * path traversal into other nodes.
 */
final class DeviceId
{
    public const PATTERN = '[A-Za-z0-9_-]{1,128}';

    public static function isValid(string $deviceId): bool
    {
        return preg_match('/^'.self::PATTERN.'$/', $deviceId) === 1;
    }

    /**
     * The device linked to the logged account. Web and mobile app sign in
     * to the same Firebase project with the same account, and the mobile
     * app writes devices/{auth.uid}/location (see database.rules.json:
     * write requires auth.uid === $deviceId). So the account's own device
     * id is its Firebase uid.
     */
    public static function ownedBy(Authenticatable $user): ?string
    {
        $uid = (string) $user->getAuthIdentifier();

        return self::isValid($uid) ? $uid : null;
    }

    public static function isOwnedBy(Authenticatable $user, string $deviceId): bool
    {
        $own = self::ownedBy($user);

        return $own !== null && hash_equals($own, $deviceId);
    }
}
