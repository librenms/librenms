<?php

// Global strongSwan rekey rates (single app-level rrd 'global', multiple ds).

$name = 'strongswan';
$unit_text = 'Rekeys/s';
$unitlen = 15;
$bigdescrlen = 18;
$smalldescrlen = 18;
$colours = 'mega';
$scale_min = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'global']);

$rrd_list = [];
if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_list[] = ['filename' => $rrd_filename, 'descr' => 'IKE rekey', 'ds' => 'ike_rekey'];
    $rrd_list[] = ['filename' => $rrd_filename, 'descr' => 'Child rekey', 'ds' => 'child_rekey'];
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
