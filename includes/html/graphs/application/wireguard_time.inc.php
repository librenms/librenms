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

$unit_text = 'Minutes';
$colours = 'psychedelic';

$rrdArray = [
    'minutes_since_last_handshake' => ['descr' => 'Last Handshake'],
];

$rrd_filename = Rrd::name($device['hostname'], [
    $polling_type,
    $name,
    $app->app_id,
    $interface_client,
]);

if (Rrd::checkRrdExists($rrd_filename)) {
    foreach ($rrdArray as $rrdVar => $rrdValues) {
        $rrd_list[] = [
            'filename' => $rrd_filename,
            'descr' => $rrdValues['descr'],
            'ds' => $rrdVar,
        ];
    }
} else {
    d_echo('RRD ' . $rrd_filename . ' not found');
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
