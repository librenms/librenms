<?php

// Total connections of every monitored port, for the Apps overview page (no port selected).
$name = 'portactivity';
$unit_text = 'Connections';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 0;
$transparency = 15;

$ports = $app->data['ports'] ?? [];
sort($ports);

$rrd_list = [];
foreach ($ports as $port) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $port]);
    if (Rrd::checkRrdExists($rrd_filename)) {
        $rrd_list[] = [
            'filename' => $rrd_filename,
            'descr' => $port,
            'ds' => 'total_conns',
        ];
    }
}

if (empty($rrd_list)) {
    throw new \LibreNMS\Exceptions\RrdGraphException('No Data to Display', 'No Data');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
