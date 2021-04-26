<?php

$unitlen = 10;
$bigdescrlen = 20;
$smalldescrlen = 20;
$dostack = 0;
$printtotal = 0;
$unit_text = 'Resources';
$colours = 'psychedelic';
$rrd_list = [];

$rrd_filename = Rrd::name($device['hostname'], ['app', 'puppet-agent', $app['app_id'], 'resources']);
$array = [
    'changed',
    'corrective_change',
    'failed',
    'failed_to_restart',
    'out_of_sync',
    'restarted',
    'scheduled',
    'skipped',
    'total',
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
