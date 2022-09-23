<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use LibreNMS\Config;

class SocialiteListenersServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        foreach (Config::get('auth.socialite.configs', []) as $provider => $config) {
            // Treat not set as "disabled"
            if (! isset($config['listener'])) {
                continue;
            }
            $listener = $config['listener'];

            if (class_exists($listener)) {
                $this->app['events']->listen(\SocialiteProviders\Manager\SocialiteWasCalled::class, "$listener@handle");
            } else {
                $this->app['log']->error("Wrong value for auth.socialite.configs.$provider.listener set, class: '$listener' does not exist!");
            }
        }
    }
}
