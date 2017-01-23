<?php

$colours = 'mixed';
$simple_rrd = true;
$scale_min = '0';
$rrd_filename = rrd_name($device['hostname'], 'mikrotik-wifi' . $port['ifIndex']);
$sql = "SELECT ifAlias FROM ports WHERE device_id = ? AND ifIndex = ?";
if (rrdtool_check_rrd_exists($rrd_filename)) {
    $port_name = dbFetchCell($sql, array($device['device_id'], $port['ifIndex']));
    $rrd_list[] = array(
            'ds' => 'mtxrWlApOveralTxCCQ',
            'filename' => $rrd_filename,
            'descr' => $port_name . ' Overall TxCCQ',
        );
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/graphs/generic_multi_line.inc.php';
