<?php

namespace Tests\Feature;

use App\Auth\FirebaseUser;
use App\Services\FirebaseAccountService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\Concerns\InteractsWithFakeFirebase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use InteractsWithFakeFirebase;
    use RefreshDatabase;

    private function firebaseUser(bool $verified = true): FirebaseUser
    {
        return new FirebaseUser('firebase-test-user', 'Test User', 'test@example.com', 'user', $verified);
    }

    public function test_profile_page_is_displayed(): void
    {
        $this->actingAs($this->firebaseUser())->get('/profile')->assertOk();
    }

    public function test_profile_information_is_updated_in_firebase(): void
    {
        $user = $this->firebaseUser();
        $this->mock(FirebaseAccountService::class, function (MockInterface $mock) use ($user) {
            $mock->shouldReceive('updateProfile')->once()->with($user, 'New User', 'new@example.com');
        });

        $this->actingAs($user)->patch('/profile', [
            'name' => 'New User',
            'email' => 'new@example.com',
        ])->assertSessionHasNoErrors()->assertRedirect('/profile');
    }

    public function test_unchanged_email_profile_update_is_accepted(): void
    {
        $user = $this->firebaseUser();
        $this->mock(FirebaseAccountService::class, function (MockInterface $mock) use ($user) {
            $mock->shouldReceive('updateProfile')->once()->with($user, 'New Name', 'test@example.com');
        });

        $this->actingAs($user)->patch('/profile', [
            'name' => 'New Name',
            'email' => 'test@example.com',
        ])->assertSessionHasNoErrors()->assertRedirect('/profile');
    }

    public function test_user_can_delete_their_firebase_account_after_reauthenticating(): void
    {
        $this->fakeFirebaseUser();
        $user = $this->firebaseUser();
        $this->mock(FirebaseAccountService::class, function (MockInterface $mock) use ($user) {
            $mock->shouldReceive('verifyPassword')->once()->with($user, 'password')->andReturn(true);
            $mock->shouldReceive('delete')->once()->with($user);
        });

        $this->actingAs($user)->delete('/profile', ['password' => 'password'])
            ->assertSessionHasNoErrors()->assertRedirect('/');

        $this->assertGuest();
    }

    public function test_correct_password_is_required_to_delete_account(): void
    {
        $this->fakeFirebaseUser();
        $this->actingAs($this->firebaseUser())
            ->from('/profile')
            ->delete('/profile', ['password' => 'wrong-password'])
            ->assertSessionHasErrors('password')
            ->assertRedirect('/profile');
    }
}
