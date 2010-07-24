<?php

$scale_min = "0";

$data = mysql_fetch_array(mysql_query("SELECT * FROM bgpPeers AS B, devices AS D WHERE bgpPeer_id = '".$_GET['peer']."' AND D.device_id = B.device_id"));

$rrd_filename = $config['rrd_dir'] . "/" . $data['hostname'] . "/" . safename("bgp-" . $data['bgpPeerIdentifier'] . ".rrd");

$rra_in = "bgpPeerInUpdates";
$rra_out = "bgpPeerOutUpdates";

$colour_area_in = "AA66AA";
$colour_line_in = "330033";
$colour_area_out = "FF6600";
$colour_line_out = "FFDD88";

$colour_area_in_max = "FFEE99";
$colour_area_out_max = "FF7711";

$graph_max = 1;

$unit_text = "Updates";

include("includes/graphs/generic_duplex.inc.php");

?>
