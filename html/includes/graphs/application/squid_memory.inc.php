<?php

$name = 'squid';
$app_id = $app['app_id'];
$scale_min     = 0;
$colours       = 'mixed';
$unit_text     = 'Kilobytes';
$unitlen       = 10;
$bigdescrlen   = 15;
$smalldescrlen = 15;
$dostack       = 0;
$printtotal    = 0;
$addarea       = 1;
$transparency  = 15;

$rrd_filename = rrd_name($device['hostname'], array('app', $name, $app_id));

if (is_file($rrd_filename)) {
    $rrd_list = array(
        array(
            'filename' => $rrd_filename,
            'descr'    => 'Max Mem',
            'ds'       => 'MemMaxSize',
            'colour'   => '582A72'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'Max Swap',
            'ds'       => 'SwapMaxSize',
            'colour'   => '28774F'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'High Swap WM',
            'ds'       => 'SwapHighWM',
            'colour'   => '28536C'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'Low Swap WM',
            'ds'       => 'SwapLowWM',
            'colour'   => 'D46A6A'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'Swap Usage',
            'ds'       => 'CurrentSwapSize',
            'colour'   => 'FF11BB'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'Memory Usage',
            'ds'       => 'MemUsage',
            'colour'   => 'D853DC'
        )
    );
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/graphs/generic_v3_multiline.inc.php';
