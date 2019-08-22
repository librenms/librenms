<?php
$name = 'smart';
$app_id = $app['app_id'];
$colours       = 'mega';
$dostack       = 0;
$printtotal    = 0;
$addarea       = 1;
$transparency  = 15;

if (isset($vars['disk'])) {
    $disks=array($vars['disk']);
} else {
    $disks=get_disks_with_smart($device, $app['app_id']);
}

$smart_enhancements = array('id9');

$int=0;
while (isset($disks[$int])) {
    $disk=$disks[$int];

    if (in_array($rrdVar, $smart_enhancements)) {
        $rrd_filename = rrd_name($device['hostname'], array('app', $name.'_'.$rrdVar, $app_id, $disk));
    } else {
        $rrd_filename = rrd_name($device['hostname'], array('app', $name, $app_id, $disk));
    }

    if (rrdtool_check_rrd_exists($rrd_filename)) {
        $rrd_list[]=array(
            'filename' => $rrd_filename,
            'descr'    => $disk,
            'ds'       => $rrdVar,
        );
    }
    $int++;
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
