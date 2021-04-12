<?php

namespace App\Providers;

use App\Guards\ApiTokenGuard;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        \App\Models\User::class => \App\Policies\UserPolicy::class,
        \App\Models\Device::class => \App\Policies\DevicePolicy::class,
        \App\Models\DeviceGroup::class => \App\Policies\DeviceGroupPolicy::class,
        \App\Models\PollerCluster::class => \App\Policies\PollerClusterPolicy::class,
        \App\Models\Port::class => \App\Policies\PortPolicy::class,
        \App\Models\ServiceTemplate::class => \App\Policies\ServiceTemplatePolicy::class,
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
