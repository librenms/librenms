<?php

$name = 'php-fpm';
$unit_text = 'Totals';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$descr_len = 20;
$addarea = 1;
$transparency = 15;

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
    if (isset($vars['phpfpm_pool'])) {
        $filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'pools___' . $vars['phpfpm_pool'] . '___' . $stat]);
    } else {
        $filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___' . $stat]);
    }
    if (Rrd::checkRrdExists($filename)) {
        $rrd_list[] = [
            'filename' => $filename,
            'descr' => $descr,
            'ds' => 'data',
        ];
    }
}

require 'includes/html/graphs/generic_multi_line.inc.php';
