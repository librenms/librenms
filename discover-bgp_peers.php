#!/usr/bin/php
<?
include("config.php");
include("includes/functions.php");


### Discover BGP peers on Cisco devices


$device_query = mysql_query("SELECT * FROM `devices` WHERE status = '1' AND os = 'IOS' ORDER BY device_id desc");
while ($device = mysql_fetch_array($device_query)) {
  echo("\nPolling ". $device['hostname'] . "\n");

  $as_cmd  = $config['snmpwalk'] . " -CI -Oqvn -" . $device['snmpver'] . " -c" . $device['community'] . " " . $device['hostname'] . " ";
  $as_cmd .= ".1.3.6.1.2.1.15.2";
  $bgpLocalAs = trim(shell_exec($as_cmd));

  if($bgpLocalAs) {

    echo("Host AS is $bgpLocalAs\n");

    if($bgpLocalAs != $device['bgpLocalAs']) { mysql_query("UPDATE devices SET bgpLocalAs = '$bgpLocalAs' WHERE device_id = '".$device['device_id']."'"); echo("Updated AS\n"); }

    $peers_cmd  = $config['snmpwalk'] . " -CI -Oq -" . $device['snmpver'] . " -c" . $device['community'] . " " . $device['hostname'] . " ";
    $peers_cmd .= "BGP4-MIB::bgpPeerRemoteAs"; 
    $peers = trim(str_replace("BGP4-MIB::bgpPeerRemoteAs.", "", `$peers_cmd`));  
    foreach (explode("\n", $peers) as $peer)  {

      if($peer) {
        list($peer_ip, $peer_as) = split(" ",  $peer);

	$peerlist[] = $device['device_id'] ." $peer_ip";

	$astext = trim(str_replace("\"", "", shell_exec("/usr/bin/dig +short AS$peer_as.asn.cymru.com TXT | cut -d '|' -f 5")));

        echo(str_pad($peer_ip, 32). str_pad($astext, 32) . " $peer_as ");

        if(mysql_result(mysql_query("SELECT COUNT(*) FROM `bgpPeers` WHERE `device_id` = '".$device['device_id']."' AND bgpPeerIdentifier = '$peer_ip'"),0) < '1') {
          $add = mysql_query("INSERT INTO bgpPeers (`device_id`, `bgpPeerIdentifier`, `bgpPeerRemoteAS`) VALUES ('".$device['device_id']."','$peer_ip','$peer_as')");
          if($add) { echo(" Added \n"); } else { echo(" Add failed\n"); }
        } else { echo(" Exists\n"); }

        ### Poll BGP Peer

#        $peer_cmd  = $config['snmpget'] . " -Ovq -" . $device['snmpver'] . " -c" . $device['community'] . " " . $device['hostname'] . " ";
#        $peer_cmd .= "bgpPeerState.$peer_ip bgpPeerAdminStatus.$peer_ip bgpPeerInUpdates.$peer_ip bgpPeerOutUpdates.$peer_ip bgpPeerInTotalMessages.$peer_ip ";
#        $peer_cmd .= "bgpPeerOutTotalMessages.$peer_ip bgpPeerFsmEstablishedTime.$peer_ip bgpPeerInUpdateElapsedTime.$peer_ip";
#	$peer_data = trim(shell_exec($peer_cmd));

#	$peerrrd    = $rrd_dir . "/" . $device['hostname'] . "/bgp-$peer_ip.rrd";

#        if(!is_file($peerrrd)) {
#          $woo = `rrdtool create $peerrrd \
#	    DS:bgpPeerOutUpdates:COUNTER:600:U:100000000000 \
#	    DS:bgpPeerInUpdates:COUNTER:600:U:100000000000 \
 #           DS:bgpPeerOutTotal:COUNTER:600:U:100000000000 \
  #          DS:bgpPeerInTotal:COUNTER:600:U:100000000000 \
  #          DS:bgpPeerEstablished:GAUGE:600:0:U \
  #          RRA:AVERAGE:0.5:1:600 \
  #          RRA:AVERAGE:0.5:6:700 \
  #          RRA:AVERAGE:0.5:24:775 \
  #          RRA:AVERAGE:0.5:288:797`;
   #     }

#	rrdtool_update($peerrrd, "N:$bgpPeerOutUpdates:$bgpPeerInUpdates:$bgpPeerOutTotalMessages:$bgpPeerInTotalMesages:$bgpPeerFsmEstablishedTime");

 #       list($bgpPeerState, $bgpPeerAdminStatus, $bgpPeerInUpdates, $bgpPeerOutUpdates, $bgpPeerInTotalMessages, $bgpPeerOutTotalMessages, $bgpPeerFsmEstablishedTime, $bgpPeerInUpdateElapsedTime) = explode("\n", $peer_data);

#	$update  = "UPDATE bgpPeers SET bgpPeerState = '$bgpPeerState', bgpPeerAdminStatus = '$bgpPeerAdminStatus', ";
 #       $update .= "bgpPeerFsmEstablishedTime = '$bgpPeerFsmEstablishedTime', astext = '$astext'";
  #      $update .= " WHERE `device_id` = '".$device['device_id']."' AND bgpPeerIdentifier = '$peer_ip'";

#	mysql_query($update);

      } # end if $peer 
    } # End foreach 
  } # End BGP check
} # End While

## Delete removed peers

$sql = "SELECT * FROM bgpPeers AS B, devices AS D WHERE B.device_id = D.device_id AND D.status = '1'";
$query = mysql_query($sql);

while ($entry = mysql_fetch_array($query)) {
        unset($exists);
        $i = 0;
        while ($i < count($peerlist) && !$exists) {
            $this = $entry['device_id'] . " " . $entry['bgpPeerIdentifier'];
            if ($peerlist[$i] == $this) { $exists = 1; }
            $i++;
        }
        if(!$exists) { 
          mysql_query("DELETE FROM bgpPeers WHERE bgpPeer_id = '" . $entry['bgpPeer_id'] . "'"); 
        }
}


?>

