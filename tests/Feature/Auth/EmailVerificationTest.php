<?php

namespace Tests\Feature\Auth;

use App\Auth\FirebaseUser;
use App\Services\FirebaseAccountService;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Mockery\MockInterface;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    private function firebaseUser(): FirebaseUser
    {
        return new FirebaseUser('firebase-test-user', 'Test User', 'test@example.com', 'user', false);
    }

    public function test_email_verification_screen_can_be_rendered(): void
    {
        $this->actingAs($this->firebaseUser())->get('/verify-email')->assertOk();
    }

    public function test_email_can_be_verified_in_firebase(): void
    {
        $user = $this->firebaseUser();
        Event::fake();
        $this->mock(FirebaseAccountService::class, function (MockInterface $mock) {
            $mock->shouldReceive('markEmailAsVerified')->once();
        });

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->getAuthIdentifier(), 'hash' => sha1($user->getEmailForVerification())],
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $response->assertRedirect(route('dashboard', absolute: false).'?verified=1');
        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->hasVerifiedEmail());
        $response->assertRedirect(route('dashboard', absolute: false).'?verified=1');
    }

    public function test_email_is_not_verified_with_invalid_hash(): void
    {
        $user = $this->firebaseUser();
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->getAuthIdentifier(), 'hash' => sha1('wrong-email')],
        );

        $this->actingAs($user)->get($verificationUrl);
        $this->assertFalse($user->hasVerifiedEmail());
    }
}
