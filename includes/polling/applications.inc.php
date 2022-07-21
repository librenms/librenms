<?php

$found_apps = \DeviceCache::getPrimary()->applications;
foreach ($found_apps as $app_model) {
        echo 'Application: ' . $app_model->app_type. ', app_id=' . $app_model->app_id;
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
}
