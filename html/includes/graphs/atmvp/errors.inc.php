<?php

if($_GET['id']) { $atm_vp_id = $_GET['id']; }

$sql =  "SELECT * FROM `juniAtmVp` as J, `ports` AS I, `devices` AS D";
$sql .= " WHERE J.juniAtmVp_id = '".$atm_vp_id."' AND I.interface_id = J.interface_id AND I.device_id = D.device_id";

$query = mysql_query($sql);
$vp = mysql_fetch_array($query);


$rrd_test = $config['rrd_dir'] . "/" . $vp['hostname'] . "/" . safename("vp-" . $vp['ifIndex'] . "-".$vp['vp_id'].".rrd");
if(is_file($rrd_test)) {
  $rrd_filename = $rrd_test;
}

$rra_in = "inpacketerrors";
$rra_out = "outpacketerrors";

$colour_area_in = "FF3300";
$colour_line_in = "FF0000";
$colour_area_out = "FF6633";
$colour_line_out = "CC3300";

$colour_area_in_max = "FF6633";
$colour_area_out_max = "FF9966";

$graph_max = 1;

$unit_text = "Errors";

include("includes/graphs/generic_duplex.inc.php");

?>
