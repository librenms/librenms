<?php

if($_GET['id']) { $id = $_GET['id']; }

$query = mysql_query("SELECT * FROM `ucd_diskio` AS U, `devices` AS D WHERE U.diskio_id = '".$id."' AND U.device_id = D.device_id");

$disk = mysql_fetch_array($query);
if(is_file($config['rrd_dir'] . "/" . $disk['hostname'] . "/ucd_diskio-" . safename($disk['diskio_descr'] . ".rrd"))) {
  $rrd_filename = $config['rrd_dir'] . "/" . $disk['hostname'] . "/ucd_diskio-" . safename($disk['diskio_descr'] . ".rrd");
}

$rra_in = "reads";
$rra_out = "writes";

$colour_area_in = "FF3300";
$colour_line_in = "FF0000";
$colour_area_out = "FF6633";
$colour_line_out = "CC3300";

$colour_area_in_max = "FF6633";
$colour_area_out_max = "FF9966";

$graph_max = 1;

$unit_text = "Operations";

include("generic_duplex.inc.php");

?>
