<?php

use App\Models\Eventlog;
use Illuminate\Support\Str;
use LibreNMS\Enum\Severity;
use LibreNMS\Exceptions\InvalidIpException;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Util\IP;
use LibreNMS\Util\Oid;

$peers = dbFetchRows('SELECT * FROM `bgpPeers` AS B LEFT JOIN `vrfs` AS V ON `B`.`vrf_id` = `V`.`vrf_id` WHERE `B`.`device_id` = ?', [$device['device_id']]);

if (! empty($peers)) {
    $intFields = [
        'bgpPeerRemoteAs',
        'bgpPeerLastErrorCode',
        'bgpPeerLastErrorSubCode',
        'bgpPeerIface',
        'bgpPeerInUpdates',
        'bgpPeerOutUpdates',
        'bgpPeerInTotalMessages',
        'bgpPeerOutTotalMessages',
        'bgpPeerOutFsmEstablishedTime',
        'bgpPeerInUpdateElapsedTime',
    ];

    $generic = false;
    if ($device['os'] == 'junos') {
        $peer_data_check = SnmpQuery::mibDir('junos')
            ->enumStrings()
            ->numericIndex()
            ->abortOnFailure()
            ->walk([
                'BGP4-V2-MIB-JUNIPER::jnxBgpM2PeerIndex',
                'BGP4-V2-MIB-JUNIPER::jnxBgpM2PeerState',
                'BGP4-V2-MIB-JUNIPER::jnxBgpM2PeerStatus',
                'BGP4-V2-MIB-JUNIPER::jnxBgpM2PeerInUpdates',
                'BGP4-V2-MIB-JUNIPER::jnxBgpM2PeerOutUpdates',
                'BGP4-V2-MIB-JUNIPER::jnxBgpM2PeerInTotalMessages',
                'BGP4-V2-MIB-JUNIPER::jnxBgpM2PeerOutTotalMessages',
                'BGP4-V2-MIB-JUNIPER::jnxBgpM2PeerFsmEstablishedTime',
                'BGP4-V2-MIB-JUNIPER::jnxBgpM2PeerLocalAddr',
                'BGP4-V2-MIB-JUNIPER::jnxBgpM2PeerRemoteAddrType',
                'BGP4-V2-MIB-JUNIPER::jnxBgpM2PeerLastErrorReceived',
                'BGP4-V2-MIB-JUNIPER::jnxBgpM2PeerLastErrorReceivedText',
            ])->valuesByIndex();
    } elseif ($device['os_group'] === 'arista') {
        $peer_data_check = snmpwalk_cache_oid($device, 'aristaBgp4V2PeerRemoteAs', [], 'ARISTA-BGP4V2-MIB');
    } elseif ($device['os'] === 'dell-os10') {
        $peer_data_check = snmpwalk_cache_oid($device, 'os10bgp4V2PeerRemoteAs', [], 'DELLEMC-OS10-BGP4V2-MIB', 'dell'); // practically identical MIB as arista
    } elseif ($device['os'] === 'timos') {
        $peer_data_check = SnmpQuery::enumStrings()->numericIndex()->abortOnFailure()->walk([
            'TIMETRA-BGP-MIB::tBgpPeerNgTable',
            'TIMETRA-BGP-MIB::tBgpPeerNgOperTable',
        ])->valuesByIndex();
    } elseif ($device['os'] === 'firebrick') {
        $peer_data_check = snmpwalk_cache_multi_oid($device, 'fbBgpPeerTable', [], 'FIREBRICK-BGP-MIB', 'firebrick');
    } elseif ($device['os'] === 'aos7') {
        $peer_data_check = snmpwalk_cache_multi_oid($device, 'alaBgpPeerAS', [], 'ALCATEL-IND1-BGP-MIB', 'aos7');
    } elseif ($device['os'] === 'vrp') {
        $peer_data_check = snmpwalk_cache_multi_oid($device, 'hwBgpPeerEntry', [], 'HUAWEI-BGP-VPN-MIB', 'huawei');
    } elseif ($device['os_group'] == 'cisco') {
        $peer_data_check = snmpwalk_cache_oid($device, 'cbgpPeer2RemoteAs', [], 'CISCO-BGP4-MIB');
    } elseif ($device['os'] == 'cumulus') {
        $peer_data_check = snmpwalk_cache_oid($device, 'bgpPeerRemoteAs', [], 'CUMULUS-BGPUN-MIB');
    } else {
        $peer_data_check = snmpwalk_cache_oid($device, 'bgpPeerRemoteAs', [], 'BGP4-MIB');
    }
    // If a Cisco device has BGP peers in VRF(s), but no BGP peers in
    // the default VRF: don't fall back to the default MIB, to avoid
    // skipping IPv6 peers (CISCO-BGP4-MIB is required.)
    // count(getVrfContexts) returns VRF's with a configured SNMP context
    // e.g. snmp-server context context_name vrf vrf_name
    // "> 0" because the default VRF is only included in the count,
    // if it has a switch configured SNMP context.
    // The issues occured on NX-OS (Nexus) and IOS-XR (ASR) devices.
    // Using os_group 'cisco' breaks the 3560g snmpsim tests.
    $vrf_contexts = DeviceCache::getPrimary()->getVrfContexts();
    $cisco_with_vrf = (($device['os'] == 'iosxr' || $device['os'] == 'nxos') && ! empty($vrf_contexts[0]));
    if (empty($peer_data_check) && ! $cisco_with_vrf) {
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
            // If a Cisco device has BGP peers in VRF(s),
            // but no BGP peers in the default VRF,
            // a SNMP (v3) walk without context will not find any
            // cbgpPeer2RemoteAs, resulting in empty $peer_data_check.
            // Without the or clause, we won't see the VRF BGP peers.
            // ($peer_data_check isn't used in the Cisco code path,)
            if (count($peer_data_check) > 0 || $cisco_with_vrf) {
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
                        // parse peer IP
                        $junos = [];
                        foreach ($peer_data_check as $peers => $jnx_peer_data) {
                            $exploded_ip = explode('.', $peers);
                            $ip_offset = count($exploded_ip) > 30 ? -16 : -4;
                            $tmp_peer_ip = Oid::of(implode('.', array_slice($exploded_ip, $ip_offset)))->toIp()->uncompressed();
                            $junos[$tmp_peer_ip] = $jnx_peer_data;
                        }
                    }

                    $address = $peer_ip->uncompressed();
                    $peer_data = [
                        'bgpPeerState' => $junos[$address]['BGP4-V2-MIB-JUNIPER::jnxBgpM2PeerState'] ?? 'unknown',
                        'bgpPeerAdminStatus' => $junos[$address]['BGP4-V2-MIB-JUNIPER::jnxBgpM2PeerStatus'] ?? 'unknown',
                        'bgpPeerInUpdates' => $junos[$address]['BGP4-V2-MIB-JUNIPER::jnxBgpM2PeerInUpdates'] ?? 0,
                        'bgpPeerOutUpdates' => $junos[$address]['BGP4-V2-MIB-JUNIPER::jnxBgpM2PeerOutUpdates'] ?? 0,
                        'bgpPeerInTotalMessages' => $junos[$address]['BGP4-V2-MIB-JUNIPER::jnxBgpM2PeerInTotalMessages'] ?? 0,
                        'bgpPeerOutTotalMessages' => $junos[$address]['BGP4-V2-MIB-JUNIPER::jnxBgpM2PeerOutTotalMessages'] ?? 0,
                        'bgpPeerFsmEstablishedTime' => $junos[$address]['BGP4-V2-MIB-JUNIPER::jnxBgpM2PeerFsmEstablishedTime'] ?? 0,
                        'bgpPeerLastErrorText' => $junos[$address]['BGP4-V2-MIB-JUNIPER::jnxBgpM2PeerLastErrorReceivedText'] ?? null,
                    ];

                    $error_data = explode(' ', $junos[$address]['BGP4-V2-MIB-JUNIPER::jnxBgpM2PeerLastErrorReceived'] ?? ' ');
                    $peer_data['bgpPeerLastErrorCode'] = intval($error_data[0]);
                    $peer_data['bgpPeerLastErrorSubCode'] = intval($error_data[1]);
                    $peer_data['bgpPeerInUpdateElapsedTime'] = null;

                    try {
                        $peer_data['bgpLocalAddr'] = IP::fromHexString($junos[$address]['BGP4-V2-MIB-JUNIPER::jnxBgpM2PeerLocalAddr'])->uncompressed();
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
                            if (is_array($bgpPeers[$address]) && is_array($bgpPeers[$address][$vrfInstance])) {
                                $bgpPeers[$address][$vrfInstance] = array_merge($bgpPeers[$address][$vrfInstance], $value);
                            }
                        }
                    }
                    $address = (string) $peer_ip;
                    $bgpPeer = $bgpPeers[$address];
                    $peer_data = [];
                    if ($bgpPeer && count(array_keys($bgpPeer)) == 1) { // We have only one vrf with a peer with this IP
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
                        $bgpPeers = [];
                        foreach ($peer_data_check as $key => $value) {
                            $oid = explode('.', $key);
                            $vrfInstance = $oid[0];
                            $address = implode('.', array_slice($oid, 3));
                            if (strlen($address) > 15) {
                                $address = IP::fromHexString($address)->compressed();
                            }
                            $bgpPeers[$vrfInstance][$address] = $value;
                        }
                    }
                    $address = (string) $peer_ip;
                    $establishedTime = $bgpPeers[$vrfOid][$address]['TIMETRA-BGP-MIB::tBgpPeerNgLastChanged'] / 100;

                    $peer_data = [];
                    $peer_data['bgpPeerState'] = $bgpPeers[$vrfOid][$address]['TIMETRA-BGP-MIB::tBgpPeerNgConnState'];
                    if ($bgpPeers[$vrfOid][$address]['TIMETRA-BGP-MIB::tBgpPeerNgShutdown'] == '1') {
                        $peer_data['bgpPeerAdminStatus'] = 'adminShutdown';
                    } else {
                        $peer_data['bgpPeerAdminStatus'] = $bgpPeers[$vrfOid][$address]['TIMETRA-BGP-MIB::tBgpPeerNgOperLastEvent'];
                    }
                    $peer_data['bgpPeerInTotalMessages'] = $bgpPeers[$vrfOid][$address]['TIMETRA-BGP-MIB::tBgpPeerNgOperMsgOctetsRcvd'] % (2 ** 32);  // That are actually only octets available,
                    $peer_data['bgpPeerOutTotalMessages'] = $bgpPeers[$vrfOid][$address]['TIMETRA-BGP-MIB::tBgpPeerNgOperMsgOctetsSent'] % (2 ** 32); // not messages
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
                    } elseif ($device['os'] == 'aos7') {
                        $peer_identifier = $peer['bgpPeerIdentifier'];
                        $peer_data = [];
                        $al_descr = snmpwalk_cache_multi_oid($device, 'alaBgpPeerName', $al_descr, 'ALCATEL-IND1-BGP-MIB', 'aos7', '-OQUs');
                        $al_peer = snmpwalk_cache_multi_oid($device, 'BgpPeerEntry', [], 'BGP4-MIB', 'aos7', '-OQUs');
                        $peer_data['bgpPeerDescr'] = $al_descr[$peer_identifier]['alaBgpPeerName'];
                        $peer_data['bgpPeerState'] = $al_peer[$peer_identifier]['bgpPeerState'];
                        $peer_data['bgpPeerAdminStatus'] = $al_peer[$peer_identifier]['bgpPeerAdminStatus'];
                        $peer_data['bgpPeerInUpdates'] = $al_peer[$peer_identifier]['bgpPeerInUpdates'];
                        $peer_data['bgpPeerOutUpdates'] = $al_peer[$peer_identifier]['bgpPeerOutUpdates'];
                        $peer_data['bgpPeerInTotalMessages'] = $al_peer[$peer_identifier]['bgpPeerInTotalMessages'];
                        $peer_data['bgpPeerOutTotalMessages'] = $al_peer[$peer_identifier]['bgpPeerOutTotalMessages'];
                        $peer_data['bgpPeerFsmEstablishedTime'] = $al_peer[$peer_identifier]['bgpPeerFsmEstablishedTime'];
                        $peer_data['bgpPeerInUpdateElapsedTime'] = $al_peer[$peer_identifier]['bgpPeerInUpdateElapsedTime'];
                        $error_data = explode(' ', $al_peer[$peer_identifier]['bgpPeerLastError']);
                        $peer_data['bgpPeerLastErrorCode'] = intval($error_data[0]);
                        $peer_data['bgpPeerLastErrorSubCode'] = intval($error_data[1]);
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
                    } elseif ($device['os'] == 'cumulus') {
                        $peer_identifier = $peer['bgpPeerIdentifier'];
                        $mib = 'CUMULUS-BGPUN-MIB';
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
                            'bgpPeerIface' => 'bgpPeerIface',
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
                    $v = isset($peer_data_raw[$source]) ? $peer_data_raw[$source] : (in_array($target, $intFields) ? 0 : '');

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

                // --- Fill the bgpPeerIface column ---
                if (isset($peer_data['bgpPeerIface']) && ! IP::isValid($peer_data['bgpPeerIface'])) {
                    // The column is already filled with the ifName, we change it to ifIndex
                    $peer_data['bgpPeerIface'] = DeviceCache::getPrimary()->ports()->where('ifName', '=', $peer_data['bgpPeerIface'])->value('ifIndex');
                } elseif (isset($peer_data['bgpLocalAddr']) && IP::isValid($peer_data['bgpLocalAddr'])) {
                    // else we use the bgpLocalAddr to find ifIndex
                    try {
                        $ip_address = IP::parse($peer_data['bgpLocalAddr']);
                        $family = $ip_address->getFamily();
                        $peer_data['bgpPeerIface'] = DB::table('ports')->join("{$family}_addresses", 'ports.port_id', '=', "{$family}_addresses.port_id")->where("{$family}_address", '=', $ip_address->uncompressed())->value('ifIndex');
                    } catch (InvalidIpException $e) {
                        $peer_data['bgpPeerIface'] = null;
                    }
                } else {
                    $peer_data['bgpPeerIface'] = null;
                }
            }
        } catch (InvalidIpException $e) {
            // ignore
        }

        if (empty($peer_data)) {
            continue; // no data, try next peer
        }

        d_echo($peer_data);

        // --- Send event log notices ---
        if ($peer_data['bgpPeerFsmEstablishedTime']) {
            if (! (is_array(\LibreNMS\Config::get('alerts.bgp.whitelist'))
                    && ! in_array($peer['bgpPeerRemoteAs'], \LibreNMS\Config::get('alerts.bgp.whitelist')))
                && ($peer_data['bgpPeerFsmEstablishedTime'] < $peer['bgpPeerFsmEstablishedTime']
                    || $peer_data['bgpPeerState'] != $peer['bgpPeerState'])
            ) {
                if ($peer['bgpPeerState'] == $peer_data['bgpPeerState']) {
                    Eventlog::log('BGP Session Flap: ' . $peer['bgpPeerIdentifier'] . ' (AS' . $peer['bgpPeerRemoteAs'] . ' ' . $peer['bgpPeerDescr'] . '), last error: ' . describe_bgp_error_code($peer['bgpPeerLastErrorCode'], $peer['bgpPeerLastErrorSubCode']), $device['device_id'], 'bgpPeer', Severity::Warning, $peer_ip);
                } elseif ($peer_data['bgpPeerState'] == 'established') {
                    Eventlog::log('BGP Session Up: ' . $peer['bgpPeerIdentifier'] . ' (AS' . $peer['bgpPeerRemoteAs'] . ' ' . $peer['bgpPeerDescr'] . ')', $device['device_id'], 'bgpPeer', Severity::Ok, $peer_ip);
                } elseif ($peer['bgpPeerState'] == 'established') {
                    Eventlog::log('BGP Session Down: ' . $peer['bgpPeerIdentifier'] . ' (AS' . $peer['bgpPeerRemoteAs'] . ' ' . $peer['bgpPeerDescr'] . '), last error: ' . describe_bgp_error_code($peer['bgpPeerLastErrorCode'], $peer['bgpPeerLastErrorSubCode']), $device['device_id'], 'bgpPeer', Severity::Error, $peer_ip);
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
        $peer_data['bgpPeerInTotalMessages'] = set_numeric($peer_data['bgpPeerInTotalMessages']);
        $peer_data['bgpPeerOutTotalMessages'] = set_numeric($peer_data['bgpPeerOutTotalMessages']);
        $peer_data['bgpPeerInUpdateElapsedTime'] = set_numeric($peer_data['bgpPeerInUpdateElapsedTime']);

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
                    $cbgpPeerAdvertisedPrefixes = max(0, $cbgp_data['cbgpPeerAdvertisedPrefixes'] - $cbgp_data['cbgpPeerWithdrawnPrefixes']);
                    $cbgpPeerWithdrawnPrefixes = 0; // no use, it is a gauge32 value, only the difference between cbgpPeerAdvertisedPrefixes  and cbgpPeerWithdrawnPrefixes makes sense.
                    // CF CISCO-BGP4-MIB definition for both
                    $cbgpPeerSuppressedPrefixes = $cbgp_data['cbgpPeerSuppressedPrefixes'];
                    unset($cbgp_data);
                } //end if

                if ($device['os'] == 'junos') {
                    $safis = [
                        'unicast' => 1,
                        'multicast' => 2,
                        'unicastAndMulticast' => 3,
                        'labeledUnicast' => 4,
                        'mvpn' => 5,
                        'vpls' => 65,
                        'evpn' => 70,
                        'vpn' => 128,
                        'rtfilter' => 132,
                        'flow' => 133,
                    ];

                    if (! isset($j_prefixes)) {
                        $j_prefixes = SnmpQuery::walk([
                            'BGP4-V2-MIB-JUNIPER::jnxBgpM2PrefixInPrefixesAccepted',
                            'BGP4-V2-MIB-JUNIPER::jnxBgpM2PrefixInPrefixesRejected',
                            'BGP4-V2-MIB-JUNIPER::jnxBgpM2PrefixOutPrefixes',
                        ])->table(3);
                    }

                    $jnxPeerIndex = $junos[$peer_ip->uncompressed()]['BGP4-V2-MIB-JUNIPER::jnxBgpM2PeerIndex'] ?? null;
                    $current_peer_data = $j_prefixes[$jnxPeerIndex][$afi][$safis[$safi]] ?? [];
                    $cbgpPeerAcceptedPrefixes = $current_peer_data['BGP4-V2-MIB-JUNIPER::jnxBgpM2PrefixInPrefixesAccepted'] ?? null;
                    $cbgpPeerDeniedPrefixes = $current_peer_data['BGP4-V2-MIB-JUNIPER::jnxBgpM2PrefixInPrefixesRejected'] ?? null;
                    $cbgpPeerAdvertisedPrefixes = $current_peer_data['BGP4-V2-MIB-JUNIPER::jnxBgpM2PrefixOutPrefixes'] ?? null;
                    $cbgpPeerPrefixAdminLimit = null;
                    $cbgpPeerPrefixThreshold = null;
                    $cbgpPeerPrefixClearThreshold = null;
                    $cbgpPeerSuppressedPrefixes = null;
                    $cbgpPeerWithdrawnPrefixes = null;
                }//end if

                if ($device['os_group'] === 'arista') {
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

                if ($device['os'] == 'firebrick') {
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
                    'AcceptedPrefixes' => $cbgpPeerAcceptedPrefixes,
                    'DeniedPrefixes' => $cbgpPeerDeniedPrefixes,
                    'PrefixAdminLimit' => $cbgpPeerPrefixAdminLimit,
                    'PrefixThreshold' => $cbgpPeerPrefixThreshold,
                    'PrefixClearThreshold' => $cbgpPeerPrefixClearThreshold,
                    'AdvertisedPrefixes' => $cbgpPeerAdvertisedPrefixes,
                    'SuppressedPrefixes' => $cbgpPeerSuppressedPrefixes,
                    'WithdrawnPrefixes' => $cbgpPeerWithdrawnPrefixes,
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

                if (! empty($peer['c_update'])) {
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
                    'AcceptedPrefixes' => $cbgpPeerAcceptedPrefixes,
                    'DeniedPrefixes' => $cbgpPeerDeniedPrefixes,
                    'AdvertisedPrefixes' => $cbgpPeerAdvertisedPrefixes,
                    'SuppressedPrefixes' => $cbgpPeerSuppressedPrefixes,
                    'WithdrawnPrefixes' => $cbgpPeerWithdrawnPrefixes,
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

unset($peers, $peer_data_tmp, $j_prefixes);
