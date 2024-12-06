<?php

$app_name = 'linux_config_files';
$colours = 'psychedelic';
$lower_limit = 0;
$polling_type = 'app';
$unit_text = 'Configs';
$unitlen = strlen($unit_text);

$rrdArray = [
    'number_of_confs' => ['descr' => 'Out-Of-Sync'],
];

$rrd_filename = Rrd::name($device['hostname'], [
    $polling_type,
    $app_name,
    $app->app_id,
]);

if (Rrd::checkRrdExists($rrd_filename)) {
    foreach ($rrdArray as $rrdVar => $rrdValues) {
        $rrd_list[] = [
            'cdef_rpn' => $rrdValues['cdef_rpn'] ?? null,
            'colour' => $rrdValues['colour'] ?? null,
            'descr' => $rrdValues['descr'],
            'divider' => $rrdValues['divider'] ?? null,
            'ds' => $rrdVar,
            'filename' => $rrd_filename,
            'multiplier' => $rrdValues['multiplier'] ?? null,
        ];
    }
} else {
    graph_error('No Data file ' . basename($rrd_filename), 'No Data');
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
