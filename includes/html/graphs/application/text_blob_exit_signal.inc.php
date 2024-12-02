<?php

$name = 'text_blob';
$unit_text = 'exit signal';

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'blobs___' . $vars['blob_name'] . '___exit_signal']);

if (Rrd::checkRrdExists($rrd_filename)) {
    $ds = 'data';
    $filename = $rrd_filename;
} else {
    d_echo('RRD "' . $rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_stats.inc.php';
