<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class CliServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Restrict to LibreNMS CLI commands
        /** @phpstan-ignore equal.alwaysFalse, booleanAnd.alwaysFalse */
        if (defined('ARTISAN_BINARY') && ARTISAN_BINARY == 'lnms') {
            $this->app->register(\NunoMaduro\LaravelConsoleSummary\LaravelConsoleSummaryServiceProvider::class);
        }
    }
}
