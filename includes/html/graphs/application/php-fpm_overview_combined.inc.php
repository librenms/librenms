<?php

$name = 'php-fpm';
$unit_text = 'Totals';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$descr_len = 20;
$addarea = 1;
$transparency = 15;

$filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___max_active_processes']);

if (Rrd::checkRrdExists($filename)) {
    $rrd_list = [];

    $proc_stats = [
        'max_active_processes' => 'Max Active Processes',
        'active_processes' => 'Active Processes',
        'idle_processes' => 'Idle Processes',
        'max_listen_queue' => 'Max Listen Queue',
        'listen_queue' => 'Listen Queue',
        'listen_queue_len' => 'Listen Queue Len',
        'listen_queue' => 'Listen Queue',
    ];

    foreach ($proc_stats as $stat => $descr) {
        $filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___' . $stat]);
        if (Rrd::checkRrdExists($filename)) {
            $rrd_list[] = [
                'filename' => $filename,
                'descr' => $descr,
                'ds' => 'data',
            ];
        }
    }
} else {
    $filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id]);
    if (Rrd::checkRrdExists($filename)) {
        $rrd_list = [
            [
                'filename' => $filename,
                'descr' => 'Listen Queue',
                'ds' => 'lq',
            ],
            [
                'filename' => $filename,
                'descr' => 'Max Listen Queue',
                'ds' => 'mlq',
            ],
            [
                'filename' => $filename,
                'descr' => 'Idle Procs',
                'ds' => 'ip',
            ],
            [
                'filename' => $filename,
                'descr' => 'Active Procs',
                'ds' => 'ap',
            ],
            [
                'filename' => $filename,
                'descr' => 'Total Procs',
                'ds' => 'tp',
            ],
            [
                'filename' => $filename,
                'descr' => 'Max Active Procs',
                'ds' => 'map',
            ],
            [
                'filename' => $filename,
                'descr' => 'Max Children Reached',
                'ds' => 'mcr',
            ],
            [
                'filename' => $filename,
                'descr' => 'Slow Reqs',
                'ds' => 'sr',
            ],
        ];
    } else {
        echo 'file missing: ' . $filename;
    }
}

require 'includes/html/graphs/generic_multi_line.inc.php';
