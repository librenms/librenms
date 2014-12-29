<?php

// We should walk, so we can discover here too.

global $debug;

if ($config['enable_bgp'])
{
  foreach (dbFetchRows("SELECT * FROM bgpPeers WHERE device_id = ?", array($device['device_id'])) as $peer)
  {
    //add context if exist  
    $device['context_name']= $peer['context_name'];
    // Poll BGP Peer

    echo("Checking BGP peer ".$peer['bgpPeerIdentifier']." ");

    if (!strstr($peer['bgpPeerIdentifier'],':'))
    {
      //change for work snmp_get_multi
      # v4 BGP4 MIB
      // FIXME - needs moved to function
     // $peer_cmd  = $config['snmpget'] . " -M ".$config['mibdir'] . " -m BGP4-MIB -OUvq " . snmp_gen_auth($device) . " " . $device['hostname'].":".$device['port'] . " ";
      $oids = "bgpPeerState." . $peer['bgpPeerIdentifier'] . " bgpPeerAdminStatus." . $peer['bgpPeerIdentifier'] . " bgpPeerInUpdates." . $peer['bgpPeerIdentifier'] . " bgpPeerOutUpdates." . $peer['bgpPeerIdentifier'] . " bgpPeerInTotalMessages." . $peer['bgpPeerIdentifier'] . " ";
      $oids .= "bgpPeerOutTotalMessages." . $peer['bgpPeerIdentifier'] . " bgpPeerFsmEstablishedTime." . $peer['bgpPeerIdentifier'] . " bgpPeerInUpdateElapsedTime." . $peer['bgpPeerIdentifier'] . " ";
      $oids .= "bgpPeerLocalAddr." . $peer['bgpPeerIdentifier'] . "";
      $peer_data=snmp_get_multi($device,$oids,'-OUQs','BGP4-MIB');
      $peer_data=  array_pop($peer_data);
      if($debug){
          var_dump($peer_data);  
      }
       $bgpPeerState=  !empty($peer_data['bgpPeerState'])?$peer_data['bgpPeerState']:'';
       $bgpPeerAdminStatus=  !empty($peer_data['bgpPeerAdminStatus'])?$peer_data['bgpPeerAdminStatus']:'';
       $bgpPeerInUpdates=  !empty($peer_data['bgpPeerInUpdates'])?$peer_data['bgpPeerInUpdates']:'';
       $bgpPeerOutUpdates=  !empty($peer_data['bgpPeerOutUpdates'])?$peer_data['bgpPeerOutUpdates']:'';
       $bgpPeerInTotalMessages=  !empty($peer_data['bgpPeerInTotalMessages'])?$peer_data['bgpPeerInTotalMessages']:'';
       $bgpPeerOutTotalMessages=  !empty($peer_data['bgpPeerOutTotalMessages'])?$peer_data['bgpPeerOutTotalMessages']:'';
       $bgpPeerFsmEstablishedTime=  !empty($peer_data['bgpPeerFsmEstablishedTime'])?$peer_data['bgpPeerFsmEstablishedTime']:'';
       $bgpPeerInUpdateElapsedTime=  !empty($peer_data['bgpPeerInUpdateElapsedTime'])?$peer_data['bgpPeerInUpdateElapsedTime']:'';
       $bgpLocalAddr=  !empty($peer_data['bgpPeerLocalAddr'])?$peer_data['bgpPeerLocalAddr']:'';
 
      //list($bgpPeerState, $bgpPeerAdminStatus, $bgpPeerInUpdates, $bgpPeerOutUpdates, $bgpPeerInTotalMessages, $bgpPeerOutTotalMessages, $bgpPeerFsmEstablishedTime, $bgpPeerInUpdateElapsedTime, $bgpLocalAddr) = array_values($peer_data) ;
      
      
      
      
      unset($peer_data);
      
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
        //snmp_get($device,'jnxBgpM2PeerStatus.0.ipv6','-OUnq','BGP4-V2-MIB-JUNIPER',$config['mibdir'] . "/junos");
        
        foreach (explode("\n",snmp_get($device,'jnxBgpM2PeerStatus.0.ipv6"','-OUnq','BGP4-V2-MIB-JUNIPER',$config['mibdir'] . "/junos")) as $oid)
        {
          list($peer_oid) = explode(' ',$oid);
          $peer_id = explode('.',$peer_oid);
          $junos_v6[implode('.',array_slice($peer_id,35))] = implode('.',array_slice($peer_id,18));
        }
      }

      // FIXME - move to function (and clean up, wtf?)
      $oids = " jnxBgpM2PeerState.0.ipv6." . $junos_v6[$peer_ip];
      $oids .= " jnxBgpM2PeerStatus.0.ipv6." . $junos_v6[$peer_ip]; # Should be jnxBgpM2CfgPeerAdminStatus but doesn't seem to be implemented?
      $oids .= " jnxBgpM2PeerInUpdates.0.ipv6." . $junos_v6[$peer_ip];
      $oids .= " jnxBgpM2PeerOutUpdates.0.ipv6." . $junos_v6[$peer_ip];
      $oids .= " jnxBgpM2PeerInTotalMessages.0.ipv6." . $junos_v6[$peer_ip];
      $oids .= " jnxBgpM2PeerOutTotalMessages.0.ipv6." . $junos_v6[$peer_ip];
      $oids .= " jnxBgpM2PeerFsmEstablishedTime.0.ipv6." . $junos_v6[$peer_ip];
      $oids .= " jnxBgpM2PeerInUpdatesElapsedTime.0.ipv6." . $junos_v6[$peer_ip];
      $oids .= " jnxBgpM2PeerLocalAddr.0.ipv6." . $junos_v6[$peer_ip];
      //$peer_cmd .= '|grep -v "No Such Instance"'; WHAT TO DO WITH THIS??,USE TO SEE -Ln?? 
      $peer_data=snmp_get_multi($device,$oids,'-OUQs -Ln','BGP4-V2-MIB-JUNIPER',$config['mibdir'] . "/junos");
      $peer_data=  array_pop($peer_data);
      if($debug){
          var_dump($peer_data);
      }
      $bgpPeerState=  !empty($peer_data['bgpPeerState'])?$peer_data['bgpPeerState']:'';
       $bgpPeerAdminStatus=  !empty($peer_data['bgpPeerAdminStatus'])?$peer_data['bgpPeerAdminStatus']:'';
       $bgpPeerInUpdates=  !empty($peer_data['bgpPeerInUpdates'])?$peer_data['bgpPeerInUpdates']:'';
       $bgpPeerOutUpdates=  !empty($peer_data['bgpPeerOutUpdates'])?$peer_data['bgpPeerOutUpdates']:'';
       $bgpPeerInTotalMessages=  !empty($peer_data['bgpPeerInTotalMessages'])?$peer_data['bgpPeerInTotalMessages']:'';
       $bgpPeerOutTotalMessages=  !empty($peer_data['bgpPeerOutTotalMessages'])?$peer_data['bgpPeerOutTotalMessages']:'';
       $bgpPeerFsmEstablishedTime=  !empty($peer_data['bgpPeerFsmEstablishedTime'])?$peer_data['bgpPeerFsmEstablishedTime']:'';
       $bgpPeerInUpdateElapsedTime=  !empty($peer_data['bgpPeerInUpdateElapsedTime'])?$peer_data['bgpPeerInUpdateElapsedTime']:'';
       $bgpLocalAddr=  !empty($peer_data['bgpPeerLocalAddr'])?$peer_data['bgpPeerLocalAddr']:'';
      
      //list($bgpPeerState, $bgpPeerAdminStatus, $bgpPeerInUpdates, $bgpPeerOutUpdates, $bgpPeerInTotalMessages, $bgpPeerOutTotalMessages, $bgpPeerFsmEstablishedTime, $bgpPeerInUpdateElapsedTime, $bgpLocalAddr) = array_values($peer_data);
      
      
      unset($peer_data);
      
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

        if ($device['os_group'] == "cisco")
        {
          // FIXME - move to function
          $oids = " cbgpPeerAcceptedPrefixes." . $peer['bgpPeerIdentifier'] . ".$afi.$safi";
          $oids .= " cbgpPeerDeniedPrefixes." . $peer['bgpPeerIdentifier'] . ".$afi.$safi";
          $oids .= " cbgpPeerPrefixAdminLimit." . $peer['bgpPeerIdentifier'] . ".$afi.$safi";
          $oids .= " cbgpPeerPrefixThreshold." . $peer['bgpPeerIdentifier'] . ".$afi.$safi";
          $oids .= " cbgpPeerPrefixClearThreshold." . $peer['bgpPeerIdentifier'] . ".$afi.$safi";
          $oids .= " cbgpPeerAdvertisedPrefixes." . $peer['bgpPeerIdentifier'] . ".$afi.$safi";
          $oids .= " cbgpPeerSuppressedPrefixes." . $peer['bgpPeerIdentifier'] . ".$afi.$safi";
          $oids .= " cbgpPeerWithdrawnPrefixes." . $peer['bgpPeerIdentifier'] . ".$afi.$safi";
          
          $cbgp_data=  snmp_get_multi($device,$oids,'-OUQs ','CISCO-BGP4-MIB');
          $cbgp_data=  array_pop($cbgp_data);

          $cbgpPeerAcceptedPrefixes=  !empty($cbgp_data['cbgpPeerAcceptedPrefixes'])?$cbgp_data['cbgpPeerAcceptedPrefixes']:'';
          $cbgpPeerDeniedPrefixes=  !empty($cbgp_data['cbgpPeerDeniedPrefixes'])?$cbgp_data['cbgpPeerDeniedPrefixes']:'';
          $cbgpPeerPrefixAdminLimit=  !empty($cbgp_data['cbgpPeerPrefixAdminLimit'])?$cbgp_data['cbgpPeerPrefixAdminLimit']:'';
          $cbgpPeerPrefixThreshold=  !empty($cbgp_data['cbgpPeerPrefixThreshold'])?$cbgp_data['cbgpPeerPrefixThreshold']:'';
          $cbgpPeerPrefixClearThreshold=  !empty($cbgp_data['cbgpPeerPrefixClearThreshold'])?$cbgp_data['cbgpPeerPrefixClearThreshold']:'';
          $cbgpPeerAdvertisedPrefixes=  !empty($cbgp_data['cbgpPeerAdvertisedPrefixes'])?$cbgp_data['cbgpPeerAdvertisedPrefixes']:'';
          $cbgpPeerSuppressedPrefixes=  !empty($cbgp_data['cbgpPeerSuppressedPrefixes'])?$cbgp_data['cbgpPeerSuppressedPrefixes']:'';
          $cbgpPeerWithdrawnPrefixes=  !empty($cbgp_data['cbgpPeerWithdrawnPrefixes'])?$cbgp_data['cbgpPeerWithdrawnPrefixes']:'';
          //list($cbgpPeerAcceptedPrefixes,$cbgpPeerDeniedPrefixes,$cbgpPeerPrefixAdminLimit,$cbgpPeerPrefixThreshold,$cbgpPeerPrefixClearThreshold,$cbgpPeerAdvertisedPrefixes,$cbgpPeerSuppressedPrefixes,$cbgpPeerWithdrawnPrefixes) =array_values( $cbgp_data);
          unset($cbgp_data);
          
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

    unset($device['context_name']);
  } // End While loop on peers
} // End check for BGP support

?>
