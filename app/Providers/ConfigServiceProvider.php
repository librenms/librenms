<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use LibreNMS\Config;

class ConfigServiceProvider extends ServiceProvider
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
        Config::load();
    }
}
