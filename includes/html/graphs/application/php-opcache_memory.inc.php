<?php

$scale_min = 0;

require 'includes/html/graphs/common.inc.php';

$rrd_filename = rrd_name($device['hostname'], array('app', 'php-opcache', $app['app_id']));

$array = array(
    'mw'         => array(
        'descr'  => 'Waisted',
        'colour' => 'FF0000FF',
    ),
    'mu'         => array(
        'descr'  => 'Used',
        'colour' => '4444FFFF',
    ),
    'mf'         => array(
        'descr'  => 'Free',
        'colour' => '00FF00FF',
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
$unit_text = 'Memory MB';

require 'includes/html/graphs/generic_multi_simplex_seperated.inc.php';
