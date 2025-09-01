<?php

$name = 'borgbackup';
$unit_text = 'Chunks';
$descr = 'Unique';
$ds = 'data';
$no_hourly = true;

$name_part = 'total_unique_chunks';

if (isset($vars['borgrepo'])) {
    $name_part = 'repos___' . $vars['borgrepo'] . '___' . $name_part;
} else {
    $name_part = 'totals___' . $name_part;
}

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $name_part]);

require 'includes/html/graphs/generic_stats.inc.php';
