<?php

require 'includes/html/graphs/common.inc.php';

$name = 'reboot-required';
$unit_text = 'Reboot';
$colours = 'mixed';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id]);

$rrd_list = [];
if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Reboot Required',
        'ds'       => 'reboot',
    ];
}

require 'includes/html/graphs/generic_multi_line.inc.php';
