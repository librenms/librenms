<?php
$id = $device['device_id'];
$hostname = $device['hostname'];
$community = $device['community'];
$snmpver = $device['snmpver'];
$port = $device['port'];

echo("Humidity : ");

include_dir("includes/discovery/humidity");

if ($debug) { print_r($valid['humidity']); }

check_valid_sensors($device, 'humidity', $valid_sensor);

echo("\n");

?>