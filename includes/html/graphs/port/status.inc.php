<?php

$oids = [
    'ifOperStatus',
];

$i = 0;
$rrd_filename = get_port_rrdfile_path($device['hostname'], $port['port_id'], 'status');

if (Rrd::checkRrdExists($rrd_filename)) {
    foreach ($oids as $oid) {
        $rrd_list[$i]['filename'] = $rrd_filename;
        $rrd_list[$i]['descr'] = 'Port Status';
        $rrd_list[$i]['ds'] = $oid;
        $i++;
    }
}

$colours = 'mixed';
$nototal = 1;
$unit_text = 'Up';
$divider = 1;
$scale_min = 0;
$scale_max = 1;

require 'includes/html/graphs/generic_v3_multiline_float.inc.php';
