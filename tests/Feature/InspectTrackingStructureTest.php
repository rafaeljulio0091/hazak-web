<?php

namespace Tests\Feature;

use Kreait\Firebase\Auth\UserRecord;
use Kreait\Firebase\Contract\Auth as FirebaseAuth;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Database\Reference;
use Kreait\Firebase\Exception\Auth\UserNotFound;
use Mockery;
use Tests\TestCase;

class InspectTrackingStructureTest extends TestCase
{
    private function fakeDatabase(array $childKeys, mixed $location): void
    {
        $database = Mockery::mock(Database::class);

        foreach ($childKeys as $path => $keys) {
            $reference = Mockery::mock(Reference::class);
            $reference->shouldReceive('getChildKeys')->andReturn($keys);
            $database->shouldReceive('getReference')->with($path)->andReturn($reference);
        }

        $locationRef = Mockery::mock(Reference::class);
        $locationRef->shouldReceive('getValue')->andReturn($location);
        $database->shouldReceive('getReference')->with('devices/uid123/location')->andReturn($locationRef);

        $this->app->instance(Database::class, $database);
    }

    private function fakeAuth(): void
    {
        $auth = Mockery::mock(FirebaseAuth::class);
        $auth->shouldReceive('getUserByEmail')->with('a@b.c')->andReturn(
            UserRecord::fromResponseData(['localId' => 'uid123', 'email' => 'a@b.c', 'createdAt' => '0']),
        );
        $auth->shouldReceive('getUserByEmail')->with('none@b.c')->andThrow(new UserNotFound('not found'));

        $this->app->instance(FirebaseAuth::class, $auth);
    }

    public function test_it_reports_key_names_and_a_valid_location_without_coordinates(): void
    {
        $this->fakeAuth();
        $this->fakeDatabase([
            '/' => ['devices', 'users'],
            'users/uid123' => ['profile'],
            'users/uid123/sharing' => [],
            'devices/uid123' => ['location', 'history'],
        ], ['latitude' => -23.123456, 'longitude' => -45.654321, 'updatedAt' => 1]);

        $this->artisan('tracking:inspect', ['email' => 'a@b.c'])
            ->expectsOutput('Nós na raiz do banco: devices, users')
            ->expectsOutput('users/uid123/sharing (dispositivos compartilhados com a conta): (vazio)')
            ->expectsOutput('devices/uid123/location: OK (latitude, longitude, updatedAt presentes).')
            ->doesntExpectOutputToContain('-23.123456')
            ->assertSuccessful();
    }

    public function test_it_flags_a_missing_location_node(): void
    {
        $this->fakeAuth();
        $this->fakeDatabase([
            '/' => ['shares', 'users'],
            'users/uid123' => ['profile'],
            'users/uid123/sharing' => [],
            'devices/uid123' => [],
        ], null);

        $this->artisan('tracking:inspect', ['email' => 'a@b.c'])
            ->expectsOutput('devices/uid123/location: ausente ou incompleto, faltam: latitude, longitude, updatedAt.')
            ->assertSuccessful();
    }

    public function test_it_fails_for_unknown_accounts(): void
    {
        $this->fakeAuth();

        $this->artisan('tracking:inspect', ['email' => 'none@b.c'])->assertFailed();
    }
}
