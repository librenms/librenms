<?php

echo("Polling BGP peers\n");

if(!$config['enable_bgp']) { echo("BGP Support Disabled\n"); } else {

$query = "SELECT * FROM bgpPeers WHERE device_id = '" . $device['device_id'] . "'";
$peers = mysql_query($query);
while($peer = mysql_fetch_array($peers)) {

  ### Poll BGP Peer

  echo("Checking ".$peer['bgpPeerIdentifier']." ");

if (!strstr($peer['bgpPeerIdentifier'],':'))
{
  # v4 BGP4 MIB
  $peer_cmd  = $config['snmpget'] . " -m BGP4-MIB -Ovq -" . $device['snmpver'] . " -c" . $device['community'] . " " . $device['hostname'].":".$device['port'] . " ";
  $peer_cmd .= "bgpPeerState." . $peer['bgpPeerIdentifier'] . " bgpPeerAdminStatus." . $peer['bgpPeerIdentifier'] . " bgpPeerInUpdates." . $peer['bgpPeerIdentifier'] . " bgpPeerOutUpdates." . $peer['bgpPeerIdentifier'] . " bgpPeerInTotalMessages." . $peer['bgpPeerIdentifier'] . " ";
  $peer_cmd .= "bgpPeerOutTotalMessages." . $peer['bgpPeerIdentifier'] . " bgpPeerFsmEstablishedTime." . $peer['bgpPeerIdentifier'] . " bgpPeerInUpdateElapsedTime." . $peer['bgpPeerIdentifier'] . " ";
  $peer_cmd .= "bgpPeerLocalAddr." . $peer['bgpPeerIdentifier'] . "";
  $peer_data = trim(`$peer_cmd`);
  list($bgpPeerState, $bgpPeerAdminStatus, $bgpPeerInUpdates, $bgpPeerOutUpdates, $bgpPeerInTotalMessages, $bgpPeerOutTotalMessages, $bgpPeerFsmEstablishedTime, $bgpPeerInUpdateElapsedTime, $bgpLocalAddr) = explode("\n", $peer_data);
} 
else
if ($device['os'] == "junos")
{
  # v6 for JunOS via Juniper MIB
  $peer_ip = ipv62snmp($peer['bgpPeerIdentifier']);

  if (!isset($junos_v6))
  {
    echo "\nCaching Oids...";
    $peer_cmd  = $config['snmpwalk'] . " -m BGP4-V2-MIB-JUNIPER -Onq -" . $device['snmpver'] . " -c" . $device['community'] . " " . $device['hostname'].":".$device['port'];
    $peer_cmd .= " jnxBgpM2PeerStatus.0.ipv6";
    foreach (explode("\n",trim(`$peer_cmd`)) as $oid)
    {
      list($peer_oid) = split(' ',$oid);
      $peer_id = explode('.',$peer_oid);
      $junos_v6[implode('.',array_slice($peer_id,35))] = implode('.',array_slice($peer_id,18));
    }
  }
  
  $peer_cmd  = $config['snmpget'] . " -m BGP4-V2-MIB-JUNIPER -Ovq -" . $device['snmpver'] . " -c" . $device['community'] . " " . $device['hostname'].":".$device['port'];
  $peer_cmd .= " jnxBgpM2PeerState.0.ipv6." . $junos_v6[$peer_ip];
  $peer_cmd .= " jnxBgpM2PeerStatus.0.ipv6." . $junos_v6[$peer_ip]; # Should be jnxBgpM2CfgPeerAdminStatus but doesn't seem to be implemented?
  $peer_cmd .= " jnxBgpM2PeerInUpdates.0.ipv6." . $junos_v6[$peer_ip];
  $peer_cmd .= " jnxBgpM2PeerOutUpdates.0.ipv6." . $junos_v6[$peer_ip];
  $peer_cmd .= " jnxBgpM2PeerInTotalMessages.0.ipv6." . $junos_v6[$peer_ip];
  $peer_cmd .= " jnxBgpM2PeerOutTotalMessages.0.ipv6." . $junos_v6[$peer_ip];
  $peer_cmd .= " jnxBgpM2PeerFsmEstablishedTime.0.ipv6." . $junos_v6[$peer_ip];
  $peer_cmd .= " jnxBgpM2PeerInUpdatesElapsedTime.0.ipv6." . $junos_v6[$peer_ip];
  $peer_cmd .= " jnxBgpM2PeerLocalAddr.0.ipv6." . $junos_v6[$peer_ip];
  $peer_cmd .= ' -M"+' . $config['install_dir'] . '/mibs/junos"|grep -v "No Such Instance"';
  if ($debug) echo "\n$peer_cmd\n";
  $peer_data = trim(`$peer_cmd`);
  list($bgpPeerState, $bgpPeerAdminStatus, $bgpPeerInUpdates, $bgpPeerOutUpdates, $bgpPeerInTotalMessages, $bgpPeerOutTotalMessages, $bgpPeerFsmEstablishedTime, $bgpPeerInUpdateElapsedTime, $bgpLocalAddr) = explode("\n", $peer_data);
  
  if ($debug) { echo "State = $bgpPeerState - AdminStatus: $bgpPeerAdminStatus\n"; }
  
  $bgpLocalAddr = str_replace('"','',str_replace(' ','',$bgpLocalAddr));
  if ($bgpLocalAddr == "00000000000000000000000000000000") 
  {
    $bgpLocalAddr = ''; # Unknown?
  }
  else
  {
    $bgpLocalAddr = strtolower($bgpLocalAddr);
    for ($i = 0;$i < 32;$i+=4)
      $bgpLocalAddr6[] = substr($bgpLocalAddr,$i,4);
    $bgpLocalAddr = Net_IPv6::compress(implode(':',$bgpLocalAddr6)); unset($bgpLocalAddr6);
  }
}

  if ($bgpPeerFsmEstablishedTime)
  {
    if ($bgpPeerFsmEstablishedTime < $peer['bgpPeerFsmEstablishedTime'] || $bgpPeerState != $peer['bgpPeerState'])
    {
      if ($device['sysContact']) { $email = $device['sysContact']; } else { $email = $config['email_default']; }
      if ($peer['bgpPeerState'] == $bgpPeerState)
      {
        mail($email, "BGP Session flapped: " . $peer['bgpPeerIdentifier'] . ' (AS' . $peer['bgpPeerRemoteAs'] . ' - ' . $peer['astext'] . ')', "BGP Session flapped " . formatUptime($bgpPeerFsmEstablishedTime) . " ago.\n\nHostname : " . $device['hostname'] . "\nPeer IP  : " . $peer['bgpPeerIdentifier'] . "\nRemote AS: " . $peer['bgpPeerRemoteAs'] . ' ('.$peer['astext'].')', $config['email_headers']);
        log_event('BGP Session Flap: ' . $peer['bgpPeerIdentifier'] . ' (AS' . $peer['bgpPeerRemoteAs'] . ')', $device['device_id'], 'bgpPeer', $bgpPeer_id);
      }
      else if ($bgpPeerState == "established")
      {
        mail($email, "BGP Session up: " . $peer['bgpPeerIdentifier'] . ' (AS' . $peer['bgpPeerRemoteAs'] . ' - ' . $peer['astext'] . ')', "BGP Session up since " . formatUptime($bgpPeerFsmEstablishedTime) . ".\n\nHostname : " . $device['hostname'] . "\nPeer IP  : " . $peer['bgpPeerIdentifier'] . "\nRemote AS: " . $peer['bgpPeerRemoteAs'] . ' ('.$peer['astext'].')', $config['email_headers']);
        log_event('BGP Session Up: ' . $peer['bgpPeerIdentifier'] . ' (AS' . $peer['bgpPeerRemoteAs'] . ')', $device['device_id'], 'bgpPeer', $bgpPeer_id);
      }
      else if ($peer['bgpPeerState'] == "established")
      {
        mail($email, "BGP Session down: " . $peer['bgpPeerIdentifier'] . ' (AS' . $peer['bgpPeerRemoteAs'] . ' - ' . $peer['astext'] . ')', "BGP Session down since " . formatUptime($bgpPeerFsmEstablishedTime) . ".\n\nHostname : " . $device['hostname'] . "\nPeer IP  : " . $peer['bgpPeerIdentifier'] . "\nRemote AS: " . $peer['bgpPeerRemoteAs'] . ' ('.$peer['astext'].')', $config['email_headers']);
        log_event('BGP Session Down: ' . $peer['bgpPeerIdentifier'] . ' (AS' . $peer['bgpPeerRemoteAs'] . ')', $device['device_id'], 'bgpPeer', $bgpPeer_id);
      }
    }
  }

  $peerrrd    = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("bgp-" . $peer['bgpPeerIdentifier'] . ".rrd");
  if(!is_file($peerrrd)) {
      $create_rrd = "DS:bgpPeerOutUpdates:COUNTER:600:U:100000000000 \
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
      RRA:MAX:0.5:288:797";

      rrdtool_create($peerrrd, $create_rrd);

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
     if($debug) { echo("$afi $safi". $config['afi'][$afi][$safi]. "\n"); }

     $cbgp_cmd  = $config['snmpget'] . " -m CISCO-BGP4-MIB -Ovq -" . $device['snmpver'] . " -c" . $device['community'] . " " . $device['hostname'].":".$device['port'];
     $cbgp_cmd .= " cbgpPeerAcceptedPrefixes." . $peer['bgpPeerIdentifier'] . ".$afi.$safi";
     $cbgp_cmd .= " cbgpPeerDeniedPrefixes." . $peer['bgpPeerIdentifier'] . ".$afi.$safi";
     $cbgp_cmd .= " cbgpPeerPrefixAdminLimit." . $peer['bgpPeerIdentifier'] . ".$afi.$safi";
     $cbgp_cmd .= " cbgpPeerPrefixThreshold." . $peer['bgpPeerIdentifier'] . ".$afi.$safi";
     $cbgp_cmd .= " cbgpPeerPrefixClearThreshold." . $peer['bgpPeerIdentifier'] . ".$afi.$safi";
     $cbgp_cmd .= " cbgpPeerAdvertisedPrefixes." . $peer['bgpPeerIdentifier'] . ".$afi.$safi";
     $cbgp_cmd .= " cbgpPeerSuppressedPrefixes." . $peer['bgpPeerIdentifier'] . ".$afi.$safi";
     $cbgp_cmd .= " cbgpPeerWithdrawnPrefixes." . $peer['bgpPeerIdentifier'] . ".$afi.$safi";
    
     if($debug) { echo("$cbgp_cmd\n"); }
     $cbgp_data = preg_replace("/^OID.*$/", "", trim(`$cbgp_cmd`));
     if($debug) { echo("$cbgp_data\n"); }
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
     if($debug) { echo("MYSQL: $update\n"); }
     mysql_query($update);

     $cbgp_rrd    = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("cbgp-" . $peer['bgpPeerIdentifier'] . ".$afi.$safi.rrd");
     if(!is_file($cbgp_rrd)) {
       $rrd_create = "DS:AcceptedPrefixes:GAUGE:600:U:100000000000 \
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
         RRA:MAX:0.5:288:797";
         rrdtool_create($cbgp_rrd, $rrd_create);
     }
     rrdtool_update("$cbgp_rrd", "N:$cbgpPeerAcceptedPrefixes:$cbgpPeerDeniedPrefixes:$cbgpPeerAdvertisedPrefixes:$cbgpPeerSuppressedPrefixes:$cbgpPeerWithdrawnPrefixes");
   }
  }
  echo("\n");

} ## End While loop on peers

} ## End check for BGP support
?>
