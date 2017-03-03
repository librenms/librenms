<?php

$name = 'squid';
$app_id = $app['app_id'];
$scale_min     = 0;
$colours       = 'mixed';
$unit_text     = 'Hit Ratio';
$unitlen       = 9;
$bigdescrlen   = 9;
$smalldescrlen = 9;
$dostack       = 0;
$printtotal    = 0;
$addarea       = 1;
$transparency  = 15;

$rrd_filename = rrd_name($device['hostname'], array('app', $name, $app_id));

if (is_file($rrd_filename)) {
    $rrd_list = array(
        array(
            'filename' => $rrd_filename,
            'descr'    => '1 minute',
            'ds'       => 'ReqHitRatio1',
            'colour'   => '582A72'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => '5 minute',
            'ds'       => 'ReqHitRatio5',
            'colour'   => '28774F'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => '60 minute',
            'ds'       => 'ReqHitRatio60',
            'colour'   => '28536C'
        )
    );
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/graphs/generic_v3_multiline.inc.php';
