<?php

namespace Tests\Feature\Auth;

use App\Services\FirebaseAccountService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $this->get('/forgot-password')->assertOk();
    }

    public function test_reset_password_link_is_requested_from_firebase(): void
    {
        $this->mock(FirebaseAccountService::class, function (MockInterface $mock) {
            $mock->shouldReceive('sendPasswordResetLink')->once()->with('test@example.com');
        });

        $this->post('/forgot-password', ['email' => 'test@example.com'])
            ->assertSessionHas('status', trans('passwords.sent'));
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        $this->get('/reset-password/firebase-code?email=test%40example.com')->assertOk();
    }

    public function test_password_can_be_reset_with_a_firebase_action_code(): void
    {
        $this->mock(FirebaseAccountService::class, function (MockInterface $mock) {
            $mock->shouldReceive('resetPassword')
                ->once()
                ->with('test@example.com', 'firebase-code', 'new-password');
        });

        $this->post('/reset-password', [
            'token' => 'firebase-code',
            'email' => 'test@example.com',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertSessionHasNoErrors()->assertRedirect(route('login'));
    }
}
