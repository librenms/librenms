<?php
require 'includes/graphs/common.inc.php';
$name = 'squid';
$app_id = $app['app_id'];
$scale_min     = 0;
$colours       = 'mixed';
$unit_text     = 'File Descr.';
$unitlen       = 11;
$bigdescrlen   = 11;
$smalldescrlen = 11;
$dostack       = 0;
$printtotal    = 0;
$addarea       = 1;
$transparency  = 15;

$rrd_filename = rrd_name($device['hostname'], array('app', $name, $app_id));

if (is_file($rrd_filename)) {
    $rrd_list = array(
        array(
            'filename' => $rrd_filename,
            'descr'    => 'In Use',
            'ds'       => 'CurFileDescrCnt',
            'colour'   => '28536C'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'Max',
            'ds'       => 'CurFileDescrMax',
            'colour'   => 'D46A6A'
        )
    );
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/graphs/generic_v3_multiline.inc.php';
