<?php

$name = 'squid';
$app_id = $app['app_id'];
$scale_min     = 0;
$colours       = 'mixed';
$unit_text     = 'kB/s';
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
            'descr'    => 'Server In',
            'ds'       => 'ServerInKb',
            'colour'   => 'D46A6A'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'Server Out',
            'ds'       => 'ServerOutKb',
            'colour'   => '28774F'
        )
    );
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/graphs/generic_v3_multiline.inc.php';
