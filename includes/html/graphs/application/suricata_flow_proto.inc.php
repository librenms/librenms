<?php

$name = 'suricata';
$unit_text = 'flows';
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
        'descr' => 'ICMPv4',
        'ds' => 'f_icmpv4',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'ICMPv6',
        'ds' => 'f_icmpv6',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'TCP',
        'ds' => 'f_tcp',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'UDP',
        'ds' => 'f_udp',
    ],
];

require 'includes/html/graphs/generic_multi_line.inc.php';
