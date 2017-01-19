<?php

$rrd_filename = rrd_name($device['hostname'], 'mikrotik-wifi');

$colours = 'mixed';
$print_total = true;
$simple_rrd = true;
if (rrdtool_check_rrd_exists($rrd_filename)) {
    $rrd_list = array(
        array(
            'ds' => 'mtxrWlApTxRate',
            'filename' => $rrd_filename,
            'descr' => 'Tx Rate',
        ),
        array(
            'ds' => 'mtxrWlApRxRate',
            'filename' => $rrd_filename,
            'descr' => 'Rx Rate',
        ),
    );
} else {
    echo "file missing: $rrd_filename";
}
require 'includes/graphs/generic_multi.inc.php';
