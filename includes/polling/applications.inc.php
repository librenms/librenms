<?php

use Illuminate\Support\Facades\Log;
use LibreNMS\Modules\UnixAgent;

$agent_data = UnixAgent::getData($device['device_id']);
\DeviceCache::getPrimary()->applications->each(function ($app) use ($device, $agent_data): void {
    Log::info('Application: ' . $app->app_type . ', app_id=' . $app->app_id);

    $app_include = base_path('includes/polling/applications/' . \LibreNMS\Util\Clean::fileName($app->app_type) . '.inc.php');
    if (is_file($app_include)) {
        include $app_include;
    } else {
        Log::error('ERROR: ' . $app_include . ' include file missing!');
    }
});
