<?php

// $units                = "b";
// $total_units        = "B";
// $colours_in                = "greens";
// $multiplier        = "0";
// $colours_out        = "blues";
$nototal = 1;

$ds_in  = 'packets_recv';
$ds_out = 'packets_sent';

$graph_title .= '::packets';
$unit_text    = 'Packets';

$colour_line_in      = '330033';
$colour_line_out     = 'FF6600';
$colour_area_in      = 'AA66AA';
$colour_area_out     = 'FFDD88';
$colour_area_in_max  = 'CC88CC';
$colour_area_out_max = 'FFEFAA';

$ntpdserver_rrd = rrd_name($device['hostname'], array('app', 'ntpdserver', $app['app_id']));

if (is_file($ntpdserver_rrd)) {
    $rrd_filename = $ntpdserver_rrd;
}

// include("includes/graphs/generic_bits.inc.php");
require 'includes/graphs/generic_duplex.inc.php';
