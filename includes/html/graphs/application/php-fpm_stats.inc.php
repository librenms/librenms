<?php

require 'includes/html/graphs/common.inc.php';
$name = 'php-fpm';
$app_id = $app['app_id'];
$scale_min = 0;
$colours = 'mixed';
$unit_text = '';
$unitlen = 10;
$bigdescrlen = 15;
$smalldescrlen = 15;
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app_id]);

$array = [
    'lq' => ['descr' => 'Listen Queue', 'colour' => '582A72'],
    'mlq' => ['descr' => 'Max Listen Queue', 'colour' => '28774F'],
    'ip' => ['descr' => 'Idle Procs', 'colour' => '88CC88'],
    'ap' => ['descr' => 'Active Procs', 'colour' => 'D46A6A'],
    'tp' => ['descr' => 'Total Procs', 'colour' => 'FFD1AA'],
    'map' => ['descr' => 'Max Active Procs', 'colour' => '582A72'],
    'mcr' => ['descr' => 'Max Children Reached', 'colour' => 'AA5439'],
    'sr' => ['descr' => 'Slow Reqs.', 'colour' => '28536C'],
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
