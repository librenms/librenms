<?php

include("includes/graphs/common.inc.php");

$rrd_filename   = $config['rrd_dir'] . "/" . $device['hostname'] . "/ucd_ssRawInterrupts.rrd";

$ds = "value";

$colour_area = "CC9999";
$colour_line = "CC0000";

$colour_area_max = "cc9999";

$graph_max = 1;
$graph_min = 0;

$unit_text = "Interrupts/s";

include("includes/graphs/generic_simplex.inc.php");

?>