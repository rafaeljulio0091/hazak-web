<?php

namespace App\Services\Tracking;

use Kreait\Firebase\Contract\Database;

/**
 * Reads and writes continuous GPS positions on the Firebase Realtime
 * Database. Mobile devices normally write directly to the database using
 * their own Firebase Auth session (lowest latency); this service exists
 * for server-side reads (dashboard SSR, other integrations) and for any
 * write path that must go through Laravel (e.g. a legacy device that
 * cannot talk to Firebase directly).
 */
class DeviceTrackingService
{
    public function __construct(private readonly Database $database)
    {
    }

    /**
     * @return array{latitude: float, longitude: float, heading: ?float, speed: ?float, updatedAt: int}|null
     */
    public function latestPosition(string $deviceId): ?array
    {
        $value = $this->database
            ->getReference($this->locationPath($deviceId))
            ->getValue();

        return is_array($value) ? $value : null;
    }

    public function recordPosition(
        string $deviceId,
        float $latitude,
        float $longitude,
        ?float $heading = null,
        ?float $speed = null,
    ): void {
        $payload = [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'heading' => $heading,
            'speed' => $speed,
            'updatedAt' => now()->getTimestampMs(),
        ];

        // Overwrite the "current position" node...
        $this->database->getReference($this->locationPath($deviceId))->set($payload);

        // ...and append to the history trail for later playback/audit.
        $this->database->getReference("devices/{$deviceId}/history")->push($payload);
    }

    private function locationPath(string $deviceId): string
    {
        return "devices/{$deviceId}/location";
    }
}
