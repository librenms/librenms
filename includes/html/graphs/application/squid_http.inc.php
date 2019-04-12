<?php

$name = 'squid';
$app_id = $app['app_id'];
$colours       = 'mixed';
$unit_text     = '';
$unitlen       = 10;
$bigdescrlen   = 15;
$smalldescrlen = 15;
$dostack       = 0;
$printtotal    = 0;
$addarea       = 1;
$transparency  = 15;

$rrd_filename = rrd_name($device['hostname'], array('app', $name, $app_id));

if (rrdtool_check_rrd_exists($rrd_filename)) {
    $rrd_list = array(
        array(
            'filename' => $rrd_filename,
            'descr'    => 'requests',
            'ds'       => 'protoclienthttpreq',
            'colour'   => '582a72'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'hits',
            'ds'       => 'httphits',
            'colour'   => '28774f'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'errs to clnt',
            'ds'       => 'httperrors',
            'colour'   => '28536c'
        )
    );
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/html/graphs/generic_v3_multiline.inc.php';
