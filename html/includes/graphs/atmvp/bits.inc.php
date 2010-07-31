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

$rra_in = "inpacketoctets";
$rra_out = "outpacketoctets";

include("includes/graphs/generic_bits.inc.php");

?>
