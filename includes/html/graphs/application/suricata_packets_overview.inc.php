<?php

$name = 'suricata';
$unit_text = 'packets/sec';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['sinstance']]);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id]);
}

if (isset($vars['sinstance'])) {
    $capture__kernel_packets_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] .'___capture__kernel_packets' ]);
} else {
    $capture__kernel_packets_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___capture__kernel_packets']);
}

if (isset($vars['sinstance'])) {
    $decoder__ethernet_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__ethernet' ]);
} else {
    $decoder__ethernet_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__ethernet']);
}

if (isset($vars['sinstance'])) {
    $capture__kernel_drops_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___capture__kernel_drops' ]);
} else {
    $capture__kernel_drops_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___capture__kernel_drops']);
}

if (isset($vars['sinstance'])) {
    $capture__kernel_ifdrops_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___capture__kernel_ifdrops' ]);
} else {
    $capture__kernel_ifdrops_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___capture__kernel_ifdrops']);
}

$rrd_list = [];
if (Rrd::checkRrdExists($capture__kernel_packets_rrd_filename)) {
    if (Rrd::checkRrdExists($capture__kernel_packets_rrd_filename)) {
        $rrd_list[] = [
            'filename' => $capture__kernel_packets_rrd_filename,
            'descr' => 'Packets',
            'ds' => 'data',
        ];
    } else {
        d_echo('RRD "' . $capture__kernel_packets_rrd_filename . '" not found');
    }
    if (Rrd::checkRrdExists($capture__kernel_packets_rrd_filename)) {
        $rrd_list[] = [
            'filename' => $decoder__ethernet_rrd_filename,
            'descr' => 'Eth Pkts',
            'ds' => 'data',
        ];
    } else {
        d_echo('RRD "' . $capture__kernel_packets_rrd_filename . '" not found');
    }
    if (Rrd::checkRrdExists($capture__kernel_drops_rrd_filename)) {
        $rrd_list[] = [
            'filename' => $capture__kernel_drops_rrd_filename,
            'descr' => 'Drops',
            'ds' => 'data',
        ];
    } else {
        d_echo('RRD "' . $capture__kernel_drops_rrd_filename . '" not found');
    }
    if (Rrd::checkRrdExists($capture__kernel_ifdrops_rrd_filename)) {
        $rrd_list[] = [
            'filename' => $capture__kernel_ifdrops_rrd_filename,
            'descr' => 'If Dropped',
            'ds' => 'data',
        ];
    } else {
        d_echo('RRD "' . $capture__kernel_ifdrops_rrd_filename . '" not found');
    }
} elseif (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'Packets',
        'ds' => 'packets',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'Dec. Packets',
        'ds' => 'dec_packets',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'Dropped',
        'ds' => 'dropped',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'If Dropped',
        'ds' => 'ifdropped',
    ];
} else {
    d_echo('RRD "' . $rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
