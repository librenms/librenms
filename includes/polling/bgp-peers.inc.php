<?php

## We should walk, so we can discover here too.

echo("Polling BGP peers\n");

if (!$config['enable_bgp'])
{
  echo("BGP Support Disabled\n");
}
else
{
  foreach (dbFetchRows("SELECT * FROM bgpPeers WHERE device_id = ?", array($device['device_id'])) as $peer)
  {
    ### Poll BGP Peer

    echo("Checking ".$peer['bgpPeerIdentifier']." ");

    if (!strstr($peer['bgpPeerIdentifier'],':'))
    {
      # v4 BGP4 MIB
      ## FIXME - needs moved to function
      $peer_cmd  = $config['snmpget'] . " -M ".$config['mibdir'] . " -m BGP4-MIB -OUvq -" . $device['snmpver'] . " -c" . $device['community'] . " " . $device['hostname'].":".$device['port'] . " ";
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
        echo("\nCaching Oids...");
        ## FIXME - needs moved to function
        $peer_cmd  = $config['snmpwalk'] . " -M ".$config['mibdir'] . "/junos -m BGP4-V2-MIB-JUNIPER -OUnq -" . $device['snmpver'] . " -c" . $device['community'] . " " . $device['hostname'].":".$device['port'];
        $peer_cmd .= " jnxBgpM2PeerStatus.0.ipv6";
        foreach (explode("\n",trim(`$peer_cmd`)) as $oid)
        {
          list($peer_oid) = explode(' ',$oid);
          $peer_id = explode('.',$peer_oid);
          $junos_v6[implode('.',array_slice($peer_id,35))] = implode('.',array_slice($peer_id,18));
        }
      }

      ## FIXME - move to function (and clean up, wtf?)
      $peer_cmd  = $config['snmpget'] . " -M ".$config['mibdir'] . "/junos -m BGP4-V2-MIB-JUNIPER -OUvq -" . $device['snmpver'] . " -c" . $device['community'] . " " . $device['hostname'].":".$device['port'];
      $peer_cmd .= " jnxBgpM2PeerState.0.ipv6." . $junos_v6[$peer_ip];
      $peer_cmd .= " jnxBgpM2PeerStatus.0.ipv6." . $junos_v6[$peer_ip]; # Should be jnxBgpM2CfgPeerAdminStatus but doesn't seem to be implemented?
      $peer_cmd .= " jnxBgpM2PeerInUpdates.0.ipv6." . $junos_v6[$peer_ip];
      $peer_cmd .= " jnxBgpM2PeerOutUpdates.0.ipv6." . $junos_v6[$peer_ip];
      $peer_cmd .= " jnxBgpM2PeerInTotalMessages.0.ipv6." . $junos_v6[$peer_ip];
      $peer_cmd .= " jnxBgpM2PeerOutTotalMessages.0.ipv6." . $junos_v6[$peer_ip];
      $peer_cmd .= " jnxBgpM2PeerFsmEstablishedTime.0.ipv6." . $junos_v6[$peer_ip];
      $peer_cmd .= " jnxBgpM2PeerInUpdatesElapsedTime.0.ipv6." . $junos_v6[$peer_ip];
      $peer_cmd .= " jnxBgpM2PeerLocalAddr.0.ipv6." . $junos_v6[$peer_ip];
      $peer_cmd .= ' -M"' . $config['install_dir'] . '/mibs/junos"|grep -v "No Such Instance"';
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
      if (!(is_array($config['alerts']['bgp']['whitelist']) && !in_array($bgppeerremoteas, $config['alerts']['bgp']['whitelist'])) && ($bgpPeerFsmEstablishedTime < $peer['bgpPeerFsmEstablishedTime'] || $bgpPeerState != $peer['bgpPeerState']))
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

    $peer['update']['bgpPeerState'] = $bgpPeerState;
    $peer['update']['bgpPeerAdminStatus'] = $bgpPeerAdminStatus;
    $peer['update']['bgpPeerFsmEstablishedTime'] = $bgpPeerFsmEstablishedTime;
    $peer['update']['bgpPeerInUpdates'] = $bgpPeerInUpdates;
    $peer['update']['bgpLocalAddr'] = $bgpLocalAddr;
    $peer['update']['bgpPeerOutUpdates'] = $bgpPeerOutUpdates;

    dbUpdate($peer['update'], 'bgpPeers', '`device_id` = ? AND `bgpPeerIdentifier` = ?', array($device['device_id'], $peer['bgpPeerIdentifier']));

    if ($device['os_group'] == "ios" || $device['os'] == "junos")
    {
      ## Poll each AFI/SAFI for this peer (using CISCO-BGP4-MIB or BGP4-V2-JUNIPER MIB)
      $peer_afis = dbFetchRows("SELECT * FROM bgpPeers_cbgp WHERE `device_id` = ? AND bgpPeerIdentifier = ?", array($device['device_id'], $peer['bgpPeerIdentifier']));
      foreach ($peer_afis as $peer_afi)
      {
        $afi = $peer_afi['afi'];
        $safi = $peer_afi['safi'];
        if ($debug) { echo("$afi $safi\n"); }

        if ($device['os_group'] == "ios")
        {
          ## FIXME - move to function
          $cbgp_cmd  = $config['snmpget'] . " -M ".$config['mibdir'] . " -m CISCO-BGP4-MIB -Ovq -" . $device['snmpver'] . " -c" . $device['community'] . " " . $device['hostname'].":".$device['port'];
          $cbgp_cmd .= " cbgpPeerAcceptedPrefixes." . $peer['bgpPeerIdentifier'] . ".$afi.$safi";
          $cbgp_cmd .= " cbgpPeerDeniedPrefixes." . $peer['bgpPeerIdentifier'] . ".$afi.$safi";
          $cbgp_cmd .= " cbgpPeerPrefixAdminLimit." . $peer['bgpPeerIdentifier'] . ".$afi.$safi";
          $cbgp_cmd .= " cbgpPeerPrefixThreshold." . $peer['bgpPeerIdentifier'] . ".$afi.$safi";
          $cbgp_cmd .= " cbgpPeerPrefixClearThreshold." . $peer['bgpPeerIdentifier'] . ".$afi.$safi";
          $cbgp_cmd .= " cbgpPeerAdvertisedPrefixes." . $peer['bgpPeerIdentifier'] . ".$afi.$safi";
          $cbgp_cmd .= " cbgpPeerSuppressedPrefixes." . $peer['bgpPeerIdentifier'] . ".$afi.$safi";
          $cbgp_cmd .= " cbgpPeerWithdrawnPrefixes." . $peer['bgpPeerIdentifier'] . ".$afi.$safi";

          if ($debug) { echo("$cbgp_cmd\n"); }
          $cbgp_data = preg_replace("/^OID.*$/", "", trim(`$cbgp_cmd`));
          if ($debug) { echo("$cbgp_data\n"); }
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

        # FIXME THESE FIELDS DO NOT EXIST IN THE DATABASE!
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
      } # while
    } # os=ios | junos
    echo("\n");

  } ## End While loop on peers
} ## End check for BGP support

?>
