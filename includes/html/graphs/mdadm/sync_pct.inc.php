<?php

$unit_text = 'Sync %';
$unitlen = 8;
$scale_min = 0;
$scale_max = 100;
$graph_metric_title = 'Sync Progress';
$datasets = [
    ['ds' => 'completed_pct', 'descr' => 'Completed %'],
];

require 'includes/html/graphs/mdadm/app_common.inc.php';
