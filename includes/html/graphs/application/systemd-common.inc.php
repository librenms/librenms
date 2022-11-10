<?php

$name = 'systemd';
$colours = 'psychedelic';
$scale_min = 0;
$polling_type = 'app';
$unit_text = 'Units';
$unitlen = 5;
$bigdescrlen = 20;
$smalldescrlen = 20;

$rrd_list = [];

foreach (array_keys($rrdArray) as $state_type) {
    $rrd_filename = Rrd::name($device['hostname'], [
        $polling_type,
        $name,
        $app->app_id,
        $state_type,
    ]);

    if (Rrd::checkRrdExists($rrd_filename)) {
        $i = 0;
        foreach ($rrdArray[$state_type] as $state_status => $state_status_desc) {
            $rrd_list[$i]['filename'] = $rrd_filename;
            $rrd_list[$i]['descr'] = $state_status_desc['descr'];
            $rrd_list[$i]['ds'] = $state_status;
            $i++;
        }
    } else {
        d_echo('RRD ' . $rrd_filename . ' not found');
    }
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
