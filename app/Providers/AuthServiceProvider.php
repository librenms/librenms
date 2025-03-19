<?php

namespace App\Providers;

use App\Guards\ApiTokenGuard;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\User::class => \App\Policies\UserPolicy::class,
        \App\Models\CustomMap::class => \App\Policies\CustomMapPolicy::class,
        \App\Models\Dashboard::class => \App\Policies\DashboardPolicy::class,
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
    public function boot(): void
    {
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

        Gate::define('global-admin', function (User $user) {
            return $user->hasAnyRole('admin', 'demo');
        });
        Gate::define('admin', function (User $user) {
            return $user->hasRole('admin');
        });
        Gate::define('global-read', function (User $user) {
            return $user->hasAnyRole('admin', 'global-read');
        });
        Gate::define('device', function (User $user, $device) {
            return $user->canAccessDevice($device);
        });

        // define super admin and global read
        Gate::before(function (User $user, string $ability) {
            if ($user->hasRole('admin')) {
                return true;  // super admin
            }

            if (in_array($ability, ['view', 'viewAny']) && $user->hasRole('global-read')) {
                return true; // global read access
            }

            return null;
        });

    }
}
