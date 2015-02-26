<?php

$overview = 1;

$ports['total']    = dbFetchCell("SELECT COUNT(*) FROM `ports` WHERE device_id = ?", array($device['device_id']));
$ports['up']       = dbFetchCell("SELECT COUNT(*) FROM `ports` WHERE device_id = ? AND `ifOperStatus` = 'up'", array($device['device_id']));
$ports['down']     = dbFetchCell("SELECT COUNT(*) FROM `ports` WHERE device_id = ? AND `ifOperStatus` = 'down' AND `ifAdminStatus` = 'up'", array($device['device_id']));
$ports['disabled'] = dbFetchCell("SELECT COUNT(*) FROM `ports` WHERE device_id = ? AND `ifAdminStatus` = 'down'", array($device['device_id']));

$services['total']    = dbFetchCell("SELECT COUNT(service_id) FROM `services` WHERE `device_id` = ?", array($device['device_id']));
$services['up']       = dbFetchCell("SELECT COUNT(service_id) FROM `services` WHERE `device_id` = ? AND `service_status` = '1' AND `service_ignore` ='0'", array($device['device_id']));
$services['down']     = dbFetchCell("SELECT COUNT(service_id) FROM `services` WHERE `device_id` = ? AND `service_status` = '0' AND `service_ignore` = '0'", array($device['device_id']));
$services['disabled'] = dbFetchCell("SELECT COUNT(service_id) FROM `services` WHERE `device_id` = ? AND `service_ignore` = '1'", array($device['device_id']));

if ($services['down']) { $services_colour = $warn_colour_a; } else { $services_colour = $list_colour_a; }
if ($ports['down']) { $ports_colour = $warn_colour_a; } else { $ports_colour = $list_colour_a; }

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
include("includes/dev-overview-data.inc.php");
include("overview/ports.inc.php");
echo('
    </div>
    <div class="col-md-6">
');
// Right Pane
include("overview/processors.inc.php");
include("overview/mempools.inc.php");
include("overview/storage.inc.php");

if(is_array($entity_state['group']['c6kxbar'])) { include("overview/c6kxbar.inc.php"); }

include("overview/toner.inc.php");
include("overview/sensors/charge.inc.php");
include("overview/sensors/temperatures.inc.php");
include("overview/sensors/humidity.inc.php");
include("overview/sensors/fanspeeds.inc.php");
include("overview/sensors/dbm.inc.php");
include("overview/sensors/voltages.inc.php");
include("overview/sensors/current.inc.php");
include("overview/sensors/power.inc.php");
include("overview/sensors/frequencies.inc.php");
include("overview/eventlog.inc.php");
include("overview/services.inc.php");
include("overview/syslog.inc.php");

echo('</div></div></div>');

#include("overview/current.inc.php");

?>
