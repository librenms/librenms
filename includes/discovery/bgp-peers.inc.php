<?php

use LibreNMS\Config;
use LibreNMS\Exceptions\InvalidIpException;
use LibreNMS\Util\IP;

//
// Load OS specific file
//
if (file_exists(Config::get('install_dir') . "/includes/discovery/bgp-peers/{$device['os']}.inc.php")) {
    include Config::get('install_dir') . "/includes/discovery/bgp-peers/{$device['os']}.inc.php";
}

if (empty($bgpLocalAs)) {
    $bgpLocalAs = \SnmpQuery::get('BGP4-MIB::bgpLocalAs.0')->value();
}

foreach (DeviceCache::getPrimary()->getVrfContexts() as $context_name) {
    $device['context_name'] = $context_name;
    $peer2 = false;
    $peers_data = '';
    $bgp4_mib = false;

    if (is_numeric($bgpLocalAs)) {
        echo "AS$bgpLocalAs ";
        if ($bgpLocalAs != $device['bgpLocalAs']) {
            dbUpdate(['bgpLocalAs' => $bgpLocalAs], 'devices', 'device_id=?', [$device['device_id']]);
            echo 'Updated AS ';
        }

        if ($device['os_group'] === 'arista') {
            $peers_data = snmp_walk($device, 'aristaBgp4V2PeerRemoteAs', '-Oq', 'ARISTA-BGP4V2-MIB');
            $peer2 = true;
        } elseif ($device['os'] == 'junos') {
            $peers_data = snmp_walk($device, 'jnxBgpM2PeerRemoteAs', '-Onq', 'BGP4-V2-MIB-JUNIPER', 'junos');
        } elseif ($device['os_group'] === 'cisco') {
            $peers_data = snmp_walk($device, 'cbgpPeer2RemoteAs', '-Oq', 'CISCO-BGP4-MIB');
            $peer2 = ! empty($peers_data);
        } elseif ($device['os'] === 'cumulus') {
            $peers_data = snmp_walk($device, 'bgpPeerRemoteAs', '-Oq', 'CUMULUS-BGPUN-MIB');
            $peer2 = ! empty($peers_data);
        }

        if (empty($peers_data)) {
            $bgp4_mib = true;
            $peers_data = preg_replace('/= /', '', snmp_walk($device, 'bgpPeerRemoteAs', '-OQ', 'BGP4-MIB'));
        }
    } else {
        echo 'No BGP on host';
        if ($device['bgpLocalAs']) {
            dbUpdate(['bgpLocalAs' => null], 'devices', 'device_id=?', [$device['device_id']]);
            echo ' (Removed ASN) ';
        }
    }

    $peerlist = build_bgp_peers($device, $peers_data, $peer2);

    // Process discovered peers
    if (! empty($peerlist)) {
        $af_data = [];
        $af_list = [];

        foreach ($peerlist as $peer) {
            $peer['astext'] = \LibreNMS\Util\AutonomousSystem::get($peer['as'])->name();

            add_bgp_peer($device, $peer);

            if (empty($af_data)) {
                if ($device['os_group'] == 'cisco') {
                    if ($peer2 === true) {
                        $af_data = snmpwalk_cache_oid($device, 'cbgpPeer2AddrFamilyEntry', [], 'CISCO-BGP4-MIB');
                    }
                    if (empty($af_data)) {
                        $af_data = snmpwalk_cache_oid($device, 'cbgpPeerAddrFamilyEntry', [], 'CISCO-BGP4-MIB');
                        $peer2 = false;
                    }
                } elseif ($device['os_group'] === 'arista') {
                    $af_data = snmpwalk_cache_oid($device, 'aristaBgp4V2PrefixInPrefixes', $af_data, 'ARISTA-BGP4V2-MIB');
                } elseif ($device['os'] === 'aos7') {
                    $af_data = snmpwalk_cache_oid($device, 'alaBgpPeerRcvdPrefixes', $af_data, 'ALCATEL-IND1-BGP-MIB');
                }
            }

            // build the list
            if (! empty($af_data)) {
                $af_list = build_cbgp_peers($device, $peer, $af_data, $peer2);
            }

            if (! $bgp4_mib && $device['os'] == 'junos') {
                $afis['ipv4'] = 'ipv4';
                $afis['ipv6'] = 'ipv6';
                $afis[25] = 'l2vpn';
                $safis[1] = 'unicast';
                $safis[2] = 'multicast';
                $safis[3] = 'unicastAndMulticast';
                $safis[4] = 'labeledUnicast';
                $safis[5] = 'mvpn';
                $safis[65] = 'vpls';
                $safis[70] = 'evpn';
                $safis[128] = 'vpn';
                $safis[132] = 'rtfilter';
                $safis[133] = 'flow';

                if (! isset($j_peerIndexes)) {
                    $j_bgp = snmpwalk_cache_multi_oid($device, 'jnxBgpM2PeerTable', [], 'BGP4-V2-MIB-JUNIPER', 'junos');
                    d_echo($j_bgp);
                    $j_peerIndexes = [];
                    foreach ($j_bgp as $index => $entry) {
                        $peer_index = $entry['jnxBgpM2PeerIndex'];
                        try {
                            $ip = IP::fromHexString($entry['jnxBgpM2PeerRemoteAddr']);
                            d_echo('peerindex for ' . $ip->getFamily() . " $ip is $peer_index\n");
                            $j_peerIndexes[(string) $ip] = $peer_index;
                        } catch (InvalidIpException $e) {
                            d_echo("Unable to parse IP for peer $peer_index: " . $entry['jnxBgpM2PeerRemoteAddr'] . PHP_EOL);
                        }
                    }
                }

                if (! isset($j_afisafi)) {
                    $j_prefixes = snmpwalk_cache_multi_oid($device, 'jnxBgpM2PrefixCountersTable', [], 'BGP4-V2-MIB-JUNIPER', 'junos');
                    $j_afisafi = [];
                    foreach (array_keys($j_prefixes) as $key) {
                        [$index,$afisafi] = explode('.', $key, 2);
                        $j_afisafi[$index][] = $afisafi;
                    }
                }

                foreach ($j_afisafi[$j_peerIndexes[$peer['ip']]] ?? [] as $afisafi) {
                    [$afi,$safi] = explode('.', $afisafi);
                    $afi = $afis[$afi];
                    $safi = $safis[$safi];
                    $af_list[$peer['ip']][$afi][$safi] = 1;
                    add_cbgp_peer($device, $peer, $afi, $safi);
                }
            }

            $af_query = 'SELECT bgpPeerIdentifier, afi, safi FROM bgpPeers_cbgp WHERE `device_id`=? AND bgpPeerIdentifier=? AND context_name=?';
            foreach (dbFetchRows($af_query, [$device['device_id'], $peer['ip'], $device['context_name']]) as $entry) {
                $afi = $entry['afi'];
                $safi = $entry['safi'];
                if (! $af_list[$entry['bgpPeerIdentifier']][$afi][$safi]) {
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
    if (! empty($peerlist)) {
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

$existing_contexts = DeviceCache::getPrimary()->getVrfContexts();
foreach ($contexts as $context) {
    if (! in_array($context, $existing_contexts)) {
        dbDelete('bgpPeers', 'device_id=? and context_name=?', [$device['device_id'], $context]);
        dbDelete('bgpPeers_cbgp', 'device_id=? and context_name=?', [$device['device_id'], $context]);
        echo '-';
    }
}

unset(
    $device['context_name'],
    $peers_data,
    $af_data,
    $contexts
);
