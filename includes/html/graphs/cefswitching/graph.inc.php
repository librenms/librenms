<?php

$rrd_list = [];
foreach (['drop', 'punt', 'hostpunt'] as $ds) {
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => $ds,
        'ds' => $ds,
    ];
}

$colours = 'mixed';
$nototal = 1;
$unit_text = 'Errors';

require 'includes/html/graphs/generic_multi_simplex_seperated.inc.php';
