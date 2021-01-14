<?php

namespace App\Providers;

use App\Listeners\MarkNotificationsRead;
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
        \App\Events\UserCreated::class => [MarkNotificationsRead::class],
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
