<?php

namespace Tests\Feature\Auth;

use App\Auth\FirebaseUser;
use App\Services\FirebaseAccountService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\Concerns\InteractsWithFakeFirebase;
use Tests\TestCase;

class PasswordUpdateTest extends TestCase
{
    use InteractsWithFakeFirebase;
    use RefreshDatabase;

    private function firebaseUser(): FirebaseUser
    {
        return new FirebaseUser('firebase-test-user', 'Test User', 'test@example.com', 'user', true);
    }

    public function test_password_can_be_updated_in_firebase_after_current_password_check(): void
    {
        $this->fakeFirebaseUser();
        $user = $this->firebaseUser();
        $this->mock(FirebaseAccountService::class, function (MockInterface $mock) use ($user) {
            $mock->shouldReceive('verifyPassword')->once()->with($user, 'password')->andReturn(true);
            $mock->shouldReceive('updatePassword')->once()->with($user, 'new-password');
        });

        $this->actingAs($user)->from('/profile')->put('/password', [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertSessionHasNoErrors()->assertRedirect('/profile');
    }

    public function test_correct_current_password_is_required_before_update(): void
    {
        $this->fakeFirebaseUser();
        $this->mock(FirebaseAccountService::class, function (MockInterface $mock) {
            $mock->shouldReceive('verifyPassword')->once()->andReturn(false);
        });
        $this->actingAs($this->firebaseUser())->from('/profile')->put('/password', [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertSessionHasErrors('current_password')->assertRedirect('/profile');
    }
}
