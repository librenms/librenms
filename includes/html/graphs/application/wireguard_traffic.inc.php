<?php

$name = 'wireguard';
$polling_type = 'app';

if (isset($vars['interface']) && isset($vars['client'])) {
    $interface = $vars['interface'];
    $client = $vars['client'];
    $interface_client = $vars['interface'] . '-' . $vars['client'];
} else {
    $interface_client_list = Rrd::getRrdApplicationArrays($device, $app->app_id, $name);
    $interface_client = $interface_client_list[0] ?? '';
}

$unit_text = 'Bytes';

$ds_in = 'bytes_rcvd';
$in_text = 'Rcvd';
$ds_out = 'bytes_sent';
$out_text = 'Sent';

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
    $interface_client,
]);

if (! Rrd::checkRrdExists($rrd_filename)) {
    d_echo('RRD ' . $rrd_filename . ' not found');
}

require 'includes/html/graphs/generic_duplex.inc.php';
