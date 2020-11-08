<?php

namespace App\Providers;

use Illuminate\Foundation\Providers\ArtisanServiceProvider;

class CliServiceProvider extends ArtisanServiceProvider
{
    public function register()
    {
        // Restrict LibreNMS CLI commands
        if (defined('LIBRENMS_CLI') && $this->app->environment() == 'production') {
            $this->commands = [];
            $this->devCommands = [];

            // Many commands have moved into their own Service Providers now
            // So this should be rewritten to something else (more custom service providers maybe?)
            $this->app->extend('command.migrate.install', function ($command, $app) {
                return $command->setHidden(true);
            });

            $this->app->extend('command.tinker', function ($command, $app) {
                return $command->setHidden(true);
            });
        }

        parent::register();
    }
}
