<?php

use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Modules\UnixAgent;

// the unix agent check fetched the output, usually already done by the unix-agent module
$agent_data = UnixAgent::parse((string) $os->getMethodResults()->result(PollingMethodType::UnixAgent)?->stat('output'));
\DeviceCache::getPrimary()->applications->each(function ($app) use ($device, $agent_data): void {
    echo 'Application: ' . $app->app_type . ', app_id=' . $app->app_id;

    $app_include = base_path('includes/polling/applications/' . \LibreNMS\Util\Clean::fileName($app->app_type) . '.inc.php');
    if (is_file($app_include)) {
        include $app_include;
    } else {
        echo 'ERROR: ' . $app_include . ' include file missing!';
    }
    echo "\n";
});
