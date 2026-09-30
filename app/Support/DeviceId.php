<?php

namespace App\Support;

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
}
