<?php

$scale_min = 0;

include("includes/graphs/common.inc.php");

$apache_rrd   = $config['rrd_dir'] . "/" . $device['hostname'] . "/app-apache-".$app['app_id'].".rrd";

if (is_file($apache_rrd))
{
  $rrd_filename = $apache_rrd;
}

$ds = "kbyte";

$colour_area = "CDEB8B";
$colour_line = "006600";

$colour_area_max = "FFEE99";

$graph_max = 1;
$multiplier = 8;


$unit_text = "Kbps";

include("includes/graphs/generic_simplex.inc.php");

?>
