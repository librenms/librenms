<?php

namespace App\Providers;

use Illuminate\Foundation\Providers\ArtisanServiceProvider;

class CliServiceProvider extends ArtisanServiceProvider
{
    public function register()
    {
        // Restrict LibreNMS CLI commands
        if (defined('LIBRENMS_CLI') && $this->app->environment() == 'production') {
            $this->commands = array_intersect_key($this->commands, [
                "Migrate" => true,
            ]);

            $this->registerCommands($this->commands);
        } else {
            $this->app->register(\Laravel\Tinker\TinkerServiceProvider::class);
            parent::register();
        }
    }
}
