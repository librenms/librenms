<?php

include("includes/graphs/common.inc.php");

$scale_min = "0";

$rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/". safename("cbgp-" . $data['bgpPeerIdentifier'] . ".".$_GET['afi'].".".$_GET['safi'].".rrd");

$rra = "AcceptedPrefixes";

$colour_area = "AA66AA";
$colour_line = "FFDD88";

$colour_area_max = "FFEE99";

$graph_max = 1;

$unit_text = "Prefixes";

include("includes/graphs/generic_simplex.inc.php");

?>
