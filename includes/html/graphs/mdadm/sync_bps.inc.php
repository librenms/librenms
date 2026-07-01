<?php

$unit_text = 'Bytes/s';
$unitlen = 12;
$scale_min = 0;
$graph_metric_title = 'Sync Speed';
$datasets = [
    ['ds' => 'speed_bps', 'descr' => 'Bytes/s'],
];

require 'includes/html/graphs/mdadm/app_common.inc.php';
