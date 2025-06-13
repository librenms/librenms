<?php

$name = 'cape';
$app_id = $app['app_id'];
$unit_text = 'Reg Keys Modded Per Run';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;
$float_precision = 3;

if (isset($vars['stddev'])) {
    if ($vars['stddev'] != 'on' and $vars['stddev'] != 'off') {
        $vars['stddev'] = 'off';
    }
} else {
    $vars['stddev'] = 'off';
}

if (isset($vars['package'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app['app_id'], 'pkg-reg_keys_mod___-___', $vars['package']]);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app['app_id'], 'reg_keys_mod']);
}

$rrd_list = [
[
        'filename' => $rrd_filename,
        'descr' => 'Min',
        'ds' => 's0regkeysmod',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'Max',
        'ds' => 's1regkeysmod',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'Mean',
        'ds' => 's3regkeysmod',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'Median',
        'ds' => 's4regkeysmod',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'Mode',
        'ds' => 's5regkeysmod',
    ];
    if (isset($vars['stddev']) && $vars['stddev'] == 'on') {
        $rrd_list[] = [
            'filename' => $rrd_filename,
            'descr' => 'StdDev',
            'ds' => 's7regkeysmod',
        ];
        $rrd_list[] = [
            'filename' => $rrd_filename,
            'descr' => 'StdDevP',
            'ds' => 's9regkeysmod',
    ],
];


require 'includes/html/graphs/generic_multi_line.inc.php';
