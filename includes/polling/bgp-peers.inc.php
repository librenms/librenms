<?php

use Illuminate\Support\Str;
use LibreNMS\Exceptions\InvalidIpException;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Util\IP;

if (\LibreNMS\Config::get('enable_bgp')) {
    $peers = dbFetchRows('SELECT * FROM `bgpPeers` AS B LEFT JOIN `vrfs` AS V ON `B`.`vrf_id` = `V`.`vrf_id` WHERE `B`.`device_id` = ?', [$device['device_id']]);

    if (! empty($peers)) {
        $generic = false;
        if ($device['os'] == 'junos') {
            $peer_data_check = snmpwalk_cache_long_oid($device, 'jnxBgpM2PeerIndex', '.1.3.6.1.4.1.2636.5.1.1.2.1.1.1.14', $peer_data_tmp, 'BGP4-V2-MIB-JUNIPER', 'junos');
        } elseif ($device['os_group'] === 'arista') {
            $peer_data_check = snmpwalk_cache_oid($device, 'aristaBgp4V2PeerRemoteAs', [], 'ARISTA-BGP4V2-MIB');
        } elseif ($device['os'] === 'dell-os10') {
            $peer_data_check = snmpwalk_cache_oid($device, 'os10bgp4V2PeerRemoteAs', [], 'DELLEMC-OS10-BGP4V2-MIB', 'dell'); // practically identical MIB as arista
        } elseif ($device['os'] === 'timos') {
            $peer_data_check = snmpwalk_cache_multi_oid($device, 'tBgpInstanceRowStatus', [], 'TIMETRA-BGP-MIB', 'nokia');
        } elseif ($device['os'] === 'firebrick') {
            $peer_data_check = snmpwalk_cache_multi_oid($device, 'fbBgpPeerTable', [], 'FIREBRICK-BGP-MIB', 'firebrick');
        } elseif ($device['os'] === 'aos7') {
            $peer_data_check = snmpwalk_cache_multi_oid($device, 'alaBgpPeerAS', [], 'ALCATEL-IND1-BGP-MIB', 'aos7');
        } elseif ($device['os'] === 'vrp') {
            $peer_data_check = snmpwalk_cache_multi_oid($device, 'hwBgpPeerEntry', [], 'HUAWEI-BGP-VPN-MIB', 'huawei');
        } elseif ($device['os_group'] == 'cisco') {
            $peer_data_check = snmpwalk_cache_oid($device, 'cbgpPeer2RemoteAs', [], 'CISCO-BGP4-MIB');
        } else {
            $peer_data_check = snmpwalk_cache_oid($device, 'bgpPeerRemoteAs', [], 'BGP4-MIB');
        }
        if (empty($peer_data_check)) {
            $peer_data_check = snmpwalk_cache_oid($device, 'bgpPeerRemoteAs', [], 'BGP4-MIB');
            $generic = true;
        }

        foreach ($peers as $peer) {
            //add context if exist
            $device['context_name'] = $peer['context_name'];
            $vrfOid = $peer['vrf_oid'];
            $vrfId = $peer['vrf_id'];

            try {
                $peer_ip = IP::parse($peer['bgpPeerIdentifier']);

                echo "Checking BGP peer $peer_ip ";

                // --- Collect BGP data ---
                if (count($peer_data_check) > 0) {
                    if ($generic) {
                        echo "\nfallback to default mib";

                        $peer_identifier = $peer['bgpPeerIdentifier'];
                        $mib = 'BGP4-MIB';
                        $oid_map = [
                            'bgpPeerState' => 'bgpPeerState',
                            'bgpPeerAdminStatus' => 'bgpPeerAdminStatus',
                            'bgpPeerInUpdates' => 'bgpPeerInUpdates',
                            'bgpPeerOutUpdates' => 'bgpPeerOutUpdates',
                            'bgpPeerInTotalMessages' => 'bgpPeerInTotalMessages',
                            'bgpPeerOutTotalMessages' => 'bgpPeerOutTotalMessages',
                            'bgpPeerFsmEstablishedTime' => 'bgpPeerFsmEstablishedTime',
                            'bgpPeerInUpdateElapsedTime' => 'bgpPeerInUpdateElapsedTime',
                            'bgpPeerLocalAddr' => 'bgpLocalAddr', // silly db field name
                            'bgpPeerLastError' => 'bgpPeerLastErrorCode',
                        ];
                    } elseif ($device['os'] == 'junos') {
                        if (! isset($junos)) {
                            echo "\nCaching Oids...";

                            foreach ($peer_data_check as $hash => $index) {
                                $peer_ip_snmp = ltrim($index['orig'], '.');
                                $exploded_ip = explode('.', $peer_ip_snmp);
                                if (count($exploded_ip) > 11) {
                                    // ipv6
                                    $tmp_peer_ip = (string) IP::parse(snmp2ipv6($peer_ip_snmp), true);
                                } else {
                                    // ipv4
                                    $tmp_peer_ip = implode('.', array_slice($exploded_ip, -4));
                                }
                                $junos[$tmp_peer_ip]['hash'] = $hash;
                                $junos[$tmp_peer_ip]['index'] = $index['jnxBgpM2PeerIndex'];
                            }
                        }

                        if (! isset($peer_data_tmp)) {
                            $peer_data_tmp = snmpwalk_cache_long_oid($device, 'jnxBgpM2PeerState', '.1.3.6.1.4.1.2636.5.1.1.2.1.1.1.2', $peer_data_tmp, 'BGP4-V2-MIB-JUNIPER', 'junos');
                            $peer_data_tmp = snmpwalk_cache_long_oid($device, 'jnxBgpM2PeerStatus', '.1.3.6.1.4.1.2636.5.1.1.2.1.1.1.3', $peer_data_tmp, 'BGP4-V2-MIB-JUNIPER', 'junos');
                            $peer_data_tmp = snmpwalk_cache_long_oid($device, 'jnxBgpM2PeerInUpdates', '.1.3.6.1.4.1.2636.5.1.1.2.6.1.1.1', $peer_data_tmp, 'BGP4-V2-MIB-JUNIPER', 'junos');
                            $peer_data_tmp = snmpwalk_cache_long_oid($device, 'jnxBgpM2PeerOutUpdates', '.1.3.6.1.4.1.2636.5.1.1.2.6.1.1.2', $peer_data_tmp, 'BGP4-V2-MIB-JUNIPER', 'junos');
                            $peer_data_tmp = snmpwalk_cache_long_oid($device, 'jnxBgpM2PeerInTotalMessages', '.1.3.6.1.4.1.2636.5.1.1.2.6.1.1.3', $peer_data_tmp, 'BGP4-V2-MIB-JUNIPER', 'junos');
                            $peer_data_tmp = snmpwalk_cache_long_oid($device, 'jnxBgpM2PeerOutTotalMessages', '.1.3.6.1.4.1.2636.5.1.1.2.6.1.1.4', $peer_data_tmp, 'BGP4-V2-MIB-JUNIPER', 'junos');
                            $peer_data_tmp = snmpwalk_cache_long_oid($device, 'jnxBgpM2PeerFsmEstablishedTime', '.1.3.6.1.4.1.2636.5.1.1.2.4.1.1.1', $peer_data_tmp, 'BGP4-V2-MIB-JUNIPER', 'junos');
                            $peer_data_tmp = snmpwalk_cache_long_oid($device, 'jnxBgpM2PeerInUpdatesElapsedTime', '.1.3.6.1.4.1.2636.5.1.1.2.4.1.1.2', $peer_data_tmp, 'BGP4-V2-MIB-JUNIPER', 'junos');
                            $peer_data_tmp = snmpwalk_cache_long_oid($device, 'jnxBgpM2PeerLocalAddr', '.1.3.6.1.4.1.2636.5.1.1.2.1.1.1.7', $peer_data_tmp, 'BGP4-V2-MIB-JUNIPER', 'junos');
                            $peer_data_tmp = snmpwalk_cache_long_oid($device, 'jnxBgpM2PeerRemoteAddrType', '.1.3.6.1.4.1.2636.5.1.1.2.1.1.1.10', $peer_data_tmp, 'BGP4-V2-MIB-JUNIPER', 'junos');
                            $peer_data_tmp = snmpwalk_cache_long_oid($device, 'jnxBgpM2PeerLastErrorReceived', '.1.3.6.1.4.1.2636.5.1.1.2.2.1.1.1', $peer_data_tmp, 'BGP4-V2-MIB-JUNIPER', 'junos');
                            $peer_data_tmp = snmpwalk_cache_long_oid($device, 'jnxBgpM2PeerLastErrorReceivedText', '.1.3.6.1.4.1.2636.5.1.1.2.2.1.1.5', $peer_data_tmp, 'BGP4-V2-MIB-JUNIPER', 'junos');
                            d_echo($peer_data_tmp);
                        }

                        $peer_hash = $junos[(string) $peer_ip]['hash'];
                        $peer_data = [];
                        $peer_data['bgpPeerState'] = $peer_data_tmp[$peer_hash]['jnxBgpM2PeerState'];
                        $peer_data['bgpPeerAdminStatus'] = $peer_data_tmp[$peer_hash]['jnxBgpM2PeerStatus'];
                        $peer_data['bgpPeerInUpdates'] = $peer_data_tmp[$peer_hash]['jnxBgpM2PeerInUpdates'];
                        $peer_data['bgpPeerOutUpdates'] = $peer_data_tmp[$peer_hash]['jnxBgpM2PeerOutUpdates'];
                        $peer_data['bgpPeerInTotalMessages'] = $peer_data_tmp[$peer_hash]['jnxBgpM2PeerInTotalMessages'];
                        $peer_data['bgpPeerOutTotalMessages'] = $peer_data_tmp[$peer_hash]['jnxBgpM2PeerOutTotalMessages'];
                        $peer_data['bgpPeerFsmEstablishedTime'] = $peer_data_tmp[$peer_hash]['jnxBgpM2PeerFsmEstablishedTime'];
                        $peer_data['bgpPeerLastErrorText'] = $peer_data_tmp[$peer_hash]['jnxBgpM2PeerLastErrorReceivedText'];

                        $error_data = explode(' ', $peer_data_tmp[$peer_hash]['jnxBgpM2PeerLastErrorReceived']);
                        $peer_data['bgpPeerLastErrorCode'] = intval($error_data[0]);
                        $peer_data['bgpPeerLastErrorSubCode'] = intval($error_data[1]);

                        try {
                            $peer_data['bgpLocalAddr'] = IP::fromHexString($peer_data_tmp[$peer_hash]['jnxBgpM2PeerLocalAddr'])->uncompressed();
                        } catch (InvalidIpException $e) {
                            $peer_data['bgpLocalAddr'] = '';
                        }
                        d_echo("State = {$peer_data['bgpPeerState']} - AdminStatus: {$peer_data['bgpPeerAdminStatus']}\n");
                    } elseif ($device['os'] == 'vrp') {
                        echo "\nCaching Oids VRP...";
                        if (! isset($bgpPeers)) {
                            //if not available, we timeout each time, to be fixed when split
                            $bgpPeersCache = snmpwalk_cache_oid($device, 'hwBgpPeerEntry', [], 'HUAWEI-BGP-VPN-MIB', 'huawei');
                            $bgpPeersStats = snmpwalk_cache_oid($device, 'hwBgpPeerStatisticTable', [], 'HUAWEI-BGP-VPN-MIB', 'huawei', '-OQUbs');
                            $bgp4updates = snmpwalk_cache_oid($device, 'bgpPeerEntry', [], 'BGP4-MIB', 'huawei', '-OQUbs');
                            foreach ($bgpPeersCache as $key => $value) {
                                $oid = explode('.', $key, 5);
                                $vrfInstance = $oid[0];
                                $afi = $oid[1];
                                $safi = $oid[2];
                                $transp = $oid[3];
                                $address = $oid[4];
                                if (strlen($address) > 15) {
                                    $address = IP::fromHexString($address)->compressed();
                                }
                                if (! isset($bgpPeers[$address][$vrfInstance])) {
                                    $bgpPeers[$address][$vrfInstance] = [];
                                }
                                $bgpPeers[$address][$vrfInstance] = array_merge($bgpPeers[$address][$vrfInstance], $value);
                                //d_echo("$vrfInstance -- $address \t-- $value");
                            }
                            foreach ($bgpPeersStats as $key => $value) {
                                $oid = explode('.', $key, 4);
                                $vrfInstance = $oid[1];
                                $address = $oid[3];
                                if ($oid[2] > 4) { //ipv6 so we have to translate
                                    $address = IP::fromSnmpString($oid[3])->compressed();
                                }
                                $bgpPeers[$address][$vrfInstance] = array_merge($bgpPeers[$address][$vrfInstance], $value);
                                //d_echo("$vrfInstance -- $address \t-- $value");
                                //d_echo($value);
                            }
                            //d_echo($bgpPeersCache);
                            //d_echo($bgpPeersStats);
                        }
                        //d_echo($bgpPeers);
                        $address = (string) $peer_ip;
                        $bgpPeer = $bgpPeers[$address];
                        $peer_data = [];
                        if (count(array_keys($bgpPeer)) == 1) { // We have only one vrf with a peer with this IP
                            $vrfInstance = array_keys($bgpPeer)[0];
                            $peer_data['bgpPeerState'] = $bgpPeers[$address][$vrfInstance]['hwBgpPeerState'];
                            $peer_data['bgpPeerAdminStatus'] = $bgpPeers[$address][$vrfInstance]['hwBgpPeerAdminStatus'];
                            $peer_data['bgpPeerInUpdates'] = $bgpPeers[$address][$vrfInstance]['hwBgpPeerInUpdateMsgs'];
                            $peer_data['bgpPeerOutUpdates'] = $bgpPeers[$address][$vrfInstance]['hwBgpPeerOutUpdateMsgs'];
                            $peer_data['bgpPeerInTotalMessages'] = $bgpPeers[$address][$vrfInstance]['hwBgpPeerInTotalMsgs'];
                            $peer_data['bgpPeerOutTotalMessages'] = $bgpPeers[$address][$vrfInstance]['hwBgpPeerOutTotalMsgs'];
                            $peer_data['bgpPeerFsmEstablishedTime'] = $bgpPeers[$address][$vrfInstance]['hwBgpPeerFsmEstablishedTime'];
                            $peer_data['bgpPeerLastError'] = $bgpPeers[$address][$vrfInstance]['hwBgpPeerLastError'];
                        }
                        d_echo("VPN : $vrfInstance for $address :\n");
                        d_echo($peer_data);
                        if (empty($peer_data['bgpPeerInUpdates']) || empty($peer_data['bgpPeerOutUpdates'])) {
                            $peer_data['bgpPeerInUpdates'] = $bgp4updates[$address]['bgpPeerInUpdates'];
                            $peer_data['bgpPeerOutUpdates'] = $bgp4updates[$address]['bgpPeerOutUpdates'];
                        }
                        if (empty($peer_data['bgpPeerInTotalMessages']) || empty($peer_data['bgpPeerOutTotalMessages'])) {
                            $peer_data['bgpPeerInTotalMessages'] = $bgp4updates[$address]['bgpPeerInTotalMessages'];
                            $peer_data['bgpPeerOutTotalMessages'] = $bgp4updates[$address]['bgpPeerOutTotalMessages'];
                        }
                        if (empty($peer_data['bgpPeerState'])) {
                            $peer_data['bgpPeerState'] = $bgp4updates[$address]['bgpPeerState'];
                        }
                        if (empty($peer_data['bgpPeerAdminStatus'])) {
                            $peer_data['bgpPeerAdminStatus'] = $bgp4updates[$address]['bgpPeerAdminStatus'];
                        }
                        if (empty($peer_data['bgpPeerLastError'])) {
                            $peer_data['bgpPeerLastError'] = $bgp4updates[$address]['bgpPeerLastError'];
                        }
                        $error_data = explode(' ', $peer_data['bgpPeerLastError']);
                        $peer_data['bgpPeerLastErrorCode'] = intval($error_data[0]);
                        $peer_data['bgpPeerLastErrorSubCode'] = intval($error_data[1]);
                        unset($peer_data['bgpPeerLastError']);
                    } elseif ($device['os'] == 'timos') {
                        if (! isset($bgpPeers)) {
                            echo "\nCaching Oids...";
                            $bgpPeersCache = snmpwalk_cache_multi_oid($device, 'tBgpPeerNgTable', [], 'TIMETRA-BGP-MIB', 'nokia');
                            $bgpPeersCache = snmpwalk_cache_multi_oid($device, 'tBgpPeerNgOperEntry', $bgpPeersCache, 'TIMETRA-BGP-MIB', 'nokia');
                            foreach ($bgpPeersCache as $key => $value) {
                                $oid = explode('.', $key);
                                $vrfInstance = $oid[0];
                                $address = str_replace($oid[0] . '.' . $oid[1] . '.', '', $key);
                                if (strlen($address) > 15) {
                                    $address = IP::fromHexString($address)->compressed();
                                }
                                $bgpPeers[$vrfInstance][$address] = $value;
                            }
                        }
                        $address = (string) $peer_ip;
                        $tmpTime = $bgpPeers[$vrfOid][$address]['tBgpPeerNgLastChanged'];
                        $tmpTime = explode('.', $tmpTime);
                        $tmpTime = explode(':', $tmpTime[0]);
                        $establishedTime = ($tmpTime[0] * 86400) + ($tmpTime[1] * 3600) + ($tmpTime[2] * 60) + $tmpTime[3];

                        $peer_data = [];
                        $peer_data['bgpPeerState'] = $bgpPeers[$vrfOid][$address]['tBgpPeerNgConnState'];
                        if ($bgpPeers[$vrfOid][$address]['tBgpPeerNgShutdown'] == '1') {
                            $peer_data['bgpPeerAdminStatus'] = 'adminShutdown';
                        } else {
                            $peer_data['bgpPeerAdminStatus'] = $bgpPeers[$vrfOid][$address]['tBgpPeerNgOperLastEvent'];
                        }
                        $peer_data['bgpPeerInTotalMessages'] = $bgpPeers[$vrfOid][$address]['tBgpPeerNgOperMsgOctetsRcvd'];  // That are actually only octets available,
                        $peer_data['bgpPeerOutTotalMessages'] = $bgpPeers[$vrfOid][$address]['tBgpPeerNgOperMsgOctetsSent']; // not messages
                        $peer_data['bgpPeerFsmEstablishedTime'] = $establishedTime;
                    } elseif ($device['os'] == 'firebrick') {
                        // ToDo, It seems that bgpPeer(In|Out)Updates and bgpPeerInUpdateElapsedTime are actually not available over SNMP
                        $bgpPeer = null;
                        foreach ($peer_data_check as $key => $value) {
                            $oid = explode('.', $key);
                            $protocol = $oid[0];
                            $address = str_replace($oid[0] . '.', '', $key);
                            if (strlen($address) > 15) {
                                $address = IP::fromHexString($address)->compressed();
                            }

                            // Some older Firebrick software versions don't have this field
                            if (isset($value['fbBgpPeerLocalAddress'])) {
                                $peer_data['bgpLocalAddr'] = IP::fromHexString($value['fbBgpPeerLocalAddress'])->uncompressed();
                            }

                            if ($address == $peer_ip) {
                                switch ($value['fbBgpPeerState']) {
                                    case 0:
                                        $peer_data['bgpPeerState'] = 'idle';
                                        break;
                                    case 1:
                                    case 2:
                                        $peer_data['bgpPeerState'] = 'active';
                                        break;
                                    case 3:
                                        $peer_data['bgpPeerState'] = 'opensent';
                                        break;
                                    case 4:
                                        $peer_data['bgpPeerState'] = 'openconfig';
                                        break;
                                    case 5:
                                        $peer_data['bgpPeerState'] = 'established';
                                        break;
                                    case 6:
                                        $peer_data['bgpPeerState'] = 'closed';
                                        break;
                                    case 7:
                                        $peer_data['bgpPeerState'] = 'free';
                                        break;
                                }
                                $peer_data['bgpPeerRemoteAddr'] = $address;
                                $peer_data['bgpPeerRemoteAs'] = $value['fbBgpPeerRemoteAS'];
                                $peer_data['bgpPeerAdminStatus'] = 'start';
                                $peer_data['bgpPeerInUpdates'] = 0;
                                $peer_data['bgpPeerOutUpdates'] = 0;
                                $peer_data['bgpPeerInTotalMessages'] = 0;
                                $peer_data['bgpPeerOutTotalMessages'] = 0;
                                $peer_data['bgpPeerFsmEstablishedTime'] = 0;
                                break;
                            }
                        }
                    } else {
                        $bgp_peer_ident = $peer_ip->toSnmpIndex();
                        $ip_ver = $peer_ip->getFamily();
                        if ($ip_ver == 'ipv6') {
                            $ip_type = 2;
                            $ip_len = 16;
                        } else {
                            $ip_type = 1;
                            $ip_len = 4;
                        }

                        if ($device['os_group'] === 'arista') {
                            $peer_identifier = '1.' . $ip_type . '.' . $ip_len . '.' . $bgp_peer_ident;
                            $mib = 'ARISTA-BGP4V2-MIB';
                            $oid_map = [
                                'aristaBgp4V2PeerState' => 'bgpPeerState',
                                'aristaBgp4V2PeerAdminStatus' => 'bgpPeerAdminStatus',
                                'aristaBgp4V2PeerInUpdates' => 'bgpPeerInUpdates',
                                'aristaBgp4V2PeerOutUpdates' => 'bgpPeerOutUpdates',
                                'aristaBgp4V2PeerInTotalMessages' => 'bgpPeerInTotalMessages',
                                'aristaBgp4V2PeerOutTotalMessages' => 'bgpPeerOutTotalMessages',
                                'aristaBgp4V2PeerFsmEstablishedTime' => 'bgpPeerFsmEstablishedTime',
                                'aristaBgp4V2PeerInUpdatesElapsedTime' => 'bgpPeerInUpdateElapsedTime',
                                'aristaBgp4V2PeerLocalAddr' => 'bgpLocalAddr',
                                'aristaBgp4V2PeerDescription' => 'bgpPeerDescr',
                                'aristaBgp4V2PeerLastErrorCodeReceived' => 'bgpPeerLastErrorCode',
                                'aristaBgp4V2PeerLastErrorSubCodeReceived' => 'bgpPeerLastErrorSubCode',
                                'aristaBgp4V2PeerLastErrorReceivedText' => 'bgpPeerLastErrorText',
                            ];
                        } elseif ($device['os'] == 'dell-os10') {
                            $peer_identifier = '1.' . $ip_type . '.' . $ip_len . '.' . $bgp_peer_ident;
                            $mib = 'DELLEMC-OS10-BGP4V2-MIB';
                            $oid_map = [
                                'os10bgp4V2PeerState' => 'bgpPeerState',
                                'os10bgp4V2PeerAdminStatus' => 'bgpPeerAdminStatus',
                                'os10bgp4V2PeerInUpdates' => 'bgpPeerInUpdates',
                                'os10bgp4V2PeerOutUpdates' => 'bgpPeerOutUpdates',
                                'os10bgp4V2PeerInTotalMessages' => 'bgpPeerInTotalMessages',
                                'os10bgp4V2PeerOutTotalMessages' => 'bgpPeerOutTotalMessages',
                                'os10bgp4V2PeerFsmEstablishedTime' => 'bgpPeerFsmEstablishedTime',
                                'os10bgp4V2PeerInUpdatesElapsedTime' => 'bgpPeerInUpdateElapsedTime',
                                'os10bgp4V2PeerLocalAddr' => 'bgpLocalAddr',
                                'os10bgp4V2PeerDescription' => 'bgpPeerDescr',
                                'os10bgp4V2PeerLastErrorCodeReceived' => 'bgpPeerLastErrorCode',
                                'os10bgp4V2PeerLastErrorSubCodeReceived' => 'bgpPeerLastErrorSubCode',
                                'os10bgp4V2PeerLastErrorReceivedText' => 'bgpPeerLastErrorText',
                            ];
                        } elseif ($device['os'] === 'aos7') {
                            $peer_identifier = $peer['bgpPeerIdentifier'];
                            $mib = 'BGP4-MIB';
                            $oid_map = [
                                'bgpPeerState' => 'bgpPeerState',
                                'bgpPeerAdminStatus' => 'bgpPeerAdminStatus',
                                'bgpPeerInUpdates' => 'bgpPeerInUpdates',
                                'bgpPeerOutUpdates' => 'bgpPeerOutUpdates',
                                'bgpPeerInTotalMessages' => 'bgpPeerInTotalMessages',
                                'bgpPeerOutTotalMessages' => 'bgpPeerOutTotalMessages',
                                'bgpPeerFsmEstablishedTime' => 'bgpPeerFsmEstablishedTime',
                                'bgpPeerInUpdateElapsedTime' => 'bgpPeerInUpdateElapsedTime',
                                'bgpPeerLocalAddr' => 'bgpLocalAddr',
                                'bgpPeerLastError' => 'bgpPeerLastErrorCode',
                            ];

                            $peer_data = [];
                            $al_descr = snmpwalk_cache_multi_oid($device, 'alaBgpPeerName', $al_descr, 'ALCATEL-IND1-BGP-MIB', 'aos7', '-OQUs');
                            $peer_data['bgpPeerDescr'] = $al_descr[$peer_identifier]['alaBgpPeerName'];
                        } elseif ($device['os_group'] == 'cisco') {
                            $peer_identifier = $ip_type . '.' . $ip_len . '.' . $bgp_peer_ident;
                            $mib = 'CISCO-BGP4-MIB';
                            $oid_map = [
                                'cbgpPeer2State' => 'bgpPeerState',
                                'cbgpPeer2AdminStatus' => 'bgpPeerAdminStatus',
                                'cbgpPeer2InUpdates' => 'bgpPeerInUpdates',
                                'cbgpPeer2OutUpdates' => 'bgpPeerOutUpdates',
                                'cbgpPeer2InTotalMessages' => 'bgpPeerInTotalMessages',
                                'cbgpPeer2OutTotalMessages' => 'bgpPeerOutTotalMessages',
                                'cbgpPeer2FsmEstablishedTime' => 'bgpPeerFsmEstablishedTime',
                                'cbgpPeer2InUpdateElapsedTime' => 'bgpPeerInUpdateElapsedTime',
                                'cbgpPeer2LocalAddr' => 'bgpLocalAddr',
                                'cbgpPeer2LastError' => 'bgpPeerLastErrorCode',
                                'cbgpPeer2LastErrorTxt' => 'bgpPeerLastErrorText',
                            ];
                        } else {
                            $peer_identifier = $peer['bgpPeerIdentifier'];
                            $mib = 'BGP4-MIB';
                            $oid_map = [
                                'bgpPeerState' => 'bgpPeerState',
                                'bgpPeerAdminStatus' => 'bgpPeerAdminStatus',
                                'bgpPeerInUpdates' => 'bgpPeerInUpdates',
                                'bgpPeerOutUpdates' => 'bgpPeerOutUpdates',
                                'bgpPeerInTotalMessages' => 'bgpPeerInTotalMessages',
                                'bgpPeerOutTotalMessages' => 'bgpPeerOutTotalMessages',
                                'bgpPeerFsmEstablishedTime' => 'bgpPeerFsmEstablishedTime',
                                'bgpPeerInUpdateElapsedTime' => 'bgpPeerInUpdateElapsedTime',
                                'bgpPeerLocalAddr' => 'bgpLocalAddr', // silly db field name
                                'bgpPeerLastError' => 'bgpPeerLastErrorCode',
                            ];
                        }
                    }
                }

                // --- Build peer data if it is not already filled in ---
                if (empty($peer_data) && isset($peer_identifier, $oid_map, $mib)) {
                    echo "Fetching $mib data... \n";

                    $get_oids = array_map(function ($oid) use ($peer_identifier) {
                        return "$oid.$peer_identifier";
                    }, array_keys($oid_map));
                    $peer_data_raw = snmp_get_multi($device, $get_oids, '-OQUs', $mib);
                    $peer_data_raw = reset($peer_data_raw);  // get the first element of the array

                    $peer_data = [];

                    foreach ($oid_map as $source => $target) {
                        $v = isset($peer_data_raw[$source]) ? $peer_data_raw[$source] : '';

                        if (Str::contains($source, 'LocalAddr')) {
                            try {
                                $v = IP::fromHexString($v)->uncompressed();
                            } catch (InvalidIpException $e) {
                                // if parsing fails, leave the data as-is
                            }
                        }
                        $peer_data[$target] = $v;
                    }
                    if (strpos($peer_data['bgpPeerLastErrorCode'], ' ')) {
                        // Some device return both Code and SubCode in the same snmp field, we need to split it
                        $splitted_codes = explode(' ', $peer_data['bgpPeerLastErrorCode']);
                        $error_code = intval($splitted_codes[0]);
                        $error_subcode = intval($splitted_codes[1]);
                        $peer_data['bgpPeerLastErrorCode'] = $error_code;
                        $peer_data['bgpPeerLastErrorSubCode'] = $error_subcode;
                    }
                }

                d_echo($peer_data);
            } catch (InvalidIpException $e) {
                // ignore
            }
            // --- Send event log notices ---
            if ($peer_data['bgpPeerFsmEstablishedTime']) {
                if (! (is_array(\LibreNMS\Config::get('alerts.bgp.whitelist'))
                        && ! in_array($peer['bgpPeerRemoteAs'], \LibreNMS\Config::get('alerts.bgp.whitelist')))
                    && ($peer_data['bgpPeerFsmEstablishedTime'] < $peer['bgpPeerFsmEstablishedTime']
                        || $peer_data['bgpPeerState'] != $peer['bgpPeerState'])
                ) {
                    if ($peer['bgpPeerState'] == $peer_data['bgpPeerState']) {
                        log_event('BGP Session Flap: ' . $peer['bgpPeerIdentifier'] . ' (AS' . $peer['bgpPeerRemoteAs'] . '), last error: ' . describe_bgp_error_code($peer['bgpPeerLastErrorCode'], $peer['bgpPeerLastErrorSubCode']), $device, 'bgpPeer', 4, $peer_ip);
                    } elseif ($peer_data['bgpPeerState'] == 'established') {
                        log_event('BGP Session Up: ' . $peer['bgpPeerIdentifier'] . ' (AS' . $peer['bgpPeerRemoteAs'] . ')', $device, 'bgpPeer', 1, $peer_ip);
                    } elseif ($peer['bgpPeerState'] == 'established') {
                        log_event('BGP Session Down: ' . $peer['bgpPeerIdentifier'] . ' (AS' . $peer['bgpPeerRemoteAs'] . '), last error: ' . describe_bgp_error_code($peer['bgpPeerLastErrorCode'], $peer['bgpPeerLastErrorSubCode']), $device, 'bgpPeer', 5, $peer_ip);
                    }
                }
            }

            // --- Update rrd data ---
            $peer_rrd_name = \LibreNMS\Data\Store\Rrd::safeName('bgp-' . $peer['bgpPeerIdentifier']);
            $peer_rrd_def = RrdDefinition::make()
                ->addDataset('bgpPeerOutUpdates', 'COUNTER', null, 100000000000)
                ->addDataset('bgpPeerInUpdates', 'COUNTER', null, 100000000000)
                ->addDataset('bgpPeerOutTotal', 'COUNTER', null, 100000000000)
                ->addDataset('bgpPeerInTotal', 'COUNTER', null, 100000000000)
                ->addDataset('bgpPeerEstablished', 'GAUGE', 0);
            // Validate data
            $peer_data['bgpPeerFsmEstablishedTime'] = set_numeric($peer_data['bgpPeerFsmEstablishedTime']);
            $peer_data['bgpPeerInUpdates'] = set_numeric($peer_data['bgpPeerInUpdates']);
            $peer_data['bgpPeerOutUpdates'] = set_numeric($peer_data['bgpPeerOutUpdates']);

            $fields = [
                'bgpPeerOutUpdates' => $peer_data['bgpPeerOutUpdates'],
                'bgpPeerInUpdates' => $peer_data['bgpPeerInUpdates'],
                'bgpPeerOutTotal' => $peer_data['bgpPeerOutTotalMessages'],
                'bgpPeerInTotal' => $peer_data['bgpPeerInTotalMessages'],
                'bgpPeerEstablished' => $peer_data['bgpPeerFsmEstablishedTime'],
            ];

            $tags = [
                'bgpPeerIdentifier' => $peer['bgpPeerIdentifier'],
                'rrd_name' => $peer_rrd_name,
                'rrd_def' => $peer_rrd_def,
            ];
            data_update($device, 'bgp', $tags, $fields);

            // --- Update Database data ---
            $peer['update'] = array_diff_assoc($peer_data, $peer);
            unset($peer_data);

            if ($peer['update']) {
                if ($vrfId) {
                    dbUpdate($peer['update'], 'bgpPeers', '`device_id` = ? AND `bgpPeerIdentifier` = ? AND `vrf_id` = ?', [$device['device_id'], $peer['bgpPeerIdentifier'], $vrfId]);
                } else {
                    dbUpdate($peer['update'], 'bgpPeers', '`device_id` = ? AND `bgpPeerIdentifier` = ?', [$device['device_id'], $peer['bgpPeerIdentifier']]);
                }
            }

            // --- Populate cbgp data ---
            if ($device['os_group'] == 'vrp' || $device['os_group'] == 'cisco' || $device['os'] == 'junos' || $device['os'] == 'aos7' || $device['os_group'] === 'arista' || $device['os'] == 'dell-os10' || $device['os'] == 'firebrick') {
                // Poll each AFI/SAFI for this peer (using CISCO-BGP4-MIB or BGP4-V2-JUNIPER MIB)
                $peer_afis = dbFetchRows('SELECT * FROM bgpPeers_cbgp WHERE `device_id` = ? AND bgpPeerIdentifier = ?', [$device['device_id'], $peer['bgpPeerIdentifier']]);
                foreach ($peer_afis as $peer_afi) {
                    $afi = $peer_afi['afi'];
                    $safi = $peer_afi['safi'];
                    d_echo("$afi $safi\n");
                    if ($device['os_group'] == 'cisco') {
                        $bgp_peer_ident = $peer_ip->toSnmpIndex();

                        $ip_ver = $peer_ip->getFamily();
                        if ($ip_ver == 'ipv6') {
                            $ip_type = 2;
                            $ip_len = 16;
                        } else {
                            $ip_type = 1;
                            $ip_len = 4;
                        }

                        $ip_cast = 1;
                        if ($peer_afi['safi'] == 'multicast') {
                            $ip_cast = 2;
                        } elseif ($peer_afi['safi'] == 'unicastAndMulticast') {
                            $ip_cast = 3;
                        } elseif ($peer_afi['safi'] == 'vpn') {
                            $ip_cast = 128;
                        }

                        $check = snmp_get($device, 'cbgpPeer2AcceptedPrefixes.' . $ip_type . '.' . $ip_len . '.' . $bgp_peer_ident . '.' . $ip_type . '.' . $ip_cast, '', 'CISCO-BGP4-MIB');

                        if (! empty($check)) {
                            $cgp_peer_identifier = $ip_type . '.' . $ip_len . '.' . $bgp_peer_ident . '.' . $ip_type . '.' . $ip_cast;
                            $cbgp2_oids = [
                                'cbgpPeer2AcceptedPrefixes.' . $cgp_peer_identifier,
                                'cbgpPeer2DeniedPrefixes.' . $cgp_peer_identifier,
                                'cbgpPeer2PrefixAdminLimit.' . $cgp_peer_identifier,
                                'cbgpPeer2PrefixThreshold.' . $cgp_peer_identifier,
                                'cbgpPeer2PrefixClearThreshold.' . $cgp_peer_identifier,
                                'cbgpPeer2AdvertisedPrefixes.' . $cgp_peer_identifier,
                                'cbgpPeer2SuppressedPrefixes.' . $cgp_peer_identifier,
                                'cbgpPeer2WithdrawnPrefixes.' . $cgp_peer_identifier,
                            ];
                            $cbgp_data_tmp = snmp_get_multi($device, $cbgp2_oids, '-OQUs', 'CISCO-BGP4-MIB');
                            $ident = "$ip_ver.\"" . $peer['bgpPeerIdentifier'] . '"' . '.' . $ip_type . '.' . $ip_cast;

                            $key = key($cbgp_data_tmp); // get key of item
                            $cbgp_data = [
                                'cbgpPeerAcceptedPrefixes' => $cbgp_data_tmp[$key]['cbgpPeer2AcceptedPrefixes'],
                                'cbgpPeerDeniedPrefixes' => $cbgp_data_tmp[$key]['cbgpPeer2DeniedPrefixes'],
                                'cbgpPeerPrefixAdminLimit' => $cbgp_data_tmp[$key]['cbgpPeer2PrefixAdminLimit'],
                                'cbgpPeerPrefixThreshold' => $cbgp_data_tmp[$key]['cbgpPeer2PrefixThreshold'],
                                'cbgpPeerPrefixClearThreshold' => $cbgp_data_tmp[$key]['cbgpPeer2PrefixClearThreshold'],
                                'cbgpPeerAdvertisedPrefixes' => $cbgp_data_tmp[$key]['cbgpPeer2AdvertisedPrefixes'],
                                'cbgpPeerSuppressedPrefixes' => $cbgp_data_tmp[$key]['cbgpPeer2SuppressedPrefixes'],
                                'cbgpPeerWithdrawnPrefixes' => $cbgp_data_tmp[$key]['cbgpPeer2WithdrawnPrefixes'],
                            ];
                        } else {
                            $cbgp_oids = [
                                'cbgpPeerAcceptedPrefixes.' . $peer['bgpPeerIdentifier'] . ".$afi.$safi",
                                'cbgpPeerDeniedPrefixes.' . $peer['bgpPeerIdentifier'] . ".$afi.$safi",
                                'cbgpPeerPrefixAdminLimit.' . $peer['bgpPeerIdentifier'] . ".$afi.$safi",
                                'cbgpPeerPrefixThreshold.' . $peer['bgpPeerIdentifier'] . ".$afi.$safi",
                                'cbgpPeerPrefixClearThreshold.' . $peer['bgpPeerIdentifier'] . ".$afi.$safi",
                                'cbgpPeerAdvertisedPrefixes.' . $peer['bgpPeerIdentifier'] . ".$afi.$safi",
                                'cbgpPeerSuppressedPrefixes.' . $peer['bgpPeerIdentifier'] . ".$afi.$safi",
                                'cbgpPeerWithdrawnPrefixes.' . $peer['bgpPeerIdentifier'] . ".$afi.$safi",
                            ];

                            $cbgp_data = snmp_get_multi($device, $cbgp_oids, '-OUQs', 'CISCO-BGP4-MIB');
                            $cbgp_data = reset($cbgp_data); // get first entry
                        }
                        d_echo($cbgp_data);

                        $cbgpPeerAcceptedPrefixes = $cbgp_data['cbgpPeerAcceptedPrefixes'];
                        $cbgpPeerDeniedPrefixes = $cbgp_data['cbgpPeerDeniedPrefixes'];
                        $cbgpPeerPrefixAdminLimit = $cbgp_data['cbgpPeerPrefixAdminLimit'];
                        $cbgpPeerPrefixThreshold = $cbgp_data['cbgpPeerPrefixThreshold'];
                        $cbgpPeerPrefixClearThreshold = $cbgp_data['cbgpPeerPrefixClearThreshold'];
                        $cbgpPeerAdvertisedPrefixes = $cbgp_data['cbgpPeerAdvertisedPrefixes'];
                        $cbgpPeerSuppressedPrefixes = $cbgp_data['cbgpPeerSuppressedPrefixes'];
                        $cbgpPeerWithdrawnPrefixes = $cbgp_data['cbgpPeerWithdrawnPrefixes'];
                        unset($cbgp_data);
                    } //end if

                    if ($device['os'] == 'junos') {
                        $afis['ipv4'] = 1;
                        $afis['ipv6'] = 2;
                        $afis['l2vpn'] = 25;
                        $safis['unicast'] = 1;
                        $safis['multicast'] = 2;
                        $safis['unicastAndMulticast'] = 3;
                        $safis['labeledUnicast'] = 4;
                        $safis['mvpn'] = 5;
                        $safis['vpls'] = 65;
                        $safis['evpn'] = 70;
                        $safis['vpn'] = 128;
                        $safis['rtfilter'] = 132;
                        $safis['flow'] = 133;

                        if (! isset($j_prefixes)) {
                            $j_prefixes = snmpwalk_cache_multi_oid($device, 'jnxBgpM2PrefixInPrefixesAccepted', $j_prefixes, 'BGP4-V2-MIB-JUNIPER', 'junos', '-OQnU');
                            $j_prefixes = snmpwalk_cache_multi_oid($device, 'jnxBgpM2PrefixInPrefixesRejected', $j_prefixes, 'BGP4-V2-MIB-JUNIPER', 'junos', '-OQnU');
                            $j_prefixes = snmpwalk_cache_multi_oid($device, 'jnxBgpM2PrefixOutPrefixes', $j_prefixes, 'BGP4-V2-MIB-JUNIPER', 'junos', '-OQnU');
                            d_echo($j_prefixes);
                        }

                        $cbgpPeerAcceptedPrefixes = array_shift($j_prefixes['1.3.6.1.4.1.2636.5.1.1.2.6.2.1.8.' . $junos[(string) $peer_ip]['index'] . ".$afis[$afi]." . $safis[$safi]]);
                        $cbgpPeerDeniedPrefixes = array_shift($j_prefixes['1.3.6.1.4.1.2636.5.1.1.2.6.2.1.9.' . $junos[(string) $peer_ip]['index'] . ".$afis[$afi]." . $safis[$safi]]);
                        $cbgpPeerAdvertisedPrefixes = array_shift($j_prefixes['1.3.6.1.4.1.2636.5.1.1.2.6.2.1.10.' . $junos[(string) $peer_ip]['index'] . ".$afis[$afi]." . $safis[$safi]]);
                    }//end if

                    if ($device['os_group'] === 'arista') {
                        $safis['unicast'] = 1;
                        $safis['multicast'] = 2;
                        $afis['ipv4'] = 1;
                        $afis['ipv6'] = 2;
                        if (preg_match('/:/', $peer['bgpPeerIdentifier'])) {
                            $tmp_peer = str_replace(':', '', $peer['bgpPeerIdentifier']);
                            $tmp_peer = preg_replace('/([\w\d]{2})/', '\1:', $tmp_peer);
                            $tmp_peer = rtrim($tmp_peer, ':');
                        } else {
                            $tmp_peer = $peer['bgpPeerIdentifier'];
                        }
                        $a_prefixes = snmpwalk_cache_multi_oid($device, 'aristaBgp4V2PrefixInPrefixesAccepted', $a_prefixes, 'ARISTA-BGP4V2-MIB', null, '-OQUs');
                        $out_prefixes = snmpwalk_cache_multi_oid($device, 'aristaBgp4V2PrefixOutPrefixes', $out_prefixes, 'ARISTA-BGP4V2-MIB', null, '-OQUs');

                        $cbgpPeerAcceptedPrefixes = $a_prefixes["1.$afi.$tmp_peer.$afi.$safi"]['aristaBgp4V2PrefixInPrefixesAccepted'];
                        $cbgpPeerAdvertisedPrefixes = $out_prefixes["1.$afi.$tmp_peer.$afi.$safi"]['aristaBgp4V2PrefixOutPrefixes'];
                    }

                    if ($device['os'] == 'dell-os10') {
                        $safis['unicast'] = 1;
                        $safis['multicast'] = 2;
                        $afis['ipv4'] = 1;
                        $afis['ipv6'] = 2;
                        if (preg_match('/:/', $peer['bgpPeerIdentifier'])) {
                            $tmp_peer = str_replace(':', '', $peer['bgpPeerIdentifier']);
                            $tmp_peer = preg_replace('/([\w\d]{2})/', '\1:', $tmp_peer);
                            $tmp_peer = rtrim($tmp_peer, ':');
                        } else {
                            $tmp_peer = $peer['bgpPeerIdentifier'];
                        }
                        $a_prefixes = snmpwalk_cache_multi_oid($device, 'os10bgp4V2PrefixInPrefixesAccepted', $a_prefixes, 'DELLEMC-OS10-BGP4V2-MIB', null, '-OQUs');
                        $out_prefixes = snmpwalk_cache_multi_oid($device, 'os10bgp4V2PrefixOutPrefixes', $out_prefixes, 'DELLEMC-OS10-BGP4V2-MIB', null, '-OQUs');

                        $cbgpPeerAcceptedPrefixes = $a_prefixes["1.$afi.$tmp_peer.$afi.$safi"]['os10bgp4V2PrefixInPrefixesAccepted'];
                        $cbgpPeerAdvertisedPrefixes = $out_prefixes["1.$afi.$tmp_peer.$afi.$safi"]['os10bgp4V2PrefixOutPrefixes'];
                    }

                    if ($device['os'] === 'aos7') {
                        $tmp_peer = $peer['bgpPeerIdentifier'];
                        $al_prefixes = snmpwalk_cache_multi_oid($device, 'alaBgpPeerRcvdPrefixes', $al_prefixes, 'ALCATEL-IND1-BGP-MIB', 'aos7', '-OQUs');
                        $cbgpPeerAcceptedPrefixes = $al_prefixes[$tmp_peer]['alaBgpPeerRcvdPrefixes'];
                    }

                    if ($device['os_group'] === 'vrp') {
                        $vrpPrefixes = snmpwalk_cache_multi_oid($device, 'hwBgpPeerPrefixRcvCounter', $vrpPrefixes, 'HUAWEI-BGP-VPN-MIB', null, '-OQUs');
                        $vrpPrefixes = snmpwalk_cache_multi_oid($device, 'hwBgpPeerPrefixAdvCounter', $vrpPrefixes, 'HUAWEI-BGP-VPN-MIB', null, '-OQUs');

                        // only works in global routing table, as the vpnInstanceId is not available
                        // for now in the VRF discovery of VRP devices
                        $key4 = $vrfInstance . '.' . $afi . '.' . $safi . '.ipv4.' . $peer['bgpPeerIdentifier'];
                        $key6 = $vrfInstance . '.' . $afi . '.' . $safi . '.ipv6.' . $peer['bgpPeerIdentifier'];

                        if (isset($vrpPrefixes[$key4])) {
                            $cbgpPeerAcceptedPrefixes = $vrpPrefixes[$key4]['hwBgpPeerPrefixRcvCounter'];
                            $cbgpPeerAdvertisedPrefixes = $vrpPrefixes[$key4]['hwBgpPeerPrefixAdvCounter'];
                        }
                        if (isset($vrpPrefixes[$key6])) {
                            $cbgpPeerAcceptedPrefixes = $vrpPrefixes[$key6]['hwBgpPeerPrefixRcvCounter'];
                            $cbgpPeerAdvertisedPrefixes = $vrpPrefixes[$key6]['hwBgpPeerPrefixAdvCounter'];
                        }
                    }

                    if ($devices['os'] == 'firebrick') {
                        foreach ($peer_data_check as $key => $value) {
                            $oid = explode('.', $key);
                            $protocol = $oid[0];
                            $address = str_replace($oid[0] . '.', '', $key);
                            if (strlen($address) > 15) {
                                $address = IP::fromHexString($address)->compressed();
                            }
                            if ($address == $peer['bgpPeerIdentifier']) {
                                $cbgpPeerAcceptedPrefixes = $value['fbBgpPeerReceivedIpv4Prefixes'] + $value['fbBgpPeerReceivedIpv6Prefixes'];
                                $cbgpPeerAdvertisedPrefixes = $value['fbBgpPeerExported'];
                                break;
                            }
                        }
                    }

                    // Validate data
                    $cbgpPeerAcceptedPrefixes = set_numeric($cbgpPeerAcceptedPrefixes);
                    $cbgpPeerDeniedPrefixes = set_numeric($cbgpPeerDeniedPrefixes);
                    $cbgpPeerPrefixAdminLimit = set_numeric($cbgpPeerPrefixAdminLimit);
                    $cbgpPeerPrefixThreshold = set_numeric($cbgpPeerPrefixThreshold);
                    $cbgpPeerPrefixClearThreshold = set_numeric($cbgpPeerPrefixClearThreshold);
                    $cbgpPeerAdvertisedPrefixes = set_numeric($cbgpPeerAdvertisedPrefixes);
                    $cbgpPeerSuppressedPrefixes = set_numeric($cbgpPeerSuppressedPrefixes);
                    $cbgpPeerWithdrawnPrefixes = set_numeric($cbgpPeerWithdrawnPrefixes);

                    $cbgpPeers_cbgp_fields = [
                        'AcceptedPrefixes'     => $cbgpPeerAcceptedPrefixes,
                        'DeniedPrefixes'       => $cbgpPeerDeniedPrefixes,
                        'PrefixAdminLimit'     => $cbgpPeerPrefixAdminLimit,
                        'PrefixThreshold'      => $cbgpPeerPrefixThreshold,
                        'PrefixClearThreshold' => $cbgpPeerPrefixClearThreshold,
                        'AdvertisedPrefixes'   => $cbgpPeerAdvertisedPrefixes,
                        'SuppressedPrefixes'   => $cbgpPeerSuppressedPrefixes,
                        'WithdrawnPrefixes'    => $cbgpPeerWithdrawnPrefixes,
                    ];

                    foreach ($cbgpPeers_cbgp_fields as $field => $value) {
                        if ($peer_afi[$field] != $value) {
                            $peer['c_update'][$field] = $value;
                        }
                    }

                    $oids = [
                        'AcceptedPrefixes',
                        'DeniedPrefixes',
                        'AdvertisedPrefixes',
                        'SuppressedPrefixes',
                        'WithdrawnPrefixes',
                    ];

                    foreach ($oids as $oid) {
                        $tmp_prev = set_numeric($peer_afi[$oid]);
                        $tmp_delta = $cbgpPeers_cbgp_fields[$oid] - $tmp_prev;
                        if ($peer_afi[$oid . '_delta'] != $tmp_delta) {
                            $peer['c_update'][$oid . '_delta'] = $tmp_delta;
                        }
                        if ($peer_afi[$oid . '_prev'] != $tmp_prev) {
                            $peer['c_update'][$oid . '_prev'] = $tmp_prev;
                        }
                    }

                    if ($peer['c_update']) {
                        dbUpdate(
                            $peer['c_update'],
                            'bgpPeers_cbgp',
                            '`device_id` = ? AND bgpPeerIdentifier = ? AND afi = ? AND safi = ?',
                            [$device['device_id'], $peer['bgpPeerIdentifier'], $afi, $safi]
                        );
                    }

                    $cbgp_rrd_name = \LibreNMS\Data\Store\Rrd::safeName('cbgp-' . $peer['bgpPeerIdentifier'] . ".$afi.$safi");
                    $cbgp_rrd_def = RrdDefinition::make()
                        ->addDataset('AcceptedPrefixes', 'GAUGE', null, 100000000000)
                        ->addDataset('DeniedPrefixes', 'GAUGE', null, 100000000000)
                        ->addDataset('AdvertisedPrefixes', 'GAUGE', null, 100000000000)
                        ->addDataset('SuppressedPrefixes', 'GAUGE', null, 100000000000)
                        ->addDataset('WithdrawnPrefixes', 'GAUGE', null, 100000000000);

                    $fields = [
                        'AcceptedPrefixes'    => $cbgpPeerAcceptedPrefixes,
                        'DeniedPrefixes'      => $cbgpPeerDeniedPrefixes,
                        'AdvertisedPrefixes'  => $cbgpPeerAdvertisedPrefixes,
                        'SuppressedPrefixes'  => $cbgpPeerSuppressedPrefixes,
                        'WithdrawnPrefixes'   => $cbgpPeerWithdrawnPrefixes,
                    ];

                    $tags = [
                        'bgpPeerIdentifier' => $peer['bgpPeerIdentifier'],
                        'afi' => $afi,
                        'safi' => $safi,
                        'rrd_name' => $cbgp_rrd_name,
                        'rrd_def' => $cbgp_rrd_def,
                    ];
                    data_update($device, 'cbgp', $tags, $fields);
                } //end foreach
            } //end if
            echo "\n";
        } //end foreach
    } //end if
} //end if

unset($peers, $peer_data_tmp, $j_prefixes);
