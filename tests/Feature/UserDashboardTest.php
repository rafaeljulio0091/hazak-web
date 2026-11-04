<?php

namespace Tests\Feature;

use App\Models\User;
use App\Repositories\DeviceSharingRepository;
use App\Services\Tracking\DeviceTrackingService;
use App\Services\Tracking\FirebaseTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Mockery\MockInterface;
use Tests\Fakes\InMemoryDeviceSharingRepository;
use Tests\TestCase;

class UserDashboardTest extends TestCase
{
    use RefreshDatabase;

    private InMemoryDeviceSharingRepository $sharing;

    private array $position = [
        'latitude' => -23.5,
        'longitude' => -46.6,
        'heading' => null,
        'speed' => null,
        'updatedAt' => 1700000000000,
    ];

    /** Device ids that have a location node in the (fake) Realtime Database. */
    private array $reporting = ['device-a', 'device-b', 'device-other', 'any-device'];

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->sharing = new InMemoryDeviceSharingRepository;
        $this->app->instance(DeviceSharingRepository::class, $this->sharing);

        $this->mock(DeviceTrackingService::class, function (MockInterface $mock) {
            $mock->shouldReceive('latestPosition')->andReturnUsing(
                fn (string $deviceId) => in_array($deviceId, $this->reporting, true) ? $this->position : null,
            );
        });

        $this->mock(FirebaseTokenService::class, function (MockInterface $mock) {
            $mock->shouldReceive('mintTrackingTokenFor')->andReturnUsing(
                fn (User $user) => $user->isAdmin() ? 'admin-token' : 'user-token',
            );
        });
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/my/dashboard')->assertRedirect('/login');
    }

    public function test_dashboard_redirects_regular_users_to_their_view(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/dashboard')
            ->assertRedirect(route('my.dashboard'));
    }

    public function test_dashboard_redirects_admins_to_the_admin_view(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get('/dashboard')
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_regular_users_cannot_access_the_admin_dashboard(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin/dashboard')
            ->assertForbidden();
    }

    public function test_admins_can_access_the_admin_dashboard(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Dashboard', true));
    }

    public function test_user_dashboard_renders_only_devices_shared_with_the_user(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->sharing
            ->share('device-a', $user, 'Carro')
            ->share('device-b', $user)
            ->share('device-other', $other);

        $this->actingAs($user)
            ->get('/my/dashboard')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('User/Dashboard', true)
                ->where('firebaseToken', 'user-token')
                ->has('devices', 2)
                ->where('devices.0.id', 'device-a')
                ->where('devices.0.label', 'Carro')
                ->where('devices.0.position.latitude', -23.5)
                ->where('devices.1.id', 'device-b')
            );
    }

    public function test_user_dashboard_renders_empty_state_without_shared_devices(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/my/dashboard')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('User/Dashboard', true)
                ->has('devices', 0)
            );
    }

    public function test_admins_are_not_served_the_user_dashboard(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get('/my/dashboard')
            ->assertForbidden();
    }

    public function test_user_can_track_a_device_shared_with_them(): void
    {
        $user = User::factory()->create();
        $this->sharing->share('device-a', $user);

        $this->actingAs($user)
            ->get('/my/devices/device-a')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Tracking/Show', true)
                ->where('deviceId', 'device-a')
                ->where('firebaseToken', 'user-token')
                ->where('initialPosition.longitude', -46.6)
            );
    }

    public function test_user_cannot_track_a_device_not_shared_with_them(): void
    {
        $user = User::factory()->create();
        $this->sharing->share('device-other', User::factory()->create());

        $this->actingAs($user)
            ->get('/my/devices/device-other')
            ->assertForbidden();
    }

    public function test_user_cannot_use_the_admin_tracking_route(): void
    {
        $user = User::factory()->create();
        $this->sharing->share('device-a', $user);

        $this->actingAs($user)
            ->get('/tracking/device-a')
            ->assertForbidden();
    }

    public function test_admin_can_track_any_device(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get('/tracking/any-device')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Tracking/Show', true)
                ->where('firebaseToken', 'admin-token')
            );
    }

    public function test_malformed_device_ids_are_rejected(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/my/devices/'.rawurlencode('a.b$c'))
            ->assertNotFound();
    }

    public function test_token_refresh_endpoint_is_role_aware(): void
    {
        $this->actingAs(User::factory()->create())
            ->postJson('/tracking/token')
            ->assertOk()
            ->assertExactJson(['token' => 'user-token']);
    }

    public function test_user_dashboard_lists_the_device_linked_to_the_logged_account(): void
    {
        $user = User::factory()->create();
        $ownId = (string) $user->getAuthIdentifier();
        $this->reporting[] = $ownId;
        $this->sharing->share('device-a', $user, 'Carro');

        $this->actingAs($user)
            ->get('/my/dashboard')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('User/Dashboard', true)
                ->has('devices', 2)
                ->where('devices.0.id', $ownId)
                ->where('devices.0.own', true)
                ->where('devices.0.label', 'Meu dispositivo')
                ->where('devices.0.position.latitude', -23.5)
                ->where('devices.1.id', 'device-a')
                ->where('devices.1.own', false)
            );
    }

    public function test_own_device_without_reported_position_keeps_the_empty_state(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/my/dashboard')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->has('devices', 0));
    }

    public function test_own_device_is_not_listed_twice_when_also_shared(): void
    {
        $user = User::factory()->create();
        $ownId = (string) $user->getAuthIdentifier();
        $this->reporting[] = $ownId;
        $this->sharing->share($ownId, $user, 'Duplicado');

        $this->actingAs($user)
            ->get('/my/dashboard')
            ->assertInertia(fn (Assert $page) => $page
                ->has('devices', 1)
                ->where('devices.0.own', true)
            );
    }

    public function test_user_can_track_the_device_linked_to_their_account(): void
    {
        $user = User::factory()->create();
        $ownId = (string) $user->getAuthIdentifier();
        $this->reporting[] = $ownId;

        $this->actingAs($user)
            ->get('/my/devices/'.$ownId)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Tracking/Show', true)
                ->where('deviceId', $ownId)
                ->where('initialPosition.latitude', -23.5)
            );
    }

    public function test_user_cannot_track_the_device_of_another_account(): void
    {
        $other = User::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get('/my/devices/'.$other->getAuthIdentifier())
            ->assertForbidden();
    }
}
