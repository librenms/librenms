<?php

$scale_min = "0";
$scale_max = "100";

$ds = "usage";

$descr = substr(str_pad(short_hrDeviceDescr($proc['processor_descr']), 28),0,28);
$descr = str_replace(":", "\:", $descr);

$colour_line = "cc0000";
$colour_minmax = "c5c5c5";

$graph_max = 1;
$unit_text = "Usage";

include("includes/graphs/generic_simplex.inc.php");

?>
