<?php

// Shared renderer for the per-RIR (trust anchor) graphs. The caller sets
// $rrdVar (the dataset to plot) and $unit_text before requiring this file.
// One line per trust anchor, read from the per-TAL rrd files.

$name = 'routinator';
$colours = 'mega';
$dostack = 0;
$printtotal = 0;
$addarea = 0;
$transparency = 15;

$unitlen = 15;
$bigdescrlen = 15;
$smalldescrlen = 10;

$tals = $app->data['tals'] ?? ['afrinic', 'apnic', 'arin', 'lacnic', 'ripe'];
sort($tals);

$rrd_list = [];
$int = 0;
while (isset($tals[$int])) {
    $tal_name = $tals[$int];
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'tal-' . $tal_name]);

    if (Rrd::checkRrdExists($rrd_filename)) {
        $rrd_list[] = [
            'filename' => $rrd_filename,
            'descr' => $tal_name,
            'ds' => $rrdVar,
        ];
    }
    $int++;
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
