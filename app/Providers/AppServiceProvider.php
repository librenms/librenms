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

        $log_file = Config::get('log_file', base_path('logs/librenms.log'));

        // check file/folder permissions
        $check = [
            base_path('bootstrap/cache'),
            base_path('storage'),
            $log_file
        ];
        foreach ($check as $path) {
            if (!is_writable($path)) {
                $user = Config::get('user', 'librenms');
                $group = Config::get('group', $user);
                $message = [
                    "Error: $path is not writable! Run these commands to fix:",
                    "chown -R $user:$group rrd/ logs/ storage/ bootstrap/cache/",
                    'setfacl -R -m g::rwx rrd/ logs/ storage/ bootstrap/cache/',
                    'setfacl -d -m g::rwx rrd/ logs/ storage/ bootstrap/cache/'
                ];
                if (App::runningInConsole()) {
                    vprintf("%s\n\n%s\n%s\n%s\n", $message);
                } else {
                    vprintf("<h3 style='color: firebrick;'>%s</h3><p>%s<br />%s<br />%s</p>", $message);
                }
                exit;
            }
        }

        // direct log output to librenms.log
        Log::useFiles($log_file);


        // Blade directives (Yucky because of < L5.5)
        Blade::directive('config', function ($key, $default=false) {
            return "<?php if (\LibreNMS\Config::get(\$key, \$default)): ?>";
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

        if ($this->app->environment() !== 'production') {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
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
