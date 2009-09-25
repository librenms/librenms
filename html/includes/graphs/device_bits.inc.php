<?php

## Generate a list of interfaces and then call the multi_bits grapher to generate from the list

$device = $_GET['device'];

$hostname = gethostbyid($device);
$query = mysql_query("SELECT `ifIndex`,`interface_id` FROM `interfaces` WHERE `device_id` = '$device' AND `ifType` NOT LIKE '%oopback%' AND `ifType` NOT LIKE '%SVI%' AND `ifType` != 'l2vlan'");
$pluses = "";
while($int = mysql_fetch_row($query)) {
  if(is_file($config['rrd_dir'] . "/" . $hostname . "/" . $int[0] . ".rrd")) {
    $interfaces .= $seperator . $int[1];
    $seperator = ",";
  }
}

include ("multi_bits.inc.php");

?>
