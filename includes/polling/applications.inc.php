<?php

use Illuminate\Support\Facades\Cache;

$submodules = App\Facades\LibrenmsConfig::get('poller_submodules.applications');
$agent_data = Cache::driver('array')->get('agent_data', []);

DeviceCache::getPrimary()->applications
    ->when($submodules, fn ($apps) => $apps->filter(fn ($app) => in_array($app->app_type, $submodules)))
    ->each(function ($app) use ($os, $device, $agent_data): void {
        echo 'Application: ' . $app->app_type . ', app_id=' . $app->app_id;

        if (! $os->pollApplication($app, $agent_data)) {
            $app_include = base_path('includes/polling/applications/' . LibreNMS\Util\Clean::fileName($app->app_type) . '.inc.php');
            if (is_file($app_include)) {
                include $app_include;
            } else {
                echo 'ERROR: ' . $app_include . ' include file missing!';
            }
        }
        echo "\n";
    });
