<?php

$scale_min = 0;

require 'includes/html/graphs/common.inc.php';

$rrd_filename = rrd_name($device['hostname'], array('app', 'php-opcache', $app['app_id']));

$array = array(
    'hu'         => array(
        'descr'  => 'Hits',
        'colour' => '00FF00FF',
    ),
    'hb'         => array(
        'descr'  => 'Blacklist',
        'colour' => '4444FFFF',
    ),
    'hm'         => array(
        'descr'  => 'Missed',
        'colour' => 'FF0000FF',
    ),
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
    echo "file missing: $file";
}

$colours   = 'mixed';
//$nototal   = 1;
$unit_text = 'Keys';

require 'includes/html/graphs/generic_multi_simplex_seperated.inc.php';
