<?php

$i = 1;

foreach (explode(',', $vars['id']) as $ifid) {
    if (strstr($ifid, '!')) {
        $rrd_inverted[$i] = true;
        $ifid = str_replace('!', '', $ifid);
    }

    $int = dbFetchRow('SELECT `hostname` FROM `ports` AS I, devices as D WHERE I.port_id = ? AND I.device_id = D.device_id', [$ifid]);
    $rrd_file = get_port_rrdfile_path($int['hostname'], $ifid);
    if (Rrd::checkRrdExists($rrd_file)) {
        $rrd_filenames[$i] = $rrd_file;
        $i++;
    }
}

$ds_in = 'INOCTETS';
$ds_out = 'OUTOCTETS';

$colour_line_in = '006600';
$colour_line_out = '000099';
$colour_area_in = 'CDEB8B';
$colour_area_out = 'C3D9FF';

require 'includes/html/graphs/generic_multi_data.inc.php';
