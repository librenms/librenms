<?php

$oids = [
    'PortCurrent',
];

$i = 0;
$rrd_filename = get_port_rrdfile_path($device['hostname'], $port['port_id'], 'poe-netgear-current');

if (Rrd::checkRrdExists($rrd_filename)) {
    foreach ($oids as $oid) {
        $rrd_list[$i]['filename'] = $rrd_filename;
        $rrd_list[$i]['descr'] = substr($oid, 4);
        $rrd_list[$i]['ds'] = $oid;
        $i++;
    }
}

$colours = 'mixed';
$nototal = 1;
$unit_text = 'A';
$divider = 1000;
$scale_min = 0;

require 'includes/html/graphs/generic_v3_multiline_float.inc.php';
