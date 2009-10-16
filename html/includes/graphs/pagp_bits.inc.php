<?php

## Generate a list of interfaces and then call the multi_bits grapher to generate from the list

$parent = mysql_fetch_array(mysql_query("SELECT * FROM `interfaces` WHERE interface_id = '".$_GET['port']."'"));

$query = mysql_query("SELECT `ifIndex`,`interface_id` FROM `interfaces` WHERE `device_id` = '".$parent['device_id']."' AND `pagpGroupIfIndex` = '".$parent['ifIndex']."'");

while($int = mysql_fetch_row($query)) {
  if(is_file($config['rrd_dir'] . "/" . $hostname . "/" . $int[0] . ".rrd")) {
    $interfaces .= $seperator . $int[1];
    $seperator = ",";
  }
}

$args['nototal'] = 1;

include ("multi_bits_separate.inc.php");



?>
