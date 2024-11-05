<?php

require 'wireguard-common.inc.php';

$unit_text = 'Minutes';
$interface_client_map = $app->data['mappings'] ?? [];
$colours = 'psychedelic';
$metric_desc = 'Last Handshake';
$metric_name = 'minutes_since_last_handshake';
$rrd_list = [];
$rrdArray = [];
$scale_min = 0;
$unitlen = 7;

if (isset($vars['interface']) && isset($vars['client'])) {
    // This section draws the individual graphs in the device application page
    // displaying the SPECIFIED wireguard interface and client metric.
    $wg_intf_client = $vars['interface'] . '-' . $vars['client'];
    $rrdArray[$wg_intf_client] = [
        $metric_name => ['descr' => $metric_desc],
    ];
} elseif (! isset($vars['interface']) && ! isset($vars['client'])) {
    // This section draws the graph for the application-specific pages
    // displaying ALL wireguard interfaces' clients' metrics.
    foreach ($interface_client_map as $wg_intf => $wg_client_list) {
        foreach ($wg_client_list as $wg_client) {
            $wg_intf_client = $wg_intf . '-' . $wg_client;
            $rrdArray[$wg_intf_client] = [
                $metric_name => [
                    'descr' => $wg_intf_client . ' ' . $metric_desc,
                ],
            ];
        }
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
        $rrd_list[$i]['descr'] = $wg_metric[$metric_name]['descr'];
        $rrd_list[$i]['ds'] = $metric_name;
        $i++;
    } else {
        graph_error('No Data file ' . basename($rrd_filename), 'No Data');
    }
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
