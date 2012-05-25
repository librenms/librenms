<?php

global $debug;

if ($config['enable_bgp'])
{
  // Discover BGP peers

  echo("BGP Sessions : ");

  $bgpLocalAs = trim(snmp_walk($device, ".1.3.6.1.2.1.15.2", "-Oqvn", "BGP4-MIB", $config['mibdir']));

  if (is_numeric($bgpLocalAs))
  {
    echo("AS$bgpLocalAs ");

    if ($bgpLocalAs != $device['bgpLocalAs'])
    {
      mysql_query("UPDATE devices SET bgpLocalAs = '$bgpLocalAs' WHERE device_id = '".$device['device_id']."'"); echo("Updated AS ");
    }

    $peers_data = snmp_walk($device, "BGP4-MIB::bgpPeerRemoteAs", "-Oq", "BGP4-MIB", $config['mibdir']);
    if ($debug) { echo("Peers : $peers_data \n"); }
    $peers = trim(str_replace("BGP4-MIB::bgpPeerRemoteAs.", "", $peers_data));

    foreach (explode("\n", $peers) as $peer)
    {
      list($peer_ip, $peer_as) = explode(" ",  $peer);

      if ($peer && $peer_ip != "0.0.0.0")
      {
        if ($debug) { echo("Found peer $peer_ip (AS$peer_as)\n"); }
        $peerlist[] = array('ip' => $peer_ip, 'as' => $peer_as);
      }
    } # Foreach

    if ($device['os'] == "junos")
    {
      // Juniper BGP4-V2 MIB

      // FIXME: needs a big cleanup! also see below.

      // FIXME: is .0.ipv6 the only possible value here?
      $result = snmp_walk($device, "jnxBgpM2PeerRemoteAs.0.ipv6", "-Onq", "BGP4-V2-MIB-JUNIPER", $config['install_dir']."/mibs/junos");
      $peers = trim(str_replace(".1.3.6.1.4.1.2636.5.1.1.2.1.1.1.13.0.","", $result));
      foreach (explode("\n", $peers) as $peer)
      {
        list($peer_ip_snmp, $peer_as) = explode(" ",  $peer);

        # Magic! Basically, takes SNMP form and finds peer IPs from the walk OIDs.
        $peer_ip = Net_IPv6::compress(snmp2ipv6(implode('.',array_slice(explode('.',$peer_ip_snmp),count(explode('.',$peer_ip_snmp))-16))));

        if ($peer)
        {
          if ($debug) echo("Found peer $peer_ip (AS$peer_as)\n");
          $peerlist[] = array('ip' => $peer_ip, 'as' => $peer_as);
        }
      } # Foreach
    } # OS junos
  } else {
    echo("No BGP on host");
    if ($device['bgpLocalAs'])
    {
     mysql_query("UPDATE devices SET bgpLocalAs = NULL WHERE device_id = '".$device['device_id']."'"); echo(" (Removed ASN) ");
    } # End if
  } # End if

  // Process disovered peers

  if (isset($peerlist))
  {
    foreach ($peerlist as $peer)
    {
      $astext = get_astext($peer['as']);

      if (mysql_result(mysql_query("SELECT COUNT(*) FROM `bgpPeers` WHERE `device_id` = '".$device['device_id']."' AND bgpPeerIdentifier = '".$peer['ip']."'"),0) < '1')
      {
        $add = mysql_query("INSERT INTO bgpPeers (`device_id`, `bgpPeerIdentifier`, `bgpPeerRemoteAS`) VALUES ('".$device['device_id']."','".$peer['ip']."','".$peer['as']."')");
        echo("+");
      } else {
        $update = mysql_query("UPDATE `bgpPeers` SET bgpPeerRemoteAs = " . $peer['as'] . ", astext = '" . mres($astext) . "' WHERE `device_id` = '".$device['device_id']."' AND bgpPeerIdentifier = '".$peer['ip']."'");
        echo(".");
      }

      if ($device['os_group'] == "cisco" || $device['os'] == "junos")
      {

        if ($device['os_group'] == "cisco")
        {
          // Get afi/safi and populate cbgp on cisco ios (xe/xr)
          unset($af_list);

          $af_data = snmp_walk($device, "cbgpPeerAddrFamilyName." . $peer['ip'], "-OsQ", "CISCO-BGP4-MIB", $config['mibdir']);
          if ($debug) { echo("afi data :: $af_data \n"); }

          $afs = trim(str_replace("cbgpPeerAddrFamilyName.".$peer['ip'].".", "", $af_data));
          foreach (explode("\n", $afs) as $af)
          {
            if ($debug) { echo("AFISAFI = $af\n"); }
            list($afisafi, $text) = explode(" = ", $af);
            list($afi, $safi) = explode(".", $afisafi);
            if ($afi && $safi)
            {
              $af_list[$afi][$safi] = 1;
              if (mysql_result(mysql_query("SELECT COUNT(*) FROM `bgpPeers_cbgp` WHERE `device_id` = '".$device['device_id']."' AND bgpPeerIdentifier = '".$peer['ip']."' AND afi = '$afi' AND safi = '$safi'"),0) == 0)
              {
                mysql_query("INSERT INTO `bgpPeers_cbgp` (device_id,bgpPeerIdentifier, afi, safi) VALUES ('".$device['device_id']."','".$peer['ip']."','$afi','$safi')");
              }
            }
          }
        } # os_group=cisco

        if ($device['os'] == "junos")
        {
          $safis[1] = "unicast";
          $safis[2] = "multicast";

          if (!isset($j_peerIndexes))
          {
            $j_bgp = snmpwalk_cache_multi_oid($device, "jnxBgpM2PeerTable", $jbgp, "BGP4-V2-MIB-JUNIPER", $config['install_dir']."/mibs/junos");

            foreach ($j_bgp as $index => $entry)
            {
              switch ($entry['jnxBgpM2PeerRemoteAddrType'])
              {
                case 'ipv4':
                  $ip = long2ip(hexdec($entry['jnxBgpM2PeerRemoteAddr']));
                  if ($debug) { echo("peerindex for ipv4 $ip is " . $entry['jnxBgpM2PeerIndex'] . "\n"); }
                  $j_peerIndexes[$ip] = $entry['jnxBgpM2PeerIndex'];
                  break;
                case 'ipv6':
                  $ip6 = trim(str_replace(' ','',$entry['jnxBgpM2PeerRemoteAddr']),'"');
                  $ip6 = substr($ip6,0,4) . ':' . substr($ip6,4,4) . ':' . substr($ip6,8,4) . ':' . substr($ip6,12,4) . ':' . substr($ip6,16,4) . ':' . substr($ip6,20,4) . ':' . substr($ip6,24,4) . ':' . substr($ip6,28,4);
                  $ip6 = Net_IPv6::compress($ip6);
                  if ($debug) { echo("peerindex for ipv6 $ip6 is " . $entry['jnxBgpM2PeerIndex'] . "\n"); }
                  $j_peerIndexes[$ip6] = $entry['jnxBgpM2PeerIndex'];
                  break;
                default:
                  echo("HALP? Don't know RemoteAddrType " . $entry['jnxBgpM2PeerRemoteAddrType'] . "!\n");
                  break;
              }
            }
          }

          if (!isset($j_afisafi))
          {
            $j_prefixes = snmpwalk_cache_multi_oid($device, "jnxBgpM2PrefixCountersTable", $jbgp, "BGP4-V2-MIB-JUNIPER", $config['install_dir']."/mibs/junos");
            foreach (array_keys($j_prefixes) as $key)
            {
              list($index,$afisafi) = explode('.',$key,2);
              $j_afisafi[$index][] = $afisafi;
            }
          }

          foreach ($j_afisafi[$j_peerIndexes[$peer['ip']]] as $afisafi)
          {
            list ($afi,$safi) = explode('.',$afisafi); $safi = $safis[$safi];
            $af_list[$afi][$safi] = 1;
            if (mysql_result(mysql_query("SELECT COUNT(*) FROM `bgpPeers_cbgp` WHERE `device_id` = '".$device['device_id']."' AND bgpPeerIdentifier = '".$peer['ip']."' AND afi = '$afi' AND safi = '$safi'"),0) == 0)
            {
              mysql_query("INSERT INTO `bgpPeers_cbgp` (device_id,bgpPeerIdentifier, afi, safi) VALUES ('".$device['device_id']."','".$peer['ip']."','$afi','$safi')");
            }
          }
        } # os=junos

        $af_query = mysql_query("SELECT * FROM bgpPeers_cbgp WHERE `device_id` = '".$device['device_id']."' AND bgpPeerIdentifier = '".$peer['ip']."'");
        while ($entry = mysql_fetch_assoc($af_query))
        {
          $afi = $entry['afi'];
          $safi = $entry['safi'];
          if (!$af_list[$afi][$safi])
          {
            mysql_query("DELETE FROM `bgpPeers_cbgp` WHERE `device_id` = '".$device['device_id']."' AND bgpPeerIdentifier = '".$peer['ip']."' AND afi = '$afi' AND safi = '$safi'");
          }
        } # AF list
      } # os=cisco|junos
    } # Foreach

    unset($j_afisafi);
    unset($j_prefixes);
    unset($j_bgp);
    unset($j_peerIndexes);
  } # isset

  // Delete removed peers

  $sql = "SELECT * FROM bgpPeers AS B, devices AS D WHERE B.device_id = D.device_id AND D.device_id = '".$device['device_id']."'";
  $query = mysql_query($sql);

  while ($entry = mysql_fetch_assoc($query))
  {
    unset($exists);
    $i = 0;

    while ($i < count($peerlist) && !isset($exists))
    {
      if ($peerlist[$i]['ip'] == $entry['bgpPeerIdentifier']) { $exists = 1; }
      $i++;
    }

    if (!isset($exists))
    {
      mysql_query("DELETE FROM bgpPeers WHERE bgpPeer_id = '" . $entry['bgpPeer_id'] . "'");
      mysql_query("DELETE FROM bgpPeers_cbgp WHERE bgpPeer_id = '" . $entry['bgpPeer_id'] . "'");
      echo("-");
    }
  }

  unset($peerlist);

  echo("\n");
}

?>
