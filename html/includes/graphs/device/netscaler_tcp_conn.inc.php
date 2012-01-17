<?php

$rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/netscaler-stats-tcp.rrd";

$ds_in = "CurClientConn";
$ds_out = "CurServerConn";

$in_text = "Client";
$out_text = "Server";

$colour_area_in = "88FF88";
$colour_line_in = "008800";
$colour_area_out = "FF8888";
$colour_line_out = "880000";

$colour_area_in_max = "cc88cc";
$colour_area_out_max = "FFefaa";

$graph_max = 1;
$unit_text = "Connections";

include("includes/graphs/generic_duplex.inc.php");

?>
