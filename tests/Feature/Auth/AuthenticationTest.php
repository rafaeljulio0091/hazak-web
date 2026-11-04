<?php

namespace Tests\Feature\Auth;

use App\Auth\FirebaseUser;
use App\Repositories\DeviceSharingRepository;
use App\Services\Tracking\FirebaseTokenService;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Testing\AssertableInertia as Assert;
use Kreait\Firebase\Auth\SignIn\FailedToSignIn;
use Kreait\Firebase\Auth\SignInWithEmailAndPassword;
use Kreait\Firebase\Contract\Auth as FirebaseAuth;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Exception\Auth\ApiConnectionFailed;
use Kreait\Firebase\Exception\Auth\UserNotFound;
use Kreait\Firebase\Exception\Database\PermissionDenied;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\InteractsWithFakeFirebase;
use Tests\Fakes\InMemoryDeviceSharingRepository;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use InteractsWithFakeFirebase;
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_users_can_authenticate_using_firebase_credentials(): void
    {
        $this->fakeFirebaseUser();

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_cannot_authenticate_with_invalid_firebase_password(): void
    {
        $this->fakeFirebaseUser();

        $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_normalizes_email_without_changing_the_password(): void
    {
        $this->fakeFirebaseUser(validPassword: ' password ');

        $this->post('/login', [
            'email' => ' TEST@EXAMPLE.COM ',
            'password' => ' password ',
        ])->assertRedirect('/dashboard')->assertSessionHasNoErrors();

        $this->assertAuthenticated();
    }

    public function test_invalid_credentials_return_generic_inertia_feedback_without_passwords(): void
    {
        $this->fakeFirebaseUser();

        $response = $this->from('/login')->post('/login', [
            'email' => 'test@example.com',
            'password' => 'private-wrong-password',
            'password_confirmation' => 'private-confirmation',
            'current_password' => 'private-current-password',
        ]);

        $response->assertRedirect('/login')
            ->assertSessionHasErrors(['email' => 'E-mail ou senha inválidos.'])
            ->assertSessionMissing('_old_input.password')
            ->assertSessionMissing('_old_input.password_confirmation')
            ->assertSessionMissing('_old_input.current_password');
        $this->assertGuest();

        $this->get('/login')->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('errors.email', 'E-mail ou senha inválidos.'))
            ->assertDontSee('private-wrong-password', false)
            ->assertDontSee('private-confirmation', false)
            ->assertDontSee('private-current-password', false);
    }

    public function test_unknown_accounts_receive_the_same_credentials_error(): void
    {
        $this->fakeFirebaseUser();
        $this->app->make(FirebaseAuth::class)->shouldReceive('getUserByEmail')
            ->with('missing@example.com')->once()->andThrow(new UserNotFound('Private account detail'));

        $this->from('/login')->post('/login', [
            'email' => 'missing@example.com',
            'password' => 'password',
        ])->assertRedirect('/login')->assertSessionHasErrors(['email' => 'E-mail ou senha inválidos.']);

        $this->assertGuest();
    }

    public function test_disabled_accounts_cannot_login(): void
    {
        $this->fakeFirebaseUser(disabled: true);
        $this->app->make(FirebaseAuth::class)->shouldNotReceive('signInWithEmailAndPassword');

        $this->from('/login')->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ])->assertRedirect('/login')->assertSessionHasErrors(['email' => 'E-mail ou senha inválidos.']);

        $this->assertGuest();
    }

    public static function invalidLoginInputs(): array
    {
        return [
            'short password' => [['password' => 'short'], 'password'],
            'oversized password' => [['password' => str_repeat('a', 4097)], 'password'],
            'password array' => [['password' => ['password']], 'password'],
            'email array' => [['email' => ['test@example.com']], 'email'],
            'invalid remember flag' => [['remember' => 'not-a-boolean'], 'remember'],
        ];
    }

    #[DataProvider('invalidLoginInputs')]
    public function test_malformed_credentials_are_rejected_before_calling_firebase(array $input, string $error): void
    {
        $this->fakeFirebaseUser();
        $this->app->make(FirebaseAuth::class)->shouldNotReceive('getUserByEmail');
        $this->app->make(FirebaseAuth::class)->shouldNotReceive('signInWithEmailAndPassword');

        $this->from('/login')->post('/login', array_merge([
            'email' => 'test@example.com',
            'password' => 'password',
        ], $input))->assertRedirect('/login')->assertSessionHasErrors($error)
            ->assertSessionMissing('_old_input.password');

        $this->assertGuest();
    }

    public static function firebaseFailures(): array
    {
        return [
            'auth lookup unavailable' => ['lookup'],
            'realtime profile unavailable' => ['profile'],
            'sign in network failure' => ['network'],
            'password provider disabled' => ['configuration'],
            'remember token write failed' => ['remember'],
        ];
    }

    #[DataProvider('firebaseFailures')]
    public function test_firebase_failures_are_safe_and_leave_no_authenticated_session(string $failure): void
    {
        $this->fakeFirebaseUser();
        Log::shouldReceive('warning')->once()->with('Firebase login unavailable.', Mockery::on(
            fn (array $context) => array_keys($context) === ['exception_type'] && is_string($context['exception_type']),
        ));

        $auth = $this->app->make(FirebaseAuth::class);
        if ($failure === 'lookup') {
            $auth->shouldReceive('getUserByEmail')->once()->andThrow(new ApiConnectionFailed('private-password token=private-token'));
        } elseif (in_array($failure, ['profile', 'remember'], true)) {
            $reference = $this->app->make(Database::class)->getReference('users/firebase-test-user/profile');
            $reference->shouldReceive($failure === 'profile' ? 'getValue' : 'update')
                ->once()->andThrow(new PermissionDenied('private-password token=private-token'));
        } else {
            $exception = $failure === 'network'
                ? new FailedToSignIn('private-password token=private-token')
                : FailedToSignIn::withActionAndResponse(
                    SignInWithEmailAndPassword::fromValues('test@example.com', 'password'),
                    new Response(400, [], '{"error":{"message":"OPERATION_NOT_ALLOWED"}}'),
                );
            $auth->shouldReceive('signInWithEmailAndPassword')->once()->andThrow($exception);
        }

        $this->from('/login')->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
            'remember' => $failure === 'remember',
        ])->assertRedirect('/login')
            ->assertSessionHasErrors(['authentication' => 'Não foi possível entrar agora. Tente novamente em instantes.'])
            ->assertSessionMissing($this->app['auth']->guard()->getName())
            ->assertSessionMissing('_old_input.password');

        $this->assertGuest();
        $this->get('/login')->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('errors.authentication', 'Não foi possível entrar agora. Tente novamente em instantes.'))
            ->assertDontSee('private-password', false)
            ->assertDontSee('private-token', false)
            ->assertDontSee('OPERATION_NOT_ALLOWED', false);
    }

    public function test_login_is_throttled_after_five_failed_attempts(): void
    {
        $this->fakeFirebaseUser();
        $auth = $this->app->make(FirebaseAuth::class);
        $auth->shouldReceive('signInWithEmailAndPassword')->times(5)
            ->andThrow(new FailedToSignIn('INVALID_LOGIN_CREDENTIALS'));

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->from('/login')->post('/login', [
                'email' => 'test@example.com',
                'password' => 'wrong-password',
            ])->assertSessionHasErrors(['email' => 'E-mail ou senha inválidos.']);
        }

        $this->post('/login', [
            'email' => ' TEST@example.com ',
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertStringContainsString('Muitas tentativas de acesso.', session('errors')->first('email'));
        $this->assertGuest();
    }

    public function test_successful_login_clears_failed_attempts_and_rotates_the_session(): void
    {
        $this->fakeFirebaseUser();
        $this->get('/login');
        $previousSession = session()->getId();
        $previousToken = session()->token();
        $throttleKey = 'login:'.hash('sha256', 'test@example.com|127.0.0.1');
        RateLimiter::hit($throttleKey);

        $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ])->assertRedirect('/dashboard');

        $this->assertSame(0, RateLimiter::attempts($throttleKey));
        $this->assertNotSame($previousSession, session()->getId());
        $this->assertNotSame($previousToken, session()->token());
    }

    public function test_authentication_responses_cannot_be_cached(): void
    {
        $response = $this->get('/login');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $response->assertHeader('Pragma', 'no-cache');

        $this->fakeFirebaseUser();
        $response = $this->from('/login')->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    public function test_production_redirects_login_form_to_https_and_rejects_insecure_submissions(): void
    {
        $this->app->instance('env', 'production');
        $this->fakeFirebaseUser();
        $this->app->make(FirebaseAuth::class)->shouldNotReceive('signInWithEmailAndPassword');

        $this->get('http://localhost/login')->assertRedirect('https://localhost/login');
        $this->withSession(['_token' => 'csrf-token'])->post('http://localhost/login', [
            '_token' => 'csrf-token',
            'email' => 'test@example.com',
            'password' => 'password',
        ])->assertStatus(400)->assertSee('Use HTTPS');

        $this->assertGuest();
    }

    public function test_production_allows_login_over_https_with_secure_http_only_cookie(): void
    {
        $this->app->instance('env', 'production');
        config(['session.secure' => true]);
        $this->fakeFirebaseUser();

        $response = $this->withSession(['_token' => 'csrf-token'])->post('https://localhost/login', [
            '_token' => 'csrf-token',
            'email' => 'test@example.com',
            'password' => 'password',
        ])->assertRedirect('/dashboard');

        $cookie = $response->getCookie(config('session.cookie'));
        $this->assertTrue($cookie->isSecure());
        $this->assertTrue($cookie->isHttpOnly());
        $this->assertSame('lax', $cookie->getSameSite());
        $this->assertAuthenticated();
    }

    public function test_login_requires_a_valid_csrf_token(): void
    {
        $this->app->bind(ValidateCsrfToken::class, fn ($app) => new class($app, $app['encrypter']) extends ValidateCsrfToken
        {
            protected function runningUnitTests(): bool
            {
                return false;
            }
        });

        $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ])->assertStatus(419);

        $this->assertGuest();
    }

    public static function dashboardRoles(): array
    {
        return [
            'regular user' => ['user', '/my/dashboard', 'User/Dashboard'],
            'administrator' => ['admin', '/admin/dashboard', 'Dashboard'],
        ];
    }

    #[DataProvider('dashboardRoles')]
    public function test_firebase_login_persists_across_database_session_requests(
        string $role,
        string $dashboard,
        string $component,
    ): void {
        config(['session.driver' => 'database']);
        $this->withoutVite();
        $this->fakeFirebaseUser(profile: ['role' => $role]);
        $this->app->instance(DeviceSharingRepository::class, new InMemoryDeviceSharingRepository);
        $this->mock(FirebaseTokenService::class)->shouldReceive('mintTrackingTokenFor')
            ->with(Mockery::on(fn ($user) => $user instanceof FirebaseUser
                && $user->getAuthIdentifier() === 'firebase-test-user'))
            ->andReturn('tracking-token');

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('sessions', ['user_id' => 'firebase-test-user']);
        $payload = unserialize(base64_decode(DB::table('sessions')->sole()->payload), ['allowed_classes' => false]);
        $this->assertSame('firebase-test-user', $payload[$this->app['auth']->guard()->getName()]);

        foreach (['/dashboard', $dashboard] as $url) {
            $cookie = $response->getCookie(config('session.cookie'));
            $this->assertNotNull($cookie);
            $this->app['auth']->forgetGuards();
            $this->app->forgetInstance('auth.driver');
            $this->app['session']->forgetDrivers();
            $this->app->forgetInstance('session.store');

            $response = $this->withCookie($cookie->getName(), $cookie->getValue())->get($url);

            if ($url === '/dashboard') {
                $response->assertRedirect($dashboard);
            }
        }

        $response->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component($component)
            ->where('auth.user.id', 'firebase-test-user')
            ->where('auth.user.role', $role));

        $forbiddenDashboard = $role === 'admin' ? '/my/dashboard' : '/admin/dashboard';
        $this->get($forbiddenDashboard)->assertForbidden();
    }

    public function test_users_can_logout(): void
    {
        $user = new FirebaseUser('firebase-test-user', 'Test User', 'test@example.com', 'user', true);

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
