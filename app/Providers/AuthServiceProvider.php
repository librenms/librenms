<?php

namespace App\Providers;

use App\Guards\ApiTokenGuard;
use App\Models\Dashboard;
use App\Models\Device;
use App\Models\DeviceGroup;
use App\Models\PollerCluster;
use App\Models\Port;
use App\Models\ServiceTemplate;
use App\Models\User;
use App\Policies\DashboardPolicy;
use App\Policies\DeviceGroupPolicy;
use App\Policies\DevicePolicy;
use App\Policies\PollerClusterPolicy;
use App\Policies\PortPolicy;
use App\Policies\ServiceTemplatePolicy;
use App\Policies\UserPolicy;
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
        User::class => UserPolicy::class,
        Dashboard::class => DashboardPolicy::class,
        Device::class => DevicePolicy::class,
        DeviceGroup::class => DeviceGroupPolicy::class,
        PollerCluster::class => PollerClusterPolicy::class,
        Port::class => PortPolicy::class,
        ServiceTemplate::class => ServiceTemplatePolicy::class,
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

        Gate::define('global-admin', function ($user) {
            return $user->hasGlobalAdmin();
        });
        Gate::define('admin', function ($user) {
            return $user->isAdmin();
        });
        Gate::define('global-read', function ($user) {
            return $user->hasGlobalRead();
        });
        Gate::define('limited-write', function ($user) {
            return $user->hasLimitedWrite();
        });
        Gate::define('device', function ($user, $device) {
            return $user->canAccessDevice($device);
        });
    }
}
