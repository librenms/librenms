<?php

$scale_min = 0;

include("includes/graphs/common.inc.php");

$powerdns_rrd   = $config['rrd_dir'] . "/" . $device['hostname'] . "/app-powerdns-".$app['app_id'].".rrd";

if (is_file($powerdns_rrd))
{
  $rrd_filename = $powerdns_rrd;
}

$ds = "latency";

$colour_area = "6699FF";
$colour_line = "336699";

$colour_area_max = "FFEE99";

$graph_max = 100;

$unit_text = "Latency";

include("includes/graphs/generic_simplex.inc.php");

?>
