<?php

require 'includes/html/graphs/common.inc.php';
$name = 'supervisord';
$app_id = $app['app_id'];
$scale_min = 0;
$colours = 'mixed';
$unit_text = 'Status';
$unitlen = 10;
$bigdescrlen = 15;
$smalldescrlen = 15;
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app_id]);

$array = [
    'STOPPED' => ['descr' => 'STOPPED', 'colour' => 'FFC107'],
    'STARTING' => ['descr' => 'STARTING', 'colour' => '28774F'],
    'RUNNING' => ['descr' => 'RUNNING', 'colour' => '4CAf50'],
    'BACKOFF' => ['descr' => 'BACKOFF', 'colour' => 'CDDC39'],
    'STOPPING' => ['descr' => 'STOPPING', 'colour' => 'D46A6A'],
    'EXITED' => ['descr' => 'EXITED', 'colour' => 'E74B00'],
    'FATAL' => ['descr' => 'FATAL', 'colour' => 'E91E63'],
    'UNKNOWN' => ['descr' => 'UNKNOWN', 'colour' => 'CCCCCC'],
];

$i = 0;
if (Rrd::checkRrdExists($rrd_filename)) {
    foreach ($array as $ds => $var) {
        $rrd_list[$i]['filename'] = $rrd_filename;
        $rrd_list[$i]['descr'] = $var['descr'];
        $rrd_list[$i]['ds'] = $ds;
        $rrd_list[$i]['colour'] = $var['colour'];
        $i++;
    }
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/html/graphs/generic_v3_multiline.inc.php';
