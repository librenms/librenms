<?php

$name = 'cape';
$app_id = $app['app_id'];
$unit_text = 'Signatures Total Per Run';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;
$float_precision = 3;

if (isset($vars['stddev'])) {
    if ($vars['stddev'] != 'on' and $vars['stddev'] != 'off') {
        $vars['stddev'] = 'off';
    }
} else {
    $vars['stddev'] = 'off';
}

if (isset($vars['package'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app['app_id'], 'pkg-signatures_total___-___', $vars['package']]);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app['app_id'], 'signatures_total']);
}

$descr = 'Average';
$ds = 's3signatures_total';

require 'includes/html/graphs/generic_stats.inc.php';
