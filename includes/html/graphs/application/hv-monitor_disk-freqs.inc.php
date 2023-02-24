<?php

$name = 'hv-monitor';
$unit_text = 'Reqs/Sec';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 0;
$transparency = 15;

if (isset($vars['vmdisk']) && isset($vars['vm'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'vmdisk', $vars['vm'], '__-__', $vars['vmdisk']]);
} elseif (isset($vars['vm'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'vm', $vars['vm']]);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id]);
}

if (Rrd::checkRrdExists($rrd_filename)) {
    $filename = $rrd_filename;
    $descr = 'Flushes';
    $ds = 'freqs';
} else {
    d_echo('RRD "' . $rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_stats.inc.php';
