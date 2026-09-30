<?php

namespace Tests\Unit\Repositories;

use App\Models\User;
use App\Repositories\Firebase\FirebaseDeviceSharingRepository;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Database\Reference;
use Mockery;
use PHPUnit\Framework\TestCase;

class FirebaseDeviceSharingRepositoryTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    private function user(int $id): User
    {
        return (new User)->forceFill(['id' => $id]);
    }

    private function repositoryReturning(string $path, mixed $value): FirebaseDeviceSharingRepository
    {
        $reference = Mockery::mock(Reference::class);
        $reference->shouldReceive('getValue')->andReturn($value);

        $database = Mockery::mock(Database::class);
        $database->shouldReceive('getReference')->with($path)->andReturn($reference);

        return new FirebaseDeviceSharingRepository($database);
    }

    public function test_it_lists_only_active_and_well_formed_shares(): void
    {
        $repository = $this->repositoryReturning('users/7/sharing', [
            'device-a' => true,
            'device-b' => ['label' => 'Moto'],
            'device-revoked' => false,
            'device-string' => 'yes',
            'bad.id' => true,
        ]);

        $this->assertSame([
            'device-a' => ['label' => null],
            'device-b' => ['label' => 'Moto'],
        ], $repository->sharedWith($this->user(7)));
    }

    public function test_it_returns_no_devices_when_the_node_is_missing(): void
    {
        $repository = $this->repositoryReturning('users/7/sharing', null);

        $this->assertSame([], $repository->sharedWith($this->user(7)));
    }

    public function test_it_checks_a_single_share_under_the_user_uid(): void
    {
        $this->assertTrue(
            $this->repositoryReturning('users/7/sharing/device-a', true)->isSharedWith($this->user(7), 'device-a'),
        );
        $this->assertFalse(
            $this->repositoryReturning('users/7/sharing/device-a', null)->isSharedWith($this->user(7), 'device-a'),
        );
    }

    public function test_it_never_queries_firebase_with_a_malformed_device_id(): void
    {
        $database = Mockery::mock(Database::class);
        $database->shouldNotReceive('getReference');

        $repository = new FirebaseDeviceSharingRepository($database);

        $this->assertFalse($repository->isSharedWith($this->user(7), '../../devices'));
    }
}
