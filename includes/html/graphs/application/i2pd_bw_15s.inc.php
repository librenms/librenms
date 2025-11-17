<?php

/*

LibreNMS Application for I2PD
Graph: bandwidth In/Out 15secAVG
Ingest from: i2p.router.net.bw.inbound.15s + i2p.router.net.bw.outbound.15s
Datasets: bw_in_15s + bw_out_15s

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

$print_total = true;
$ds_in = 'bw_in_15s';
$ds_out = 'bw_out_15s';
$unit_text = 'Bytes/s';
$format = 'bytes';
$colour_line_in = '330033';
$colour_line_out = 'FF6600';
$colour_area_in = 'AA66AA';
$colour_area_out = 'FFDD88';
$colour_area_in_max = 'CC88CC';
$colour_area_out_max = 'FFEFAA';

require 'includes/html/graphs/generic_duplex.inc.php';
