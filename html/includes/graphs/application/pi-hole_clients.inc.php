<?php

require 'includes/graphs/common.inc.php';

$scale_min     = 0;
$colours       = 'mixed';
$unit_text     = 'Stats';
$unitlen       = 6;
$bigdescrlen   = 25;
$smalldescrlen = 25;
$dostack       = 0;
$printtotal    = 0;
$addarea       = 1;
$transparency  = 33;
$rrd_filename = rrd_name($device['hostname'], array('app', $app['app_type'].'-clients', $app['app_id']));

$array = array(
    'clients_ever_seen' => array('descr' => 'All clients','colour' => '657C5E',),
    'unique_clients' => array('descr' => 'Unique clients ','colour' => 'F44842',),
);

$i = 0;

if (rrdtool_check_rrd_exists($rrd_filename)) {
    foreach ($array as $ds => $var) {
        $rrd_list[$i]['filename'] = $rrd_filename;
        $rrd_list[$i]['descr']    = $var['descr'];
        $rrd_list[$i]['ds']       = $ds;
        $rrd_list[$i]['colour']   = $var['colour'];
        $i++;
    }
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/graphs/generic_v3_multiline_float.inc.php';
