<?php

namespace Tests\Feature\Auth;

use App\Auth\FirebaseUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithFakeFirebase;
use Tests\TestCase;

class PasswordConfirmationTest extends TestCase
{
    use InteractsWithFakeFirebase;
    use RefreshDatabase;

    private function firebaseUser(): FirebaseUser
    {
        return new FirebaseUser('firebase-test-user', 'Test User', 'test@example.com', 'user', true);
    }

    public function test_confirm_password_screen_can_be_rendered(): void
    {
        $this->actingAs($this->firebaseUser())->get('/confirm-password')->assertOk();
    }

    public function test_password_can_be_confirmed_against_firebase(): void
    {
        $this->fakeFirebaseUser();
        $this->actingAs($this->firebaseUser())->post('/confirm-password', [
            'password' => 'password',
        ])->assertRedirect()->assertSessionHasNoErrors();
    }

    public function test_password_is_not_confirmed_with_invalid_firebase_password(): void
    {
        $this->fakeFirebaseUser();
        $this->actingAs($this->firebaseUser())->post('/confirm-password', [
            'password' => 'wrong-password',
        ])->assertSessionHasErrors();
    }
}
