<?php

require 'includes/html/graphs/common.inc.php';

$colours = 'mixed';
$nototal = (($width < 224) ? 1 : 0);
$unit_text = 'Seconds';
$units = 's';
$divider = 1000; // values are stored in milliseconds, display them as seconds
$graph_params->vertical_label = 'Seconds';
$rrd_filename = Rrd::name($device['hostname'], ['app', 'ntp-client', $app->app_id]);
$array = [
    'jitter' => ['descr' => 'Jitter'],
    'noise' => ['descr' => 'Noise'],
];

$i = 0;

if (Rrd::checkRrdExists($rrd_filename)) {
    foreach ($array as $ds => $var) {
        $rrd_list[$i]['filename'] = $rrd_filename;
        $rrd_list[$i]['descr'] = $var['descr'];
        $rrd_list[$i]['ds'] = $ds;
        $rrd_list[$i]['colour'] = \App\Facades\LibrenmsConfig::get("graph_colours.$colours.$i");
        $i++;
    }
} else {
    throw new \LibreNMS\Exceptions\RrdGraphException("No Data file $rrd_filename");
}

require 'includes/html/graphs/generic_multi_line.inc.php';
