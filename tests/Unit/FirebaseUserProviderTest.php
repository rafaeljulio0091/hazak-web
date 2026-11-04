<?php

namespace Tests\Unit;

use App\Auth\FirebaseUser;
use App\Auth\FirebaseUserProvider;
use Kreait\Firebase\Auth\SignIn\FailedToSignIn;
use Kreait\Firebase\Auth\SignInResult;
use Kreait\Firebase\Auth\UserRecord;
use Kreait\Firebase\Contract\Auth as FirebaseAuth;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Database\Reference;
use Kreait\Firebase\Exception\Auth\AuthError;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class FirebaseUserProviderTest extends TestCase
{
    public function test_disabled_accounts_cannot_be_restored_or_loaded_for_login(): void
    {
        $auth = Mockery::mock(FirebaseAuth::class);
        $database = Mockery::mock(Database::class);
        $record = UserRecord::fromResponseData([
            'localId' => 'disabled-user',
            'email' => 'disabled@example.com',
            'disabled' => true,
            'createdAt' => (string) now()->getTimestampMs(),
        ]);
        $auth->shouldReceive('getUser')->once()->with('disabled-user')->andReturn($record);
        $auth->shouldReceive('getUserByEmail')->once()->with('disabled@example.com')->andReturn($record);
        $database->shouldNotReceive('getReference');
        $provider = new FirebaseUserProvider($auth, $database);

        $this->assertNull($provider->retrieveById('disabled-user'));
        $this->assertNull($provider->retrieveByCredentials(['email' => 'disabled@example.com']));
    }

    public static function rememberTokens(): array
    {
        return [
            'null presented token' => [null, 'stored-token', false],
            'empty presented token' => ['', null, false],
            'missing stored token' => ['presented-token', null, false],
            'empty stored token' => ['presented-token', '', false],
            'mismatched tokens' => ['wrong-token', 'stored-token', false],
            'valid token' => ['stored-token', 'stored-token', true],
        ];
    }

    #[DataProvider('rememberTokens')]
    public function test_remember_me_requires_nonempty_matching_tokens(?string $token, ?string $storedToken, bool $accepted): void
    {
        $auth = Mockery::mock(FirebaseAuth::class);
        $database = Mockery::mock(Database::class);
        if ($token === null || $token === '') {
            $auth->shouldNotReceive('getUser');
            $database->shouldNotReceive('getReference');
        } else {
            $record = UserRecord::fromResponseData([
                'localId' => 'remember-user',
                'email' => 'remember@example.com',
                'createdAt' => (string) now()->getTimestampMs(),
            ]);
            $auth->shouldReceive('getUser')->once()->with('remember-user')->andReturn($record);
            $reference = Mockery::mock(Reference::class);
            $database->shouldReceive('getReference')->once()->with('users/remember-user/profile')->andReturn($reference);
            $reference->shouldReceive('getValue')->once()->andReturn(['remember_token' => $storedToken]);
        }

        $user = (new FirebaseUserProvider($auth, $database))->retrieveByToken('remember-user', $token);

        $this->assertSame($accepted, $user !== null);
    }

    public function test_sign_in_must_confirm_the_same_firebase_identity(): void
    {
        $auth = Mockery::mock(FirebaseAuth::class);
        $database = Mockery::mock(Database::class);
        $auth->shouldReceive('signInWithEmailAndPassword')->once()
            ->with('user@example.com', 'password')
            ->andReturn(SignInResult::fromData(['localId' => 'another-user']));
        $user = new FirebaseUser('expected-user', 'User', 'user@example.com', 'user', true);

        $this->assertFalse((new FirebaseUserProvider($auth, $database))->validateCredentials(
            $user,
            ['email' => 'user@example.com', 'password' => 'password'],
        ));
    }

    public function test_sign_in_outages_do_not_propagate_the_original_sdk_exception(): void
    {
        $auth = Mockery::mock(FirebaseAuth::class);
        $database = Mockery::mock(Database::class);
        $auth->shouldReceive('signInWithEmailAndPassword')->once()
            ->andThrow(new FailedToSignIn('private-password token=private-token'));
        $user = new FirebaseUser('expected-user', 'User', 'user@example.com', 'user', true);

        try {
            (new FirebaseUserProvider($auth, $database))->validateCredentials(
                $user,
                ['email' => 'user@example.com', 'password' => 'private-password'],
            );
            $this->fail('An unavailable authentication service must not authenticate a user.');
        } catch (AuthError $exception) {
            $this->assertNull($exception->getPrevious());
            $this->assertStringNotContainsString('private-password', $exception->getMessage());
            $this->assertStringNotContainsString('private-token', $exception->getMessage());
        }
    }

    public function test_serialized_firebase_user_does_not_expose_authentication_secrets(): void
    {
        $user = new FirebaseUser('user-id', 'User', 'user@example.com', 'user', true, 'private-remember-token');
        $serialized = json_decode(json_encode($user, JSON_THROW_ON_ERROR), true, flags: JSON_THROW_ON_ERROR);

        $this->assertArrayNotHasKey('password', $serialized);
        $this->assertArrayNotHasKey('remember_token', $serialized);
        $this->assertStringNotContainsString('private-remember-token', json_encode($serialized));
    }

    public static function unrecognizedRoles(): array
    {
        return [
            'unknown role' => ['administrator'],
            'numeric role' => [1],
            'malformed role' => [['admin']],
        ];
    }

    #[DataProvider('unrecognizedRoles')]
    public function test_unrecognized_roles_default_to_the_least_privileged_role(mixed $role): void
    {
        $auth = Mockery::mock(FirebaseAuth::class);
        $database = Mockery::mock(Database::class);
        $reference = Mockery::mock(Reference::class);
        $record = UserRecord::fromResponseData([
            'localId' => 'role-user',
            'email' => 'user@example.com',
            'createdAt' => (string) now()->getTimestampMs(),
        ]);
        $auth->shouldReceive('getUser')->once()->with('role-user')->andReturn($record);
        $database->shouldReceive('getReference')->once()->with('users/role-user/profile')->andReturn($reference);
        $reference->shouldReceive('getValue')->once()->andReturn(['role' => $role]);

        $user = (new FirebaseUserProvider($auth, $database))->retrieveById('role-user');

        $this->assertSame('user', $user->getAttribute('role'));
        $this->assertFalse($user->isAdmin());
    }

    public function test_role_and_profile_are_loaded_from_realtime_database(): void
    {
        $auth = Mockery::mock(FirebaseAuth::class);
        $database = Mockery::mock(Database::class);
        $reference = Mockery::mock(Reference::class);
        $record = UserRecord::fromResponseData([
            'localId' => 'firebase-admin-1',
            'email' => 'admin@example.com',
            'emailVerified' => true,
            'displayName' => 'Admin',
            'createdAt' => (string) now()->getTimestampMs(),
        ]);

        $auth->shouldReceive('getUser')->once()->with('firebase-admin-1')->andReturn($record);
        $database->shouldReceive('getReference')
            ->once()
            ->with('users/firebase-admin-1/profile')
            ->andReturn($reference);
        $reference->shouldReceive('getValue')->once()->andReturn([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        $user = (new FirebaseUserProvider($auth, $database))->retrieveById('firebase-admin-1');

        $this->assertSame('Admin User', $user->getAttribute('name'));
        $this->assertTrue($user->isAdmin());
        $this->assertTrue($user->hasVerifiedEmail());
    }

    public function test_missing_profile_defaults_to_least_privileged_role(): void
    {
        $auth = Mockery::mock(FirebaseAuth::class);
        $database = Mockery::mock(Database::class);
        $reference = Mockery::mock(Reference::class);
        $record = UserRecord::fromResponseData([
            'localId' => 'firebase-user-1',
            'email' => 'user@example.com',
            'createdAt' => (string) now()->getTimestampMs(),
        ]);

        $auth->shouldReceive('getUser')->once()->with('firebase-user-1')->andReturn($record);
        $database->shouldReceive('getReference')
            ->once()
            ->with('users/firebase-user-1/profile')
            ->andReturn($reference);
        $reference->shouldReceive('getValue')->once()->andReturn(null);

        $user = (new FirebaseUserProvider($auth, $database))->retrieveById('firebase-user-1');

        $this->assertFalse($user->isAdmin());
        $this->assertSame('user@example.com', $user->getAttribute('email'));
    }
}
