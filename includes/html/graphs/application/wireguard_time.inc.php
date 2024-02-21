<?php

require 'wireguard-common.inc.php';

$colours = 'psychedelic';
$unit_text = 'Minutes';
$scale_min = 0;
$unitlen = 7;
$rrd_list = [];
$rrdArray = [];
$metric_name = 'minutes_since_last_handshake';
$metric_desc = 'Last Handshake';

if (isset($vars['interface']) && isset($vars['client'])) {
    $wg_intf_client = $vars['interface'] . '-' . $vars['client'];
    $rrdArray[$wg_intf_client] = [
        $metric_name => ['descr' => $metric_desc],
    ];
} else {
    $wg_intf_client_array = Rrd::getRrdApplicationArrays($device, $app->app_id, $name);
    foreach ($wg_intf_client_array as $wg_intf_client) {
        $rrdArray[$wg_intf_client] = [
            $metric_name => ['descr' => $wg_intf_client . ' ' . $metric_desc],
        ];
    }
}

if (! $rrdArray) {
    graph_error('No Data to Display', 'No Data');
}

$i = 0;
foreach ($rrdArray as $wg_intf_client => $wg_metric) {
    $rrd_filename = Rrd::name($device['hostname'], [
        $polling_type,
        $name,
        $app->app_id,
        $wg_intf_client,
    ]);

    if (Rrd::checkRrdExists($rrd_filename)) {
        $rrd_list[$i]['filename'] = $rrd_filename;
        $rrd_list[$i]['descr'] = $wg_metric[$metric_name][$metric_desc];
        $rrd_list[$i]['ds'] = $metric_name;
        $i++;
    } else {
        graph_error('No Data file ' . basename($rrd_filename), 'No Data');
    }
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
