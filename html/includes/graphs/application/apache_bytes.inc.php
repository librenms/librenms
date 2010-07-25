<?php

include("includes/graphs/common.inc.php");

$query = mysql_query("SELECT * FROM `applications` AS A, `devices` AS D WHERE A.app_id = '".mres($_GET['id'])."'
                      AND A.device_id = D.device_id");

$app = mysql_fetch_array($query);

$apache_rrd   = $config['rrd_dir'] . "/" . $app['hostname'] . "/app-apache-".$app['app_id'].".rrd";

if(is_file($apache_rrd)) {
  $rrd_filename = $apache_rrd;
}

$rra = "kbyte";

$colour_area = "CDEB8B";
$colour_line = "006600";

$colour_area_max = "FFEE99";

$graph_max = 1;

$unit_text = "KByte/sec";

include("includes/graphs/generic_simplex.inc.php");

?>
