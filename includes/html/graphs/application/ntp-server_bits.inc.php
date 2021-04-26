<?php

$nototal = 1;
$ds_in = 'packets_recv';
$ds_out = 'packets_sent';
$graph_title .= '::packets';
$unit_text = 'Packets';
$colour_line_in = '330033';
$colour_line_out = 'FF6600';
$colour_area_in = 'AA66AA';
$colour_area_out = 'FFDD88';
$colour_area_in_max = 'CC88CC';
$colour_area_out_max = 'FFEFAA';

$ntpdserver_rrd = Rrd::name($device['hostname'], ['app', 'ntp-server', $app['app_id']]);

if (Rrd::checkRrdExists($ntpdserver_rrd)) {
    $rrd_filename = $ntpdserver_rrd;
}

require 'includes/html/graphs/generic_duplex.inc.php';
