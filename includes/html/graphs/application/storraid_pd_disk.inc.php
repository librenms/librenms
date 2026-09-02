<?php

/**
 * LibreNMS Application Graph: storraid_pd_disk
 *
 * Renders media errors, other errors, and predictive failure count
 * for a single physical disk, identified by $vars['disk'].
 */
$name = 'storraid';
$scale_min = 0;
$colours = 'mixed';
$unit_text = 'Errors';
$unitlen = 14;
$bigdes = 1;

$disk_id = $vars['disk'] ?? '';
$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, "pd_{$disk_id}"]);

$rrd_list = [];

if (file_exists($rrd_filename)) {
    $rrd_list[] = ['filename' => $rrd_filename, 'descr' => 'Media Errors',      'ds' => 'media_errors'];
    $rrd_list[] = ['filename' => $rrd_filename, 'descr' => 'Other Errors',      'ds' => 'other_errors'];
    $rrd_list[] = ['filename' => $rrd_filename, 'descr' => 'Predictive Failure', 'ds' => 'pred_failure'];
} else {
    d_echo('RRD not found: ' . $rrd_filename);
}

require 'includes/html/graphs/generic_multi_line.inc.php';
