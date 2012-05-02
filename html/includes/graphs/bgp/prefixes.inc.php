<?php

include("includes/graphs/common.inc.php");

$scale_min = "0";

$ds = "AcceptedPrefixes";

$colour_area = "AA66AA";
$colour_line = "FFDD88";

$colour_area_max = "FFEE99";

$graph_max = 1;

$unit_text = "Prefixes";

if($config['old_graphs'])
{
  include("includes/graphs/old_generic_simplex.inc.php");
} else {
  include("includes/graphs/generic_simplex.inc.php");
}

?>
