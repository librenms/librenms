<?php

$name = 'cape';
$app_id = $app['app_id'];
$unit_text = 'Reg Keys Modded Per Run';
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
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app['app_id'], 'pkg-reg_keys_mod___-___', $vars['package']]);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app['app_id'], 'reg_keys_mod']);
}

$rrd_list = [];
if (Rrd::checkRrdExists($rrd_filename)) {
    $filename = $rrd_filename;
    $descr = 'Average';
    $ds = 's3regkeysmod';
} else {
    d_echo('RRD "' . $rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_stats.inc.php';
