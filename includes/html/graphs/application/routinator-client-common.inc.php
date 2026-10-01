<?php

$name = 'routinator';
$colours = 'mega';
$dostack = 0;
$printtotal = 0;
$addarea = 0;
$transparency = 15;

$unitlen = 15;
$bigdescrlen = 20;
$smalldescrlen = 15;

if (isset($vars['client'])) {
    $clients = [$vars['client']];
} else {
    $clients = $app->data['clients'] ?? [];
}
sort($clients);

$rrd_list = [];
$int = 0;
while (isset($clients[$int])) {
    $client_name = $clients[$int];
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'client-' . $client_name]);

    if (Rrd::checkRrdExists($rrd_filename)) {
        $rrd_list[] = [
            'filename' => $rrd_filename,
            'descr' => $client_name,
            'ds' => $rrdVar,
        ];
    }
    $int++;
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
