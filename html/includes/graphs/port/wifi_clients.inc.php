<?php

$colours = 'mixed';
$print_total = true;
$simple_rrd = true;
$scale_min = '0';
///opt/librenms/rrd/adsl.geordish.org/sensor-connected-clients-wifi-6.rrd
$rrd_filename = rrd_name($device['hostname'], 'sensor-connected-clients-wifi-' . $port['ifIndex']);
$sql = "SELECT ifAlias FROM ports WHERE device_id = ? AND ifIndex = ?";
if (rrdtool_check_rrd_exists($rrd_filename)) {
    $port_name = dbFetchCell($sql, array($device['device_id'], $port['ifIndex']));
    $rrd_list[] = array(
            'ds' => 'sensor',
            'filename' => $rrd_filename,
            'descr' => $port_name,
        );
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/graphs/generic_multi.inc.php';
