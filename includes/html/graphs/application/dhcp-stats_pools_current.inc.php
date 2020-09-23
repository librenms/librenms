<?php

$unit_text = 'Current';
$unitlen = 33;
$bigdescrlen = 33;
$smalldescrlen = 33;
$category = 'pools';

$rrdVar = 'current';

$name = 'dhcp-stats';
$app_id = $app['app_id'];
$colours = 'mega';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$arrays = get_arrays_with_application($device, $app_id, $name, $category);

$int = 0;
while (isset($arrays[$int])) {
    $array = $arrays[$int];
    $rrd_filename = rrd_name($device['hostname'], ['app', $name, $app_id, $array]);

    if (rrdtool_check_rrd_exists($rrd_filename)) {
        $rrd_list[] = [
            'filename' => $rrd_filename,
            'descr'    => str_replace($category . '-', '', $array),
            'ds'       => $rrdVar,
        ];
    }

    $int++;
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
