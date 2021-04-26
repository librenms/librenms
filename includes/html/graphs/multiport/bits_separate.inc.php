<?php

$i = 0;

foreach (explode(',', $vars['id']) as $ifid) {
    $port = dbFetchRow('SELECT * FROM `ports` AS I, devices as D WHERE I.port_id = ? AND I.device_id = D.device_id', [$ifid]);
    $rrd_file = get_port_rrdfile_path($port['hostname'], $ifid);
    if (Rrd::checkRrdExists($rrd_file)) {
        $port = cleanPort($port);
        $rrd_list[$i]['filename'] = $rrd_file;
        $rrd_list[$i]['descr'] = format_hostname($port, $port['hostname']) . ' ' . $port['ifDescr'];
        $rrd_list[$i]['descr_in'] = format_hostname($port, $port['hostname']);
        $rrd_list[$i]['descr_out'] = makeshortif($port['label']);
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
