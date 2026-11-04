<?php

namespace Tests\Fakes;

use App\Repositories\DeviceSharingRepository;
use Illuminate\Contracts\Auth\Authenticatable;

class InMemoryDeviceSharingRepository implements DeviceSharingRepository
{
    /** @var array<int|string, array<string, array{label: ?string}>> */
    private array $shares = [];

    public function share(string $deviceId, Authenticatable $user, ?string $label = null): self
    {
        $this->shares[$user->getAuthIdentifier()][$deviceId] = ['label' => $label];

        return $this;
    }

    public function sharedWith(Authenticatable $user): array
    {
        return $this->shares[$user->getAuthIdentifier()] ?? [];
    }

    public function isSharedWith(Authenticatable $user, string $deviceId): bool
    {
        return isset($this->shares[$user->getAuthIdentifier()][$deviceId]);
    }
}
