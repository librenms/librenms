<?php

/*

LibreNMS Application for I2PD
Graph: network status code
Ingest from: i2p.router.net.status
Datasets: net_status

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
$ds = 'net_status';
$colour_area = 'FFCECE';
$colour_line = '880000';
$colour_area_max = 'FFCCCC';
//$scale_max = 15;
$unit_text = 'Status';
$float_precision = 0;

require 'includes/html/graphs/generic_simplex.inc.php';
