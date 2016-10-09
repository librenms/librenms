<?php

require 'includes/graphs/common.inc.php';

$mysql_rrd = rrd_name($device['hostname'], array('app', mysql, $app['app_id']));

if (rrdtool_check_rrd_exists($mysql_rrd)) {
    $rrd_filename = $mysql_rrd;

    $array = array(
        'QCQICe' => array(
            'descr' => 'Queries in cache',
            'colour' => '22FF22',
        ),
        'QCHs' => array(
            'descr' => 'Cache hits',
            'colour' => '0022FF',
        ),
        'QCIs' => array(
            'descr' => 'Inserts',
            'colour' => 'FF0000',
        ),
        'QCNCd' => array(
            'descr' => 'Not cached',
            'colour' => '00AAAA',
        ),
        'QCLMPs' => array(
            'descr' => 'Low-memory prunes',
            'colour' => 'FF00FF',
        ),
    );


    $rrd_list = array();
    foreach ($array as $ds => $vars) {
        $rrd_list[] = array(
            'filename' => $rrd_filename,
            'descr' => $vars['descr'],
            'ds' => $ds,
//                'colour' => $vars['colour']
        );
    }
} else {
    echo "data missing: $mysql_rrd";
}

$colours   = 'mixed';
$nototal   = 1;
$unit_text = 'Commands';

require 'includes/graphs/generic_multi_simplex_seperated.inc.php';
