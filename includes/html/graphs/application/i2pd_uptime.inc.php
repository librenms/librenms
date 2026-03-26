<?php

/*

LibreNMS Application for I2PD
Graph: uptime
Ingest from: i2p.router.uptime
Datasets: uptime

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

$scale_min = 0; // uptime cant go below 0
$ds = 'uptime';
$colours = 'greens';
$float_precision = 1;
$descr = 'Uptime';
$munge = true;  // do some math before presenting values
$munge_opts = '86400000,/'; // uptime is in milliseconds, convert to days
//$units = 'd';
$unit_text = 'Days';
$no_percentile = true;
$no_hourly = false;
$no_daily = true;
$no_weekly = true;

require 'includes/html/graphs/generic_stats.inc.php';
