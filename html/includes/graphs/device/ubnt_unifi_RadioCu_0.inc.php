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
            'ds' => 'Radio0OtherBss',
            'filename' => $rrd_filename,
            'descr' => 'Radio0 Others',
        ),
        array(
            'ds' => 'Radio0CuSelfRx',
            'filename' => $rrd_filename,
            'descr' => 'Radio0 RX',
        ),
        array(
            'ds' => 'Radio0CuSelfTx',
            'filename' => $rrd_filename,
            'descr' => 'Radio0 TX',
        ),
    );
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/graphs/generic_multi.inc.php';
