<?php

include("includes/graphs/common.inc.php");

$rrd_filename   = $config['rrd_dir'] . "/" . $device['hostname'] . "/ucd_ssRawContexts.rrd";

$ds = "value";

$colour_area = "9999cc";
$colour_line = "0000cc";

$colour_area_max = "9999cc";

$graph_max = 1;

$unit_text = "Switches/s";

include("includes/graphs/generic_simplex.inc.php");

?>