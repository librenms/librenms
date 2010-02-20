<?php

if($_GET['port']) { $ports = $_GET['port']; }
if($_GET['ports']) { $ports = $_GET['ports']; }


$i = 1;
foreach(explode(",", $ports) as $ifid) {
  $query = mysql_query("SELECT `ifIndex`, `hostname` FROM `ports` AS I, devices as D WHERE I.interface_id = '" . $ifid . "' AND I.device_id = D.device_id");
  $int = mysql_fetch_row($query);
  if(is_file($config['rrd_dir'] . "/" . $int[1] . "/" . safename($int[0] . ".rrd"))) {
    $rrd_filenames[] = $config['rrd_dir'] . "/" . $int[1] . "/" . safename($int[0] . ".rrd");
    $i++;
  }
}

$rra_in  = "INOCTETS";
$rra_out = "OUTOCTETS";

$colour_line_in = "006600";
$colour_line_out = "000099";
$colour_area_in = "CDEB8B";
$colour_area_out = "C3D9FF";

include ("generic_multi_bits.inc.php");


?>
