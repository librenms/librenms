<?php

$colours = 'mixed';
$unit_text = 'Clients';
$scale_min = '0';


$i = 1;
$rrd_filename = rrd_name($device['hostname'], "wificlients-radio$i");
while (rrdtool_check_rrd_exists($rrd_filename)) {
    $rrd_list[$i] =
        array(
            'filename' => $rrd_filename,
            'ds' => 'wificlients',
            'descr' => "Radio$i Clients",
        );
    $i++;
    $rrd_filename = rrd_name($device['hostname'], "wificlients-radio$i");
};

require 'includes/graphs/generic_multi_line.inc.php';
