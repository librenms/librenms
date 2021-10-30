<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
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
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {

        //
    }
}
