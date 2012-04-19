
<?php 

$total = mysql_result(mysql_query("SELECT count(*) FROM `bgpPeers`"),0);
$up    = mysql_result(mysql_query("SELECT count(*) FROM `bgpPeers` WHERE `bgpPeerState` = 'established'"),0);
$stop  = mysql_result(mysql_query("SELECT count(*) FROM `bgpPeers` WHERE `bgpPeerAdminStatus` = 'stop'"),0);

echo('
  <div>
    <span style="device-list">Sessions: '.$total.' Up: '.$up.' Down: '.($total-$up) . ($stop != 0 ? ' ( Shutdown: '.$stop.' )' : '') . '</span>
  </div>');

?>
