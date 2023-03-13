<?php

namespace App\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use LibreNMS\Config;

class ConfigServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        /** @phpstan-ignore-next-line */
        $this->app->singleton(Config::class, function ($app) {
            /** @phpstan-ignore-next-line */
            $config = new Config;
            $config->load();

            return $config;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        /** @phpstan-ignore-next-line */
        return [Config::class];
    }
}
