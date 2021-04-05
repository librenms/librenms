<?php

$name = 'smart';
$app_id = $app['app_id'];
$unit_text = '';
$unitlen = 10;
$bigdescrlen = 25;
$smalldescrlen = 25;
$colours = 'mega';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app_id, $vars['disk']]);

if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Worst_Case_Erase_Count',
        'ds'       => 'id173',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Wear_Leveling_Count',
        'ds'       => 'id177',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'SSD_Life_Left',
        'ds'       => 'id231',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Media_Wearout_Indicator',
        'ds'       => 'id233',
    ];
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
