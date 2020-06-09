<?php
$name = 'mdadm';
$app_id = $app['app_id'];
$colours       = 'mega';
$dostack       = 0;
$printtotal    = 0;
$addarea       = 1;
$transparency  = 15;

if (isset($vars['array'])) {
    $arrays=array($vars['array']);
} else {
    $arrays=get_arrays_with_mdadm($device, $app['app_id']);
}

$int=0;
while (isset($arrays[$int])) {
    $array=$arrays[$int];
    $rrd_filename = rrd_name($device['hostname'], array('app', $name, $app_id, $array));

    if (rrdtool_check_rrd_exists($rrd_filename)) {
        $rrd_list[]=array(
            'filename' => $rrd_filename,
            'descr'    => $array,
            'ds'       => $rrdVar,
        );
    }
    $int++;
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
