<?php
$name = 'postgres';
$app_id = $app['app_id'];
$scale_min     = 0;
$colours       = 'mixed';
$unit_text     = 'Rows/Sec';
$unitlen       = 10;
$bigdescrlen   = 15;
$smalldescrlen = 15;
$dostack       = 0;
$printtotal    = 0;
$addarea       = 1;
$transparency  = 15;

if (isset($vars['database'])) {
    $rrd_name_array=array('app', $name, $app_id, $vars['database']);
} else {
    $rrd_name_array=array('app', $name, $app_id);
}

$rrd_filename = rrd_name($device['hostname'], $rrd_name_array);

if (rrdtool_check_rrd_exists($rrd_filename)) {
    $rrd_list = array(
        array(
            'filename' => $rrd_filename,
            'descr'    => 'Scans',
            'ds'       => 'idxscan',
            'colour'   => '582A72'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'Tuples Read',
            'ds'       => 'idxtupread',
            'colour'   => 'AA6C39'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'Tuples Fetched',
            'ds'       => 'idxtupfetch',
            'colour'   => 'FFD1AA'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'Blocks Read',
            'ds'       => 'idxblksread',
            'colour'   => '88CC88'
        ),
        array(
            'filename' => $rrd_filename,
            'descr'    => 'Buffer Hits',
            'ds'       => 'idxblkshit',
            'colour'   => '28536C'
        )
    );
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/html/graphs/generic_v3_multiline.inc.php';
