<?php

$scale_min = 0;

include("includes/graphs/common.inc.php");

$agent_rrd   = $config['rrd_dir'] . "/" . $device['hostname'] . "/agent.rrd";

if (is_file($agent_rrd))
{
  $rrd_filename = $agent_rrd;
}

$ds = "time";

$colour_area = "CDEB8B";
$colour_line = "006600";

$colour_area_max = "FFEE99";

$graph_max = 1;

$unit_text = "msec";

include("includes/graphs/generic_simplex.inc.php");

?>
