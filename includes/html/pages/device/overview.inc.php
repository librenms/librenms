<?php

use App\Plugins\Hooks\DeviceOverviewHook;

$overview = 1;

echo '
<div class="container-fluid">
  <div class="row">
    <div class="col-md-12">
      &nbsp;
    </div>
  </div>
  <div class="row">
    <div class="col-md-6">
';
require 'includes/html/dev-overview-data.inc.php';
require 'includes/html/dev-groups-overview-data.inc.php';
require 'overview/puppet_agent.inc.php';
require 'overview/tracepath.inc.php';

echo LibreNMS\Plugins::call('device_overview_container', [$device]);
PluginManager::call(DeviceOverviewHook::class, ['device' => DeviceCache::getPrimary()])->each(function ($view) {
    echo $view;
});

require 'overview/ports.inc.php';

if ($device['os'] == 'cimc') {
    require 'overview/cimc.inc.php';
}

if ($device['os'] == 'ping') {
    require 'overview/ping.inc.php';
}

echo '
    </div>
    <div class="col-md-6">
';
// Right Pane
require 'overview/processors.inc.php';
require 'overview/mempools.inc.php';
require 'overview/storage.inc.php';

if (! isset($entity_state)) {
    $entity_state = get_dev_entity_state($device['device_id']);
}
if (! empty($entity_state['group']['c6kxbar'])) {
    require 'overview/c6kxbar.inc.php';
}

require 'overview/toner.inc.php';
require 'overview/sensors/charge.inc.php';
require 'overview/sensors/temperature.inc.php';
require 'overview/sensors/humidity.inc.php';
require 'overview/sensors/fanspeed.inc.php';
require 'overview/sensors/dbm.inc.php';
require 'overview/sensors/voltage.inc.php';
require 'overview/sensors/current.inc.php';
require 'overview/sensors/runtime.inc.php';
require 'overview/sensors/power.inc.php';
require 'overview/sensors/power_consumed.inc.php';
require 'overview/sensors/power_factor.inc.php';
require 'overview/sensors/frequency.inc.php';
require 'overview/sensors/load.inc.php';
require 'overview/sensors/state.inc.php';
require 'overview/sensors/count.inc.php';
require 'overview/sensors/percent.inc.php';
require 'overview/sensors/signal.inc.php';
require 'overview/sensors/tv_signal.inc.php';
require 'overview/sensors/bitrate.inc.php';
require 'overview/sensors/airflow.inc.php';
require 'overview/sensors/snr.inc.php';
require 'overview/sensors/pressure.inc.php';
require 'overview/sensors/cooling.inc.php';
require 'overview/sensors/delay.inc.php';
require 'overview/sensors/quality_factor.inc.php';
require 'overview/sensors/chromatic_dispersion.inc.php';
require 'overview/sensors/ber.inc.php';
require 'overview/sensors/eer.inc.php';
require 'overview/sensors/waterflow.inc.php';
require 'overview/sensors/loss.inc.php';
require 'overview/eventlog.inc.php';
require 'overview/services.inc.php';
require 'overview/syslog.inc.php';
require 'overview/graylog.inc.php';
echo '</div></div></div>';

//require 'overview/current.inc.php");
