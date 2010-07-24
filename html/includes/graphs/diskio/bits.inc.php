<?php

if($_GET['id']) { $id = $_GET['id']; }

$query = mysql_query("SELECT * FROM `ucd_diskio` AS U, `devices` AS D WHERE U.diskio_id = '".$id."' AND U.device_id = D.device_id");

$disk = mysql_fetch_array($query);
if(is_file($config['rrd_dir'] . "/" . $disk['hostname'] . "/ucd_diskio-" . safename($disk['diskio_descr'] . ".rrd"))) {
  $rrd_filename = $config['rrd_dir'] . "/" . $disk['hostname'] . "/ucd_diskio-" . safename($disk['diskio_descr'] . ".rrd");
}

$rra_in = "read";
$rra_out = "written";

include("generic_bits.inc.php");

?>
