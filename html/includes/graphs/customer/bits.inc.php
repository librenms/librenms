<?php

## Generate a list of ports and then call the multi_bits grapher to generate from the list

$query = mysql_query("SELECT * FROM `ports` AS I, `devices` AS D WHERE `ifAlias` LIKE 'Cust: ".mres($_GET['cust'])."%' AND D.device_id = I.device_id");
while($int = mysql_fetch_array($query)) {
  if(is_file($config['rrd_dir'] . "/" . $int['hostname'] . "/" . safename($int['ifIndex'] . ".rrd"))) {
    $rrd_filenames[] = $config['rrd_dir'] . "/" . $int['hostname'] . "/" . safename($int['ifIndex'] . ".rrd");
  }
}

$rra_in  = "INOCTETS";
$rra_out = "OUTOCTETS"; 

$colour_line_in = "006600";
$colour_line_out = "000099";
$colour_area_in = "CDEB8B";
$colour_area_out = "C3D9FF";

include ("includes/graphs/generic_multi_bits.inc.php");

?>
