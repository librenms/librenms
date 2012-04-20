<?php

include("includes/graphs/common.inc.php");

$mysql_rrd = $config['rrd_dir'] . "/" . $device['hostname'] . "/app-mysql-".$app['app_id'].".rrd";

if (is_file($mysql_rrd))
{
  $rrd_filename = $mysql_rrd;
}

$multiplier = 8;

$ds_in = "BRd";
$ds_out = "BSt";

include("includes/graphs/generic_data.inc.php");

?>
