<?php

namespace App\Providers;

use App\Models\DeviceGroup;
use App\Models\User;
use App\Policies\DeviceGroupPolicy;
use App\Policies\UserPolicy;
use App\Guards\ApiTokenGuard;
use Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        User::class => UserPolicy::class,
        DeviceGroup::class => DeviceGroupPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Auth::provider('legacy', function ($app, array $config) {
            return new LegacyUserProvider();
        });

        Auth::provider('token_provider', function ($app, array $config) {
            return new TokenUserProvider();
        });

        Auth::extend('token_driver', function ($app, $name, array $config) {
            $userProvider = $app->make(TokenUserProvider::class);
            $request = $app->make('request');
            return new ApiTokenGuard($userProvider, $request);
        });

        Gate::define('global-admin', function ($user) {
            return $user->hasGlobalAdmin();
        });
        Gate::define('admin', function ($user) {
            return $user->isAdmin();
        });
        Gate::define('global-read', function ($user) {
            return $user->hasGlobalRead();
        });
        Gate::define('device', function ($user, $device) {
            return $user->canAccessDevice($device);
        });
    }
}
