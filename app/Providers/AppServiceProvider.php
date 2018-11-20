<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use LibreNMS\Config;
use LibreNMS\Exceptions\DatabaseConnectException;
use Request;

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
        // Install legacy dbFacile fetch mode listener
        \LibreNMS\DB\Eloquent::initLegacyListeners();

        // load config
        Config::load();

        // direct log output to librenms.log
        Log::getMonolog()->popHandler(); // remove existing errorlog logger
        Log::useFiles(Config::get('log_file', base_path('logs/librenms.log')), 'error');

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

        $this->configureMorphAliases();

        // Development service providers
        if ($this->app->environment() !== 'production') {
            if (class_exists(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class)) {
                $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
            }

            if (config('app.debug') && class_exists(\Barryvdh\Debugbar\ServiceProvider::class)) {
                // disable debugbar for api routes
                if (!Request::is('api/*')) {
                    $this->app->register(\Barryvdh\Debugbar\ServiceProvider::class);
                }
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
        $this->registerGeocoder();
    }

    private function configureMorphAliases()
    {
        Relation::morphMap([
            'interface' => \App\Models\Port::class,
            'sensor' => \App\Models\Sensor::class,
        ]);
    }

    private function registerGeocoder()
    {
        $this->app->alias(\LibreNMS\Interfaces\Geocoder::class, 'geocoder');
        $this->app->bind(\LibreNMS\Interfaces\Geocoder::class, function ($app) {
            $engine = Config::get('geoloc.engine');

            switch ($engine) {
                case 'mapquest':
                    Log::debug('MapQuest geocode engine');
                    return $app->make(\App\ApiClients\MapquestApi::class);
                case 'bing':
                    Log::debug('Bing geocode engine');
                    return $app->make(\App\ApiClients\BingApi::class);
                case 'openstreetmap':
                    Log::debug('OpenStreetMap geocode engine');
                    return $app->make(\App\ApiClients\NominatimApi::class);
                default:
                case 'google':
                    Log::debug('Google Maps geocode engine');
                    return $app->make(\App\ApiClients\GoogleMapsApi::class);
            }
        });
    }
}
