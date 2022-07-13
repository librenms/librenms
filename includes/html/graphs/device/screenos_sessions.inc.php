<?php

$file = Rrd::name($device['hostname'], 'screenos_sessions');

$rrd_list[0]['filename'] = $file;
$rrd_list[0]['descr'] = 'Maxiumum';
$rrd_list[0]['ds'] = 'max';

$rrd_list[1]['filename'] = $file;
$rrd_list[1]['descr'] = 'Allocated';
$rrd_list[1]['ds'] = 'allocate';

$rrd_list[2]['filename'] = $file;
$rrd_list[2]['descr'] = 'Failed';
$rrd_list[2]['ds'] = 'failed';

if ($_GET['debug']) {
    print_r($rrd_list);
}

$colours = 'mixed';
$nototal = 1;
$unit_text = 'Sessions';
$scale_min = '0';

require 'includes/html/graphs/generic_multi_line.inc.php';
