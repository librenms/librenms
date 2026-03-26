<?php

$name = 'suricata';
$unit_text = 'bytes';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 0;
$transparency = 15;

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['sinstance']]);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id]);
}

$rrd_list = [
    [
        'filename' => $rrd_filename,
        'descr' => 'Flow',
        'ds' => 'f_memuse',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'FTP',
        'ds' => 'ftp_memuse',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'HTTP',
        'ds' => 'http_memuse',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'TCP',
        'ds' => 'tcp_memuse',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'TCP Reass',
        'ds' => 'tcp_reass_memuse',
    ],
];

require 'includes/html/graphs/generic_multi_line.inc.php';
