<?php

$colours = 'mixed';
$unit_text = 'Clients';
$scale_min = '0';

$radio1_filename = rrd_name($device['hostname'], 'wificlients-radio1');
$radio2_filename = rrd_name($device['hostname'], 'wificlients-radio2');

if (rrdtool_check_rrd_exists($radio2_filename)) {
    $rrd_list = array(
        array(
            'filename' => $radio1_filename,
            'ds' => 'wificlients',
            'descr' => 'Radio1 Clients',
        ),
        array(
            'filename' => $radio2_filename,
            'ds' => 'wificlients',
            'descr' => 'Radio2 Clients',
        ),
    );
} elseif (rrdtool_check_rrd_exists($radio1_filename)) {
    $rrd_list = array(
        array(
            'ds' => 'wificlients',
            'filename' => $radio1_filename,
            'descr' => 'Radio1 Clients',
        ),
    );
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/graphs/generic_multi_line.inc.php';
