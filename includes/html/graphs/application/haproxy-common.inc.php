<?php

$name = 'haproxy';
$colours = 'rainbow';
$dostack = 0;
$printtotal = 0;
$addarea = 0;
$transparency = 15;

$haproxy_graphs = require 'includes/html/graphs/application/haproxy-definitions.inc.php';
$haproxy_graph_def = $haproxy_graphs[$haproxy_graph];

$unit_text = $haproxy_graph_def['unit'];

if (($haproxy_graph_def['rrd'] ?? null) == 'counts') {
    $haproxy_rrd = 'counts';
} elseif (isset($vars['haproxy_be']) && isset($vars['haproxy_srv'])) {
    $haproxy_rrd = 'srv_-_' . $vars['haproxy_be'] . '_-_' . $vars['haproxy_srv'];
} elseif (isset($vars['haproxy_fe']) && isset($vars['haproxy_li'])) {
    $haproxy_rrd = 'li_-_' . $vars['haproxy_fe'] . '_-_' . $vars['haproxy_li'];
} elseif (isset($vars['haproxy_be'])) {
    $haproxy_rrd = 'be_-_' . $vars['haproxy_be'];
} elseif (isset($vars['haproxy_fe'])) {
    $haproxy_rrd = 'fe_-_' . $vars['haproxy_fe'];
} else {
    $haproxy_rrd = 'totals';
}

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $haproxy_rrd]);

$rrd_list = [];
if (Rrd::checkRrdExists($rrd_filename)) {
    foreach ($haproxy_graph_def['ds'] as $ds => $descr) {
        $rrd_list[] = [
            'filename' => $rrd_filename,
            'descr' => $descr,
            'ds' => $ds,
        ];
    }
} else {
    d_echo('RRD "' . $rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
