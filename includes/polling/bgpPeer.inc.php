<?

echo("Polling BGP peers\n");

if(!$config['enable_bgp']) { echo("BGP Support Disabled\n"); } else {

$query = "SELECT * FROM bgpPeers WHERE device_id = '" . $device['device_id'] . "'";
$peers = mysql_query($query);
while($peer = mysql_fetch_array($peers)) {

  ### Poll BGP Peer

  echo("Checking ".$peer['bgpPeerIdentifier']."\n");

  $peer_cmd  = $config['snmpget'] . " -Ovq -" . $device['snmpver'] . " -c" . $device['community'] . " " . $device['hostname'].":".$device['port'] . " ";
  $peer_cmd .= "bgpPeerState." . $peer['bgpPeerIdentifier'] . " bgpPeerAdminStatus." . $peer['bgpPeerIdentifier'] . " bgpPeerInUpdates." . $peer['bgpPeerIdentifier'] . " bgpPeerOutUpdates." . $peer['bgpPeerIdentifier'] . " bgpPeerInTotalMessages." . $peer['bgpPeerIdentifier'] . " ";
  $peer_cmd .= "bgpPeerOutTotalMessages." . $peer['bgpPeerIdentifier'] . " bgpPeerFsmEstablishedTime." . $peer['bgpPeerIdentifier'] . " bgpPeerInUpdateElapsedTime." . $peer['bgpPeerIdentifier'] . " ";
  $peer_cmd .= "bgpPeerLocalAddr." . $peer['bgpPeerIdentifier'] . "";
  $peer_data = trim(`$peer_cmd`);

  list($bgpPeerState, $bgpPeerAdminStatus, $bgpPeerInUpdates, $bgpPeerOutUpdates, $bgpPeerInTotalMessages, $bgpPeerOutTotalMessages, $bgpPeerFsmEstablishedTime, $bgpPeerInUpdateElapsedTime, $bgpLocalAddr) = explode("\n", $peer_data);

  $peerrrd    = $rrd_dir . "/" . $device['hostname'] . "/bgp-" . $peer['bgpPeerIdentifier'] . ".rrd";

  if(!is_file($peerrrd)) {
    $woo = `rrdtool create $peerrrd \
      DS:bgpPeerOutUpdates:COUNTER:600:U:100000000000 \
      DS:bgpPeerInUpdates:COUNTER:600:U:100000000000 \
      DS:bgpPeerOutTotal:COUNTER:600:U:100000000000 \
      DS:bgpPeerInTotal:COUNTER:600:U:100000000000 \
      DS:bgpPeerEstablished:GAUGE:600:0:U \
      RRA:AVERAGE:0.5:1:600 \
      RRA:AVERAGE:0.5:6:700 \
      RRA:AVERAGE:0.5:24:775 \
      RRA:AVERAGE:0.5:288:797`;
  }

  rrdtool_update($peerrrd, "N:$bgpPeerOutUpdates:$bgpPeerInUpdates:$bgpPeerOutTotalMessages:$bgpPeerInTotalMesages:$bgpPeerFsmEstablishedTime");

  $update  = "UPDATE bgpPeers SET bgpPeerState = '$bgpPeerState', bgpPeerAdminStatus = '$bgpPeerAdminStatus', ";
  $update .= "bgpPeerFsmEstablishedTime = '$bgpPeerFsmEstablishedTime', bgpPeerInUpdates = '$bgpPeerInUpdates' , bgpLocalAddr = '$bgpLocalAddr' , bgpPeerOutUpdates = '$bgpPeerOutUpdates'";
  $update .= " WHERE `device_id` = '".$device['device_id']."' AND bgpPeerIdentifier = '" . $peer['bgpPeerIdentifier'] . "'";

  mysql_query($update);

} ## End While loop on peers

} ## End check for BGP support
?>
