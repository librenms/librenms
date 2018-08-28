<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Handlers\Fallback;

class SnmptrapProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(SnmptrapHandler::class, function ($app, $oid) {
            if ($handler = config('snmptraps.trap_handlers.' . reset($oid))) {
                return $app->make($handler);
            }

            return $app->make(Fallback::class);
        });
    }
}
