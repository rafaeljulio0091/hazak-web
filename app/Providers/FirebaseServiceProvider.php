<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Contract\Auth as FirebaseAuth;
use Kreait\Firebase\Contract\Database as FirebaseDatabase;
use Kreait\Firebase\Factory;

class FirebaseServiceProvider extends ServiceProvider
{
    /**
     * Register the Firebase Admin SDK factory and the Auth/Database
     * contracts the app depends on, wired from config/services.php.
     */
    public function register(): void
    {
        $this->app->singleton(Factory::class, function () {
            $factory = (new Factory())->withServiceAccount([
                'type' => 'service_account',
                'project_id' => config('services.firebase.project_id'),
                'client_email' => config('services.firebase.client_email'),
                'private_key' => config('services.firebase.private_key'),
            ]);

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
    }
}
