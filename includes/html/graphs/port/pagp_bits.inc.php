<?php

// Generate a list of ports and then call the multi_bits grapher to generate from the list
$i = 0;
foreach (dbFetchRows('SELECT * FROM `ports` WHERE `device_id` = ? AND `pagpGroupIfIndex` = ?', [$port['device_id'], $port['ifIndex']]) as $int) {
    $rrd_file = get_port_rrdfile_path($hostname, $int['port_id']);
    if (Rrd::checkRrdExists($rrd_file)) {
        $rrd_list[$i]['filename'] = $rrd_file;
        $rrd_list[$i]['descr'] = $int['ifDescr'];
        $i++;
    }
}

$units = 'bps';
$total_units = 'B';
$colours_in = 'greens';
$multiplier = '8';
$colours_out = 'blues';

$nototal = 1;
$ds_in = 'INOCTETS';
$ds_out = 'OUTOCTETS';

require 'includes/html/graphs/generic_multi_bits_separated.inc.php';
