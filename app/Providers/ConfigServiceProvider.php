<?php

namespace App\Providers;

use App\ConfigRepository;
use App\Facades\LibrenmsConfig;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class ConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton('librenms-config', function () {
            return new ConfigRepository;
        });

        // if we skipped loading the DB the first time config was called, load it when it is available
        $this->callAfterResolving('db', function () {
            if ($this->app->resolved('librenms-config')) {
                Log::error('Loaded config twice due to bad initialization order');
                LibrenmsConfig::reload();
            }
        });
    }
}
