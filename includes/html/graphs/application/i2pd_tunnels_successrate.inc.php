<?php

/*

LibreNMS Application for I2PD
Graph: tunnel success rate (pct)
Ingest from: i2p.router.net.tunnels.successrate
Datasets: tunnels_successrate

@author     Kossusukka <kossusukka@kossulab.net>

LICENSE - GPLv3

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
version 3. See https://www.gnu.org/licenses/gpl-3.0.txt

*/

$rrd_filename = Rrd::name($device['hostname'], ['app', 'i2pd', $app->app_id]);
if (! isset($rrd_filename)) {
    graph_error('No Data to Display', 'No Data');
}

if (! Rrd::checkRrdExists($rrd_filename)) {
    graph_error('No Data file ' . basename((string) $rrd_filename), 'No Data');
}

$scale_min = 0;
$ds = 'tunnels_successrate';
$float_precision = 0;
$descr = 'Successrate';
$units = '%%';
//$unit_text = 'Rate';
$no_percentile = true;

require 'includes/html/graphs/generic_stats.inc.php';
