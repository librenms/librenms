<?php

$name = 'syslog-ng';
$stat = 'connections';
$unit_text = 'conns/sec';
$colours = 'rainbow';
$dostack = 0;
$printtotal = 0;
$addarea = 0;
$transparency = 15;

if (isset($vars['syslog_ng_source'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'source_-_' . $stat . '_-_' . $vars['syslog_ng_source']]);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'global_-_' . $stat]);
}

$rrd_list = [];
if (Rrd::checkRrdExists($rrd_filename)) {
    if (isset($vars['syslog_ng_source'])) {
            $rrd_list[] = [
                'filename' => $rrd_filename,
                'descr'    => ucfirst($ds),
                'ds'       => $ds,
            ];
    } else {
        foreach (['max', 'mean', 'median', 'min', 'mode', 'sum'] as $ds) {
            $rrd_list[] = [
                'filename' => $rrd_filename,
                'descr'    => ucfirst($ds),
                'ds'       => $ds,
            ];
        }
    }
} else {
    d_echo('RRD "' . $rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
