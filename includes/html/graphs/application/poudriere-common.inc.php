<?php

$name = 'poudriere';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;
$scale_min = 0;

$rrd_list = [];
foreach ($stats_list as $stat_to_add) {


    if (Rrd::checkRrdExists($rrd_filename)) {
        $rrd_list[] = [
            'filename' => $rrd_filename,
            'descr' => $disk,
            'ds' => 'data',
        ];
    }
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
