<?php

/*

LibreNMS Application for I2PD
Graph: total bytes transferred in+out
Ingest from: i2p.router.net.total.received.bytes + i2p.router.net.total.sent.bytes
Datasets: total_rx_bytes + total_tx_bytes

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

$ds_in = 'total_rx_bytes';
$ds_out = 'total_tx_bytes';
$format = 'bytes';
/*$unit_text = 'Transferred';
$colour_line_in = '006600';
$colour_line_out = '000099';
$colour_area_in = 'CDEB8B';
$colour_area_out = 'C3D9FF';
$print_total = false;*/

require 'includes/html/graphs/generic_data.inc.php';
//require 'includes/html/graphs/generic_duplex.inc.php';
