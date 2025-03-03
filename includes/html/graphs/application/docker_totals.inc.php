<?php

require 'includes/html/graphs/common.inc.php';
$name = 'docker';
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

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id]);

$array = [
    'created' => ['descr' => 'created', 'colour' => 'FFC107'],
    'restarting' => ['descr' => 'restarting', 'colour' => '28774F'],
    'running' => ['descr' => 'running', 'colour' => '4CAf50'],
    'removing' => ['descr' => 'removing', 'colour' => 'CDDC39'],
    'paused' => ['descr' => 'paused', 'colour' => 'D46A6A'],
    'exited' => ['descr' => 'exited', 'colour' => 'E74B00'],
    'dead' => ['descr' => 'dead', 'colour' => 'E91E63'],
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
