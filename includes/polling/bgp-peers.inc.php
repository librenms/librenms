<?php

if ($config['enable_bgp']) {

    $peers = dbFetchRows('SELECT * FROM bgpPeers WHERE device_id = ?', array($device['device_id']));

    if (!empty($peers)) {

        if ($device['os'] == 'junos') {
            $peer_data_check = snmp_walk($device, 'jnxBgpM2PeerIndex', '-Onq', 'BGP4-V2-MIB-JUNIPER', $config['install_dir'].'/mibs/junos');
            $peer_data_check  = trim(str_replace('.1.3.6.1.4.1.2636.5.1.1.2.1.1.1.14.', '', $peer_data_check));
        } else {
            $peer_data_check = snmpwalk_cache_oid($device, 'cbgpPeer2RemoteAs', array(), 'CISCO-BGP4-MIB', $config['mibdir']);
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
                        }
                        else {
                            $bgp_peer_ident = $peer['bgpPeerIdentifier'];
                        }

                        if (strstr($peer['bgpPeerIdentifier'], ':')) {
                            $ip_type = 2;
                            $ip_len  = 16;
                            $ip_ver  = 'ipv6';
                        }
                        else {
                            $ip_type = 1;
                            $ip_len  = 4;
                            $ip_ver  = 'ipv4';
                        }

                        $peer_identifier = $ip_type.'.'.$ip_len.'.'.$bgp_peer_ident;
                        $peer_data_tmp   = snmp_get_multi(
                            $device,
                            ' cbgpPeer2State.'.$peer_identifier.' cbgpPeer2AdminStatus.'.$peer_identifier.' cbgpPeer2InUpdates.'.$peer_identifier.' cbgpPeer2OutUpdates.'.$peer_identifier.' cbgpPeer2InTotalMessages.'.$peer_identifier.' cbgpPeer2OutTotalMessages.'.$peer_identifier.' cbgpPeer2FsmEstablishedTime.'.$peer_identifier.' cbgpPeer2InUpdateElapsedTime.'.$peer_identifier.' cbgpPeer2LocalAddr.'.$peer_identifier,
                            '-OQUs',
                            'CISCO-BGP4-MIB',
                            $config['mibdir']
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
                                }
                                else {
                                    $v = hex_to_ip($v);
                                }
                            }

                            $peer_data .= "$v\n";
                        }
                    }
                    else {
                        $peer_cmd  = $config['snmpget'].' -M '.$config['mibdir'].' -m BGP4-MIB -OUvq '.snmp_gen_auth($device).' '.$device['hostname'].':'.$device['port'].' ';
                        $peer_cmd .= 'bgpPeerState.'.$peer['bgpPeerIdentifier'].' bgpPeerAdminStatus.'.$peer['bgpPeerIdentifier'].' bgpPeerInUpdates.'.$peer['bgpPeerIdentifier'].' bgpPeerOutUpdates.'.$peer['bgpPeerIdentifier'].' bgpPeerInTotalMessages.'.$peer['bgpPeerIdentifier'].' ';
                        $peer_cmd .= 'bgpPeerOutTotalMessages.'.$peer['bgpPeerIdentifier'].' bgpPeerFsmEstablishedTime.'.$peer['bgpPeerIdentifier'].' bgpPeerInUpdateElapsedTime.'.$peer['bgpPeerIdentifier'].' ';
                        $peer_cmd .= 'bgpPeerLocalAddr.'.$peer['bgpPeerIdentifier'].'';
                        $peer_data = trim(`$peer_cmd`);
                    }//end if
                    d_echo($peer_data);
                    list($bgpPeerState, $bgpPeerAdminStatus, $bgpPeerInUpdates, $bgpPeerOutUpdates, $bgpPeerInTotalMessages, $bgpPeerOutTotalMessages, $bgpPeerFsmEstablishedTime, $bgpPeerInUpdateElapsedTime, $bgpLocalAddr) = explode("\n", $peer_data);
                    $bgpLocalAddr = str_replace('"', '', str_replace(' ', '', $bgpLocalAddr));
                }
                elseif ($device['os'] == 'junos') {

                    if (!isset($junos)) {
                        echo "\nCaching Oids...";
                        // FIXME - needs moved to function
                        //$peer_cmd  = $config['snmpwalk'].' -M '.$config['mibdir'].'/junos -m BGP4-V2-MIB-JUNIPER -OUnq -'.$device['snmpver'].' '.snmp_gen_auth($device).' '.$device['hostname'].':'.$device['port'];

                        foreach (explode("\n", $peer_data_check) as $tmp) {
                            list($peer_ip_snmp, $peer_index) = explode(' ', $tmp);
                            $octets = count(explode(".", $peer_ip_snmp));
                            if ($octets > 11) {
                                // ipv6
                                $peer_ip = Net_IPv6::compress(snmp2ipv6(implode('.', array_slice(explode('.', $peer_ip_snmp), (count(explode('.', $peer_ip_snmp)) - 16)))));
                            } else {
                                // ipv4
                                $peer_ip = implode('.', array_slice(explode('.', $peer_ip_snmp), (count(explode('.', $peer_ip_snmp)) - 4)));
                            }
                            $junos[$peer_ip]['oid'] = $peer_ip_snmp;
                            $junos[$peer_ip]['index'] = $peer_index;
                        }
                    }

                    $peer_data_tmp = snmp_get_multi(
                        $device,
                        array(
                            'jnxBgpM2PeerState.'.$junos[$peer_ip]['oid'],
                            'jnxBgpM2PeerStatus.'.$junos[$peer_ip]['oid'],
                            'jnxBgpM2PeerInUpdates.'.$junos[$peer_ip]['oid'],
                            'jnxBgpM2PeerOutUpdates.'.$junos[$peer_ip]['oid'],
                            'jnxBgpM2PeerInTotalMessages.'.$junos[$peer_ip]['oid'],
                            'jnxBgpM2PeerOutTotalMessages.'.$junos[$peer_ip]['oid'],
                            'jnxBgpM2PeerFsmEstablishedTime.'.$junos[$peer_ip]['oid'],
                            'jnxBgpM2PeerInUpdatesElapsedTime.'.$junos[$peer_ip]['oid'],
                            'jnxBgpM2PeerLocalAddr.'.$junos[$peer_ip]['oid'],
                            'jnxBgpM2PeerRemoteAddrType.'.$junos[$peer_ip]['oid'],
                        ),
                        '-OQnUs',
                        'BGP4-V2-MIB-JUNIPER',
                        $config['mibdir'].'/junos'
                    );
                    $peer_data_tmp = array_shift($peer_data_tmp);
                    d_echo($peer_data_tmp);

                    $bgpPeerState = $peer_data_tmp['jnxBgpM2PeerState'];
                    $bgpPeerAdminStatus = $peer_data_tmp['jnxBgpM2PeerStatus'];
                    $bgpPeerInUpdates = $peer_data_tmp['jnxBgpM2PeerInUpdates'];
                    $bgpPeerOutUpdates = $peer_data_tmp['jnxBgpM2PeerOutUpdates'];
                    $bgpPeerInTotalMessages = $peer_data_tmp['jnxBgpM2PeerInTotalMessages'];
                    $bgpPeerOutTotalMessages = $peer_data_tmp['jnxBgpM2PeerOutTotalMessages'];
                    $bgpPeerFsmEstablishedTime = $peer_data_tmp['jnxBgpM2PeerFsmEstablishedTime'];
                    $bgpPeerInUpdateElapsedTime = $peer_data_tmp['jnxBgpM2PeerInUpdatesElapsedTime'];
                    if ($peer_data_tmp['jnxBgpM2PeerRemoteAddrType'] == 'ipv4') {
                        $bgpLocalAddr = long2ip(hexdec($peer_data_tmp['jnxBgpM2PeerLocalAddr']));
                    } elseif ($peer_data_tmp['jnxBgpM2PeerRemoteAddrType'] == 'ipv6') {
                        $ip6 = trim(str_replace(' ', '', $peer_data_tmp['jnxBgpM2PeerLocalAddr']), '"');
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
                    }
                    else if ($bgpPeerState == 'established') {
                        log_event('BGP Session Up: '.$peer['bgpPeerIdentifier'].' (AS'.$peer['bgpPeerRemoteAs'].')', $device, 'bgpPeer', $bgpPeer_id);
                    }
                    else if ($peer['bgpPeerState'] == 'established') {
                        log_event('BGP Session Down: '.$peer['bgpPeerIdentifier'].' (AS'.$peer['bgpPeerRemoteAs'].')', $device, 'bgpPeer', $bgpPeer_id);
                    }
                }
            }

            $peerrrd = $config['rrd_dir'].'/'.$device['hostname'].'/'.safename('bgp-'.$peer['bgpPeerIdentifier'].'.rrd');
            if (!is_file($peerrrd)) {
                $create_rrd = 'DS:bgpPeerOutUpdates:COUNTER:600:U:100000000000
                    DS:bgpPeerInUpdates:COUNTER:600:U:100000000000
                    DS:bgpPeerOutTotal:COUNTER:600:U:100000000000
                    DS:bgpPeerInTotal:COUNTER:600:U:100000000000
                    DS:bgpPeerEstablished:GAUGE:600:0:U '.$config['rrd_rra'];

                rrdtool_create($peerrrd, $create_rrd);
            }

            $fields = array(
                'bgpPeerOutUpdates'    => $bgpPeerOutUpdates,
                'bgpPeerInUpdates'     => $bgpPeerInUpdates,
                'bgpPeerOutTotal'      => $bgpPeerOutTotalMessages,
                'bgpPeerInTotal'       => $bgpPeerInTotalMessages,
                'bgpPeerEstablished'   => $bgpPeerFsmEstablishedTime,
            );
            rrdtool_update("$peerrrd", $fields);

            $tags = array('bgpPeerIdentifier' => $peer['bgpPeerIdentifier']);
            influx_update($device,'bgp',$tags,$fields);

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
                        }
                        else {
                            $ip_type = 1;
                            $ip_len  = 4;
                            $ip_ver  = 'ipv4';
                        }

                        $ip_cast = 1;
                        if ($peer_afi['safi'] == 'multicast') {
                            $ip_cast = 2;
                        }
                        else if ($peer_afi['safi'] == 'unicastAndMulticast') {
                            $ip_cast = 3;
                        }
                        else if ($peer_afi['safi'] == 'vpn') {
                            $ip_cast = 128;
                        }

                        $check = snmp_get($device, 'cbgpPeer2AcceptedPrefixes.'.$ip_type.'.'.$ip_len.'.'.$bgp_peer_ident.'.'.$ip_type.'.'.$ip_cast, '', 'CISCO-BGP4-MIB', $config['mibdir']);

                        if (!empty($check)) {
                            $cgp_peer_identifier = $ip_type.'.'.$ip_len.'.'.$bgp_peer_ident.'.'.$ip_type.'.'.$ip_cast;
                            $cbgp_data_tmp       = snmp_get_multi(
                                $device,
                                ' cbgpPeer2AcceptedPrefixes.'.$cgp_peer_identifier.' cbgpPeer2DeniedPrefixes.'.$cgp_peer_identifier.' cbgpPeer2PrefixAdminLimit.'.$cgp_peer_identifier.' cbgpPeer2PrefixThreshold.'.$cgp_peer_identifier.' cbgpPeer2PrefixClearThreshold.'.$cgp_peer_identifier.' cbgpPeer2AdvertisedPrefixes.'.$cgp_peer_identifier.' cbgpPeer2SuppressedPrefixes.'.$cgp_peer_identifier.' cbgpPeer2WithdrawnPrefixes.'.$cgp_peer_identifier,
                                '-OQUs',
                                'CISCO-BGP4-MIB',
                                $config['mibdir']
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
                        }
                        else {
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

                            $cbgp_data=  snmp_get_multi($device,$oids,'-OUQs ','CISCO-BGP4-MIB');
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
                        // Missing: cbgpPeerAdminLimit cbgpPeerPrefixThreshold cbgpPeerPrefixClearThreshold cbgpPeerSuppressedPrefixes cbgpPeerWithdrawnPrefixes
                        $safis['unicast']   = 1;
                        $safis['multicast'] = 2;

                        if (!isset($j_prefixes)) {
                            $j_prefixes = snmpwalk_cache_multi_oid($device, 'jnxBgpM2PrefixCountersTable', $jbgp, 'BGP4-V2-MIB-JUNIPER', $config['install_dir'].'/mibs/junos');
                        }

                        $cbgpPeerAcceptedPrefixes   = $j_prefixes[$junos[$peer_ip]['index'].".$afi.".$safis[$safi]]['jnxBgpM2PrefixInPrefixesAccepted'];
                        $cbgpPeerDeniedPrefixes     = $j_prefixes[$junos[$peer_ip]['index'].".$afi.".$safis[$safi]]['jnxBgpM2PrefixInPrefixesRejected'];
                        $cbgpPeerAdvertisedPrefixes = $j_prefixes[$junos[$peer_ip]['index'].".$afi.".$safis[$safi]]['jnxBgpM2PrefixOutPrefixes'];

                        unset($j_prefixes);
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

                    $cbgp_rrd = $config['rrd_dir'].'/'.$device['hostname'].'/'.safename('cbgp-'.$peer['bgpPeerIdentifier'].".$afi.$safi.rrd");
                    if (!is_file($cbgp_rrd)) {
                        $rrd_create = 'DS:AcceptedPrefixes:GAUGE:600:U:100000000000
                            DS:DeniedPrefixes:GAUGE:600:U:100000000000
                            DS:AdvertisedPrefixes:GAUGE:600:U:100000000000
                            DS:SuppressedPrefixes:GAUGE:600:U:100000000000
                            DS:WithdrawnPrefixes:GAUGE:600:U:100000000000 '.$config['rrd_rra'];
                        rrdtool_create($cbgp_rrd, $rrd_create);
                    }

                    $fields = array(
                        'AcceptedPrefixes'    => $cbgpPeerAcceptedPrefixes,
                        'DeniedPrefixes'      => $cbgpPeerDeniedPrefixes,
                        'AdvertisedPrefixes'  => $cbgpPeerAdvertisedPrefixes,
                        'SuppressedPrefixes'  => $cbgpPeerSuppressedPrefixes,
                        'WithdrawnPrefixes'   => $cbgpPeerWithdrawnPrefixes,
                    );
                    rrdtool_update("$cbgp_rrd", $fields);

                    $tags = array('bgpPeerIdentifier' => $peer['bgpPeerIdentifier'], 'afi' => $afi, 'safi' => $safi);
                    influx_update($device,'cbgp',$tags,$fields);

                } //end foreach
            } //end if
            echo "\n";
        } //end foreach
    } //end if
} //end if
