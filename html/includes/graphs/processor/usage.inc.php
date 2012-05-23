<?php

$scale_min = "0";
$scale_max = "100";

$ds = "usage";

$descr = rrdtool_escape(short_hrDeviceDescr($proc['processor_descr']), 28);

$colour_line = "cc0000";
$colour_area = "FFBBBB";
$colour_minmax = "c5c5c5";

$graph_max = 1;
$unit_text = "Usage";

include("includes/graphs/generic_simplex.inc.php");

?>
