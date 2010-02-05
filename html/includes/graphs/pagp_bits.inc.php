<?php

## Generate a list of interfaces and then call the multi_bits grapher to generate from the list

$parent = mysql_fetch_array(mysql_query("SELECT * FROM `interfaces` WHERE interface_id = '".mres($_GET['port'])."'"));

$query = mysql_query("SELECT * FROM `interfaces` WHERE `device_id` = '".$parent['device_id']."' AND `pagpGroupIfIndex` = '".$parent['ifIndex']."'");

$i=0;
while($int = mysql_fetch_array($query)) {
  if(is_file($config['rrd_dir'] . "/" . $hostname . "/" . safename($int['ifIndex'] . ".rrd"))) {
    $rrd_list[$i]['filename'] = $config['rrd_dir'] . "/" . $hostname . "/" . safename($int['ifIndex'] . ".rrd");
    $rrd_list[$i]['descr'] = $int['ifDescr'];
    $i++;
  }
}

$units='bps'; 
$total_units='B'; 
$colours_in='greens'; 
$multiplier = "8"; 
$colours_out = 'blues';

$nototal = 1;
$rra_in  = "INOCTETS";
$rra_out = "OUTOCTETS";

include ("generic_multi_bits_separated.inc.php");



?>
