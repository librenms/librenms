<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class CliServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Restrict LibreNMS CLI commands
        if (defined('LIBRENMS_CLI') && $this->app->environment() == 'production') {
            $this->app->register(\NunoMaduro\LaravelConsoleSummary\LaravelConsoleSummaryServiceProvider::class);
        }
    }
}
