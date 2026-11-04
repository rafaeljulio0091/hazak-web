<?php

namespace App\Providers;

use App\Auth\FirebaseUserProvider;
use App\Repositories\DeviceSharingRepository;
use App\Repositories\Firebase\FirebaseDeviceSharingRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Contract\Auth as FirebaseAuth;
use Kreait\Firebase\Contract\Database as FirebaseDatabase;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Http\HttpClientOptions;

class FirebaseServiceProvider extends ServiceProvider
{
    /**
     * Register the Firebase Admin SDK factory and the Auth/Database
     * contracts the app depends on, wired from config/services.php.
     */
    public function register(): void
    {
        $this->app->singleton(Factory::class, function () {
            $factory = (new Factory)->withServiceAccount([
                'type' => 'service_account',
                'project_id' => config('services.firebase.project_id'),
                'client_email' => config('services.firebase.client_email'),
                'private_key' => config('services.firebase.private_key'),
            ])->withHttpClientOptions(
                HttpClientOptions::default()
                    ->withConnectTimeout(max(1, (float) config('services.firebase.connect_timeout', 3)))
                    ->withTimeout(max(1, (float) config('services.firebase.timeout', 10))),
            );

            if ($databaseUrl = config('services.firebase.database_url')) {
                $factory = $factory->withDatabaseUri($databaseUrl);
            }

            return $factory;
        });

        $this->app->singleton(
            FirebaseAuth::class,
            fn ($app) => $app->make(Factory::class)->createAuth(),
        );

        $this->app->singleton(
            FirebaseDatabase::class,
            fn ($app) => $app->make(Factory::class)->createDatabase(),
        );

        $this->app->bind(DeviceSharingRepository::class, FirebaseDeviceSharingRepository::class);
    }

    public function boot(): void
    {
        Auth::provider('firebase', fn ($app) => new FirebaseUserProvider(
            $app->make(FirebaseAuth::class),
            $app->make(FirebaseDatabase::class),
        ));
    }
}
