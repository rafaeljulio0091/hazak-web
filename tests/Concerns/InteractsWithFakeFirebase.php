<?php

namespace Tests\Concerns;

use GuzzleHttp\Psr7\Response;
use Kreait\Firebase\Auth\SignIn\FailedToSignIn;
use Kreait\Firebase\Auth\SignInResult;
use Kreait\Firebase\Auth\SignInWithEmailAndPassword;
use Kreait\Firebase\Auth\UserRecord;
use Kreait\Firebase\Contract\Auth as FirebaseAuth;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Database\Reference;
use Mockery;

trait InteractsWithFakeFirebase
{
    protected function fakeFirebaseUser(
        string $email = 'test@example.com',
        string $uid = 'firebase-test-user',
        array $profile = [],
        string $validPassword = 'password',
        bool $disabled = false,
    ): void {
        $auth = Mockery::mock(FirebaseAuth::class);
        $database = Mockery::mock(Database::class);
        $reference = Mockery::mock(Reference::class);
        $record = UserRecord::fromResponseData([
            'localId' => $uid,
            'email' => $email,
            'emailVerified' => (bool) ($profile['email_verified_at'] ?? true),
            'disabled' => $disabled,
            'displayName' => $profile['name'] ?? 'Test User',
            'createdAt' => (string) now()->getTimestampMs(),
        ]);

        $auth->shouldReceive('getUserByEmail')->with($email)->andReturn($record)->byDefault();
        $auth->shouldReceive('getUser')->with($uid)->andReturn($record)->byDefault();
        $auth->shouldReceive('signInWithEmailAndPassword')
            ->with($email, Mockery::type('string'))
            ->andReturnUsing(function (string $providedEmail, string $password) use ($validPassword, $uid) {
                if (! hash_equals($validPassword, $password)) {
                    throw FailedToSignIn::withActionAndResponse(
                        SignInWithEmailAndPassword::fromValues($providedEmail, $password),
                        new Response(400, [], '{"error":{"message":"INVALID_LOGIN_CREDENTIALS"}}'),
                    );
                }

                return SignInResult::fromData(['localId' => $uid]);
            })->byDefault();

        $database->shouldReceive('getReference')
            ->with("users/{$uid}/profile")
            ->andReturn($reference)
            ->byDefault();
        $reference->shouldReceive('getValue')
            ->andReturn(array_merge([
                'name' => 'Test User',
                'email' => $email,
                'role' => 'user',
            ], $profile))
            ->byDefault();

        $this->app->instance(FirebaseAuth::class, $auth);
        $this->app->instance(Database::class, $database);
    }
}
