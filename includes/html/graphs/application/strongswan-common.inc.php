<?php

// Multi-instance (per-tunnel) overview helper for the strongSwan application.
// Each per-metric graph sets $rrdVar (and optionally $multiplier) then requires this.

$name = 'strongswan';
$colours = 'mega';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;
$scale_min = 0;

if (isset($vars['tunnel'])) {
    $tunnels = [$vars['tunnel']];
} else {
    $tunnels = Rrd::getRrdApplicationArrays($device, $app->app_id, $name);
}

$labels = $app->data['labels'] ?? [];

$int = 0;
$rrd_list = [];
while (isset($tunnels[$int])) {
    $tunnel = $tunnels[$int];
    $int++;

    if ($tunnel === 'global') {
        continue; // global counters live in their own rrd / graphs
    }

    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $tunnel]);
    if (Rrd::checkRrdExists($rrd_filename)) {
        $rrd_list[] = [
            'filename' => $rrd_filename,
            'descr' => $labels[$tunnel] ?? $tunnel,
            'ds' => $rrdVar,
        ];
    }
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
