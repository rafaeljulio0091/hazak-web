<?php

namespace Tests\Fakes;

use App\Models\User;
use App\Repositories\DeviceSharingRepository;

class InMemoryDeviceSharingRepository implements DeviceSharingRepository
{
    /** @var array<int|string, array<string, array{label: ?string}>> */
    private array $shares = [];

    public function share(string $deviceId, User $user, ?string $label = null): self
    {
        $this->shares[$user->getAuthIdentifier()][$deviceId] = ['label' => $label];

        return $this;
    }

    public function sharedWith(User $user): array
    {
        return $this->shares[$user->getAuthIdentifier()] ?? [];
    }

    public function isSharedWith(User $user, string $deviceId): bool
    {
        return isset($this->shares[$user->getAuthIdentifier()][$deviceId]);
    }
}
