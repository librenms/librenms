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

if (! $rrdArray) {
    graph_error('No Data to Display', 'No Data');
}

$i = 0;
foreach (array_keys($rrdArray) as $state_type) {
    $shared_rrd_filename = Rrd::name($device['hostname'], [
        $polling_type,
        $name,
        $app->app_id,
        $state_type,
    ]);

    if (Rrd::checkRrdExists($rrd_filename)) {
        foreach ($rrdArray[$state_type] as $state_status => $state_status_aa) {
            if ($state_status_aa['rrd_location'] === 'individual') {
                $individual_rrd_filename = Rrd::name($device['hostname'], [
                    $polling_type,
                    $name,
                    $app->app_id,
                    $state_type,
                    $state_status,
                ]);
                $rrd_list[$i]['filename'] = $individual_rrd_filename;
            } else {
                $rrd_list[$i]['filename'] = $shared_rrd_filename;
            }
            $rrd_list[$i]['descr'] = $state_status_aa['descr'];
            $rrd_list[$i]['ds'] = $state_status;
            $i++;
        }
    } else {
        graph_error('No Data file ' . basename($rrd_filename), 'No Data');
    }
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
