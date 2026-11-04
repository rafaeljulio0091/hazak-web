<?php

namespace Tests\Feature\Auth;

use App\Auth\FirebaseUser;
use App\DTOs\Auth\RegisterUserData;
use App\Services\FirebaseAccountService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $this->get('/register')->assertOk();
    }

    public function test_new_users_are_created_and_authenticated_through_firebase(): void
    {
        $user = new FirebaseUser('firebase-new-user', 'Test User', 'test@example.com', 'user', false);

        $this->mock(FirebaseAccountService::class, function (MockInterface $mock) use ($user) {
            $mock->shouldReceive('register')
                ->once()
                ->with(Mockery::type(RegisterUserData::class))
                ->andReturn($user);
            $mock->shouldReceive('sendEmailVerification')->once()->with($user);
        });

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false))->assertSessionHasNoErrors();
        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }
}
