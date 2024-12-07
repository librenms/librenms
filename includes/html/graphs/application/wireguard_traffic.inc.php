<?php

require 'wireguard-common.inc.php';

$unit_text = 'Bytes/s';
$format = 'bytes';
$print_total = true;
$in_text = 'In';
$out_text = 'Out';
$colour_area_in = 'FF3300';
$colour_line_in = 'FF0000';
$colour_area_out = 'FF6633';
$colour_line_out = 'CC3300';
$colour_area_in_max = 'FF6633';
$colour_area_out_max = 'FF9966';

if (! isset($vars['interface']) && ! isset($vars['client'])) {
    // This section is called if we're being asked to graph
    // the host's wireguard global metrics.
    $ds_in = 'bytes_rcvd_total';
    $ds_out = 'bytes_sent_total';
} elseif (isset($vars['interface']) && isset($vars['client'])) {
    // This section is called if we're being asked to graph
    // a wireguard interface's client metrics.
    $flattened_name = $vars['interface'] . '-' . $vars['client'];
    $ds_in = 'bytes_rcvd';
    $ds_out = 'bytes_sent';
} elseif (isset($vars['interface'])) {
    // This section is called if we're being asked to graph
    // a wireguard interface's metrics.
    $flattened_name = $vars['interface'];
    $ds_in = 'bytes_rcvd_total_intf';
    $ds_out = 'bytes_sent_total_intf';
}

if (! isset($vars['interface']) && ! isset($vars['client'])) {
    $rrd_filename = Rrd::name($device['hostname'], [
        $polling_type,
        $name,
        $app->app_id,
    ]);
} elseif (isset($vars['interface'])) {
    $rrd_filename = Rrd::name($device['hostname'], [
        $polling_type,
        $name,
        $app->app_id,
        $flattened_name,
    ]);
}

if (! isset($rrd_filename)) {
    graph_error('No Data to Display', 'No Data');
}

if (! Rrd::checkRrdExists($rrd_filename)) {
    graph_error('No Data file ' . basename($rrd_filename), 'No Data');
}

require 'includes/html/graphs/generic_duplex.inc.php';
