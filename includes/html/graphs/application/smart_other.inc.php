<?php

$name = 'smart';
$unit_text = '';
$unitlen = 10;
$bigdescrlen = 18;
$smalldescrlen = 18;
$colours = 'mega';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['disk']]);

if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'Spin Retry Count',
        'ds' => 'id10',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'Runtime Bad Block',
        'ds' => 'id183',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'End-to-End Error',
        'ds' => 'id184',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'Reall Evnt Cnt',
        'ds' => 'id196',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'Crnt Pnd Sct Cnt',
        'ds' => 'id197',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'UDMA CRC Err Count',
        'ds' => 'id199',
    ];
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
