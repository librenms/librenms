<?php

$component = new \LibreNMS\Component();
$components = $component->getComponents($device['device_id'], array('type' => 'cisco-qfp'));
$components = $components[$device['device_id']];

foreach ($components as $component_id => $component_tmp) {

    $rrd_filename = rrd_name($device['hostname'], array('cisco-qfp', 'util', $component_tmp['entPhysicalIndex']));

    if (rrdtool_check_rrd_exists($rrd_filename)) {
        $descr = short_hrDeviceDescr($component_tmp['name']);

        $rrd_list[$i]['filename'] = $rrd_filename;
        $rrd_list[$i]['descr']    = $descr;
        $rrd_list[$i]['ds']       = 'ProcessingLoad';
        $rrd_list[$i]['area']     = 1;
        $i++;
    }
}

$unit_text = 'Util %';

$units       = '';
$total_units = '%';
$colours     = 'mixed';

$scale_min = '0';
$scale_max = '100';

$nototal = 1;

require 'includes/html/graphs/generic_multi_line.inc.php';
