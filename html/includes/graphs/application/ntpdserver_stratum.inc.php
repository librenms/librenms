<?php

include("includes/graphs/common.inc.php");

$scale_min       = 0;
$ds              = "stratum";
$colour_area     = "FFCECE";
$colour_line     = "880000";
$colour_area_max = "FFCCCC";
$graph_max       = 0;
$unit_text       = "Stratum";
$ntpdserver_rrd  = $config['rrd_dir'] . "/" . $device['hostname'] . "/app-ntpdserver-".$app['app_id'].".rrd";

if (is_file($ntpdserver_rrd))
{
  $rrd_filename = $ntpdserver_rrd;
}

include("includes/graphs/generic_simplex.inc.php");

?>
