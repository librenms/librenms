<?php

### Discover BGP peers on Cisco and Juniper devices

  echo("BGP Sessions : ");

  $as_cmd  = $config['snmpwalk'] . " -m BGP4-MIB -CI -Oqvn -" . $device['snmpver'] . " -c" . $device['community'] . " " . $device['hostname'].":".$device['port'] . " ";
  $as_cmd .= ".1.3.6.1.2.1.15.2";
  $bgpLocalAs = trim(shell_exec($as_cmd));

  if($bgpLocalAs && !strstr($bgpLocalAs, " ")) {

    echo("AS$bgpLocalAs ");

    if($bgpLocalAs != $device['bgpLocalAs']) { mysql_query("UPDATE devices SET bgpLocalAs = '$bgpLocalAs' WHERE device_id = '".$device['device_id']."'"); echo("Updated AS "); }

    $peers_cmd  = $config['snmpwalk'] . " -m BGP4-MIB -CI -Oq -" . $device['snmpver'] . " -c" . $device['community'] . " " . $device['hostname'].":".$device['port'] . " ";
    $peers_cmd .= "BGP4-MIB::bgpPeerRemoteAs"; 
    $peers = trim(str_replace("BGP4-MIB::bgpPeerRemoteAs.", "", `$peers_cmd`));  
    foreach (explode("\n", $peers) as $peer)  {

      list($peer_ip, $peer_as) = split(" ",  $peer);
      if($peer && $peer_ip != "0.0.0.0") {

	$peerlist[] = $device['device_id'] ." $peer_ip";

	$astext = get_astext($peer_as);

#        echo(str_pad($peer_ip, 40) . " AS$peer_as  ");
        
        #echo("$peer_ip AS$peer_as ");
        if(mysql_result(mysql_query("SELECT COUNT(*) FROM `bgpPeers` WHERE `device_id` = '".$device['device_id']."' AND bgpPeerIdentifier = '$peer_ip'"),0) < '1') {
          $add = mysql_query("INSERT INTO bgpPeers (`device_id`, `bgpPeerIdentifier`, `bgpPeerRemoteAS`) VALUES ('".$device['device_id']."','$peer_ip','$peer_as')");
          echo("+"); 
        } else { 
          $update = mysql_query("UPDATE `bgpPeers` SET bgpPeerRemoteAs = " . $peer_as . ", astext = '" . mysql_escape_string($astext) . "' WHERE `device_id` = '".$device['device_id']."' AND bgpPeerIdentifier = '$peer_ip'");
          echo(".");
        }

        ## Get afi/safi and populate cbgp on cisco ios (xe/xr)
	if($device['os'] == "ios") {
          unset($af_list);
          $af_cmd  = $config['snmpwalk'] . " -CI -m CISCO-BGP4-MIB -OsQ -" . $device['snmpver'] . " -c" . $device['community'] . " " . $device['hostname'].":".$device['port'] . " ";
          $af_cmd .= "cbgpPeerAddrFamilyName." . $peer_ip;
          $afs = trim(str_replace("cbgpPeerAddrFamilyName.".$peer_ip.".", "", `$af_cmd`));
          foreach (explode("\n", $afs) as $af)  {
	    list($afisafi, $text) = explode(" = ", $af);
            list($afi, $safi) = explode(".", $afisafi);
            if($afi && $safi) {
#	     echo("($afi:$safi)");
             $af_list['$afi']['$safi'] = 1;
	     if(mysql_result(mysql_query("SELECT COUNT(*) FROM `bgpPeers_cbgp` WHERE `device_id` = '".$device['device_id']."' AND bgpPeerIdentifier = '$peer_ip' AND afi = '$afi' AND safi = '$safi'"),0) == 0) {
               mysql_query("INSERT INTO `bgpPeers_cbgp` (device_id,bgpPeerIdentifier, afi, safi) VALUES ('".$device['device_id']."','$peer_ip','$afi','$safi')");
             }
            }
          }          
          $af_query = mysql_query("SELECT * FROM bgpPeers_cbgp WHERE `device_id` = '".$device['device_id']."' AND bgpPeerIdentifier = '$peer_ip'");
          while ($entry = mysql_fetch_array($af_query)) {
            $afi = $entry['afi'];
	    $afi = $entry['safi'];
            if (!$af_list['$afi']['$safi']) {
              mysql_query("DELETE FROM `bgpPeers_cbgp` WHERE `device_id` = '".$device['device_id']."' AND bgpPeerIdentifier = '$peer_ip' AND afi = '$afi' AND safi = '$safi'");
            }
          } # AF list
        } # if os = ios 
      } # If Peer
    } # Foreach  

    if ($device['os'] == "junos")
    ## Juniper BGP4-V2 MIB, ipv6 only for now, because v4 should be covered in BGP4-MIB above
    $peers_cmd  = $config['snmpwalk'] . " -m BGP4-V2-MIB-JUNIPER -CI -Onq -" . $device['snmpver'] . " -c" . $device['community'] . " " . $device['hostname'].":".$device['port'] . " ";
    $peers_cmd .= "jnxBgpM2PeerRemoteAs.0.ipv6";  # FIXME: is .0 the only possible value here?
    $peers = trim(str_replace(".1.3.6.1.4.1.2636.5.1.1.2.1.1.1.13.0.","", `$peers_cmd`));  
    foreach (explode("\n", $peers) as $peer)  {
      list($peer_ip_snmp, $peer_as) = split(" ",  $peer);

      # Magic! Basically, takes SNMP form and finds peer IPs from the walk OIDs.
      $peer_ip = Net_IPv6::compress(snmp2ipv6(implode('.',array_slice(explode('.',$peer_ip_snmp),count($ipv6)-16))));
      
      if($peer) {

	$peerlist[] = $device['device_id'] ." $peer_ip";

	$astext = get_astext($peer_as);
        
        #echo("$peer_ip AS$peer_as ");
        if(mysql_result(mysql_query("SELECT COUNT(*) FROM `bgpPeers` WHERE `device_id` = '".$device['device_id']."' AND bgpPeerIdentifier = '$peer_ip'"),0) < '1') {
          $add = mysql_query("INSERT INTO bgpPeers (`device_id`, `bgpPeerIdentifier`, `bgpPeerRemoteAS`) VALUES ('".$device['device_id']."','$peer_ip','$peer_as')");
          echo("+"); 
        } else { 
          $update = mysql_query("UPDATE `bgpPeers` SET bgpPeerRemoteAs = " . $peer_as . ", astext = '" . mysql_escape_string($astext) . "' WHERE `device_id` = '".$device['device_id']."' AND bgpPeerIdentifier = '$peer_ip'");
          echo(".");
        }

      } # If Peer
    } # Foreach  
  } else { 
    echo("No BGP on host");
    if($device['bgpLocalAs']) {
     mysql_query("UPDATE devices SET bgpLocalAs = NULL WHERE device_id = '".$device['device_id']."'"); echo(" (Removed ASN) "); 
    } # End if
  } # End if

## Delete removed peers

$sql = "SELECT * FROM bgpPeers AS B, devices AS D WHERE B.device_id = D.device_id AND D.device_id = '".$device['device_id']."'";
$query = mysql_query($sql);

while ($entry = mysql_fetch_array($query)) {
        unset($exists);
        $i = 0;
        while ($i < count($peerlist) && !$exists) {
            $checkme = $entry['device_id'] . " " . $entry['bgpPeerIdentifier'];
            if ($peerlist[$i] == $checkme) { $exists = 1; }
            $i++;
        }
        if(!$exists) { 
          mysql_query("DELETE FROM bgpPeers WHERE bgpPeer_id = '" . $entry['bgpPeer_id'] . "'"); 
	  mysql_query("DELETE FROM bgpPeers_cbgp WHERE bgpPeer_id = '" . $entry['bgpPeer_id'] . "'");
          echo("-");
        }
}

echo("\n");

?>

