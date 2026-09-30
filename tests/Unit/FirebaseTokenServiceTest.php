<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\Tracking\FirebaseTokenService;
use Kreait\Firebase\Contract\Auth;
use Lcobucci\JWT\UnencryptedToken;
use Mockery;
use PHPUnit\Framework\TestCase;

class FirebaseTokenServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    private function serviceExpectingClaims(array $claims): FirebaseTokenService
    {
        $token = Mockery::mock(UnencryptedToken::class);
        $token->shouldReceive('toString')->andReturn('jwt');

        $auth = Mockery::mock(Auth::class);
        $auth->shouldReceive('createCustomToken')->once()->with('5', $claims)->andReturn($token);

        return new FirebaseTokenService($auth);
    }

    public function test_regular_users_do_not_get_the_dashboard_claim(): void
    {
        $user = (new User)->forceFill(['id' => 5, 'role' => 'user']);

        $this->assertSame('jwt', $this->serviceExpectingClaims([])->mintTrackingTokenFor($user));
    }

    public function test_admins_get_the_dashboard_claim(): void
    {
        $user = (new User)->forceFill(['id' => 5, 'role' => 'admin']);

        $this->assertSame('jwt', $this->serviceExpectingClaims(['role' => 'dashboard'])->mintTrackingTokenFor($user));
    }
}
