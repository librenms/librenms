<?php
require 'includes/graphs/common.inc.php';
$name = 'fbsd-nfs-server';
$app_id = $app['app_id'];
$scale_min     = 0;
$colours       = 'mixed';
$unit_text     = 'Per Second';
$unitlen       = 10;
$bigdescrlen   = 10;
$smalldescrlen = 10;
$dostack       = 0;
$printtotal    = 0;
$addarea       = 1;
$transparency  = 15;

$rrd_filename = rrd_name($device['hostname'], array('app', $name, $app_id));

if (is_file($rrd_filename)) {
    $rrd_list = array(
        array(
            'filename' => $rrd_filename,
            'descr'    => 'WriteOps',
            'ds'       => 'WriteOps',
            'colour'   => 'AA6C39'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'WriteRPC',
            'ds'       => 'WriteRPC',
            'colour'   => '582A72'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'Opsaved',
            'ds'       => 'Opsaved',
            'colour'   => '28536C'
        )
    );
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/graphs/generic_v3_multiline.inc.php';
