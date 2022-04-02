<?php

$app_rows = dbFetchRows('SELECT * FROM `applications` WHERE `device_id`  = ?', [$device['device_id']]);

if (count($app_rows) > 0) {
    foreach ($app_rows as $app) {
        echo 'Application: ' . $app['app_type'] . ', app_id=' . $app['app_id'];
        $app_include = \LibreNMS\Config::get('install_dir') . '/includes/polling/applications/' . $app['app_type'] . '.inc.php';
        if (is_file($app_include)) {
            include $app_include;
        } else {
            echo 'ERROR: ' . $app_include . ' include file missing!';
        }
    }
    echo "\n";
}

unset($app_rows);
