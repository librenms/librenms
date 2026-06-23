<?php

use LibreNMS\Interfaces\Plugins\Hooks\DeviceOverviewHook;

$overview = 1;

echo '
<div class="tw:grid tw:grid-cols-1 tw:md:grid-cols-2 tw:gap-4">
    <div class="tw:min-w-0">
';
require 'includes/html/dev-overview-data.inc.php';
require 'overview/maps.inc.php';
require 'includes/html/dev-groups-overview-data.inc.php';
require 'overview/puppet_agent.inc.php';

echo LibreNMS\Plugins::call('device_overview_container', [$device]);
foreach (PluginManager::call(DeviceOverviewHook::class, ['device' => DeviceCache::getPrimary()]) as $view) {
    echo $view;
}

require 'overview/ports.inc.php';
require 'overview/availability_bar.inc.php';
require 'overview/transceivers.inc.php';

if ($device['os'] == 'ping') {
    require 'overview/ping.inc.php';
}

echo '
    </div>
    <div class="tw:min-w-0">
';
// Right Pane
require 'overview/processors.inc.php';
require 'overview/mempools.inc.php';
require 'overview/storage.inc.php';
require 'overview/toner.inc.php';
require 'overview/sensor.inc.php';
require 'overview/eventlog.inc.php';
require 'overview/services.inc.php';
require 'overview/syslog.inc.php';
require 'overview/graylog.inc.php';
echo '</div></div>';

//require 'overview/current.inc.php");
