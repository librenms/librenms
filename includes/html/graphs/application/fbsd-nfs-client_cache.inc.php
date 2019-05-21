<?php
$name = 'fbsd-nfs-client';
$app_id = $app['app_id'];
$scale_min     = 0;
$colours       = 'mixed';
$unit_text     = 'per second';
$unitlen       = 10;
$bigdescrlen   = 12;
$smalldescrlen = 12;
$dostack       = 0;
$printtotal    = 0;
$addarea       = 1;
$transparency  = 15;

$rrd_filename = rrd_name($device['hostname'], array('app', $name, $app_id));

if (rrdtool_check_rrd_exists($rrd_filename)) {
    $rrd_list = array(
        array(
            'filename' => $rrd_filename,
            'descr'    => 'attr hits',
            'ds'       => 'attrhits',
            'colour'   => '582a72'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'attr misses',
            'ds'       => 'attrmisses',
            'colour'   => '28774f'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'lkup hits',
            'ds'       => 'lkuphits',
            'colour'   => 'aa6c39'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'lkup misses',
            'ds'       => 'lkupmisses',
            'colour'   => '88cc88'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'bioR hits',
            'ds'       => 'biorhits',
            'colour'   => 'd46a6a'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'bioR misses',
            'ds'       => 'biormisses',
            'colour'   => 'ffd1aa'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'bioW hits',
            'ds'       => 'biowhits',
            'colour'   => '582a72'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'bioW misses',
            'ds'       => 'biowmisses',
            'colour'   => 'aa8839'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'biorRL hits',
            'ds'       => 'biorlhits',
            'colour'   => '28536c'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'bioRL misses',
            'ds'       => 'biorlmisses',
            'colour'   => '880000'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'bioD hits',
            'ds'       => 'biodhits',
            'colour'   => 'ff0000'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'bioD misses',
            'ds'       => 'biodmisses',
            'colour'   => '285300'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'dirE hits',
            'ds'       => 'direhits',
            'colour'   => '2800ff'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'dirE misses',
            'ds'       => 'diremisses',
            'colour'   => '000080'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'accs hits',
            'ds'       => 'accshits',
            'colour'   => '500000'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'accs misses',
            'ds'       => 'accsmisses',
            'colour'   => 'aa6511'
        )
    );
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/html/graphs/generic_v3_multiline.inc.php';
