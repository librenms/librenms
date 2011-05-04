<?php

$valid['sensor'] = array();

echo("Sensors: ");

include("includes/discovery/cisco-entity-sensor.inc.php");
print_r($valid['sensors']);
include("includes/discovery/entity-sensor.inc.php");
include("includes/discovery/ipmi.inc.php");
print_r($valid['sensors']);

include("includes/discovery/temperatures.inc.php");
include("includes/discovery/humidity.inc.php");
include("includes/discovery/voltages.inc.php");
include("includes/discovery/frequencies.inc.php");
include("includes/discovery/current.inc.php");
include("includes/discovery/power.inc.php");
include("includes/discovery/fanspeeds.inc.php");

?>
