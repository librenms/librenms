<?php

use App\Facades\LibrenmsConfig;
use App\Models\Link;
use App\Models\Ospfv3Nbr;
use LibreNMS\Util\IP;
use LibreNMS\Util\StringHelpers;

global $link_exists;

if ($device['os'] == 'ironware') {
    echo ' Brocade FDP: ';
    $fdp_array = SnmpQuery::hideMib()->walk('FOUNDRY-SN-SWITCH-GROUP-MIB::snFdpCacheEntry')->table(2);

    foreach ($fdp_array as $ifIndex => $fdp_if_array) {
        $interface = \App\Facades\PortCache::getByIfIndex($ifIndex, $device['device_id']);
        d_echo($fdp_if_array);

        foreach ($fdp_if_array as $fdp) {
            $remote_device_id = find_device_id($fdp['snFdpCacheDeviceId']);

            if (! $remote_device_id &&
                    ! can_skip_discovery($fdp['snFdpCacheDeviceId'], $fdp['snFdpCacheVersion'])
            ) {
                if (LibrenmsConfig::get('autodiscovery.xdp') === true) {
                    $remote_device_id = discover_new_device($fdp['snFdpCacheDeviceId'], $device, 'FDP', $interface);
                }
            }

            $remote_port_id = find_port_id($fdp['snFdpCacheDevicePort'], '', $remote_device_id);
            discover_link(
                $interface->port_id,
                $fdp['snFdpCacheVendorId'],
                $remote_port_id,
                $fdp['snFdpCacheDeviceId'],
                $fdp['snFdpCacheDevicePort'],
                $fdp['snFdpCachePlatform'],
                $fdp['snFdpCacheVersion'],
                $device['device_id'],
                $remote_device_id
            );
        }
    }//end foreach
    echo PHP_EOL;
}//end if

if (isset($device['os_group']) && $device['os_group'] == 'cisco') {
    echo ' CISCO-CDP-MIB: ';
    $cdp_array = SnmpQuery::hideMib()->walk('CISCO-CDP-MIB::cdpCache')->table(2);

    foreach ($cdp_array as $ifIndex => $cdp_if_array) {
        $interface = PortCache::getByIfIndex($ifIndex, $device['device_id']);

        foreach ($cdp_if_array as $cdp) {
            d_echo($cdp);

            if (! isset($cdp['cdpCacheDeviceId'])) {
                continue;
            }

            $cdp_ip = null;
            if (isset($cdp['cdpCacheAddress'])) {
                $cdp_ip = IP::fromHexString($cdp['cdpCacheAddress'], true);
            }

            $remote_device_id = find_device_id($cdp['cdpCacheDeviceId'], $cdp_ip);

            if (
                ! $remote_device_id &&
                ! can_skip_discovery($cdp['cdpCacheDeviceId'], $cdp['cdpCacheVersion'], $cdp['cdpCachePlatform']) &&
                LibrenmsConfig::get('autodiscovery.xdp') === true
            ) {
                $remote_device_id = discover_new_device($cdp['cdpCacheDeviceId'], $device, 'CDP', $interface);

                if ($cdp_ip && ! $remote_device_id && LibrenmsConfig::get('discovery_by_ip', false)) {
                    $remote_device_id = discover_new_device($cdp_ip, $device, 'CDP', $interface);
                }
            }

            if ($interface?->port_id && $cdp['cdpCacheDeviceId'] && $cdp['cdpCacheDevicePort']) {
                $remote_port_id = find_port_id($cdp['cdpCacheDevicePort'], '', $remote_device_id);
                discover_link(
                    $interface->port_id,
                    'cdp',
                    $remote_port_id,
                    $cdp['cdpCacheDeviceId'],
                    $cdp['cdpCacheDevicePort'],
                    $cdp['cdpCachePlatform'],
                    $cdp['cdpCacheVersion'],
                    $device['device_id'],
                    $remote_device_id
                );
            }
        } //end foreach
    } //end foreach
    echo PHP_EOL;
}//end if

if (($device['os'] == 'routeros') && version_compare($device['version'], '7.7', '<')) {
    echo ' LLDP-MIB: ';
    $lldp_array = SnmpQuery::hideMib()->walk('LLDP-MIB::lldpRemEntry')->table(3);
    if (! empty($lldp_array)) {
        // workaround for routeros returning the incorrect index
        if (! empty($lldp_array[0][0])) {
            $lldp_array = $lldp_array[0][0];
        }

        $lldp_ports = SnmpQuery::hideMib()->walk('MIKROTIK-MIB::mtxrInterfaceStatsName')->table();
        $lldp_ports_num = SnmpQuery::hideMib()->walk('MIKROTIK-MIB::mtxrNeighborInterfaceID')->table();

        foreach ($lldp_array as $key => $lldp) {
            if (! isset($lldp_ports_num['mtxrNeighborInterfaceID'][$key]) ||
                ! isset($lldp_ports['mtxrInterfaceStatsName'][hexdec($lldp_ports_num['mtxrNeighborInterfaceID'][$key])])
            ) {
                continue;
            }

            $local_port_id = find_port_id($lldp_ports['mtxrInterfaceStatsName'][hexdec($lldp_ports_num['mtxrNeighborInterfaceID'][$key])], null, $device['device_id']);
            $interface = get_port_by_id($local_port_id);

            if ($lldp['lldpRemPortIdSubtype'] == 3) { // 3 = macaddress
                $remote_port_mac = str_replace([' ', ':', '-'], '', strtolower((string) $lldp['lldpRemPortId']));
            }

            $remote_device_id = find_device_id($lldp['lldpRemSysName'], $lldp['lldpRemManAddr'] ?? '', $remote_port_mac ?? '');

            if (! $remote_device_id &&
                    \LibreNMS\Util\Validate::hostname($lldp['lldpRemSysName']) &&
                    ! can_skip_discovery($lldp['lldpRemSysName'], $lldp['lldpRemSysDesc']) &&
                    LibrenmsConfig::get('autodiscovery.xdp') === true) {
                $remote_device_id = discover_new_device($lldp['lldpRemSysName'], $device, 'LLDP', $interface);
            }

            if ($interface['port_id'] && $lldp['lldpRemSysName'] && $lldp['lldpRemPortId']) {
                $remote_port_id = find_port_id($lldp['lldpRemPortDesc'] ?? '', $lldp['lldpRemPortId'], $remote_device_id);
                discover_link(
                    $interface['port_id'],
                    'lldp',
                    $remote_port_id,
                    $lldp['lldpRemSysName'],
                    $lldp['lldpRemPortId'],
                    null,
                    $lldp['lldpRemSysDesc'],
                    $device['device_id'],
                    $remote_device_id
                );
            }
        }//end foreach
    }
    echo PHP_EOL;
} elseif ($device['os'] == 'pbn' || $device['os'] == 'bdcom' || $device['os'] == 'fs-bdcom') {
    echo ' NMS-LLDP-MIB: ';
    $lldp_array = SnmpQuery::hideMib()->walk('NMS-LLDP-MIB::lldpRemoteSystemsData')->table(2);
    foreach ($lldp_array as $lldp_array_inner) {
        foreach ($lldp_array_inner as $lldp) {
            d_echo($lldp);
            $interface = PortCache::getByIfIndex($lldp['lldpRemLocalPortNum'] ?? null, $device['device_id']);
            $remote_device_id = find_device_id($lldp['lldpRemSysName'] ?? null);

            if (LibrenmsConfig::get('autodiscovery.xdp') && isset($lldp['lldpRemSysName']) && ! $remote_device_id &&
                    \LibreNMS\Util\Validate::hostname($lldp['lldpRemSysName']) &&
                    ! can_skip_discovery($lldp['lldpRemSysName'], $lldp['lldpRemSysDesc'])
            ) {
                $remote_device_id = discover_new_device($lldp['lldpRemSysName'], $device, 'LLDP', $interface);
            }

            if ($interface?->port_id && $lldp['lldpRemSysName'] && $lldp['lldpRemPortId']) {
                $remote_port_id = find_port_id($lldp['lldpRemPortDesc'], $lldp['lldpRemPortId'], $remote_device_id);
                discover_link(
                    $interface->port_id,
                    'lldp',
                    $remote_port_id,
                    $lldp['lldpRemSysName'],
                    $lldp['lldpRemPortId'],
                    null,
                    $lldp['lldpRemSysDesc'],
                    $device['device_id'],
                    $remote_device_id
                );
            }
        } //end foreach $lldp_array_inner
    }//end foreach $lldp_array
    echo PHP_EOL;
} elseif ($device['os'] == 'timos') {
    echo ' TIMETRA-LLDP-MIB: ';
    $lldp_array = SnmpQuery::hideMib()->walk('TIMETRA-LLDP-MIB::tmnxLldpRemoteSystemsData')->table(4);
    foreach ($lldp_array as $sub_lldp_1) {
        foreach ($sub_lldp_1 as $ifIndex => $sub_lldp_2) {
            foreach ($sub_lldp_2 as $sub_lldp_3) {
                foreach ($sub_lldp_3 as $lldp) {
                    $interface = PortCache::getByIfIndex($ifIndex, $device['device_id']);
                    $remote_device_id = find_device_id($lldp['tmnxLldpRemSysName']);

                    if (! $remote_device_id &&
                            \LibreNMS\Util\Validate::hostname($lldp['tmnxLldpRemSysName']) &&
                            ! can_skip_discovery($lldp['tmnxLldpRemSysName'], $lldp['tmnxLldpRemSysDesc']) &&
                            LibrenmsConfig::get('autodiscovery.xdp') === true
                    ) {
                        $remote_device_id = discover_new_device($lldp['tmnxLldpRemSysName'], $device, 'LLDP', $interface);
                    }

                    if ($interface?->port_id && $lldp['tmnxLldpRemSysName'] && $lldp['tmnxLldpRemPortId']) {
                        $remote_port_id = find_port_id($lldp['tmnxLldpRemPortDesc'], $lldp['tmnxLldpRemPortId'], $remote_device_id);
                        discover_link(
                            $interface->port_id,
                            'lldp',
                            $remote_port_id,
                            $lldp['tmnxLldpRemSysName'],
                            $lldp['tmnxLldpRemPortId'],
                            null,
                            $lldp['tmnxLldpRemSysDesc'],
                            $device['device_id'],
                            $remote_device_id
                        );
                    }
                }
            }
        }
    }//end foreach
    echo PHP_EOL;
} elseif ($device['os'] == 'jetstream') {
    echo ' JETSTREAM-LLDP MIB: ';

    $lldp = SnmpQuery::hideMib()->walk('TPLINK-LLDPINFO-MIB::lldpNeighborInfoEntry')->table();

    if (isset($lldp['lldpNeighborPortIndexId']) && is_array($lldp['lldpNeighborPortIndexId'])) {
        foreach ($lldp['lldpNeighborPortIndexId'] as $IndexId => $lldp_data) {
            if (! is_array($lldp_data)) {
                // code below will fail so no need to finish this loop occurence.
                continue;
            }

            $interface = PortCache::getByIfIndex($IndexId, $device['device_id']);
            if (empty($interface->port_id)) {
                $local_ifName = $lldp['lldpNeighborPortId'][$IndexId][1];
                $local_port_id = find_port_id('gigabitEthernet ' . $local_ifName, null, $device['device_id']);
                $interface = PortCache::get($local_port_id);
            }

            $remote_device_id = find_device_id($lldp['lldpNeighborDeviceName'][$IndexId][1]);
            $remote_device_name = $lldp['lldpNeighborDeviceName'][$IndexId][1];
            $remote_device_sysDescr = $lldp['lldpNeighborDeviceDescr'][$IndexId][1];
            $remote_device_ip = $lldp['lldpNeighborManageIpAddr'][$IndexId][1];
            $remote_port_descr = $lldp['lldpNeighborPortIdDescr'][$IndexId][1];
            $remote_port_id = find_port_id($remote_port_descr, null, $remote_device_id);

            if (! $remote_device_id &&
                    \LibreNMS\Util\Validate::hostname($remote_device_name) &&
                    ! can_skip_discovery($remote_device_name, $remote_device_ip) &&
                    LibrenmsConfig::get('autodiscovery.xdp') === true) {
                $remote_device_id = discover_new_device($remote_device_name, $device, 'LLDP', $interface);
            }

            if ($interface?->port_id && $remote_device_name && $remote_port_descr) {
                discover_link(
                    $interface->port_id, //our port id from database
                    'lldp',
                    $remote_port_id, //remote port id from database if applicable
                    $remote_device_name, //remote device name from SNMP walk
                    $remote_port_descr, //remote port description from SNMP walk
                    null,
                    $remote_device_sysDescr, //remote device description from SNMP walk
                    $device['device_id'], //our device id
                    $remote_device_id //remote device id if applicable
                );
            }
        }
    }
    echo PHP_EOL;
} else {
    echo ' LLDP-MIB: ';
    $lldp_array = SnmpQuery::hideMib()->walk('LLDP-MIB::lldpRemTable')->table(3);
    if (! empty($lldp_array)) {
        $lldp_remAddr_num = SnmpQuery::hideMib()->numeric()->walk('.1.0.8802.1.1.2.1.4.2.1.3')->values();
        foreach ($lldp_remAddr_num as $key => $value) {
            $res = preg_match("/1\.0\.8802\.1\.1\.2\.1\.4\.2\.1\.3\.([^\.]*)\.([^\.]*)\.([^\.]*)\.([^\.]*)\.([^\.]*).(([^\.]*)(\.([^\.]*))+)/", (string) $key, $matches);
            if ($res) {
                //collect the Management IP address from the OID
                if ($matches[5] == 4) {
                    $lldp_array[$matches[1]][$matches[2]][$matches[3]]['lldpRemManAddr'] = $matches[6];
                } else {
                    $ipv6 = implode(
                        ':',
                        array_map(
                            fn ($v) => sprintf('%02x', $v),
                            explode('.', $matches[6])
                        )
                    );
                    $ipv6 = preg_replace('/([^:]{2}):([^:]{2})/i', '$1$2', $ipv6);
                    $lldp_array[$matches[1]][$matches[2]][$matches[3]]['lldpRemManAddr'] = $ipv6;
                }
            }
        }
        if ($device['os'] == 'aos7') {
            $lldp_local = snmpwalk_cache_oid($device, 'lldpLocPortEntry', [], 'LLDP-MIB');
            $lldp_ports = snmpwalk_group($device, 'lldpLocPortId', 'LLDP-MIB');
        } else {
            $dot1d_array = snmpwalk_group($device, 'dot1dBasePortIfIndex', 'BRIDGE-MIB');
            $lldp_ports = snmpwalk_group($device, 'lldpLocPortId', 'LLDP-MIB');
        }
    } else {
        echo ' LLDP-V2-MIB: ';
        $lldpv2_array = SnmpQuery::hideMib()->walk('LLDP-V2-MIB::lldpV2RemTable')->table(4);
    }

    $mapV2toV1 = [
        'lldpV2RemChassisIdSubtype' => 'lldpRemChassisIdSubtype',
        'lldpV2RemChassisId' => 'lldpRemChassisId',
        'lldpV2RemPortIdSubtype' => 'lldpRemPortIdSubtype',
        'lldpV2RemPortId' => 'lldpRemPortId',
        'lldpV2RemPortDesc' => 'lldpRemPortDesc',
        'lldpV2RemSysName' => 'lldpRemSysName',
        'lldpV2RemSysDesc' => 'lldpRemSysDesc',
        'lldpV2RemSysCapSupported' => 'lldpRemSysCapSupported',
        'lldpV2RemSysCapEnabled' => 'lldpRemSysCapEnabled',
        'lldpV2RemRemoteChanges' => 'lldpRemRemoteChanges',
        'lldpV2RemTooManyNeighbors' => 'lldpRemTooManyNeighbors',
        'lldpV2RemManAddrTable' => 'lldpRemManAddrTable',
        'lldpV2RemManAddrEntry' => 'lldpRemManAddrEntry',
        'lldpV2RemManAddrSubtype' => 'lldpRemManAddrSubtype',
        'lldpV2RemManAddr' => 'lldpRemManAddr',
        'lldpV2RemManAddrIfSubtype' => 'lldpRemManAddrIfSubtype',
        'lldpV2RemManAddrIfId' => 'lldpRemManAddrIfId',
        'lldpV2RemManAddrOID' => 'lldpRemManAddrOID',
    ];

    if (! empty($lldpv2_array)) {
        // map it to lldp_array
        foreach ($lldpv2_array as $lldpV2RemTimeMark => $timeMark_data) {
            if (! is_array($timeMark_data)) {
                continue;
            }
            foreach ($timeMark_data as $lldpV2RemLocalIfIndex => $ifIndex_data) {
                if (! is_array($ifIndex_data)) {
                    continue;
                }
                foreach ($ifIndex_data as $lldpV2RemLocalDestMACAddress => $mac_data) {
                    if (! is_array($mac_data)) {
                        continue;
                    }
                    foreach ($mac_data as $lldpV2RemIndex => $lldpv2_array_entries) {
                        if (! is_array($lldpv2_array_entries)) {
                            continue;
                        }
                        foreach ($lldpv2_array_entries as $key => $entry_value) {
                            $newKey = $mapV2toV1[$key] ?? $key;
                            $lldp_array[$lldpV2RemTimeMark][$lldpV2RemLocalIfIndex][$lldpV2RemIndex][$newKey] = $entry_value;
                        }
                        $lldp_array[$lldpV2RemTimeMark][$lldpV2RemLocalIfIndex][$lldpV2RemIndex]['lldpRemLocalDestMACAddress'] = $lldpV2RemLocalDestMACAddress;
                    }
                }
            }
        }
    }

    foreach ($lldp_array as $lldp_if_array) {
        foreach ($lldp_if_array as $entry_key => $lldp_instance) {
            if ($device['os'] == 'aos7') {
                if (! isset($lldp_local[$entry_key]['lldpLocPortDesc'])) {
                    continue;
                }
                $ifName = $lldp_local[$entry_key]['lldpLocPortDesc'];
            } elseif ($device['os'] == 'routeros') {
                $ifIndex = $entry_key;
            } elseif (isset($dot1d_array) && isset($dot1d_array[$entry_key]) && is_numeric($dot1d_array[$entry_key]['dot1dBasePortIfIndex'])) {
                $ifIndex = $dot1d_array[$entry_key]['dot1dBasePortIfIndex'];
            } else {
                $ifIndex = $entry_key;
            }
            if ($device['os'] == 'aos7') {
                $local_port_id = find_port_id($ifName, null, $device['device_id']);
            } else {
                $local_port_id = find_port_id($lldp_ports[$entry_key]['lldpLocPortId'] ?? null, $ifIndex, $device['device_id']);
            }
            $interface = get_port_by_id($local_port_id);

            d_echo($lldp_instance);

            if (! is_array($lldp_instance)) {
                continue;
            }

            foreach ($lldp_instance as $lldp) {
                // If lldpRemPortIdSubtype is 5 and lldpRemPortId is hex, convert it to ASCII.
                if (isset($lldp['lldpRemPortId']) && $lldp['lldpRemPortIdSubtype'] == 5 && ctype_xdigit(str_replace([' ', ':', '-'], '', strtolower((string) $lldp['lldpRemPortId'])))) {
                    $lldp['lldpRemPortId'] = StringHelpers::hexToAscii($lldp['lldpRemPortId'], ':');
                }
                // normalize MAC address if present
                $remote_port_mac = '';
                $remote_port_name = $lldp['lldpRemPortId'] ?? null;
                if (isset($lldp['lldpRemChassisIdSubtype']) && $lldp['lldpRemChassisIdSubtype'] == 4 && isset($lldp['lldpRemChassisId'])) { // 4 = macaddress
                    $remote_port_mac = str_replace([' ', ':', '-'], '', strtolower((string) $lldp['lldpRemChassisId']));
                }
                if (isset($lldp['lldpRemPortIdSubtype']) && $lldp['lldpRemPortIdSubtype'] == 3 && isset($lldp['lldpRemPortId'])) { // 3 = macaddress
                    $remote_port_mac = str_replace([' ', ':', '-'], '', strtolower((string) $lldp['lldpRemPortId']));
                }
                if (isset($lldp['lldpRemChassisId']) && isset($lldp['lldpRemChassisIdSubtype']) && ($lldp['lldpRemChassisIdSubtype'] == 6 || $lldp['lldpRemChassisIdSubtype'] == 2)) { // 6=ifName 2=ifAlias
                    $remote_port_name = $lldp['lldpRemChassisId'];
                }
                // Linksys / Cisco SRW2016/24/48 all have lldpRemSysDesc Ethernet Interface, which makes all lldp mappings go to port g1.
                // ex:
                //     'lldpRemSysDesc' => '16-Port 10/100/1000 Gigabit Switch w/WebView',
                if (isset($lldp['lldpRemSysDesc']) && str_ends_with((string) $lldp['lldpRemSysDesc'], 'Gigabit Switch w/WebView')) {
                    $lldp['lldpRemPortDesc'] = '';
                }

                $remote_device_id = find_device_id($lldp['lldpRemSysName'] ?? '', $lldp['lldpRemManAddr'] ?? '', $remote_port_mac);
                // add device if configured to do so
                if (! $remote_device_id && LibrenmsConfig::get('autodiscovery.xdp') && ! can_skip_discovery($lldp['lldpRemSysName'] ?? null, $lldp['lldpRemSysDesc'] ?? null)) {
                    if (isset($lldp['lldpRemSysName'])) {
                        $remote_device_id = discover_new_device($lldp['lldpRemSysName'], $device, 'LLDP', $interface);
                    }
                    if (! $remote_device_id && LibrenmsConfig::get('discovery_by_ip', false)) {
                        $ptopo_array = snmpwalk_cache_multi_oid($device, 'ptopoConnEntry', [], 'PTOPO-MIB');
                        d_echo($ptopo_array);
                        foreach ($ptopo_array as $ptopo) {
                            if (strcmp(trim((string) $ptopo['ptopoConnRemoteChassis']), trim((string) $lldp['lldpRemChassisId'])) == 0) {
                                $ip = IP::fromHexString($ptopo['ptopoConnAgentNetAddr'], true);
                                $remote_device_id = discover_new_device($ip, $device, 'LLDP', $interface);
                                break;
                            }
                        }
                        if (! $remote_device_id && isset($lldp['lldpRemManAddr'])) {
                            $remote_device_id = discover_new_device($lldp['lldpRemManAddr'], $device, 'LLDP', $interface);
                        }
                        unset($ptopo_array);
                    }
                }

                if (! empty($remote_device_id)) {
                    $remote_device = device_by_id_cache($remote_device_id);
                    if ($remote_device['os'] == 'calix') {
                        $remote_port_name = 'EthPort ' . $lldp['lldpRemPortId'];
                    }

                    if ($remote_device['os'] == 'xos') {
                        $slot_port = explode(':', (string) $remote_port_name);
                        if (count($slot_port) == 2) {
                            $n_slot = (int) $slot_port[0];
                            $n_port = (int) $slot_port[1];
                        } else {
                            $n_slot = 1;
                            $n_port = (int) $slot_port[0];
                        }
                        $remote_port_name = (string) ($n_slot * 1000 + $n_port);
                    }

                    if ($remote_device['os'] == 'netgear' &&
                            $remote_device['sysDescr'] == 'GS108T' &&
                            $lldp['lldpRemSysDesc'] == 'Smart Switch') {
                        // Some netgear switches, as Netgear GS108Tv1 presents it's port name over snmp as
                        // "Port 1 Gigabit Ethernet" but as 'lldpRemPortId' => 'g1' and
                        // 'lldpRemPortDesc' => 'Port #1' over lldp.
                        // So remap g1 to 1 so it matches ifIndex
                        if (preg_match("/^g(\d+)$/", (string) $lldp['lldpRemPortId'], $matches)) {
                            $remote_port_name = $matches[1];
                        }
                    }
                }

                $remote_port_id = find_port_id(
                    $lldp['lldpRemPortDesc'] ?? null,
                    $remote_port_name,
                    $remote_device_id,
                    $remote_port_mac
                );
                if ($remote_port_id == 0) { //We did not find it
                    $remote_port_name = $remote_port_name . ' (' . $remote_port_mac . ')';
                }
                if (empty($lldp['lldpRemSysName']) && isset($remote_device)) {
                    $lldp['lldpRemSysName'] = $remote_device['sysName'] ?: $remote_device['hostname'];
                }
                if (empty($lldp['lldpRemSysName']) && isset($lldp['lldpRemSysDesc'])) {
                    $lldp['lldpRemSysName'] = $lldp['lldpRemSysDesc'];
                }
                if (is_array($interface) && $interface['port_id'] && isset($lldp['lldpRemSysName']) && $lldp['lldpRemSysName'] && $remote_port_name) {
                    discover_link(
                        $interface['port_id'],
                        'lldp',
                        $remote_port_id,
                        $lldp['lldpRemSysName'],
                        $remote_port_name,
                        null,
                        $lldp['lldpRemSysDesc'] ?? null,
                        $device['device_id'],
                        $remote_device_id
                    );
                }
            }//end foreach
        }//end foreach
    }//end foreach

    unset(
        $dot1d_array
    );
    echo PHP_EOL;
}//end elseif

if (LibrenmsConfig::get('autodiscovery.ospf') === true) {
    echo ' OSPF Discovery: ';
    $sql = 'SELECT DISTINCT(`ospfNbrIpAddr`),`device_id` FROM `ospf_nbrs` WHERE `device_id`=?';
    foreach (dbFetchRows($sql, [$device['device_id']]) as $nbr) {
        try {
            $ip = IP::parse($nbr['ospfNbrIpAddr']);

            if ($ip->inNetworks(LibrenmsConfig::get('autodiscovery.nets-exclude'))) {
                echo 'x';
                continue;
            }

            if (! $ip->inNetworks(LibrenmsConfig::get('nets'))) {
                echo 'i';
                continue;
            }

            $name = gethostbyaddr($ip);
            $remote_device_id = discover_new_device($name, $device, 'OSPF');
        } catch (\LibreNMS\Exceptions\InvalidIpException) {
            //
        }
    }
    echo PHP_EOL;
}

if (LibrenmsConfig::get('autodiscovery.ospfv3') === true) {
    echo ' OSPFv3 Discovery: ';
    $ospf_nbrs = Ospfv3Nbr::select('ospfv3NbrAddress', 'device_id')
       ->distinct()
       ->where('device_id', $device['device_id'])
       ->get();
    foreach ($ospf_nbrs as $nbr) {
        try {
            $ip = IP::parse($nbr->ospfv3NbrAddress);

            if ($ip->inNetworks(LibrenmsConfig::get('autodiscovery.nets-exclude'))) {
                echo 'x';
                continue;
            }

            if (! $ip->inNetworks(LibrenmsConfig::get('nets'))) {
                echo 'i';
                continue;
            }

            $name = gethostbyaddr($ip);
            $remote_device_id = discover_new_device($name, $device, 'OSPFv3');
        } catch (\LibreNMS\Exceptions\InvalidIpException) {
            //
        }
    }
    echo PHP_EOL;
}

d_echo($link_exists);

$sql = 'SELECT * FROM `links` AS L LEFT JOIN `ports` AS I ON L.local_port_id = I.port_id WHERE L.local_device_id = ?';
foreach (dbFetchRows($sql, [$device['device_id']]) as $test) {
    $local_port_id = $test['local_port_id'];
    $remote_hostname = $test['remote_hostname'];
    $remote_port = $test['remote_port'];
    d_echo("$local_port_id -> $remote_hostname -> $remote_port \n");

    if (! isset($link_exists[$local_port_id][$remote_hostname][$remote_port])) {
        echo '-';
        $rows = dbDelete('links', '`id` = ?', [$test['id']]);
        d_echo("$rows deleted ");
    }
}

// remove orphaned links
$deleted = Link::doesntHave('device')->delete();
echo str_repeat('-', $deleted);
d_echo(" $deleted orphaned links deleted\n");

unset(
    $link_exists,
    $sql,
    $fdp_array,
    $cdp_array,
    $lldp_array,
    $deleted
);
