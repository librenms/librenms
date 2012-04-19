<?php

$device = device_by_id_cache($id);

$scale_min = "0";

include("includes/graphs/common.inc.php");

$database = $config['rrd_dir'] . "/" . $device['hostname'] . "/hr_users.rrd";

$rrd_options .= " DEF:users=$database:users:AVERAGE";
$rrd_options .= " DEF:users_max=$database:users:MAX";
$rrd_options .= " COMMENT:'Users      Cur     Ave      Min     Max\\n'";
$rrd_options .= " AREA:users_max#defc9c:";
$rrd_options .= " AREA:users#CDEB8B:";
$rrd_options .= " LINE1.25:users#008C00: ";
$rrd_options .= " GPRINT:users:LAST:'    %6.2lf'";
$rrd_options .= " GPRINT:users:AVERAGE:%6.2lf";
$rrd_options .= " GPRINT:users:MIN:%6.2lf";
$rrd_options .= " GPRINT:users_max:MAX:'%6.2lf\\n'";

?>
