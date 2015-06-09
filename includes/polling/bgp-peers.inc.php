<?php

// We should walk, so we can discover here too.

global $debug;

if ($config['enable_bgp'])
{
  foreach (dbFetchRows("SELECT * FROM bgpPeers WHERE device_id = ?", array($device['device_id'])) as $peer)
  {
    // Poll BGP Peer

      $peer2 = FALSE;
    echo("Checking BGP peer ".$peer['bgpPeerIdentifier']." ");

    if (!empty($peer['bgpPeerIdentifier']) && $device['os'] != "junos")
    {
      # v4 BGP4 MIB
      // FIXME - needs moved to function
        $peer_data_check = snmpwalk_cache_oid($device, "cbgpPeer2RemoteAs", array(), "CISCO-BGP4-MIB", $config['mibdir']);
        if (count($peer_data_check) > 0) {
            $peer2 = TRUE;
        }

        if ($peer2 === TRUE) {
            if (strstr($peer['bgpPeerIdentifier'], ":")) {
                $bgp_peer_ident = ipv62snmp($peer['bgpPeerIdentifier']);
            } else {
                $bgp_peer_ident = $peer['bgpPeerIdentifier'];
            }
            if (strstr($peer['bgpPeerIdentifier'],":")) {
                $ip_type = 2;
                $ip_len = 16;
                $ip_ver = 'ipv6';
            } else {
                $ip_type = 1;
                $ip_len = 4;
                $ip_ver = 'ipv4';
            }
            $peer_identifier = $ip_type . '.' . $ip_len . '.' . $bgp_peer_ident;
            $peer_data_tmp = snmp_get_multi($device,
                " cbgpPeer2State.". $peer_identifier .
                " cbgpPeer2AdminStatus.". $peer_identifier .
                " cbgpPeer2InUpdates." . $peer_identifier .
                " cbgpPeer2OutUpdates." . $peer_identifier .
                " cbgpPeer2InTotalMessages." . $peer_identifier .
                " cbgpPeer2OutTotalMessages." . $peer_identifier .
                " cbgpPeer2FsmEstablishedTime." . $peer_identifier .
                " cbgpPeer2InUpdateElapsedTime." . $peer_identifier .
                " cbgpPeer2LocalAddr." . $peer_identifier, "-OQUs", "CISCO-BGP4-MIB",$config['mibdir']);
            $ident = "$ip_ver.\"".$bgp_peer_ident.'"';
            unset($peer_data);
            $ident_key = array_keys($peer_data_tmp);
            foreach ($peer_data_tmp[$ident_key[0]] as $k => $v) {
                if (strstr($k, "cbgpPeer2LocalAddr")) {
                    if ($ip_ver == 'ipv6') {
                        $v = str_replace('"','',$v);
                        $v = rtrim($v);
                        $v = preg_replace("/(\S+\s+\S+)\s/", '$1:', $v);
                        $v = strtolower($v);
                    } else {
                        $v = hex_to_ip($v);
                    }
                }
                $peer_data .= "$v\n";
            }
        } else {
            $peer_cmd = $config['snmpget'] . " -M " . $config['mibdir'] . " -m BGP4-MIB -OUvq " . snmp_gen_auth($device) . " " . $device['hostname'] . ":" . $device['port'] . " ";
            $peer_cmd .= "bgpPeerState." . $peer['bgpPeerIdentifier'] . " bgpPeerAdminStatus." . $peer['bgpPeerIdentifier'] . " bgpPeerInUpdates." . $peer['bgpPeerIdentifier'] . " bgpPeerOutUpdates." . $peer['bgpPeerIdentifier'] . " bgpPeerInTotalMessages." . $peer['bgpPeerIdentifier'] . " ";
            $peer_cmd .= "bgpPeerOutTotalMessages." . $peer['bgpPeerIdentifier'] . " bgpPeerFsmEstablishedTime." . $peer['bgpPeerIdentifier'] . " bgpPeerInUpdateElapsedTime." . $peer['bgpPeerIdentifier'] . " ";
            $peer_cmd .= "bgpPeerLocalAddr." . $peer['bgpPeerIdentifier'] . "";
            $peer_data = trim(`$peer_cmd`);
        }
      list($bgpPeerState, $bgpPeerAdminStatus, $bgpPeerInUpdates, $bgpPeerOutUpdates, $bgpPeerInTotalMessages, $bgpPeerOutTotalMessages, $bgpPeerFsmEstablishedTime, $bgpPeerInUpdateElapsedTime, $bgpLocalAddr) = explode("\n", $peer_data);
      $bgpLocalAddr = str_replace('"','',str_replace(' ','',$bgpLocalAddr));
    }
    else
    if ($device['os'] == "junos")
    {
      # v6 for JunOS via Juniper MIB
      $peer_ip = ipv62snmp($peer['bgpPeerIdentifier']);

      if (!isset($junos_v6))
      {
        echo("\nCaching Oids...");
        // FIXME - needs moved to function
        $peer_cmd  = $config['snmpwalk'] . " -M ".$config['mibdir'] . "/junos -m BGP4-V2-MIB-JUNIPER -OUnq -" . $device['snmpver'] . " " . snmp_gen_auth($device) . " " . $device['hostname'].":".$device['port'];
        $peer_cmd .= " jnxBgpM2PeerStatus.0.ipv6";
        foreach (explode("\n",trim(`$peer_cmd`)) as $oid)
        {
          list($peer_oid) = explode(' ',$oid);
          $peer_id = explode('.',$peer_oid);
          $junos_v6[implode('.',array_slice($peer_id,35))] = implode('.',array_slice($peer_id,18));
        }
      }

      // FIXME - move to function (and clean up, wtf?)
      $peer_cmd  = $config['snmpget'] . " -M ".$config['mibdir'] . "/junos -m BGP4-V2-MIB-JUNIPER -OUvq " . snmp_gen_auth($device);
      $peer_cmd .= ' -M"' . $config['install_dir'] . '/mibs/junos"';
      $peer_cmd .= " " . $device['hostname'].":".$device['port'];
      $peer_cmd .= " jnxBgpM2PeerState.0.ipv6." . $junos_v6[$peer_ip];
      $peer_cmd .= " jnxBgpM2PeerStatus.0.ipv6." . $junos_v6[$peer_ip]; # Should be jnxBgpM2CfgPeerAdminStatus but doesn't seem to be implemented?
      $peer_cmd .= " jnxBgpM2PeerInUpdates.0.ipv6." . $junos_v6[$peer_ip];
      $peer_cmd .= " jnxBgpM2PeerOutUpdates.0.ipv6." . $junos_v6[$peer_ip];
      $peer_cmd .= " jnxBgpM2PeerInTotalMessages.0.ipv6." . $junos_v6[$peer_ip];
      $peer_cmd .= " jnxBgpM2PeerOutTotalMessages.0.ipv6." . $junos_v6[$peer_ip];
      $peer_cmd .= " jnxBgpM2PeerFsmEstablishedTime.0.ipv6." . $junos_v6[$peer_ip];
      $peer_cmd .= " jnxBgpM2PeerInUpdatesElapsedTime.0.ipv6." . $junos_v6[$peer_ip];
      $peer_cmd .= " jnxBgpM2PeerLocalAddr.0.ipv6." . $junos_v6[$peer_ip];
      $peer_cmd .= '|grep -v "No Such Instance"';
      if ($debug) echo("\n$peer_cmd\n");
      $peer_data = trim(`$peer_cmd`);
      list($bgpPeerState, $bgpPeerAdminStatus, $bgpPeerInUpdates, $bgpPeerOutUpdates, $bgpPeerInTotalMessages, $bgpPeerOutTotalMessages, $bgpPeerFsmEstablishedTime, $bgpPeerInUpdateElapsedTime, $bgpLocalAddr) = explode("\n", $peer_data);

      if ($debug) { echo("State = $bgpPeerState - AdminStatus: $bgpPeerAdminStatus\n"); }

      $bgpLocalAddr = str_replace('"','',str_replace(' ','',$bgpLocalAddr));
      if ($bgpLocalAddr == "00000000000000000000000000000000")
      {
        $bgpLocalAddr = ''; # Unknown?
      }
      else
      {
        $bgpLocalAddr = strtolower($bgpLocalAddr);
        for ($i = 0;$i < 32;$i+=4)
        {
          $bgpLocalAddr6[] = substr($bgpLocalAddr,$i,4);
        }
        $bgpLocalAddr = Net_IPv6::compress(implode(':',$bgpLocalAddr6)); unset($bgpLocalAddr6);
      }
    }

    if ($bgpPeerFsmEstablishedTime)
    {
      if (!(is_array($config['alerts']['bgp']['whitelist']) && !in_array($peer['bgpPeerRemoteAs'], $config['alerts']['bgp']['whitelist'])) && ($bgpPeerFsmEstablishedTime < $peer['bgpPeerFsmEstablishedTime'] || $bgpPeerState != $peer['bgpPeerState']))
      {
        if ($peer['bgpPeerState'] == $bgpPeerState)
        {
          notify($device, "BGP Session flapped: " . $peer['bgpPeerIdentifier'] . ' (AS' . $peer['bgpPeerRemoteAs'] . ' - ' . $peer['astext'] . ')', "BGP Session flapped " . formatUptime($bgpPeerFsmEstablishedTime) . " ago.\n\nHostname : " . $device['hostname'] . "\nPeer IP  : " . $peer['bgpPeerIdentifier'] . "\nRemote AS: " . $peer['bgpPeerRemoteAs'] . ' ('.$peer['astext'].')');
          log_event('BGP Session Flap: ' . $peer['bgpPeerIdentifier'] . ' (AS' . $peer['bgpPeerRemoteAs'] . ')', $device, 'bgpPeer', $bgpPeer_id);
        }
        else if ($bgpPeerState == "established")
        {
          notify($device, "BGP Session up: " . $peer['bgpPeerIdentifier'] . ' (AS' . $peer['bgpPeerRemoteAs'] . ' - ' . $peer['astext'] . ')', "BGP Session up since " . formatUptime($bgpPeerFsmEstablishedTime) . ".\n\nHostname : " . $device['hostname'] . "\nPeer IP  : " . $peer['bgpPeerIdentifier'] . "\nRemote AS: " . $peer['bgpPeerRemoteAs'] . ' ('.$peer['astext'].')');
          log_event('BGP Session Up: ' . $peer['bgpPeerIdentifier'] . ' (AS' . $peer['bgpPeerRemoteAs'] . ')', $device, 'bgpPeer', $bgpPeer_id);
        }
        else if ($peer['bgpPeerState'] == "established")
        {
          notify($device, "BGP Session down: " . $peer['bgpPeerIdentifier'] . ' (AS' . $peer['bgpPeerRemoteAs'] . ' - ' . $peer['astext'] . ')', "BGP Session down since " . formatUptime($bgpPeerFsmEstablishedTime) . ".\n\nHostname : " . $device['hostname'] . "\nPeer IP  : " . $peer['bgpPeerIdentifier'] . "\nRemote AS: " . $peer['bgpPeerRemoteAs'] . ' ('.$peer['astext'].')');
          log_event('BGP Session Down: ' . $peer['bgpPeerIdentifier'] . ' (AS' . $peer['bgpPeerRemoteAs'] . ')', $device, 'bgpPeer', $bgpPeer_id);
        }
      }
    }

    $peerrrd = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("bgp-" . $peer['bgpPeerIdentifier'] . ".rrd");
    if (!is_file($peerrrd))
    {
      $create_rrd = "DS:bgpPeerOutUpdates:COUNTER:600:U:100000000000 \
        DS:bgpPeerInUpdates:COUNTER:600:U:100000000000 \
        DS:bgpPeerOutTotal:COUNTER:600:U:100000000000 \
        DS:bgpPeerInTotal:COUNTER:600:U:100000000000 \
        DS:bgpPeerEstablished:GAUGE:600:0:U " . $config['rrd_rra'];

      rrdtool_create($peerrrd, $create_rrd);
    }

    rrdtool_update("$peerrrd", "N:$bgpPeerOutUpdates:$bgpPeerInUpdates:$bgpPeerOutTotalMessages:$bgpPeerInTotalMesages:$bgpPeerFsmEstablishedTime");

    $peer['update']['bgpPeerState'] = $bgpPeerState;
    $peer['update']['bgpPeerAdminStatus'] = $bgpPeerAdminStatus;
    $peer['update']['bgpPeerFsmEstablishedTime'] = $bgpPeerFsmEstablishedTime;
    $peer['update']['bgpPeerInUpdates'] = $bgpPeerInUpdates;
    $peer['update']['bgpLocalAddr'] = $bgpLocalAddr;
    $peer['update']['bgpPeerOutUpdates'] = $bgpPeerOutUpdates;

    dbUpdate($peer['update'], 'bgpPeers', '`device_id` = ? AND `bgpPeerIdentifier` = ?', array($device['device_id'], $peer['bgpPeerIdentifier']));

    if ($device['os_group'] == "cisco" || $device['os'] == "junos")
    {
      // Poll each AFI/SAFI for this peer (using CISCO-BGP4-MIB or BGP4-V2-JUNIPER MIB)
      $peer_afis = dbFetchRows("SELECT * FROM bgpPeers_cbgp WHERE `device_id` = ? AND bgpPeerIdentifier = ?", array($device['device_id'], $peer['bgpPeerIdentifier']));
      foreach ($peer_afis as $peer_afi)
      {
        $afi = $peer_afi['afi'];
        $safi = $peer_afi['safi'];
        if ($debug) { echo("$afi $safi\n"); }

        if ($device['os_group'] == "cisco") {
            $bgp_peer_ident = ipv62snmp($peer['bgpPeerIdentifier']);
            if (strstr($peer['bgpPeerIdentifier'], ":")) {
                $ip_type = 2;
                $ip_len = 16;
                $ip_ver = 'ipv6';
            } else {
                $ip_type = 1;
                $ip_len = 4;
                $ip_ver = 'ipv4';
            }
            $ip_cast = 1;
            if ($peer_afi['safi'] == "multicast") {
                $ip_cast = 2;
            } elseif ($peer_afi['safi'] == "unicastAndMulticast") {
                $ip_cast = 3;
            } elseif ($peer_afi['safi'] == "vpn") {
                $ip_cast = 128;
            }
            $check = snmp_get($device,"cbgpPeer2AcceptedPrefixes.".$ip_type.'.'.$ip_len.'.'.$bgp_peer_ident.'.'.$ip_type.'.'.$ip_cast,"","CISCO-BGP4-MIB", $config['mibdir']);

            if(!empty($check)) {
                $cgp_peer_identifier = $ip_type . '.' . $ip_len . '.' . $bgp_peer_ident . '.' . $ip_type . '.' . $ip_cast;
                $cbgp_data_tmp = snmp_get_multi($device,
                    " cbgpPeer2AcceptedPrefixes.". $cgp_peer_identifier .
                    " cbgpPeer2DeniedPrefixes.". $cgp_peer_identifier .
                    " cbgpPeer2PrefixAdminLimit." . $cgp_peer_identifier .
                    " cbgpPeer2PrefixThreshold." . $cgp_peer_identifier .
                    " cbgpPeer2PrefixClearThreshold." . $cgp_peer_identifier .
                    " cbgpPeer2AdvertisedPrefixes." . $cgp_peer_identifier .
                    " cbgpPeer2SuppressedPrefixes." . $cgp_peer_identifier .
                    " cbgpPeer2WithdrawnPrefixes." . $cgp_peer_identifier, "-OQUs", "CISCO-BGP4-MIB",$config['mibdir']);
                $ident = "$ip_ver.\"".$peer['bgpPeerIdentifier'].'"'. '.' . $ip_type . '.' . $ip_cast;
                unset($cbgp_data);
                $temp_keys = array_keys($cbgp_data_tmp);
                unset($temp_data);
                $temp_data['cbgpPeer2AcceptedPrefixes'] = $cbgp_data_tmp[$temp_keys[0]]['cbgpPeer2AcceptedPrefixes'];
                $temp_data['cbgpPeer2DeniedPrefixes'] = $cbgp_data_tmp[$temp_keys[0]]['cbgpPeer2DeniedPrefixes'];
                $temp_data['cbgpPeer2PrefixAdminLimit'] = $cbgp_data_tmp[$temp_keys[0]]['cbgpPeer2PrefixAdminLimit'];
                $temp_data['cbgpPeer2PrefixThreshold'] = $cbgp_data_tmp[$temp_keys[0]]['cbgpPeer2PrefixThreshold'];
                $temp_data['cbgpPeer2PrefixClearThreshold'] = $cbgp_data_tmp[$temp_keys[0]]['cbgpPeer2PrefixClearThreshold'];
                $temp_data['cbgpPeer2AdvertisedPrefixes'] = $cbgp_data_tmp[$temp_keys[0]]['cbgpPeer2AdvertisedPrefixes'];
                $temp_data['cbgpPeer2SuppressedPrefixes'] = $cbgp_data_tmp[$temp_keys[0]]['cbgpPeer2SuppressedPrefixes'];
                $temp_data['cbgpPeer2WithdrawnPrefixes'] = $cbgp_data_tmp[$temp_keys[0]]['cbgpPeer2WithdrawnPrefixes'];
                foreach ($temp_data as $k => $v) {
                    $cbgp_data .= "$v\n";
                }
                if ($debug) {
                    echo("$cbgp_data\n");
                }

            } else {

                // FIXME - move to function
                $cbgp_cmd = $config['snmpget'] . " -M " . $config['mibdir'] . " -m CISCO-BGP4-MIB -Ovq " . snmp_gen_auth($device) . " " . $device['hostname'] . ":" . $device['port'];
                $cbgp_cmd .= " cbgpPeerAcceptedPrefixes." . $peer['bgpPeerIdentifier'] . ".$afi.$safi";
                $cbgp_cmd .= " cbgpPeerDeniedPrefixes." . $peer['bgpPeerIdentifier'] . ".$afi.$safi";
                $cbgp_cmd .= " cbgpPeerPrefixAdminLimit." . $peer['bgpPeerIdentifier'] . ".$afi.$safi";
                $cbgp_cmd .= " cbgpPeerPrefixThreshold." . $peer['bgpPeerIdentifier'] . ".$afi.$safi";
                $cbgp_cmd .= " cbgpPeerPrefixClearThreshold." . $peer['bgpPeerIdentifier'] . ".$afi.$safi";
                $cbgp_cmd .= " cbgpPeerAdvertisedPrefixes." . $peer['bgpPeerIdentifier'] . ".$afi.$safi";
                $cbgp_cmd .= " cbgpPeerSuppressedPrefixes." . $peer['bgpPeerIdentifier'] . ".$afi.$safi";
                $cbgp_cmd .= " cbgpPeerWithdrawnPrefixes." . $peer['bgpPeerIdentifier'] . ".$afi.$safi";

                if ($debug) {
                    echo("$cbgp_cmd\n");
                }
                $cbgp_data = preg_replace("/^OID.*$/", "", trim(`$cbgp_cmd`));
                $cbgp_data = preg_replace("/No Such Instance currently exists at this OID/", "0", $cbgp_data);
                if ($debug) {
                    echo("$cbgp_data\n");
                }
            }
          list($cbgpPeerAcceptedPrefixes,$cbgpPeerDeniedPrefixes,$cbgpPeerPrefixAdminLimit,$cbgpPeerPrefixThreshold,$cbgpPeerPrefixClearThreshold,$cbgpPeerAdvertisedPrefixes,$cbgpPeerSuppressedPrefixes,$cbgpPeerWithdrawnPrefixes) = explode("\n", $cbgp_data);
        }

        if ($device['os'] == "junos")
        {
          # Missing: cbgpPeerAdminLimit cbgpPeerPrefixThreshold cbgpPeerPrefixClearThreshold cbgpPeerSuppressedPrefixes cbgpPeerWithdrawnPrefixes

          $safis['unicast'] = 1;
          $safis['multicast'] = 2;

          if (!isset($peerIndexes))
          {
            $j_bgp = snmpwalk_cache_multi_oid($device, "jnxBgpM2PeerTable", $jbgp, "BGP4-V2-MIB-JUNIPER", $config['install_dir']."/mibs/junos");
            foreach ($j_bgp as $index => $entry)
            {
              switch ($entry['jnxBgpM2PeerRemoteAddrType'])
              {
                case 'ipv4':
                  $ip = long2ip(hexdec($entry['jnxBgpM2PeerRemoteAddr']));
                  $j_peerIndexes[$ip] = $entry['jnxBgpM2PeerIndex'];
                  break;
                case 'ipv6':
                  $ip6 = trim(str_replace(' ','',$entry['jnxBgpM2PeerRemoteAddr']),'"');
                  $ip6 = substr($ip6,0,4) . ':' . substr($ip6,4,4) . ':' . substr($ip6,8,4) . ':' . substr($ip6,12,4) . ':' . substr($ip6,16,4) . ':' . substr($ip6,20,4) . ':' . substr($ip6,24,4) . ':' . substr($ip6,28,4);
                  $ip6 = Net_IPv6::compress($ip6);
                  $j_peerIndexes[$ip6] = $entry['jnxBgpM2PeerIndex'];
                  break;
                default:
                  echo("PANIC: Don't know RemoteAddrType " . $entry['jnxBgpM2PeerRemoteAddrType'] . "!\n");
                  break;
              }
            }
          }

          $j_prefixes = snmpwalk_cache_multi_oid($device, "jnxBgpM2PrefixCountersTable", $jbgp, "BGP4-V2-MIB-JUNIPER", $config['install_dir']."/mibs/junos");

          $cbgpPeerAcceptedPrefixes = $j_prefixes[$j_peerIndexes[$peer['bgpPeerIdentifier']].".$afi." . $safis[$safi]]['jnxBgpM2PrefixInPrefixesAccepted'];
          $cbgpPeerDeniedPrefixes = $j_prefixes[$j_peerIndexes[$peer['bgpPeerIdentifier']].".$afi." . $safis[$safi]]['jnxBgpM2PrefixInPrefixesRejected'];
          $cbgpPeerAdvertisedPrefixes = $j_prefixes[$j_peerIndexes[$peer['bgpPeerIdentifier']].".$afi." . $safis[$safi]]['jnxBgpM2PrefixOutPrefixes'];

          unset($j_prefixes);
          unset($j_bgp);
          unset($j_peerIndexes);
        }

        // FIXME THESE FIELDS DO NOT EXIST IN THE DATABASE!
        $update  = "UPDATE bgpPeers_cbgp SET";
        $peer['c_update']['AcceptedPrefixes'] = $cbgpPeerAcceptedPrefixes;
        $peer['c_update']['DeniedPrefixes'] = $cbgpPeerDeniedPrefixes;
        $peer['c_update']['PrefixAdminLimit'] = $cbgpPeerAdminLimit;
        $peer['c_update']['PrefixThreshold'] = $cbgpPeerPrefixThreshold;
        $peer['c_update']['PrefixClearThreshold'] = $cbgpPeerPrefixClearThreshold;
        $peer['c_update']['AdvertisedPrefixes'] = $cbgpPeerAdvertisedPrefixes;
        $peer['c_update']['SuppressedPrefixes'] = $cbgpPeerSuppressedPrefixes;
        $peer['c_update']['WithdrawnPrefixes'] = $cbgpPeerWithdrawnPrefixes;

        dbUpdate($peer['c_update'], 'bgpPeers_cbgp', '`device_id` = ? AND bgpPeerIdentifier = ? AND afi = ? AND safi = ?', array($device['device_id'], $peer['bgpPeerIdentifier'], $afi, $safi));

        $cbgp_rrd    = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("cbgp-" . $peer['bgpPeerIdentifier'] . ".$afi.$safi.rrd");
        if (!is_file($cbgp_rrd))
        {
          $rrd_create = "DS:AcceptedPrefixes:GAUGE:600:U:100000000000 \
           DS:DeniedPrefixes:GAUGE:600:U:100000000000 \
           DS:AdvertisedPrefixes:GAUGE:600:U:100000000000 \
           DS:SuppressedPrefixes:GAUGE:600:U:100000000000 \
           DS:WithdrawnPrefixes:GAUGE:600:U:100000000000 ".$config['rrd_rra'];
          rrdtool_create($cbgp_rrd, $rrd_create);
        }
        rrdtool_update("$cbgp_rrd", "N:$cbgpPeerAcceptedPrefixes:$cbgpPeerDeniedPrefixes:$cbgpPeerAdvertisedPrefixes:$cbgpPeerSuppressedPrefixes:$cbgpPeerWithdrawnPrefixes");
      } # while
    } # os_group=cisco | junos
    echo("\n");

  } // End While loop on peers
} // End check for BGP support

?>
