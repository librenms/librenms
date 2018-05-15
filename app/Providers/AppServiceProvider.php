<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use LibreNMS\Config;
use LibreNMS\Exceptions\DatabaseConnectException;

include_once __DIR__ . '/../../includes/dbFacile.php';

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     * @throws DatabaseConnectException caught by App\Exceptions\Handler and displayed to the user
     */
    public function boot()
    {
        // connect legacy db
        //FIXME this is for auth right now, remove later
        $db_config = config('database.connections')[config('database.default')];
        dbConnect(
            $db_config['host'],
            $db_config['username'],
            $db_config['password'],
            $db_config['database'],
            $db_config['port'],
            $db_config['unix_socket']
        );

        // load config
        Config::load();

        // direct log output to librenms.log
        Log::useFiles(Config::get('log_file', base_path('logs/librenms.log')));


        // Blade directives (Yucky because of < L5.5)
        Blade::directive('config', function ($key) {
            return "<?php if (\LibreNMS\Config::get(($key))): ?>";
        });
        Blade::directive('notconfig', function ($key) {
            return "<?php if (!\LibreNMS\Config::get(($key))): ?>";
        });
        Blade::directive('endconfig', function () {
            return "<?php endif; ?>";
        });
        Blade::directive('admin', function () {
            return "<?php if (auth()->check() && auth()->user()->isAdmin()): ?>";
        });
        Blade::directive('endadmin', function () {
            return "<?php endif; ?>";
        });

        // Development service providers
        if ($this->app->environment() !== 'production') {
            if (class_exists(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class)) {
                $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
            }

            if (config('app.debug') && class_exists(\Barryvdh\Debugbar\ServiceProvider::class)) {
                $this->app->register(\Barryvdh\Debugbar\ServiceProvider::class);
            }
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
