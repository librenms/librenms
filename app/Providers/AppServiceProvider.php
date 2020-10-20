<?php

namespace App\Providers;

use App\Models\Sensor;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use LibreNMS\Config;
use LibreNMS\Permissions;
use LibreNMS\Util\IP;
use LibreNMS\Util\Validate;
use Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerFacades();
        $this->registerGeocoder();

        $this->app->singleton('permissions', function ($app) {
            return new Permissions();
        });
        $this->app->singleton('device-cache', function ($app) {
            return new \LibreNMS\Cache\Device();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        \Illuminate\Pagination\Paginator::useBootstrap();

        $this->app->booted('\LibreNMS\DB\Eloquent::initLegacyListeners');
        $this->app->booted('\LibreNMS\Config::load');

        $this->bootCustomBladeDirectives();
        $this->bootCustomValidators();
        $this->configureMorphAliases();
        $this->bootObservers();
    }

    private function bootCustomBladeDirectives()
    {
        Blade::if('config', function ($key) {
            return \LibreNMS\Config::get($key);
        });
        Blade::if('notconfig', function ($key) {
            return ! \LibreNMS\Config::get($key);
        });
        Blade::if('admin', function () {
            return auth()->check() && auth()->user()->isAdmin();
        });

        Blade::directive('deviceLink', function ($arguments) {
            return "<?php echo \LibreNMS\Util\Url::deviceLink($arguments); ?>";
        });

        Blade::directive('deviceUrl', function ($arguments) {
            return "<?php echo \LibreNMS\Util\Url::deviceUrl($arguments); ?>";
        });

        Blade::directive('portLink', function ($arguments) {
            return "<?php echo \LibreNMS\Util\Url::portLink($arguments); ?>";
        });
    }

    private function configureMorphAliases()
    {
        $sensor_types = [];
        foreach (Sensor::getTypes() as $sensor_type) {
            $sensor_types[$sensor_type] = \App\Models\Sensor::class;
        }
        Relation::morphMap(array_merge([
            'interface' => \App\Models\Port::class,
            'sensor' => \App\Models\Sensor::class,
            'device' => \App\Models\Device::class,
            'device_group' => \App\Models\DeviceGroup::class,
            'location' => \App\Models\Location::class,
        ], $sensor_types));
    }

    private function registerFacades()
    {
        // replace log manager so we can add the event function
        $this->app->bind('log', function ($app) {
            return new \App\Facades\LogManager($app);
        });
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

    private function bootObservers()
    {
        \App\Models\Device::observe(\App\Observers\DeviceObserver::class);
    }

    private function bootCustomValidators()
    {
        Validator::extend('alpha_space', function ($attribute, $value) {
            return preg_match('/^[\w\s]+$/u', $value);
        });

        Validator::extend('ip_or_hostname', function ($attribute, $value, $parameters, $validator) {
            $ip = substr($value, 0, strpos($value, '/') ?: strlen($value)); // allow prefixes too

            return IP::isValid($ip) || Validate::hostname($value);
        }, __('The :attribute must a valid IP address/network or hostname.'));

        Validator::extend('is_regex', function ($attribute, $value) {
            return @preg_match($value, null) !== false;
        }, __(':attribute is not a valid regular expression'));

        Validator::extend('zero_or_exists', function ($attribute, $value, $parameters, $validator) {
            if ($value === 0) {
                return true;
            }

            $validator = Validator::make([$attribute => $value], [$attribute => 'exists:' . implode(',', $parameters)]);

            return $validator->passes();
        }, __('validation.exists'));
    }
}
