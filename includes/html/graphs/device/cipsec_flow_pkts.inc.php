<?php

$rrd_filename = Rrd::name($device['hostname'], 'cipsec_flow');

$ds_in = 'InPkts';
$ds_out = 'OutPkts';

$colour_area_in = 'AA66AA';
$colour_line_in = '330033';
$colour_area_out = 'FFDD88';
$colour_line_out = 'FF6600';

$colour_area_in_max = 'CC88CC';
$colour_area_out_max = 'FFEFAA';

$graph_max = 1;
$unit_text = 'Pkts   ';

require 'includes/html/graphs/generic_duplex.inc.php';
