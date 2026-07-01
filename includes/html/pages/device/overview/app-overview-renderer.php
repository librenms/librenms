<?php

use LibreNMS\Util\Clean;

$_overviewApps = DeviceCache::getPrimary()->applications->sortBy('app_type');

foreach ($_overviewApps as $app) {
    $_overviewFile = 'includes/html/pages/device/overview/apps/' . Clean::fileName($app->app_type) . '.inc.php';
    if (is_file($_overviewFile)) {
        include $_overviewFile;
    }
}

unset($_overviewApps, $_overviewFile, $app);
