<?php

$name = 'syslog-ng';
$unit_text = 'msgs/sec';
$colours = 'rainbow';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'center']);

$rrd_list = [];
if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Queued',
        'ds'       => 'queued',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Received',
        'ds'       => 'received',
    ];
} else {
    d_echo('RRD "' . $rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
