<?php

## Generate a list of ports and then call the multi_bits grapher to generate from the list

$query = mysql_query("SELECT * FROM `ports` WHERE `device_id` = '".$port['device_id']."' AND `pagpGroupIfIndex` = '".$port['ifIndex']."'");

$i=0;
while($int = mysql_fetch_array($query)) {
  if(is_file($config['rrd_dir'] . "/" . $hostname . "/port-" . safename($int['ifIndex'] . ".rrd"))) {
    $rrd_list[$i]['filename'] = $config['rrd_dir'] . "/" . $hostname . "/port-" . safename($int['ifIndex'] . ".rrd");
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

include ("includes/graphs/generic_multi_bits_separated.inc.php");



?>
