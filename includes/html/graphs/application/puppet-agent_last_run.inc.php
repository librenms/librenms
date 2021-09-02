<?php

$unitlen = 10;
$bigdescrlen = 9;
$smalldescrlen = 9;
$dostack = 0;
$printtotal = 0;
$unit_text = 'Minutes';
$colours = 'psychedelic';
$rrd_list = [];

$rrd_filename = Rrd::name($device['hostname'], ['app', 'puppet-agent', $app['app_id'], 'last_run']);
$array = [
    'last_run',
];
if (Rrd::checkRrdExists($rrd_filename)) {
    foreach ($array as $ds) {
        $rrd_list[] = [
            'filename' => $rrd_filename,
            'descr' => $ds,
            'ds' => $ds,
        ];
    }
} else {
    echo "file missing: $file";
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
