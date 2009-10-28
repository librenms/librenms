<?php

$data = mysql_fetch_array(mysql_query("SELECT * FROM bgpPeers AS B, devices AS D WHERE bgpPeer_id = '".$_GET['peer']."' AND D.device_id = B.device_id"));

$rrd_filename = $config['rrd_dir'] . "/" . $data['hostname'] . "/cbgp-" . $data['bgpPeerIdentifier'] . ".".$_GET['afi'].".".$_GET['safi'].".rrd";

$rra = "AcceptedPrefixes";

$colour_area = "AA66AA";
$colour_line = "FFDD88";

$colour_area_max = "FFEE99";

$graph_max = 1;

$unit_text = "Prefixes";

include("generic_simplex.inc.php");

?>
