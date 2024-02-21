<?php

require 'wireguard-common.inc.php';

$wg_intf_client = $vars['interface'] . '-' . $vars['client'];

$unit_text = 'Bytes/s';

$ds_in = 'bytes_rcvd';
$in_text = 'In';
$ds_out = 'bytes_sent';
$out_text = 'Out';

$format = 'bytes';
$print_total = true;

$colour_area_in = 'FF3300';
$colour_line_in = 'FF0000';
$colour_area_out = 'FF6633';
$colour_line_out = 'CC3300';

$colour_area_in_max = 'FF6633';
$colour_area_out_max = 'FF9966';

$rrd_filename = Rrd::name($device['hostname'], [
    $polling_type,
    $name,
    $app->app_id,
    $wg_intf_client,
]);

if (! Rrd::checkRrdExists($rrd_filename)) {
    graph_error('No Data file ' . basename($rrd_filename), 'No Data');
}

require 'includes/html/graphs/generic_duplex.inc.php';
