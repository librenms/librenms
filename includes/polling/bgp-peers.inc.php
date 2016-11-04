<?php

if ($config['enable_bgp']) {
    $peers = dbFetchRows('SELECT * FROM bgpPeers WHERE device_id = ?', array($device['device_id']));

    if (!empty($peers)) {
        if ($device['os'] == 'junos') {
            $peer_data_check = snmpwalk_cache_long_oid($device, 'jnxBgpM2PeerIndex', '.1.3.6.1.4.1.2636.5.1.1.2.1.1.1.14', $peer_data_tmp, 'BGP4-V2-MIB-JUNIPER', 'junos');
        } else {
            $peer_data_check = snmpwalk_cache_oid($device, 'cbgpPeer2RemoteAs', array(), 'CISCO-BGP4-MIB');
        }

        foreach ($peers as $peer) {
            //add context if exist
            $device['context_name']= $peer['context_name'];
            if (strstr(":", $peer['bgpPeerIdentifier'])) {
                $peer_ip = ipv62snmp($peer['bgpPeerIdentifier']);
            } else {
                $peer_ip = $peer['bgpPeerIdentifier'];
            }

            // Poll BGP Peer
            echo 'Checking BGP peer '.$peer['bgpPeerIdentifier'].' ';

            if (!empty($peer['bgpPeerIdentifier'])) {
                if ($device['os'] != 'junos') {
                    // v4 BGP4 MIB
                    if (count($peer_data_check) > 0) {
                        if (strstr($peer['bgpPeerIdentifier'], ':')) {
                            $bgp_peer_ident = ipv62snmp($peer['bgpPeerIdentifier']);
                        } else {
                            $bgp_peer_ident = $peer['bgpPeerIdentifier'];
                        }

                        if (strstr($peer['bgpPeerIdentifier'], ':')) {
                            $ip_type = 2;
                            $ip_len  = 16;
                            $ip_ver  = 'ipv6';
                        } else {
                            $ip_type = 1;
                            $ip_len  = 4;
                            $ip_ver  = 'ipv4';
                        }

                        $peer_identifier = $ip_type.'.'.$ip_len.'.'.$bgp_peer_ident;
                        $peer_data_tmp   = snmp_get_multi(
                            $device,
                            ' cbgpPeer2State.'.$peer_identifier.' cbgpPeer2AdminStatus.'.$peer_identifier.' cbgpPeer2InUpdates.'.$peer_identifier.' cbgpPeer2OutUpdates.'.$peer_identifier.' cbgpPeer2InTotalMessages.'.$peer_identifier.' cbgpPeer2OutTotalMessages.'.$peer_identifier.' cbgpPeer2FsmEstablishedTime.'.$peer_identifier.' cbgpPeer2InUpdateElapsedTime.'.$peer_identifier.' cbgpPeer2LocalAddr.'.$peer_identifier,
                            '-OQUs',
                            'CISCO-BGP4-MIB'
                        );
                        $ident           = "$ip_ver.\"".$bgp_peer_ident.'"';
                        unset($peer_data);
                        $ident_key = array_keys($peer_data_tmp);
                        foreach ($peer_data_tmp[$ident_key[0]] as $k => $v) {
                            if (strstr($k, 'cbgpPeer2LocalAddr')) {
                                if ($ip_ver == 'ipv6') {
                                    $v = str_replace('"', '', $v);
                                    $v = rtrim($v);
                                    $v = preg_replace('/(\S+\s+\S+)\s/', '$1:', $v);
                                    $v = strtolower($v);
                                } else {
                                    $v = hex_to_ip($v);
                                }
                            }

                            $peer_data .= "$v\n";
                        }
                    } else {
                        $peer_cmd  = $config['snmpget'].' -M '.$config['mibdir'].' -m BGP4-MIB -OUvq '.snmp_gen_auth($device).' '.$device['hostname'].':'.$device['port'].' ';
                        $peer_cmd .= 'bgpPeerState.'.$peer['bgpPeerIdentifier'].' bgpPeerAdminStatus.'.$peer['bgpPeerIdentifier'].' bgpPeerInUpdates.'.$peer['bgpPeerIdentifier'].' bgpPeerOutUpdates.'.$peer['bgpPeerIdentifier'].' bgpPeerInTotalMessages.'.$peer['bgpPeerIdentifier'].' ';
                        $peer_cmd .= 'bgpPeerOutTotalMessages.'.$peer['bgpPeerIdentifier'].' bgpPeerFsmEstablishedTime.'.$peer['bgpPeerIdentifier'].' bgpPeerInUpdateElapsedTime.'.$peer['bgpPeerIdentifier'].' ';
                        $peer_cmd .= 'bgpPeerLocalAddr.'.$peer['bgpPeerIdentifier'].'';
                        $peer_data = trim(`$peer_cmd`);
                    }//end if
                    d_echo($peer_data);
                    list($bgpPeerState, $bgpPeerAdminStatus, $bgpPeerInUpdates, $bgpPeerOutUpdates, $bgpPeerInTotalMessages, $bgpPeerOutTotalMessages, $bgpPeerFsmEstablishedTime, $bgpPeerInUpdateElapsedTime, $bgpLocalAddr) = explode("\n", $peer_data);
                    $bgpLocalAddr = str_replace('"', '', str_replace(' ', '', $bgpLocalAddr));
                } elseif ($device['os'] == 'junos') {
                    if (!isset($junos)) {
                        echo "\nCaching Oids...";

                        foreach ($peer_data_check as $hash => $index) {
                            $peer_ip_snmp = ltrim($index['orig'], '.');
                            $octets = count(explode(".", $peer_ip_snmp));
                            if ($octets > 11) {
                                // ipv6
                                $tmp_peer_ip = Net_IPv6::compress(snmp2ipv6(implode('.', array_slice(explode('.', $peer_ip_snmp), (count(explode('.', $peer_ip_snmp)) - 16)))));
                            } else {
                                // ipv4
                                $tmp_peer_ip = implode('.', array_slice(explode('.', $peer_ip_snmp), (count(explode('.', $peer_ip_snmp)) - 4)));
                            }
                            $junos[$tmp_peer_ip]['hash'] = $hash;
                            $junos[$tmp_peer_ip]['index'] = $index['jnxBgpM2PeerIndex'];
                        }
                    }

                    if (!isset($peer_data_tmp)) {
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
                        d_echo($peer_data_tmp);
                    }
                    $bgpPeerState = $peer_data_tmp[$junos[$peer_ip]['hash']]['jnxBgpM2PeerState'];
                    $bgpPeerAdminStatus = $peer_data_tmp[$junos[$peer_ip]['hash']]['jnxBgpM2PeerStatus'];
                    $bgpPeerInUpdates = $peer_data_tmp[$junos[$peer_ip]['hash']]['jnxBgpM2PeerInUpdates'];
                    $bgpPeerOutUpdates = $peer_data_tmp[$junos[$peer_ip]['hash']]['jnxBgpM2PeerOutUpdates'];
                    $bgpPeerInTotalMessages = $peer_data_tmp[$junos[$peer_ip]['hash']]['jnxBgpM2PeerInTotalMessages'];
                    $bgpPeerOutTotalMessages = $peer_data_tmp[$junos[$peer_ip]['hash']]['jnxBgpM2PeerOutTotalMessages'];
                    $bgpPeerFsmEstablishedTime = $peer_data_tmp[$junos[$peer_ip]['hash']]['jnxBgpM2PeerFsmEstablishedTime'];
                    $bgpPeerInUpdateElapsedTime = $peer_data_tmp[$junos[$peer_ip]['hash']]['jnxBgpM2PeerInUpdatesElapsedTime'];
                    if ($peer_data_tmp[$junos[$peer_ip]['hash']]['jnxBgpM2PeerRemoteAddrType'] == 'ipv4') {
                        $bgpLocalAddr = long2ip(hexdec($peer_data_tmp[$junos[$peer_ip]['hash']]['jnxBgpM2PeerLocalAddr']));
                    } elseif ($peer_data_tmp[$junos[$peer_ip]['hash']]['jnxBgpM2PeerRemoteAddrType'] == 'ipv6') {
                        $ip6 = trim(str_replace(' ', '', $peer_data_tmp[$junos[$peer_ip]['hash']]['jnxBgpM2PeerLocalAddr']), '"');
                        $ip6 = substr($ip6, 0, 4).':'.substr($ip6, 4, 4).':'.substr($ip6, 8, 4).':'.substr($ip6, 12, 4).':'.substr($ip6, 16, 4).':'.substr($ip6, 20, 4).':'.substr($ip6, 24, 4).':'.substr($ip6, 28, 4);
                        $bgpLocalAddr = Net_IPv6::compress($ip6);
                    }
                    d_echo("State = $bgpPeerState - AdminStatus: $bgpPeerAdminStatus\n");

                    if ($bgpLocalAddr == '00000000000000000000000000000000') {
                        $bgpLocalAddr = '';
                        // Unknown?
                    }
                }//end if
            }//end if

            if ($bgpPeerFsmEstablishedTime) {
                if (!(is_array($config['alerts']['bgp']['whitelist']) && !in_array($peer['bgpPeerRemoteAs'], $config['alerts']['bgp']['whitelist'])) && ($bgpPeerFsmEstablishedTime < $peer['bgpPeerFsmEstablishedTime'] || $bgpPeerState != $peer['bgpPeerState'])) {
                    if ($peer['bgpPeerState'] == $bgpPeerState) {
                        log_event('BGP Session Flap: '.$peer['bgpPeerIdentifier'].' (AS'.$peer['bgpPeerRemoteAs'].')', $device, 'bgpPeer', $bgpPeer_id);
                    } elseif ($bgpPeerState == 'established') {
                        log_event('BGP Session Up: '.$peer['bgpPeerIdentifier'].' (AS'.$peer['bgpPeerRemoteAs'].')', $device, 'bgpPeer', $bgpPeer_id);
                    } elseif ($peer['bgpPeerState'] == 'established') {
                        log_event('BGP Session Down: '.$peer['bgpPeerIdentifier'].' (AS'.$peer['bgpPeerRemoteAs'].')', $device, 'bgpPeer', $bgpPeer_id);
                    }
                }
            }

            $peer_rrd_name = safename('bgp-'.$peer['bgpPeerIdentifier']);
            $peer_rrd_def = array(
                'DS:bgpPeerOutUpdates:COUNTER:600:U:100000000000',
                'DS:bgpPeerInUpdates:COUNTER:600:U:100000000000',
                'DS:bgpPeerOutTotal:COUNTER:600:U:100000000000',
                'DS:bgpPeerInTotal:COUNTER:600:U:100000000000',
                'DS:bgpPeerEstablished:GAUGE:600:0:U'
            );

            $fields = array(
                'bgpPeerOutUpdates'    => $bgpPeerOutUpdates,
                'bgpPeerInUpdates'     => $bgpPeerInUpdates,
                'bgpPeerOutTotal'      => $bgpPeerOutTotalMessages,
                'bgpPeerInTotal'       => $bgpPeerInTotalMessages,
                'bgpPeerEstablished'   => $bgpPeerFsmEstablishedTime,
            );

            $tags = array(
                'bgpPeerIdentifier' => $peer['bgpPeerIdentifier'],
                'rrd_name' => $peer_rrd_name,
                'rrd_def' => $peer_rrd_def
            );
            data_update($device, 'bgp', $tags, $fields);

            $peer['update']['bgpPeerState']              = $bgpPeerState;
            $peer['update']['bgpPeerAdminStatus']        = $bgpPeerAdminStatus;
            $peer['update']['bgpPeerFsmEstablishedTime'] = $bgpPeerFsmEstablishedTime;
            $peer['update']['bgpPeerInUpdates']          = $bgpPeerInUpdates;
            $peer['update']['bgpLocalAddr']              = $bgpLocalAddr;
            $peer['update']['bgpPeerOutUpdates']         = $bgpPeerOutUpdates;

            dbUpdate($peer['update'], 'bgpPeers', '`device_id` = ? AND `bgpPeerIdentifier` = ?', array($device['device_id'], $peer['bgpPeerIdentifier']));

            if ($device['os_group'] == 'cisco' || $device['os'] == 'junos') {
                // Poll each AFI/SAFI for this peer (using CISCO-BGP4-MIB or BGP4-V2-JUNIPER MIB)
                $peer_afis = dbFetchRows('SELECT * FROM bgpPeers_cbgp WHERE `device_id` = ? AND bgpPeerIdentifier = ?', array($device['device_id'], $peer['bgpPeerIdentifier']));
                foreach ($peer_afis as $peer_afi) {
                    $afi  = $peer_afi['afi'];
                    $safi = $peer_afi['safi'];
                    d_echo("$afi $safi\n");

                    if ($device['os_group'] == 'cisco') {
                        $bgp_peer_ident = ipv62snmp($peer['bgpPeerIdentifier']);
                        if (strstr($peer['bgpPeerIdentifier'], ':')) {
                            $ip_type = 2;
                            $ip_len  = 16;
                            $ip_ver  = 'ipv6';
                        } else {
                            $ip_type = 1;
                            $ip_len  = 4;
                            $ip_ver  = 'ipv4';
                        }

                        $ip_cast = 1;
                        if ($peer_afi['safi'] == 'multicast') {
                            $ip_cast = 2;
                        } elseif ($peer_afi['safi'] == 'unicastAndMulticast') {
                            $ip_cast = 3;
                        } elseif ($peer_afi['safi'] == 'vpn') {
                            $ip_cast = 128;
                        }

                        $check = snmp_get($device, 'cbgpPeer2AcceptedPrefixes.'.$ip_type.'.'.$ip_len.'.'.$bgp_peer_ident.'.'.$ip_type.'.'.$ip_cast, '', 'CISCO-BGP4-MIB');

                        if (!empty($check)) {
                            $cgp_peer_identifier = $ip_type.'.'.$ip_len.'.'.$bgp_peer_ident.'.'.$ip_type.'.'.$ip_cast;
                            $cbgp_data_tmp       = snmp_get_multi(
                                $device,
                                ' cbgpPeer2AcceptedPrefixes.'.$cgp_peer_identifier.' cbgpPeer2DeniedPrefixes.'.$cgp_peer_identifier.' cbgpPeer2PrefixAdminLimit.'.$cgp_peer_identifier.' cbgpPeer2PrefixThreshold.'.$cgp_peer_identifier.' cbgpPeer2PrefixClearThreshold.'.$cgp_peer_identifier.' cbgpPeer2AdvertisedPrefixes.'.$cgp_peer_identifier.' cbgpPeer2SuppressedPrefixes.'.$cgp_peer_identifier.' cbgpPeer2WithdrawnPrefixes.'.$cgp_peer_identifier,
                                '-OQUs',
                                'CISCO-BGP4-MIB'
                            );
                            $ident               = "$ip_ver.\"".$peer['bgpPeerIdentifier'].'"'.'.'.$ip_type.'.'.$ip_cast;
                            unset($cbgp_data);
                            $temp_keys = array_keys($cbgp_data_tmp);
                            unset($temp_data);
                            $temp_data['cbgpPeer2AcceptedPrefixes']     = $cbgp_data_tmp[$temp_keys[0]]['cbgpPeer2AcceptedPrefixes'];
                            $temp_data['cbgpPeer2DeniedPrefixes']       = $cbgp_data_tmp[$temp_keys[0]]['cbgpPeer2DeniedPrefixes'];
                            $temp_data['cbgpPeer2PrefixAdminLimit']     = $cbgp_data_tmp[$temp_keys[0]]['cbgpPeer2PrefixAdminLimit'];
                            $temp_data['cbgpPeer2PrefixThreshold']      = $cbgp_data_tmp[$temp_keys[0]]['cbgpPeer2PrefixThreshold'];
                            $temp_data['cbgpPeer2PrefixClearThreshold'] = $cbgp_data_tmp[$temp_keys[0]]['cbgpPeer2PrefixClearThreshold'];
                            $temp_data['cbgpPeer2AdvertisedPrefixes']   = $cbgp_data_tmp[$temp_keys[0]]['cbgpPeer2AdvertisedPrefixes'];
                            $temp_data['cbgpPeer2SuppressedPrefixes']   = $cbgp_data_tmp[$temp_keys[0]]['cbgpPeer2SuppressedPrefixes'];
                            $temp_data['cbgpPeer2WithdrawnPrefixes']    = $cbgp_data_tmp[$temp_keys[0]]['cbgpPeer2WithdrawnPrefixes'];
                            foreach ($temp_data as $k => $v) {
                                $cbgp_data .= "$v\n";
                            }

                            d_echo("$cbgp_data\n");
                        } else {
                            // FIXME - move to function
                            $oids = " cbgpPeerAcceptedPrefixes." . $peer['bgpPeerIdentifier'] . ".$afi.$safi";
                            $oids .= " cbgpPeerDeniedPrefixes." . $peer['bgpPeerIdentifier'] . ".$afi.$safi";
                            $oids .= " cbgpPeerPrefixAdminLimit." . $peer['bgpPeerIdentifier'] . ".$afi.$safi";
                            $oids .= " cbgpPeerPrefixThreshold." . $peer['bgpPeerIdentifier'] . ".$afi.$safi";
                            $oids .= " cbgpPeerPrefixClearThreshold." . $peer['bgpPeerIdentifier'] . ".$afi.$safi";
                            $oids .= " cbgpPeerAdvertisedPrefixes." . $peer['bgpPeerIdentifier'] . ".$afi.$safi";
                            $oids .= " cbgpPeerSuppressedPrefixes." . $peer['bgpPeerIdentifier'] . ".$afi.$safi";
                            $oids .= " cbgpPeerWithdrawnPrefixes." . $peer['bgpPeerIdentifier'] . ".$afi.$safi";

                            d_echo("$oids\n");

                            $cbgp_data=  snmp_get_multi($device, $oids, '-OUQs ', 'CISCO-BGP4-MIB');
                            $cbgp_data=  array_pop($cbgp_data);
                            d_echo("$cbgp_data\n");
                        }//end if
                        $cbgpPeerAcceptedPrefixes = $cbgp_data['cbgpPeerAcceptedPrefixes'];
                        $cbgpPeerDeniedPrefixes = $cbgp_data['cbgpPeerDeniedPrefixes'];
                        $cbgpPeerPrefixAdminLimit = $cbgp_data['cbgpPeerPrefixAdminLimit'];
                        $cbgpPeerPrefixThreshold = $cbgp_data['cbgpPeerPrefixThreshold'];
                        $cbgpPeerPrefixClearThreshold = $cbgp_data['cbgpPeerPrefixClearThreshold'];
                        $cbgpPeerAdvertisedPrefixes = $cbgp_data['cbgpPeerAdvertisedPrefixes'];
                        $cbgpPeerSuppressedPrefixes = $cbgp_data['cbgpPeerSuppressedPrefixes'];
                        $cbgpPeerWithdrawnPrefixes = $cbgp_data['cbgpPeerWithdrawnPrefixes'];
                        unset($cbgp_data);
                    }//end if

                    if ($device['os'] == 'junos') {
                        $safis['unicast']   = 1;
                        $safis['multicast'] = 2;
                        $afis['ipv4']       = 1;
                        $afis['ipv6']       = 2;

                        if (!isset($j_prefixes)) {
                            $j_prefixes = snmpwalk_cache_multi_oid($device, 'jnxBgpM2PrefixInPrefixesAccepted', $j_prefixes, 'BGP4-V2-MIB-JUNIPER', 'junos', '-OQnU');
                            $j_prefixes = snmpwalk_cache_multi_oid($device, 'jnxBgpM2PrefixInPrefixesRejected', $j_prefixes, 'BGP4-V2-MIB-JUNIPER', 'junos', '-OQnU');
                            $j_prefixes = snmpwalk_cache_multi_oid($device, 'jnxBgpM2PrefixOutPrefixes', $j_prefixes, 'BGP4-V2-MIB-JUNIPER', 'junos', '-OQnU');
                            d_echo($j_prefixes);
                        }
                        
                        $cbgpPeerAcceptedPrefixes   = array_shift($j_prefixes['1.3.6.1.4.1.2636.5.1.1.2.6.2.1.8.'.$junos[$peer_ip]['index'].".$afis[$afi].".$safis[$safi]]);
                        $cbgpPeerDeniedPrefixes     = array_shift($j_prefixes['1.3.6.1.4.1.2636.5.1.1.2.6.2.1.9.'.$junos[$peer_ip]['index'].".$afis[$afi].".$safis[$safi]]);
                        $cbgpPeerAdvertisedPrefixes = array_shift($j_prefixes['1.3.6.1.4.1.2636.5.1.1.2.6.2.1.10.'.$junos[$peer_ip]['index'].".$afis[$afi].".$safis[$safi]]);
                    }//end if

                    // FIXME THESE FIELDS DO NOT EXIST IN THE DATABASE!
                    $update = 'UPDATE bgpPeers_cbgp SET';
                    $peer['c_update']['AcceptedPrefixes']     = $cbgpPeerAcceptedPrefixes;
                    $peer['c_update']['DeniedPrefixes']       = $cbgpPeerDeniedPrefixes;
                    $peer['c_update']['PrefixAdminLimit']     = $cbgpPeerAdminLimit;
                    $peer['c_update']['PrefixThreshold']      = $cbgpPeerPrefixThreshold;
                    $peer['c_update']['PrefixClearThreshold'] = $cbgpPeerPrefixClearThreshold;
                    $peer['c_update']['AdvertisedPrefixes']   = $cbgpPeerAdvertisedPrefixes;
                    $peer['c_update']['SuppressedPrefixes']   = $cbgpPeerSuppressedPrefixes;
                    $peer['c_update']['WithdrawnPrefixes']    = $cbgpPeerWithdrawnPrefixes;

                    $oids = array(
                        'AcceptedPrefixes',
                        'DeniedPrefixes',
                        'AdvertisedPrefixes',
                        'SuppressedPrefixes',
                        'WithdrawnPrefixes',
                    );

                    foreach ($oids as $oid) {
                        $peer['c_update'][$oid.'_delta'] = $peer['c_update'][$oid] - $peer_afi[$oid];
                        $peer['c_update'][$oid.'_prev'] = $peer_afi[$oid];
                    }

                    dbUpdate($peer['c_update'], 'bgpPeers_cbgp', '`device_id` = ? AND bgpPeerIdentifier = ? AND afi = ? AND safi = ?', array($device['device_id'], $peer['bgpPeerIdentifier'], $afi, $safi));

                    $cbgp_rrd_name = safename('cbgp-'.$peer['bgpPeerIdentifier'].".$afi.$safi");
                    $cbgp_rrd_def = array(
                        'DS:AcceptedPrefixes:GAUGE:600:U:100000000000',
                        'DS:DeniedPrefixes:GAUGE:600:U:100000000000',
                        'DS:AdvertisedPrefixes:GAUGE:600:U:100000000000',
                        'DS:SuppressedPrefixes:GAUGE:600:U:100000000000',
                        'DS:WithdrawnPrefixes:GAUGE:600:U:100000000000'
                    );

                    $fields = array(
                        'AcceptedPrefixes'    => $cbgpPeerAcceptedPrefixes,
                        'DeniedPrefixes'      => $cbgpPeerDeniedPrefixes,
                        'AdvertisedPrefixes'  => $cbgpPeerAdvertisedPrefixes,
                        'SuppressedPrefixes'  => $cbgpPeerSuppressedPrefixes,
                        'WithdrawnPrefixes'   => $cbgpPeerWithdrawnPrefixes,
                    );

                    $tags = array(
                        'bgpPeerIdentifier' => $peer['bgpPeerIdentifier'],
                        'afi' => $afi,
                        'safi' => $safi,
                        'rrd_name' => $cbgp_rrd_name,
                        'rrd_def' => $cbgp_rrd_def
                    );
                    data_update($device, 'cbgp', $tags, $fields);
                } //end foreach
            } //end if
            echo "\n";
        } //end foreach
    } //end if
} //end if

unset($$peer_data_tmp);
unset($j_prefixes);
