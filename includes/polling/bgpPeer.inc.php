<?php

echo("Polling BGP peers\n");

if(!$config['enable_bgp']) { echo("BGP Support Disabled\n"); } else {

$query = "SELECT * FROM bgpPeers WHERE device_id = '" . $device['device_id'] . "'";
$peers = mysql_query($query);
while($peer = mysql_fetch_array($peers)) {

  ### Poll BGP Peer

  echo("Checking ".$peer['bgpPeerIdentifier']." ");

  $peer_cmd  = $config['snmpget'] . " -m BGP4-MIB -Ovq -" . $device['snmpver'] . " -c" . $device['community'] . " " . $device['hostname'].":".$device['port'] . " ";
  $peer_cmd .= "bgpPeerState." . $peer['bgpPeerIdentifier'] . " bgpPeerAdminStatus." . $peer['bgpPeerIdentifier'] . " bgpPeerInUpdates." . $peer['bgpPeerIdentifier'] . " bgpPeerOutUpdates." . $peer['bgpPeerIdentifier'] . " bgpPeerInTotalMessages." . $peer['bgpPeerIdentifier'] . " ";
  $peer_cmd .= "bgpPeerOutTotalMessages." . $peer['bgpPeerIdentifier'] . " bgpPeerFsmEstablishedTime." . $peer['bgpPeerIdentifier'] . " bgpPeerInUpdateElapsedTime." . $peer['bgpPeerIdentifier'] . " ";
  $peer_cmd .= "bgpPeerLocalAddr." . $peer['bgpPeerIdentifier'] . "";
  $peer_data = trim(`$peer_cmd`);
  list($bgpPeerState, $bgpPeerAdminStatus, $bgpPeerInUpdates, $bgpPeerOutUpdates, $bgpPeerInTotalMessages, $bgpPeerOutTotalMessages, $bgpPeerFsmEstablishedTime, $bgpPeerInUpdateElapsedTime, $bgpLocalAddr) = explode("\n", $peer_data);

  $peerrrd    = $config['rrd_dir'] . "/" . $device['hostname'] . "/bgp-" . $peer['bgpPeerIdentifier'] . ".rrd";
  if(!is_file($peerrrd)) {
    $woo = shell_exec($config['rrdtool'] . " create $peerrrd \
      DS:bgpPeerOutUpdates:COUNTER:600:U:100000000000 \
      DS:bgpPeerInUpdates:COUNTER:600:U:100000000000 \
      DS:bgpPeerOutTotal:COUNTER:600:U:100000000000 \
      DS:bgpPeerInTotal:COUNTER:600:U:100000000000 \
      DS:bgpPeerEstablished:GAUGE:600:0:U \
      RRA:AVERAGE:0.5:1:600 \
      RRA:AVERAGE:0.5:6:700 \
      RRA:AVERAGE:0.5:24:775 \
      RRA:AVERAGE:0.5:288:797 \
      RRA:MAX:0.5:1:600 \
      RRA:MAX:0.5:6:700 \
      RRA:MAX:0.5:24:775 \
      RRA:MAX:0.5:288:797");

  }
  rrdtool_update("$peerrrd", "N:$bgpPeerOutUpdates:$bgpPeerInUpdates:$bgpPeerOutTotalMessages:$bgpPeerInTotalMesages:$bgpPeerFsmEstablishedTime");
  $update  = "UPDATE bgpPeers SET bgpPeerState = '$bgpPeerState', bgpPeerAdminStatus = '$bgpPeerAdminStatus', ";
  $update .= "bgpPeerFsmEstablishedTime = '$bgpPeerFsmEstablishedTime', bgpPeerInUpdates = '$bgpPeerInUpdates' , bgpLocalAddr = '$bgpLocalAddr' , bgpPeerOutUpdates = '$bgpPeerOutUpdates'";
  $update .= " WHERE `device_id` = '".$device['device_id']."' AND bgpPeerIdentifier = '" . $peer['bgpPeerIdentifier'] . "'";

  mysql_query($update);

  if($device['os_group'] == "ios") {

   ## Poll each AFI/SAFI for this peer (using CISCO-BGP4-MIB)
   $afi_query = mysql_query("SELECT * FROM bgpPeers_cbgp WHERE `device_id` = '".$device['device_id']."' AND bgpPeerIdentifier = '" . $peer['bgpPeerIdentifier'] . "'");
   while($peer_afi = mysql_fetch_array($afi_query)) {
   
     $afi = $peer_afi['afi'];
     $safi = $peer_afi['safi'];
     #echo($config['afi'][$afi][$safi]. " ");

     $cbgp_cmd  = $config['snmpget'] . " -m CISCO-BGP4-MIB -Ovq -" . $device['snmpver'] . " -c" . $device['community'] . " " . $device['hostname'].":".$device['port'];
     $cbgp_cmd .= " cbgpPeerAcceptedPrefixes." . $peer['bgpPeerIdentifier'] . ".$afi.$safi";
     $cbgp_cmd .= " cbgpPeerDeniedPrefixes." . $peer['bgpPeerIdentifier'] . ".$afi.$safi";
     $cbgp_cmd .= " cbgpPeerPrefixAdminLimit." . $peer['bgpPeerIdentifier'] . ".$afi.$safi";
     $cbgp_cmd .= " cbgpPeerPrefixThreshold." . $peer['bgpPeerIdentifier'] . ".$afi.$safi";
     $cbgp_cmd .= " cbgpPeerPrefixClearThreshold." . $peer['bgpPeerIdentifier'] . ".$afi.$safi";
     $cbgp_cmd .= " cbgpPeerAdvertisedPrefixes." . $peer['bgpPeerIdentifier'] . ".$afi.$safi";
     $cbgp_cmd .= " cbgpPeerSuppressedPrefixes." . $peer['bgpPeerIdentifier'] . ".$afi.$safi";
     $cbgp_cmd .= " cbgpPeerWithdrawnPrefixes." . $peer['bgpPeerIdentifier'] . ".$afi.$safi";
     #echo("\n$cbgp_cmd\n");
     $cbgp_data = preg_replace("/^OID.*$/", "", trim(`$cbgp_cmd`));

     list($cbgpPeerAcceptedPrefixes,$cbgpPeerDeniedPrefixes,$cbgpPeerPrefixAdminLimit,$cbgpPeerPrefixThreshold,$cbgpPeerPrefixClearThreshold,$cbgpPeerAdvertisedPrefixes,$cbgpPeerSuppressedPrefixes,$cbgpPeerWithdrawnPrefixes) = explode("\n", $cbgp_data);

     $update  = "UPDATE bgpPeers_cbgp SET";
     $update .= " `cbgpPeerAcceptedPrefixes` = '$cbgpPeerAcceptedPrefixes'";
     $update .= ", `cbgpPeerDeniedPrefixes` = '$cbgpPeerDeniedPrefixes'";
     $update .= ", `cbgpPeerPrefixAdminLimit` = '$cbgpPeerAdminLimit'";
     $update .= ", `cbgpPeerPrefixThreshold` = '$cbgpPeerPrefixThreshold'";
     $update .= ", `cbgpPeerPrefixClearThreshold` = '$cbgpPeerPrefixClearThreshold'";
     $update .= ", `cbgpPeerAdvertisedPrefixes` = '$cbgpPeerAdvertisedPrefixes'";
     $update .= ", `cbgpPeerSuppressedPrefixes` = '$cbgpPeerSuppressedPrefixes'";
     $update .= ", `cbgpPeerWithdrawnPrefixes` = '$cbgpPeerWithdrawnPrefixes'";
     $update .= " WHERE `device_id` = '".$device['device_id']."' AND bgpPeerIdentifier = '" . $peer['bgpPeerIdentifier'] . "' AND afi = '$afi' AND safi = '$safi'";

     mysql_query($update);

     $cbgp_rrd    = $config['rrd_dir'] . "/" . $device['hostname'] . "/cbgp-" . $peer['bgpPeerIdentifier'] . ".$afi.$safi.rrd";
     if(!is_file($cbgp_rrd)) {
       $woo = shell_exec($config['rrdtool'] . " create $cbgp_rrd \
         DS:AcceptedPrefixes:GAUGE:600:U:100000000000 \
         DS:DeniedPrefixes:GAUGE:600:U:100000000000 \
         DS:AdvertisedPrefixes:GAUGE:600:U:100000000000 \
         DS:SuppressedPrefixes:GAUGE:600:U:100000000000 \
         DS:WithdrawnPrefixes:GAUGE:600:U:100000000000 \
         RRA:AVERAGE:0.5:1:600 \
         RRA:AVERAGE:0.5:6:700 \
         RRA:AVERAGE:0.5:24:775 \
         RRA:AVERAGE:0.5:288:797 \
         RRA:MAX:0.5:1:600 \
         RRA:MAX:0.5:6:700 \
         RRA:MAX:0.5:24:775 \
         RRA:MAX:0.5:288:797");
     }
     rrdtool_update("$cbgp_rrd", "N:$cbgpPeerAcceptedPrefixes:$cbgpPeerDeniedPrefixes:$cbgpPeerAdvertisedPrefixes:$cbgpPeerSuppressedPrefixes:$cbgpPeerWithdrawnPrefixes");
   }
  }
  echo("\n");

} ## End While loop on peers

} ## End check for BGP support
?>
