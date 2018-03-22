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
        Config::load();
        try {
            Config::loadFromDatabase();
        } catch (DatabaseConnectException $e) {
            //
        }

        // direct log output to librenms.log
        Log::useFiles(Config::get('log_file'));


        // check file/folder permissions
        $check = [
            base_path('bootstrap/cache'),
            base_path('storage'),
            Config::get('log_file')
        ];
        foreach ($check as $path) {
            if (!is_writable($path)) {
                $user = Config::get('user');
                $group = Config::get('group', $user);
                $message = [
                    "Error: $path is not writable! Run these commands to fix:",
                    "chown -R $user:$group rrd/ logs/ storage/ bootstrap/cache/",
                    'setfacl -R -m g::rwx rrd/ logs/ storage/ bootstrap/cache/',
                    'setfacl -d -m g::rwx rrd/ logs/ storage/ bootstrap/cache/'
                ];
                if (App::runningInConsole()) {
                    vprintf("%s\n\n%s\n%s\n%s", $message);
                } else {
                    vprintf("<h3 style='color: firebrick;'>%s</h3><p>%s<br />%s<br />%s</p>", $message);
                }
                exit;
            }
        }


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
