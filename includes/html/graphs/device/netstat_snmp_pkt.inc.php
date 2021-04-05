<?php

$rrd_filename = Rrd::name($device['hostname'], 'netstats-snmp');

$ds_in = 'snmpInPkts';
$ds_out = 'snmpOutPkts';

$colour_area_in = 'AA66AA';
$colour_line_in = '330033';
$colour_area_out = 'FFDD88';
$colour_line_out = 'FF6600';

$colour_area_in_max = 'cc88cc';
$colour_area_out_max = 'FFefaa';

$graph_max = 1;
$unit_text = 'Packets';

require 'includes/html/graphs/generic_duplex.inc.php';
