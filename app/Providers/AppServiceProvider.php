<?php

namespace App\Providers;

use App\Models\Sensor;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use LibreNMS\Cache\PermissionsCache;
use LibreNMS\Config;
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
    public function register(): void
    {
        $this->registerFacades();
        $this->registerGeocoder();

        $this->app->singleton('permissions', function ($app) {
            return new PermissionsCache();
        });
        $this->app->singleton('device-cache', function ($app) {
            return new \LibreNMS\Cache\Device();
        });
        $this->app->singleton('git', function ($app) {
            return new \LibreNMS\Util\Git();
        });

        $this->app->bind(\App\Models\Device::class, function () {
            /** @var \LibreNMS\Cache\Device $cache */
            $cache = $this->app->make('device-cache');

            return $cache->hasPrimary() ? $cache->getPrimary() : new \App\Models\Device;
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        \Illuminate\Pagination\Paginator::useBootstrap();

        $this->bootCustomBladeDirectives();
        $this->bootCustomValidators();
        $this->configureMorphAliases();
        $this->bootObservers();
    }

    private function bootCustomBladeDirectives()
    {
        Blade::if('config', function ($key, $value = true) {
            return \LibreNMS\Config::get($key) == $value;
        });
        Blade::if('notconfig', function ($key) {
            return ! \LibreNMS\Config::get($key);
        });
        Blade::if('admin', function () {
            return auth()->check() && auth()->user()->isAdmin();
        });

        Blade::directive('deviceUrl', function ($arguments) {
            return "<?php echo \LibreNMS\Util\Url::deviceUrl($arguments); ?>";
        });

        // Graphing
        Blade::directive('signedGraphUrl', function ($vars) {
            return "<?php echo \LibreNMS\Util\Url::forExternalGraph($vars); ?>";
        });

        Blade::directive('signedGraphTag', function ($vars) {
            return "<?php echo '<img class=\"librenms-graph\" src=\"' . \LibreNMS\Util\Url::forExternalGraph($vars) . '\" />'; ?>";
        });

        Blade::directive('graphImage', function ($vars, $flags = 0) {
            return "<?php echo \LibreNMS\Util\Graph::getImageData($vars, $flags); ?>";
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
        \App\Models\Package::observe(\App\Observers\PackageObserver::class);
        \App\Models\Service::observe(\App\Observers\ServiceObserver::class);
        \App\Models\Stp::observe(\App\Observers\StpObserver::class);
        \App\Models\User::observe(\App\Observers\UserObserver::class);
        \App\Models\Vminfo::observe(\App\Observers\VminfoObserver::class);
    }

    private function bootCustomValidators()
    {
        Validator::extend('alpha_space', function ($attribute, $value) {
            return preg_match('/^[\w\s]+$/u', $value);
        });

        Validator::extend('ip_or_hostname', function ($attribute, $value, $parameters, $validator) {
            $ip = substr($value, 0, strpos($value, '/') ?: strlen($value)); // allow prefixes too

            return IP::isValid($ip) || Validate::hostname($value);
        });

        Validator::extend('is_regex', function ($attribute, $value) {
            return @preg_match($value, '') !== false;
        });

        Validator::extend('keys_in', function ($attribute, $value, $parameters, $validator) {
            $extra_keys = is_array($value) ? array_diff(array_keys($value), $parameters) : [];

            $validator->addReplacer('keys_in', function ($message, $attribute, $rule, $parameters) use ($extra_keys) {
                return str_replace(
                    [':extra', ':values'],
                    [implode(',', $extra_keys), implode(',', $parameters)],
                    $message);
            });

            return is_array($value) && empty($extra_keys);
        });

        Validator::extend('zero_or_exists', function ($attribute, $value, $parameters, $validator) {
            if ($value === 0 || $value === '0') {
                return true;
            }

            $validator = Validator::make([$attribute => $value], [$attribute => 'exists:' . implode(',', $parameters)]);

            return $validator->passes();
        }, trans('validation.exists'));

        Validator::extend('url_or_xml', function ($attribute, $value): bool {
            if (! is_string($value)) {
                return false;
            }

            if (filter_var($value, FILTER_VALIDATE_URL) !== false) {
                return true;
            }

            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($value);
            if ($xml !== false) {
                return true;
            }

            return false;
        });
    }
}
