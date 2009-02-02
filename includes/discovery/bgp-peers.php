<?

### Discover BGP peers on Cisco devices

  echo("BGP Sessions : ");

  $as_cmd  = $config['snmpwalk'] . " -CI -Oqvn -" . $device['snmpver'] . " -c" . $device['community'] . " " . $device['hostname'].":".$device['port'] . " ";
  $as_cmd .= ".1.3.6.1.2.1.15.2";
  $bgpLocalAs = trim(shell_exec($as_cmd));

  if($bgpLocalAs) {

    echo("AS$bgpLocalAs ");

    if($bgpLocalAs != $device['bgpLocalAs']) { mysql_query("UPDATE devices SET bgpLocalAs = '$bgpLocalAs' WHERE device_id = '".$device['device_id']."'"); echo("Updated AS\n"); }

    $peers_cmd  = $config['snmpwalk'] . " -CI -Oq -" . $device['snmpver'] . " -c" . $device['community'] . " " . $device['hostname'].":".$device['port'] . " ";
    $peers_cmd .= "BGP4-MIB::bgpPeerRemoteAs"; 
    $peers = trim(str_replace("BGP4-MIB::bgpPeerRemoteAs.", "", `$peers_cmd`));  
    foreach (explode("\n", $peers) as $peer)  {

      if($peer) {
        list($peer_ip, $peer_as) = split(" ",  $peer);

	$peerlist[] = $device['device_id'] ." $peer_ip";

	$astext = trim(str_replace("\"", "", shell_exec("/usr/bin/dig +short AS$peer_as.asn.cymru.com TXT | cut -d '|' -f 5 | sed s/\\\"//g")));

        #echo(str_pad($peer_ip, 32). str_pad($astext, 32) . " $peer_as ");

        if(mysql_result(mysql_query("SELECT COUNT(*) FROM `bgpPeers` WHERE `device_id` = '".$device['device_id']."' AND bgpPeerIdentifier = '$peer_ip'"),0) < '1') {
          $add = mysql_query("INSERT INTO bgpPeers (`device_id`, `bgpPeerIdentifier`, `bgpPeerRemoteAS`) VALUES ('".$device['device_id']."','$peer_ip','$peer_as')");
          echo("+"); 
        } else { 
          echo("."); 
          $update = mysql_query("UPDATE `bgpPeers` SET astext = '$astext' WHERE `device_id` = '".$device['device_id']."' AND bgpPeerIdentifier = '$peer_ip'");
        }
      } # If Peer
    } # Foreach  
  } else { echo("No BGP on host"); } # End if

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
          echo("-");
        }
}


?>

