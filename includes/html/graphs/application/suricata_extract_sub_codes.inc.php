<?php

require 'includes/html/graphs/common.inc.php';

$scale_min = 0;
$nototal = 1;
$unit_text = 'HTTP Code';
$unitlen = 15;
$bigdescrlen = 20;
$smalldescrlen = 15;
$colours = 'rainbow';

$array = [
    'sub_2xx' => '2xx',
    'sub_3xx' => '3xx',
    'sub_4xx' => '4xx',
    'sub_5xx' => '5xx',
];

$rrd_filename = Rrd::name($device['hostname'], ['app', 'suricata_extract', $app->app_id]);

$rrd_list = [];
if (Rrd::checkRrdExists($rrd_filename)) {
    $i = 0;
    foreach ($array as $ds => $descr) {
        $rrd_list[$i]['filename'] = $rrd_filename;
        $rrd_list[$i]['descr'] = $descr;
        $rrd_list[$i]['ds'] = $ds;
        $i++;
    }
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
