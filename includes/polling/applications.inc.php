<?php

\DeviceCache::getPrimary()->applications->each(function ($app) use ($device) {
    echo 'Application: ' . $app->app_type . ', app_id=' . $app->app_id;

    $app_include = base_path('includes/polling/applications/' . \LibreNMS\Util\Clean::fileName($app->app_type) . '.inc.php');
    if (is_file($app_include)) {
        include $app_include;
        $app->save();
    } else {
        echo 'ERROR: ' . $app_include . ' include file missing!';
    }
    echo "\n";
});
