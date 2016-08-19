<?php

$overview = 1;

$ports['total']    = dbFetchCell("SELECT COUNT(*) FROM `ports` WHERE device_id = ?", array($device['device_id']));
$ports['up']       = dbFetchCell("SELECT COUNT(*) FROM `ports` WHERE device_id = ? AND `ifOperStatus` = 'up' AND `ifAdminStatus` = 'up'", array($device['device_id']));
$ports['down']     = dbFetchCell("SELECT COUNT(*) FROM `ports` WHERE device_id = ? AND `ifOperStatus` = 'down' AND `ifAdminStatus` = 'up'", array($device['device_id']));
$ports['disabled'] = dbFetchCell("SELECT COUNT(*) FROM `ports` WHERE device_id = ? AND `ifAdminStatus` = 'down'", array($device['device_id']));

$services = get_service_status($device['device_id']);
$services['total'] = array_sum($services);

if ($services[2]) {
    $services_colour = $warn_colour_a;
} else {
    $services_colour = $list_colour_a;
}
if ($ports['down']) {
    $ports_colour = $warn_colour_a;
} else {
    $ports_colour = $list_colour_a;
}

echo('
<div class="container-fluid">
  <div class="row">
    <div class="col-md-12">
      &nbsp;
    </div>
  </div>
  <div class="row">
    <div class="col-md-6">
');
require 'includes/dev-overview-data.inc.php';
use LibreNMS\Plugins;

Plugins::call('device_overview_container', array($device));

require 'overview/ports.inc.php';
echo('
    </div>
    <div class="col-md-6">
');
// Right Pane
require 'overview/processors.inc.php';
require 'overview/mempools.inc.php';
require 'overview/storage.inc.php';

if (is_array($entity_state['group']['c6kxbar'])) {
    require 'overview/c6kxbar.inc.php';
}

require 'overview/toner.inc.php';
require 'overview/sensors/charge.inc.php';
require 'overview/sensors/temperatures.inc.php';
require 'overview/sensors/humidity.inc.php';
require 'overview/sensors/fanspeeds.inc.php';
require 'overview/sensors/dbm.inc.php';
require 'overview/sensors/voltages.inc.php';
require 'overview/sensors/current.inc.php';
require 'overview/sensors/power.inc.php';
require 'overview/sensors/frequencies.inc.php';
require 'overview/sensors/load.inc.php';
require 'overview/sensors/state.inc.php';
require 'overview/sensors/signal.inc.php';
require 'overview/eventlog.inc.php';
require 'overview/services.inc.php';
require 'overview/syslog.inc.php';

echo('</div></div></div>');

#require 'overview/current.inc.php");
