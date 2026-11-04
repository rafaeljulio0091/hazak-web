<?php

namespace Tests\Unit;

use App\DTOs\Auth\RegisterUserData;
use App\Services\FirebaseAccountService;
use Kreait\Firebase\Auth\UserRecord;
use Kreait\Firebase\Contract\Auth as FirebaseAuth;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Database\Reference;
use Kreait\Firebase\Exception\Auth\UserNotFound;
use Mockery;
use Tests\TestCase;

class FirebaseAccountServiceTest extends TestCase
{
    public function test_registration_creates_credentials_and_profile_in_firebase(): void
    {
        $auth = Mockery::mock(FirebaseAuth::class);
        $database = Mockery::mock(Database::class);
        $reference = Mockery::mock(Reference::class);
        $record = UserRecord::fromResponseData([
            'localId' => 'firebase-user-1',
            'email' => 'new@example.com',
            'displayName' => 'New User',
            'createdAt' => (string) now()->getTimestampMs(),
        ]);

        $auth->shouldReceive('getUserByEmail')
            ->once()
            ->with('new@example.com')
            ->andThrow(new UserNotFound('Not found.'));
        $auth->shouldReceive('createUserWithEmailAndPassword')
            ->once()
            ->with('new@example.com', 'safe-password')
            ->andReturn($record);
        $auth->shouldReceive('updateUser')
            ->once()
            ->with('firebase-user-1', ['displayName' => 'New User'])
            ->andReturn($record);
        $database->shouldReceive('getReference')
            ->once()
            ->with('users/firebase-user-1/profile')
            ->andReturn($reference);
        $reference->shouldReceive('set')
            ->once()
            ->with(Mockery::on(fn (array $profile) => $profile['name'] === 'New User'
                && $profile['email'] === 'new@example.com'
                && $profile['role'] === 'user'))->andReturn($reference);

        $user = (new FirebaseAccountService($auth, $database))->register(
            new RegisterUserData('New User', 'new@example.com', 'safe-password'),
        );

        $this->assertSame('firebase-user-1', $user->getAuthIdentifier());
        $this->assertSame('new@example.com', $user->getAttribute('email'));
        $this->assertFalse($user->hasVerifiedEmail());
    }

    public function test_registration_removes_the_auth_account_if_profile_creation_fails(): void
    {
        $auth = Mockery::mock(FirebaseAuth::class);
        $database = Mockery::mock(Database::class);
        $reference = Mockery::mock(Reference::class);
        $record = UserRecord::fromResponseData([
            'localId' => 'firebase-user-2',
            'email' => 'new@example.com',
            'displayName' => 'New User',
            'createdAt' => (string) now()->getTimestampMs(),
        ]);

        $auth->shouldReceive('getUserByEmail')->andThrow(new UserNotFound('Not found.'));
        $auth->shouldReceive('createUserWithEmailAndPassword')->andReturn($record);
        $auth->shouldReceive('updateUser')->andReturn($record);
        $auth->shouldReceive('deleteUser')->once()->with('firebase-user-2');
        $database->shouldReceive('getReference')->andReturn($reference);
        $reference->shouldReceive('set')->andThrow(new \RuntimeException('RTDB unavailable.'));

        try {
            (new FirebaseAccountService($auth, $database))->register(
                new RegisterUserData('New User', 'new@example.com', 'safe-password'),
            );
            $this->fail('Expected RTDB failure to be propagated.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('RTDB unavailable.', $exception->getMessage());
        }
    }
}
