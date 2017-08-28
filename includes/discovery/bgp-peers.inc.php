<?php

use LibreNMS\Util\IP;

if ($config['enable_bgp']) {
    if (key_exists('vrf_lite_cisco', $device) && (count($device['vrf_lite_cisco'])!=0)) {
        $vrfs_lite_cisco = $device['vrf_lite_cisco'];
    } else {
        $vrfs_lite_cisco = array(array('context_name'=>null));
    }

    $bgpLocalAs = trim(snmp_walk($device, '.1.3.6.1.2.1.15.2', '-Oqvn'));

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
            } elseif ($device['os'] !== 'junos') {
                $peers_data = snmp_walk($device, 'cbgpPeer2RemoteAs', '-Oq', 'CISCO-BGP4-MIB');
                if (empty($peers_data)) {
                    $peers_data = snmp_walk($device, 'BGP4-MIB::bgpPeerRemoteAs', '-Oq', 'BGP4-MIB');
                } else {
                    $peer2 = true;
                }
            } elseif ($device['os'] == 'junos') {
                $peers_data = snmp_walk($device, 'jnxBgpM2PeerRemoteAs', '-Onq', 'BGP4-V2-MIB-JUNIPER', 'junos');
            }
        } else {
            echo 'No BGP on host';
            if ($device['bgpLocalAs']) {
                dbUpdate(array('bgpLocalAs' => 'NULL'), 'devices', 'device_id=?', array($device['device_id']));
                echo ' (Removed ASN) ';
            }
        }

        $peerlist = build_bgp_peers($device, $peers_data, $peer2);

        // Process discovered peers
        if (isset($peerlist)) {
            foreach ($peerlist as $peer) {
                $astext = get_astext($peer['as']);
                $peer['astext'] = $astext;

                add_bgp_peer($device, $peer);

                $af_data = array();
                $af_list = array();

                if ($device['os_group'] == 'cisco') {
                    if (empty($af_data)) {
                        if ($peer2 === true) {
                            $af_data = snmpwalk_cache_oid($device, 'cbgpPeer2AddrFamilyEntry', $cbgp, 'CISCO-BGP4-MIB');
                        } else {
                            $af_data = snmpwalk_cache_oid($device, 'cbgpPeerAddrFamilyEntry', $cbgp, 'CISCO-BGP4-MIB');
                        }
                    }
                }

                if ($device['os_group'] === 'arista') {
                    if (empty($af_data)) {
                        $af_data = snmpwalk_cache_oid($device, 'aristaBgp4V2PrefixInPrefixes', $af_data, 'ARISTA-BGP4V2-MIB');
                    }
                }

                if (!empty($af_data)) {
                    $af_list = build_cbgp_peers($device, $peer, $af_data, $peer2);
                }

                if ($device['os'] == 'junos') {
                    $safis[1] = 'unicast';
                    $safis[2] = 'multicast';

                    if (!isset($j_peerIndexes)) {
                        $j_bgp = snmpwalk_cache_multi_oid($device, 'jnxBgpM2PeerEntry', $jbgp, 'BGP4-V2-MIB-JUNIPER', 'junos');
                        d_echo($j_bgp);
                        foreach ($j_bgp as $index => $entry) {
                            $ip = IP::fromHexString($entry['jnxBgpM2PeerRemoteAddr'], true);
                            d_echo("peerindex for '.$ip->getFamily().' $ip is ".$entry['jnxBgpM2PeerIndex']."\n");
                            $j_peerIndexes[$ip] = $entry['jnxBgpM2PeerIndex'];
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
                        $safi                 = $safis[$safi];
                        $af_list[$peer['ip']][$afi][$safi] = 1;
                        add_cbgp_peer($device, $peer, $afi, $safi);
                    }
                }

                $af_query = "SELECT * FROM bgpPeers_cbgp WHERE `device_id` = '".$device['device_id']."' AND bgpPeerIdentifier = '".$peer['ip']."'";
                foreach (dbFetchRows($af_query) as $entry) {
                    $afi  = $entry['afi'];
                    $safi = $entry['safi'];
                    if (!$af_list[$entry['bgpPeerIdentifier']][$afi][$safi]) {
                        dbDelete('bgpPeers_cbgp', '`device_id` = ? AND `bgpPeerIdentifier` = ? AND afi=? AND safi=?', array($device['device_id'], $peer['ip'], $afi, $safi));
                    }
                }
            }
            unset($j_afisafi);
            unset($j_prefixes);
            unset($j_bgp);
            unset($j_peerIndexes);
        }

        // Delete removed peers
        $sql = "SELECT * FROM bgpPeers WHERE device_id = '".$device['device_id']."' AND (context_name = '".$device['context_name']."' OR context_name IS NULL)";

        foreach (dbFetchRows($sql) as $entry) {
            unset($exists);
            $i = 0;
            while ($i < count($peerlist) && !isset($exists)) {
                if ($peerlist[$i]['ip'] == $entry['bgpPeerIdentifier']) {
                    $exists = 1;
                }

                $i++;
            }

            if (!isset($exists)) {
                dbDelete('bgpPeers', '`bgpPeer_id` = ?', array($entry['bgpPeer_id']));
                dbDelete('bgpPeers_cbgp', '`bgpPeer_id` = ?', array($entry['bgpPeer_id']));
                echo '-';
            }
        }
        echo "\n";
        unset(
            $device['context_name'],
            $peerlist
        );
    }
    unset(
        $device['context_name'],
        $vrfs_c
    );
}
