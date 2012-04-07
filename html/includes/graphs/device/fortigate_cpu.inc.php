<?php

$rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("fortigate_cpu.rrd");

include("includes/graphs/common.inc.php");

$ds = "LOAD";

$colour_area = "9999cc";
$colour_line = "0000cc";

$colour_area_max = "9999cc";

$graph_max = 1;

$unit_text = "% CPU";

include("includes/graphs/generic_simplex.inc.php");

?>
