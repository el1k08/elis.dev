<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use WorkOS\WorkOS; // SDK WorkOS

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function (User $user, string $ability) {
            $workos = new WorkOS;
            $accessToken = $workos::decodeAccessToken(session('workos_access_token'));
            $permissions = $accessToken['permissions'] ?? [];
            if (in_array($ability, $permissions)) {
                return true;
            }
        });
    }
}
