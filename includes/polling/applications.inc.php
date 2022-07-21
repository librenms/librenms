<?php

\DeviceCache::getPrimary()->applications->each(function ($app_model) use ($device) {
    echo 'Application: ' . $app_model->app_type . ', app_id=' . $app_model->app_id;
    $app = [
        'app_id' => $app_model->app_id,
        'device_id' => $app_model->device_id,
        'app_type' => $app_model->app_type,
    ];
    $app_data = $app_model->data;
    $app_include = base_path('includes/polling/applications/' . \LibreNMS\Util\Clean::fileName($app_model->app_type) . '.inc.php');
    if (is_file($app_include)) {
        include $app_include;
        $app_model->data = $app_data;
        $app_model->save();
    } else {
        echo 'ERROR: ' . $app_include . ' include file missing!';
    }
    echo "\n";
});
