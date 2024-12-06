<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        \Illuminate\Auth\Events\Login::class => ['App\Listeners\AuthEventListener@login'],
        \Illuminate\Auth\Events\Logout::class => ['App\Listeners\AuthEventListener@logout'],
        \App\Events\UserCreated::class => [
            \App\Listeners\MarkNotificationsRead::class,
        ],
        \App\Events\PollingDevice::class => [
        ],
        \App\Events\DevicePolled::class => [
            \App\Listeners\CheckAlerts::class,
            \App\Listeners\UpdateDeviceGroups::class,
        ],
        \Illuminate\Database\Events\QueryExecuted::class => [
            \App\Listeners\QueryDebugListener::class,
            \App\Listeners\QueryMetricListener::class,
        ],
        \Illuminate\Database\Events\StatementPrepared::class => [
            \App\Listeners\LegacyQueryListener::class,
        ],
        \SocialiteProviders\Manager\SocialiteWasCalled::class => [
            \App\Listeners\SocialiteWasCalledListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
