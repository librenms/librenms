<?php

$unit_text = 'Disk Count';
$unitlen = 12;
$graph_metric_title = 'Disk Counts';
$datasets = [
    ['ds' => 'active', 'descr' => 'Active'],
    ['ds' => 'spare', 'descr' => 'Spare'],
    ['ds' => 'failed', 'descr' => 'Failed'],
    ['ds' => 'degraded', 'descr' => 'Degraded'],
];

require 'includes/html/graphs/mdadm/app_common.inc.php';
