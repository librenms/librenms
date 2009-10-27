<?php

$query = mysql_query("SELECT * FROM `mac_accounting` AS M, `interfaces` AS I, `devices` AS D WHERE M.ma_id = '".mres($_GET['id'])."' 
                      AND I.interface_id = M.interface_id AND I.device_id = D.device_id");

$acc = mysql_fetch_array($query);
if(is_file($config['rrd_dir'] . "/" . $acc['hostname'] . "/cip-" . $acc['ifIndex'] . "-" . $acc['mac'] . ".rrd")) {
  $rrd_filename = $config['rrd_dir'] . "/" . $acc['hostname'] . "/cip-" . $acc['ifIndex'] . "-" . $acc['mac'] . ".rrd";
}

$rra_in = "PIN";
$rra_out = "POUT";

$colour_area_in = "AA66AA";
$colour_line_in = "330033";
$colour_area_out = "FFDD88";
$colour_line_out = "FF6600";

$colour_area_in_max = "CC88CC";
$colour_area_out_max = "FFEFAA";

$graph_max = 1;
$unit_text = "Pkts\ \ \ ";

include("generic_duplex.inc.php");

?>
