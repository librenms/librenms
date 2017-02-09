<?php
$rrd_filename = rrd_glob($device['hostname'], 'mikrotik-wifi*');
$colours = 'mixed';
$print_total = true;
$simple_rrd = true;
$scale_min = '0';

$sql = "SELECT ifAlias FROM ports WHERE device_id = ? AND ifIndex = ?";
foreach (glob($rrd_filename) as $filename) {
    if (rrdtool_check_rrd_exists($filename)) {
        list($first, $second) = preg_split("/wifi/", $filename);
        list($port_index, $junk) = preg_split("/\./", $second);
        d_echo($id);
        $port_name = dbFetchCell($sql, array($device['device_id'], $port_index));
        $rrd_list[] = array(
                'ds' => 'mtxrWlApOveralTxCCQ',
                'filename' => $filename,
                'descr' => $port_name . ' Overall TxCCQ',
            );
    } else {
        echo "file missing: $rrd_filename";
    }
}

require 'includes/graphs/generic_multi.inc.php';
