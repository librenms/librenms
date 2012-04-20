<?php

$i = 1;

foreach (explode(",", $id) as $ifid)
{
  if (strstr($ifid, "!"))
  {
    $rrd_inverted[$i] = TRUE;
    $ifid = str_replace("!", "", $ifid);
  }

  $int = dbFetchRow("SELECT `ifIndex`, `hostname` FROM `ports` AS I, devices as D WHERE I.interface_id = ? AND I.device_id = D.device_id", array($ifid));
  if (is_file($config['rrd_dir'] . "/" . $int['hostname'] . "/port-" . safename($int['ifIndex'] . ".rrd")))
  {
    $rrd_filenames[$i] = $config['rrd_dir'] . "/" . $int['hostname'] . "/port-" . safename($int['ifIndex'] . ".rrd");
    $i++;
  }
}

$ds_in  = "INOCTETS";
$ds_out = "OUTOCTETS";

$colour_line_in = "006600";
$colour_line_out = "000099";
$colour_area_in = "CDEB8B";
$colour_area_out = "C3D9FF";

include("includes/graphs/generic_multi_data.inc.php");

?>
