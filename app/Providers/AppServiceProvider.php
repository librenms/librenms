<?php

namespace App\Providers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use LibreNMS\Config;
use LibreNMS\Exceptions\DatabaseConnectException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        try {
            Config::loadFromDatabase();
        } catch (\ErrorException $ee) {
            Log::error("Error in config.php!\n" . $ee->getMessage() . PHP_EOL);
        } catch (DatabaseConnectException $dbce) {
            Log::error("Error connecting to database.\n" . $dbce->getMessage() . PHP_EOL);
        }

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
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);

            if (config('app.debug')) {
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
