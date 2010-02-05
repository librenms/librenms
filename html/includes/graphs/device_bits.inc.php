<?php

## Generate a list of interfaces and then call the multi_bits grapher to generate from the list

$device = mres($_GET['device']);
$hostname = gethostbyid($device);

$query = mysql_query("SELECT `ifIndex`,`interface_id` FROM `interfaces` WHERE `device_id` = '$device' AND `ifType` NOT LIKE '%oopback%' AND `ifType` NOT LIKE '%SVI%' AND `ifType` != 'l2vlan'");
while($int = mysql_fetch_row($query)) {
  if(is_file($config['rrd_dir'] . "/" . $hostname . "/" . safename($int[0] . ".rrd"))) {
    $rrd_filenames[] = $config['rrd_dir'] . "/" . $hostname . "/" . safename($int[0] . ".rrd");
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
