<?php

$rrd_filename = Rrd::name($device['hostname'], 'venturi_transport_packets');

require 'includes/html/graphs/common.inc.php';

$ds_in = 'TransportPacketsRx';
$ds_out = 'TransportPacketsTx';

$colour_area_in = 'AA66AA';
$colour_line_in = '330033';
$colour_area_out = 'FFDD88';
$colour_line_out = 'FF6600';

$colour_area_in_max = 'CC88CC';
$colour_area_out_max = 'FFEFAA';

$graph_max = 1;
$unit_text = 'Packets';

require 'includes/html/graphs/generic_duplex.inc.php';