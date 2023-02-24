<?php

$unit_text = 'Max';
$unitlen = 20;
$bigdescrlen = 20;
$smalldescrlen = 20;
$category = 'networks';

$rrdVar = 'max';

$name = 'dhcp-stats';
$colours = 'mega';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$arrays = Rrd::getRrdApplicationArrays($device, $app->app_id, $name, $category);

$int = 0;
$rrd_list = [];
while (isset($arrays[$int])) {
    $array = $arrays[$int];
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $array]);

    if (Rrd::checkRrdExists($rrd_filename)) {
        [$net, $subnet] = explode('_', str_replace($category . '-', '', $array));
        $rrd_list[] = [
            'filename' => $rrd_filename,
            'descr'    => $net . '/' . $subnet,
            'ds'       => $rrdVar,
        ];
    }
    $int++;
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
