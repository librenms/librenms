<?php

$scale_min = '0';

$rrd_filename = Rrd::name($device['hostname'], ['bgp', $data['bgpPeerIdentifier']]);

$ds_in = 'bgpPeerInUpdates';
$ds_out = 'bgpPeerOutUpdates';

$colour_area_in = 'AA66AA';
$colour_line_in = '330033';
$colour_area_out = 'FF6600';
$colour_line_out = 'FFDD88';

$colour_area_in_max = 'FFEE99';
$colour_area_out_max = 'FF7711';

$graph_max = 1;

$unit_text = 'Updates';

require 'includes/html/graphs/generic_duplex.inc.php';
