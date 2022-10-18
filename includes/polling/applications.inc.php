<?php

$agent_data = $agent_data ?? [];
\DeviceCache::getPrimary()->applications->each(function ($app) use ($device, $agent_data) {
    echo 'Application: ' . $app->app_type . ', app_id=' . $app->app_id;

    $app_include = base_path('includes/polling/applications/' . \LibreNMS\Util\Clean::fileName($app->app_type) . '.inc.php');
    if (is_file($app_include)) {
        include $app_include;
    } else {
        echo 'ERROR: ' . $app_include . ' include file missing!';
    }
    echo "\n";
});
