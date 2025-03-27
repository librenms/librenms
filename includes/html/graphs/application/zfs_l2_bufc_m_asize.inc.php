<?php

$name = 'zfs';
$unit_text = 'Bytes';
$colours = 'psychedelic';
$descr = 'L2 BufC M Asize';
$ds = 'l2_bufc_m_asize';

$filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, '_____group2']);

if (! Rrd::checkRrdExists($filename)) {
    d_echo('RRD "' . $filename . '" not found');
}

require 'includes/html/graphs/generic_stats.inc.php';
