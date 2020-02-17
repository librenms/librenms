<?php

use LibreNMS\Config;
use LibreNMS\Exceptions\InvalidIpException;
use LibreNMS\Util\IP;

if (Config::get('enable_bgp')) {
    if ($device['os'] == 'vrp') {
        $vrfs = dbFetchRows('SELECT vrf_id, vrf_name from `vrfs` WHERE device_id = ?', [$device['device_id']]);
        foreach ($vrfs as $vrf) {
            $map_vrf['byId'][$vrf['vrf_id']]['vrf_name'] = $vrf['vrf_name'];
            $map_vrf['byName'][$vrf['vrf_name']]['vrf_id'] = $vrf['vrf_id'];
        }
        $bgpPeersCache = snmpwalk_cache_oid($device, 'hwBgpPeers', [], 'HUAWEI-BGP-VPN-MIB');
        foreach ($bgpPeersCache as $key => $value) {
            $oid = explode(".", $key);
            $vrfInstance = $value['hwBgpPeerVrfName'];
            if ($oid[0] == 0) {
                $vrfInstance = '';
                $value['hwBgpPeerVrfName'] = '';
            }
            $address = str_replace($oid[0].".".$oid[1].".".$oid[2].".".$oid[3].".", '', $key);
            if ($oid[3] == 'ipv6') {
                $address = IP::fromHexString($address)->compressed();
            } elseif ($oid[3] != 'ipv4') {
                // we have a malformed OID reply, let's skip it
                continue;
            }

            $bgpPeers[$vrfInstance][$address] = $value;
            $bgpPeers[$vrfInstance][$address]['vrf_id'] = $map_vrf['byName'][$vrfInstance]['vrf_id'];
            $bgpPeers[$vrfInstance][$address]['afi'] = $oid[1];
            $bgpPeers[$vrfInstance][$address]['safi'] = $oid[2];
            $bgpPeers[$vrfInstance][$address]['typePeer'] =  $oid[3];
        }

        foreach ($bgpPeers as $vrfName => $vrf) {
            $vrfId = $map_vrf['byName'][$vrfName]['vrf_id'];
            $checkVrf = ' AND vrf_id = ? ';
            if (empty($vrfId)) {
                $checkVrf = ' AND `vrf_id` IS NULL ';
            }

            foreach ($vrf as $address => $value) {
                $astext = get_astext($value['hwBgpPeerRemoteAs']);
                if (dbFetchCell('SELECT COUNT(*) from `bgpPeers` WHERE device_id = ? AND bgpPeerIdentifier = ? ' . $checkVrf, [$device['device_id'], $address, $vrfId]) < '1') {
                    $peers = [
                        'device_id' => $device['device_id'],
                        'vrf_id' => $vrfId,
                        'bgpPeerIdentifier' => $address,
                        'bgpPeerRemoteAs' => $value['hwBgpPeerRemoteAs'],
                        'bgpPeerState' => $value['hwBgpPeerState'],
                        'bgpPeerAdminStatus' => 'stop',
                        'bgpLocalAddr' => '0.0.0.0',
                        'bgpPeerRemoteAddr' => $value['hwBgpPeerRemoteAddr'],
                        'bgpPeerInUpdates' => 0,
                        'bgpPeerOutUpdates' => 0,
                        'bgpPeerInTotalMessages' => 0,
                        'bgpPeerOutTotalMessages' => 0,
                        'bgpPeerFsmEstablishedTime' => $value['hwBgpPeerFsmEstablishedTime'],
                        'bgpPeerInUpdateElapsedTime' => 0,
                        'astext' => $astext,
                    ];
                    if (empty($vrfId)) {
                        unset($peers['vrf_id']);
                    }
                    dbInsert($peers, 'bgpPeers');

                    if (Config::get('autodiscovery.bgp')) {
                        $name = gethostbyaddr($address);
                        discover_new_device($name, $device, 'BGP');
                    }
                    echo '+';
                    $vrp_bgp_peer_count ++;
                } else {
                    dbUpdate(['bgpPeerRemoteAs' => $value['hwBgpPeerRemoteAs'], 'astext' => $astext], 'bgpPeers', 'device_id = ? AND bgpPeerIdentifier = ? AND vrf_id = ?', [$device['device_id'], $address, $vrfId]);
                    echo '.';
                    $vrp_bgp_peer_count ++;
                }
                if (dbFetchCell('SELECT COUNT(*) from `bgpPeers_cbgp` WHERE device_id = ? AND bgpPeerIdentifier = ? AND afi=? AND safi=?', array($device['device_id'], $value['hwBgpPeerRemoteAddr'], $value['afi'], $value['safi'])) < 1) {
                    $device['context_name'] = $vrfName;
                    add_cbgp_peer($device, ['ip' => $value['hwBgpPeerRemoteAddr']], $value['afi'], $value['safi']);
                    unset($device['context_name']);
                } else {
                    //nothing to update
                }
            }
        }
        // clean up peers
        $peers = dbFetchRows('SELECT `vrf_id`, `bgpPeerIdentifier` FROM `bgpPeers` WHERE `device_id` = ?', [$device['device_id']]);
        foreach ($peers as $value) {
            $vrfId = $value['vrf_id'];
            $checkVrf = ' AND vrf_id = ? ';
            if (empty($vrfId)) {
                $checkVrf = ' AND `vrf_id` IS NULL ';
            }
            $vrfName = $map_vrf['byId'][$vrfId]['vrf_name'];
            $address = $value['bgpPeerIdentifier'];

            if ((empty($vrfId) && empty($bgpPeers[''][$address])) ||
                (!empty($vrfId) && !empty($vrfName) && empty($bgpPeers[$vrfName][$address])) ||
                (!empty($vrfId) && empty($vrfName))) {
                $deleted = dbDelete('bgpPeers', 'device_id = ? AND bgpPeerIdentifier = ? ' . $checkVrf, [$device['device_id'], $address, $vrfId]);

                echo str_repeat('-', $deleted);
                echo PHP_EOL;
            }
        }

        $af_query = "SELECT bgpPeerIdentifier, afi, safi FROM bgpPeers_cbgp WHERE `device_id`=? AND bgpPeerIdentifier=?";
        foreach (dbFetchRows($af_query, [$device['device_id'], $peer['ip']]) as $entry) {
            $afi  = $entry['afi'];
            $safi = $entry['safi'];
            $vrfName = $entry['context_name'];
            if (!exist($bgpPeersCache[$vrfName]) ||
                    !exist($bgpPeersCache[$vrfName][$entry['bgpPeerIdentifier']]) ||
                    $bgpPeersCache[$vrfName][$entry['bgpPeerIdentifier']][$entry['afi']] != $afi ||
                    $bgpPeersCache[$vrfName][$entry['bgpPeerIdentifier']][$entry['safi']] != $safi ) {
                dbDelete(
                    'bgpPeers_cbgp',
                    '`device_id`=? AND `bgpPeerIdentifier`=? AND context_name=? AND afi=? AND safi=?',
                    [$device['device_id'], $address, $vrfName, $afi, $safi]
                );
            }
        }

        unset($bgpPeersCache);
        unset($bgpPeers);
        if ($vrp_bgp_peer_count > 0) {
            return; //Finish BGP discovery here, cause we collected data
        }
        // If not, we continue with standard BGP4 MIB
    }

    if ($device['os'] == 'timos') {
        $bgpPeersCache =snmpwalk_cache_multi_oid($device, 'tBgpPeerNgTable', [], 'TIMETRA-BGP-MIB', 'nokia');
        foreach ($bgpPeersCache as $key => $value) {
            $oid = explode(".", $key);
            $vrfInstance = $oid[0];
            $address = str_replace($oid[0].".".$oid[1].".", '', $key);
            if (strlen($address) > 15) {
                $address = IP::fromHexString($address)->compressed();
            }
            $bgpPeers[$vrfInstance][$address] = $value;
        }
        unset($bgpPeersCache);

        foreach ($bgpPeers as $vrfOid => $vrf) {
            $vrfId = dbFetchCell('SELECT vrf_id from `vrfs` WHERE vrf_oid = ?', [$vrfOid]);
            foreach ($vrf as $address => $value) {
                $astext = get_astext($value['tBgpPeerNgPeerAS4Byte']);

                if (dbFetchCell('SELECT COUNT(*) from `bgpPeers` WHERE device_id = ? AND bgpPeerIdentifier = ? AND vrf_id = ?', [$device['device_id'], $address, $vrfId]) < '1') {
                    $peers = [
                        'device_id' => $device['device_id'],
                        'vrf_id' => $vrfId,
                        'bgpPeerIdentifier' => $address,
                        'bgpPeerRemoteAs' => $value['tBgpPeerNgPeerAS4Byte'],
                        'bgpPeerState' => 'idle',
                        'bgpPeerAdminStatus' => 'stop',
                        'bgpLocalAddr' => '0.0.0.0',
                        'bgpPeerRemoteAddr' => '0.0.0.0',
                        'bgpPeerInUpdates' => 0,
                        'bgpPeerOutUpdates' => 0,
                        'bgpPeerInTotalMessages' => 0,
                        'bgpPeerOutTotalMessages' => 0,
                        'bgpPeerFsmEstablishedTime' => 0,
                        'bgpPeerInUpdateElapsedTime' => 0,
                        'astext' => $astext,
                    ];
                    dbInsert($peers, 'bgpPeers');
                    if (Config::get('autodiscovery.bgp')) {
                        $name = gethostbyaddr($address);
                        discover_new_device($name, $device, 'BGP');
                    }
                    echo '+';
                } else {
                    dbUpdate(['bgpPeerRemoteAs' => $value['tBgpPeerNgPeerAS4Byte'], 'astext' => $astext], 'bgpPeers', 'device_id = ? AND bgpPeerIdentifier = ? AND vrf_id = ?', [$device['device_id'], $address, $vrfId]);
                    echo '.';
                }
            }
        }
        // clean up peers
        $peers = dbFetchRows('SELECT `B`.`vrf_id` AS `vrf_id`, `bgpPeerIdentifier`, `vrf_oid` FROM `bgpPeers` AS B LEFT JOIN `vrfs` AS V ON `B`.`vrf_id` = `V`.`vrf_id` WHERE `B`.`device_id` = ?', [$device['device_id']]);
        foreach ($peers as $value) {
            $vrfId = $value['vrf_id'];
            $vrfOid = $value['vrf_oid'];
            $address = $value['bgpPeerIdentifier'];

            if (empty($bgpPeers[$vrfOid][$address])) {
                $deleted = dbDelete('bgpPeers', 'device_id = ? AND bgpPeerIdentifier = ? AND vrf_id = ?', [$device['device_id'], $address, $vrfId]);

                echo str_repeat('-', $deleted);
                echo PHP_EOL;
            }
        }
    }
    unset($bgpPeers);

    if (key_exists('vrf_lite_cisco', $device) && (count($device['vrf_lite_cisco'])!=0)) {
        $vrfs_lite_cisco = $device['vrf_lite_cisco'];
    } else {
        $vrfs_lite_cisco = array(array('context_name'=>''));
    }

    $bgpLocalAs = snmp_getnext($device, 'bgpLocalAs', '-Oqvn', 'BGP4-MIB');

    foreach ($vrfs_lite_cisco as $vrf) {
        $device['context_name'] = $vrf['context_name'];
        if (is_numeric($bgpLocalAs)) {
            echo "AS$bgpLocalAs ";
            if ($bgpLocalAs != $device['bgpLocalAs']) {
                dbUpdate(array('bgpLocalAs' => $bgpLocalAs), 'devices', 'device_id=?', array($device['device_id']));
                echo 'Updated AS ';
            }

            $peer2 = false;

            if ($device['os_group'] === 'arista') {
                $peers_data = snmp_walk($device, 'aristaBgp4V2PeerRemoteAs', '-Oq', 'ARISTA-BGP4V2-MIB');
                $peer2 = true;
            } elseif ($device['os'] == 'junos') {
                $peers_data = snmp_walk($device, 'jnxBgpM2PeerRemoteAs', '-Onq', 'BGP4-V2-MIB-JUNIPER', 'junos');
            } elseif ($device['os_group'] === 'cisco') {
                $peers_data = snmp_walk($device, 'cbgpPeer2RemoteAs', '-Oq', 'CISCO-BGP4-MIB');
                $peer2 = !empty($peers_data);
            }

            if (empty($peers_data)) {
                $bgp4_mib = true;
                $peers_data = snmp_walk($device, 'bgpPeerRemoteAs', '-Oq', 'BGP4-MIB');
            }
        } else {
            echo 'No BGP on host';
            if ($device['bgpLocalAs']) {
                dbUpdate(array('bgpLocalAs' => array('NULL')), 'devices', 'device_id=?', array($device['device_id']));
                echo ' (Removed ASN) ';
            }
        }

        $peerlist = build_bgp_peers($device, $peers_data, $peer2);

        // Process discovered peers
        if (!empty($peerlist)) {
            $af_data = array();
            $af_list = array();

            foreach ($peerlist as $peer) {
                $peer['astext'] = get_astext($peer['as']);

                add_bgp_peer($device, $peer);

                if (empty($af_data)) {
                    if ($device['os_group'] == 'cisco') {
                        if ($peer2 === true) {
                            $af_data = snmpwalk_cache_oid($device, 'cbgpPeer2AddrFamilyEntry', array(), 'CISCO-BGP4-MIB');
                        }
                        if (empty($af_data)) {
                            $af_data = snmpwalk_cache_oid($device, 'cbgpPeerAddrFamilyEntry', array(), 'CISCO-BGP4-MIB');
                            $peer2 = false;
                        }
                    } elseif ($device['os_group'] === 'arista') {
                        $af_data = snmpwalk_cache_oid($device, 'aristaBgp4V2PrefixInPrefixes', $af_data, 'ARISTA-BGP4V2-MIB');
                    }
                }

                // build the list
                if (!empty($af_data)) {
                    $af_list = build_cbgp_peers($device, $peer, $af_data, $peer2);
                }

                if (!$bgp4_mib && $device['os'] == 'junos') {
                    $afis['ipv4'] = 'ipv4';
                    $afis['ipv6'] = 'ipv6';
                    $afis[25]     = 'l2vpn';
                    $safis[1]     = 'unicast';
                    $safis[2]     = 'multicast';
                    $safis[3]     = 'unicastAndMulticast';
                    $safis[4]     = 'labeledUnicast';
                    $safis[5]     = 'mvpn';
                    $safis[65]    = 'vpls';
                    $safis[70]    = 'evpn';
                    $safis[128]   = 'vpn';
                    $safis[132]   = 'rtfilter';
                    $safis[133]   = 'flow';

                    if (!isset($j_peerIndexes)) {
                        $j_bgp = snmpwalk_cache_multi_oid($device, 'jnxBgpM2PeerEntry', $jbgp, 'BGP4-V2-MIB-JUNIPER', 'junos');
                        d_echo($j_bgp);
                        foreach ($j_bgp as $index => $entry) {
                            $peer_index = $entry['jnxBgpM2PeerIndex'];
                            try {
                                $ip = IP::fromHexString($entry['jnxBgpM2PeerRemoteAddr']);
                                d_echo("peerindex for " . $ip->getFamily() . " $ip is $peer_index\n");
                                $j_peerIndexes[(string)$ip] = $peer_index;
                            } catch (InvalidIpException $e) {
                                d_echo("Unable to parse IP for peer $peer_index: " . $entry['jnxBgpM2PeerRemoteAddr'] . PHP_EOL);
                            }
                        }
                    }

                    if (!isset($j_afisafi)) {
                        $j_prefixes = snmpwalk_cache_multi_oid($device, 'jnxBgpM2PrefixCountersTable', $jbgp, 'BGP4-V2-MIB-JUNIPER', 'junos');
                        foreach (array_keys($j_prefixes) as $key) {
                            list($index,$afisafi) = explode('.', $key, 2);
                            $j_afisafi[$index][]  = $afisafi;
                        }
                    }

                    foreach ($j_afisafi[$j_peerIndexes[$peer['ip']]] as $afisafi) {
                        list ($afi,$safi)     = explode('.', $afisafi);
                        $afi                  = $afis[$afi];
                        $safi                 = $safis[$safi];
                        $af_list[$peer['ip']][$afi][$safi] = 1;
                        add_cbgp_peer($device, $peer, $afi, $safi);
                    }
                }

                $af_query = "SELECT bgpPeerIdentifier, afi, safi FROM bgpPeers_cbgp WHERE `device_id`=? AND bgpPeerIdentifier=? AND context_name=?";
                foreach (dbFetchRows($af_query, [$device['device_id'], $peer['ip'], $device['context_name']]) as $entry) {
                    $afi  = $entry['afi'];
                    $safi = $entry['safi'];
                    if (!$af_list[$entry['bgpPeerIdentifier']][$afi][$safi]) {
                        dbDelete(
                            'bgpPeers_cbgp',
                            '`device_id`=? AND `bgpPeerIdentifier`=? AND context_name=? AND afi=? AND safi=?',
                            [$device['device_id'], $peer['ip'], $device['context_name'], $afi, $safi]
                        );
                    }
                }
            }
            unset($j_afisafi);
            unset($j_prefixes);
            unset($j_bgp);
            unset($j_peerIndexes);
        }

        // clean up peers
        $params = [$device['device_id'], $device['context_name']];
        $query = 'device_id=? AND context_name=?';
        if (!empty($peerlist)) {
            $query .= ' AND bgpPeerIdentifier NOT IN ' . dbGenPlaceholders(count($peerlist));
            $params = array_merge($params, array_column($peerlist, 'ip'));
        }

        $deleted = dbDelete('bgpPeers', $query, $params);
        dbDelete('bgpPeers_cbgp', $query, $params);

        echo str_repeat('-', $deleted);
        echo PHP_EOL;

        unset(
            $device['context_name'],
            $peerlist,
            $af_data
        );
    }

    // delete unknown contexts
    $contexts = dbFetchColumn(
        'SELECT DISTINCT context_name FROM bgpPeers WHERE device_id=?',
        [$device['device_id']]
    );
    $existing_contexts = array_column($vrfs_lite_cisco, 'context_name');
    foreach ($contexts as $context) {
        if (!in_array($context, $existing_contexts)) {
            dbDelete('bgpPeers', 'device_id=? and context_name=?', [$device['device_id'], $context]);
            dbDelete('bgpPeers_cbgp', 'device_id=? and context_name=?', [$device['device_id'], $context]);
            echo '-';
        }
    }

    unset(
        $device['context_name'],
        $vrfs_lite_cisco,
        $peers_data,
        $af_data,
        $contexts,
        $vrfs_c
    );
}
