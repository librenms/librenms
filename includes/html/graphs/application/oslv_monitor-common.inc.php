<?php

$name = 'oslv_monitor';
if (! isset($colours)) {
    $colours = 'psychedelic';
}
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;
$scale_min = 0;

$rrd_list = [];
foreach ($stats_list as $stat_to_add) {
    if (isset($vars['oslvm'])) {
        $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'oslvm___' . $vars['oslvm'] . '___' . $stat_to_add['stat']]);
    } else {
        $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals_' . $stat_to_add['stat']]);
    }

    if (Rrd::checkRrdExists($rrd_filename)) {
        $rrd_list[] = [
            'filename' => $rrd_filename,
            'descr' => $stat_to_add['descr'],
            'ds' => 'data',
        ];
    }
}

require 'includes/html/graphs/generic_multi_line.inc.php';
