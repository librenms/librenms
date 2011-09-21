<?php

include("includes/graphs/common.inc.php");

$rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/cipsec_flow.rrd";

$ds = "Tunnels";

$colour_area = "9999cc";
$colour_line = "0000cc";

$colour_area_max = "aaaaacc";

$scale_min = 0;

$unit_text = "Active Tunnels";

include("includes/graphs/generic_simplex.inc.php");

?>