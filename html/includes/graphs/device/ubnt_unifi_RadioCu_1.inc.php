<?php

$rrd_filename = rrd_name($device['hostname'], 'ubnt-unifi-mib');

$colours = 'mixed';
$unit_text = '% used';
$scale_min = '0';
$scale_max = '100';
$rigid = true;
$print_total = true;
$simple_rrd = true;

if (is_file($rrd_filename)) {
    $rrd_list = array(
        array(
            'ds' => 'Radio1OtherBss',
            'filename' => $rrd_filename,
            'descr' => 'Radio1 Others',
        ),
        array(
            'ds' => 'Radio1CuSelfRx',
            'filename' => $rrd_filename,
            'descr' => 'Radio1 RX',
        ),
        array(
            'ds' => 'Radio1CuSelfTx',
            'filename' => $rrd_filename,
            'descr' => 'Radio1 TX',
        ),
    );
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/graphs/generic_multi.inc.php';
