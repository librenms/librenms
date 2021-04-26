<?php

$unitlen = 15;
$bigdescrlen = 20;
$smalldescrlen = 20;
$dostack = 0;
$printtotal = 0;
$unit_text = 'Runtime/sec';
$colours = 'psychedelic';
$rrd_list = [];

$rrd_filename = Rrd::name($device['hostname'], ['app', 'puppet-agent', $app['app_id'], 'time']);
$array = [
    'catalog_application',
    'config_retrieval',
    'convert_catalog',
    'fact_generation',
    'node_retrieval',
    'plugin_sync',
    'schedule',
    'transaction_evaluation',
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
