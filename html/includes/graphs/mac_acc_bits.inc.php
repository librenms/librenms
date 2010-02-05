<?php

$query = mysql_query("SELECT * FROM `mac_accounting` AS M, `interfaces` AS I, `devices` AS D WHERE M.ma_id = '".mres($_GET['id'])."' 
                      AND I.interface_id = M.interface_id AND I.device_id = D.device_id");

$acc = mysql_fetch_array($query);
if(is_file($config['rrd_dir'] . "/" . $acc['hostname'] . "/" . safename("cip-" . $acc['ifIndex'] . "-" . $acc['mac'] . ".rrd"))) {
  $rrd_filename = $config['rrd_dir'] . "/" . $acc['hostname'] . "/". safename("cip-" . $acc['ifIndex'] . "-" . $acc['mac'] . ".rrd");
}

$rra_in = "IN";
$rra_out = "OUT";

include("generic_bits.inc.php");

?>
