
<?php 

echo("<strong>AS".$device['bgpLocalAs']."</strong>"); 

echo("<br />");

$total = mysql_result(mysql_query("SELECT count(*) FROM `bgpPeers` WHERE `device_id` = '".$device['device_id']."'"),0);
$up    = mysql_result(mysql_query("SELECT count(*) FROM `bgpPeers` WHERE `device_id` = '".$device['device_id']."' AND `bgpPeerState` = 'established'"),0);
$stop  = mysql_result(mysql_query("SELECT count(*) FROM `bgpPeers` WHERE `device_id` = '".$device['device_id']."' AND `bgpPeerAdminStatus` = 'stop'"),0);

echo("Sessions: ".$total." Up: ".$up." Down: ".($total-$up) . ($stop != 0 ? " ( Shutdown: ".$stop." )" : ""));

?>
