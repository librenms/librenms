<?php

/*

LibreNMS Application for I2PD
Graph: number of peers known + active
Ingest from: i2p.router.netdb.activepeers + i2p.router.netdb.knownpeers
Datasets: active_peers + known_peers

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
$colours = 'mixed';
$nototal = true;
$unit_text = 'Peers';
$array = [
    'active_peers' => ['descr' => 'Active'],
    'known_peers' => ['descr' => 'Known'],
];

$i = 0;

foreach ($array as $ds => $var) {
    $rrd_list[$i]['filename'] = $rrd_filename;
    $rrd_list[$i]['descr'] = $var['descr'];
    $rrd_list[$i]['ds'] = $ds;
    $rrd_list[$i]['colour'] = \App\Facades\LibrenmsConfig::get("graph_colours.$colours.$i");
    $i++;
}

require 'includes/html/graphs/generic_multi_line.inc.php';
