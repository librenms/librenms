<?php

// Global strongSwan error rates: invalid messages / invalid SPI (single app-level rrd).

$name = 'strongswan';
$unit_text = 'Errors/s';
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
    $rrd_list[] = ['filename' => $rrd_filename, 'descr' => 'Invalid msg', 'ds' => 'invalid'];
    $rrd_list[] = ['filename' => $rrd_filename, 'descr' => 'Invalid SPI', 'ds' => 'invalid_spi'];
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
