<?php

use LibreNMS\Config;
use LibreNMS\Util\IP;

global $link_exists;

if ($device['os'] == 'ironware') {
    echo ' Brocade FDP: ';
    $fdp_array = snmpwalk_group($device, 'snFdpCacheEntry', 'FOUNDRY-SN-SWITCH-GROUP-MIB', 2);

    foreach ($fdp_array as $key => $fdp_if_array) {
        $interface = get_port_by_ifIndex($device['device_id'], $key);
        d_echo($fdp_if_array);

        foreach ($fdp_if_array as $entry_key => $fdp) {
            $remote_device_id = find_device_id($fdp['snFdpCacheDeviceId']);

            if (! $remote_device_id &&
                ! can_skip_discovery($fdp['snFdpCacheDeviceId'], $fdp['snFdpCacheVersion'])
            ) {
                if (Config::get('autodiscovery.xdp') === true) {
                    $remote_device_id = discover_new_device($fdp['snFdpCacheDeviceId'], $device, 'FDP', $interface);
                }
            }

            $remote_port_id = find_port_id($fdp['snFdpCacheDevicePort'], '', $remote_device_id);
            discover_link(
                $interface['port_id'],
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

echo ' CISCO-CDP-MIB: ';
$cdp_array = snmpwalk_group($device, 'cdpCache', 'CISCO-CDP-MIB', 2);

foreach ($cdp_array as $key => $cdp_if_array) {
    $interface = get_port_by_ifIndex($device['device_id'], $key);

    foreach ($cdp_if_array as $entry_key => $cdp) {
        d_echo($cdp);

        $cdp_ip = IP::fromHexString($cdp['cdpCacheAddress'], true);
        $remote_device_id = find_device_id($cdp['cdpCacheDeviceId'], $cdp_ip);

        if (! $remote_device_id &&
            ! can_skip_discovery($cdp['cdpCacheDeviceId'], $cdp['cdpCacheVersion'], $cdp['cdpCachePlatform']) &&
            Config::get('autodiscovery.xdp') === true
        ) {
            $remote_device_id = discover_new_device($cdp['cdpCacheDeviceId'], $device, 'CDP', $interface);

            if (! $remote_device_id && Config::get('discovery_by_ip', false)) {
                $remote_device_id = discover_new_device($cdp_ip, $device, 'CDP', $interface);
            }
        }

        if ($interface['port_id'] && $cdp['cdpCacheDeviceId'] && $cdp['cdpCacheDevicePort']) {
            $remote_port_id = find_port_id($cdp['cdpCacheDevicePort'], '', $remote_device_id);
            discover_link(
                $interface['port_id'],
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
    }//end foreach
}//end foreach
echo PHP_EOL;

if (($device['os'] == 'routeros')) {
    echo ' LLDP-MIB: ';
    $lldp_array = snmpwalk_group($device, 'lldpRemEntry', 'LLDP-MIB', 3);
    if (! empty($lldp_array)) {
        // workaround for routeros returning the incorrect index
        if (! empty($lldp_array[0][0])) {
            $lldp_array = $lldp_array[0][0];
        }

        $lldp_ports = snmpwalk_group($device, 'mtxrInterfaceStatsName', 'MIKROTIK-MIB');
        $lldp_ports_num = snmpwalk_group($device, 'mtxrNeighborInterfaceID', 'MIKROTIK-MIB');

        foreach ($lldp_array as $key => $lldp) {
            $local_port_ifName = $lldp_ports[hexdec($lldp_ports_num[$key]['mtxrNeighborInterfaceID'])]['mtxrInterfaceStatsName'];
            $local_port_id = find_port_id($local_port_ifName, null, $device['device_id']);
            $interface = get_port_by_id($local_port_id);
            if ($lldp['lldpRemPortIdSubtype'] == 3) { // 3 = macaddress
                $remote_port_mac = str_replace([' ', ':', '-'], '', strtolower($lldp['lldpRemPortId']));
            }

            $remote_device_id = find_device_id($lldp['lldpRemSysName'], $lldp['lldpRemManAddr'], $remote_port_mac);

            if (! $remote_device_id &&
                \LibreNMS\Util\Validate::hostname($lldp['lldpRemSysName']) &&
                ! can_skip_discovery($lldp['lldpRemSysName'], $lldp['lldpRemSysDesc']) &&
                Config::get('autodiscovery.xdp') === true) {
                $remote_device_id = discover_new_device($lldp['lldpRemSysName'], $device, 'LLDP', $interface);
            }

            if ($interface['port_id'] && $lldp['lldpRemSysName'] && $lldp['lldpRemPortId']) {
                $remote_port_id = find_port_id($lldp['lldpRemPortDesc'], $lldp['lldpRemPortId'], $remote_device_id);
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
} elseif (($device['os'] == 'pbn' || $device['os'] == 'bdcom')) {
    echo ' NMS-LLDP-MIB: ';
    $lldp_array = snmpwalk_group($device, 'lldpRemoteSystemsData', 'NMS-LLDP-MIB');

    foreach ($lldp_array as $key => $lldp) {
        d_echo($lldp);
        $interface = get_port_by_ifIndex($device['device_id'], $lldp['lldpRemLocalPortNum']);
        $remote_device_id = find_device_id($lldp['lldpRemSysName']);

        if (! $remote_device_id &&
            \LibreNMS\Util\Validate::hostname($lldp['lldpRemSysName']) &&
            ! can_skip_discovery($lldp['lldpRemSysName'], $lldp['lldpRemSysDesc'] &&
            Config::get('autodiscovery.xdp') === true)
        ) {
            $remote_device_id = discover_new_device($lldp['lldpRemSysName'], $device, 'LLDP', $interface);
        }

        if ($interface['port_id'] && $lldp['lldpRemSysName'] && $lldp['lldpRemPortId']) {
            $remote_port_id = find_port_id($lldp['lldpRemPortDesc'], $lldp['lldpRemPortId'], $remote_device_id);
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
    echo PHP_EOL;
} elseif (($device['os'] == 'timos')) {
    echo ' TIMETRA-LLDP-MIB: ';
    $lldp_array = snmpwalk_group($device, 'tmnxLldpRemoteSystemsData', 'TIMETRA-LLDP-MIB');
    foreach ($lldp_array as $key => $lldp) {
        $ifIndex = key($lldp['tmnxLldpRemPortId']);
        $MacIndex = key($lldp['tmnxLldpRemPortId'][$ifIndex]);
        $RemIndex = key($lldp['tmnxLldpRemPortId'][$ifIndex][$MacIndex]);
        $interface = get_port_by_ifIndex($device['device_id'], $ifIndex);
        $remote_device_id = find_device_id($lldp['tmnxLldpRemSysName'][$ifIndex][$MacIndex][$RemIndex]);

        if (! $remote_device_id &&
            \LibreNMS\Util\Validate::hostname($lldp['tmnxLldpRemSysName'][$ifIndex][$MacIndex][$RemIndex]) &&
            ! can_skip_discovery($lldp['tmnxLldpRemSysName'][$ifIndex][$MacIndex][$RemIndex], $lldp['tmnxLldpRemSysDesc'][$ifIndex][$MacIndex][$RemIndex]) &&
            Config::get('autodiscovery.xdp') === true
        ) {
            $remote_device_id = discover_new_device($lldp['tmnxLldpRemSysName'][$ifIndex][$MacIndex][$RemIndex], $device, 'LLDP', $interface);
        }

        if ($interface['port_id'] && $lldp['tmnxLldpRemSysName'][$ifIndex][$MacIndex][$RemIndex] && $lldp['tmnxLldpRemPortId'][$ifIndex][$MacIndex][$RemIndex]) {
            $remote_port_id = find_port_id($lldp['tmnxLldpRemPortDesc'][$ifIndex][$MacIndex][$RemIndex], $lldp['tmnxLldpRemPortId'][$ifIndex][$MacIndex][$RemIndex], $remote_device_id);
            discover_link(
                $interface['port_id'],
                'lldp',
                $remote_port_id,
                $lldp['tmnxLldpRemSysName'][$ifIndex][$MacIndex][$RemIndex],
                $lldp['tmnxLldpRemPortId'][$ifIndex][$MacIndex][$RemIndex],
                null,
                $lldp['tmnxLldpRemSysDesc'][$ifIndex][$MacIndex][$RemIndex],
                $device['device_id'],
                $remote_device_id
            );
        }
    }//end foreach
    echo PHP_EOL;
} else {
    echo ' LLDP-MIB: ';
    $lldp_array = snmpwalk_group($device, 'lldpRemTable', 'LLDP-MIB', 3);
    if (! empty($lldp_array)) {
        $lldp_remAddr_num = snmpwalk_cache_multi_oid($device, '.1.0.8802.1.1.2.1.4.2.1.3', [], 'LLDP-MIB', null, '-OQun');
        foreach ($lldp_remAddr_num as $key => $value) {
            $res = preg_match("/1\.0\.8802\.1\.1\.2\.1\.4\.2\.1\.3\.([^\.]*)\.([^\.]*)\.([^\.]*)\.([^\.]*)\.([^\.]*).(([^\.]*)(\.([^\.]*))+)/", $key, $matches);
            if ($res) {
                //collect the Management IP address from the OID
                if ($matches[5] == 4) {
                    $lldp_array[$matches[1]][$matches[2]][$matches[3]]['lldpRemManAddr'] = $matches[6];
                } else {
                    $ipv6 = implode(
                        ':',
                        array_map(
                            function ($v) {
                                return sprintf('%02x', $v);
                            },
                            explode('.', $matches[6])
                        )
                    );
                    $ipv6 = preg_replace('/([^:]{2}):([^:]{2})/i', '$1$2', $ipv6);
                    $lldp_array[$matches[1]][$matches[2]][$matches[3]]['lldpRemManAddr'] = $ipv6;
                }
            }
        }
        if (($device['os'] == 'aos7')) {
            $lldp_local = snmpwalk_cache_oid($device, 'lldpLocPortEntry', [], 'LLDP-MIB');
            $lldp_ports = snmpwalk_group($device, 'lldpLocPortId', 'LLDP-MIB');
        } else {
            $dot1d_array = snmpwalk_group($device, 'dot1dBasePortIfIndex', 'BRIDGE-MIB');
            $lldp_ports = snmpwalk_group($device, 'lldpLocPortId', 'LLDP-MIB');
        }
    }

    foreach ($lldp_array as $key => $lldp_if_array) {
        foreach ($lldp_if_array as $entry_key => $lldp_instance) {
            if (($device['os'] == 'aos7')) {
                $ifName = $lldp_local[$entry_key]['lldpLocPortDesc'];
            } elseif (is_numeric($dot1d_array[$entry_key]['dot1dBasePortIfIndex'])) {
                $ifIndex = $dot1d_array[$entry_key]['dot1dBasePortIfIndex'];
            } else {
                $ifIndex = $entry_key;
            }
            if (($device['os'] == 'aos7')) {
                $local_port_id = find_port_id($ifName, null, $device['device_id']);
            } else {
                $local_port_id = find_port_id($lldp_ports[$entry_key]['lldpLocPortId'], $ifIndex, $device['device_id']);
            }
            $interface = get_port_by_id($local_port_id);

            d_echo($lldp_instance);

            foreach ($lldp_instance as $entry_instance => $lldp) {
                // normalize MAC address if present
                $remote_port_mac = '';
                $remote_port_name = $lldp['lldpRemPortId'];
                if ($lldp['lldpRemChassisIdSubtype'] == 4) { // 4 = macaddress
                    $remote_port_mac = str_replace([' ', ':', '-'], '', strtolower($lldp['lldpRemChassisId']));
                }
                if ($lldp['lldpRemPortIdSubtype'] == 3) { // 3 = macaddress
                    $remote_port_mac = str_replace([' ', ':', '-'], '', strtolower($lldp['lldpRemPortId']));
                }
                if ($lldp['lldpRemChassisIdSubtype'] == 6 || $lldp['lldpRemChassisIdSubtype'] == 2) { // 6=ifName 2=ifAlias
                    $remote_port_name = $lldp['lldpRemChassisId'];
                }

                $remote_device_id = find_device_id($lldp['lldpRemSysName'], $lldp['lldpRemManAddr'], $remote_port_mac);

                // add device if configured to do so
                if (! $remote_device_id && ! can_skip_discovery($lldp['lldpRemSysName'], $lldp['lldpRemSysDesc']) &&
                Config::get('autodiscovery.xdp') === true) {
                    $remote_device_id = discover_new_device($lldp['lldpRemSysName'], $device, 'LLDP', $interface);

                    if (! $remote_device_id && Config::get('discovery_by_ip', false)) {
                        $ptopo_array = snmpwalk_group($device, 'ptopoConnEntry', 'PTOPO-MIB');
                        d_echo($ptopo_array);
                        foreach ($ptopo_array as $ptopo) {
                            if (strcmp(trim($ptopo['ptopoConnRemoteChassis']), trim($lldp['lldpRemChassisId'])) == 0) {
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

                $remote_device = device_by_id_cache($remote_device_id);
                if ($remote_device['os'] == 'calix') {
                    $remote_port_name = 'EthPort ' . $lldp['lldpRemPortId'];
                }

                if ($remote_device['os'] == 'xos') {
                    $slot_port = explode(':', $remote_port_name);
                    if (sizeof($slot_port) == 2) {
                        $n_slot = (int) $slot_port[0];
                        $n_port = (int) $slot_port[1];
                    } else {
                        $n_slot = 1;
                        $n_port = (int) $slot_port[0];
                    }
                    $remote_port_name = (string) ($n_slot * 1000 + $n_port);
                }

                $remote_port_id = find_port_id(
                    $lldp['lldpRemPortDesc'],
                    $remote_port_name,
                    $remote_device_id,
                    $remote_port_mac
                );
                if ($remote_port_id == 0) { //We did not find it
                    $remote_port_name = $remote_port_name . ' (' . $remote_port_mac . ')';
                }
                if (empty($lldp['lldpRemSysName'])) {
                    $lldp['lldpRemSysName'] = $remote_device['sysName'] ?: $remote_device['hostname'];
                }
                if (empty($lldp['lldpRemSysName'])) {
                    $lldp['lldpRemSysName'] = $lldp['lldpRemSysDesc'];
                }
                if ($interface['port_id'] && $lldp['lldpRemSysName'] && $remote_port_name) {
                    discover_link(
                        $interface['port_id'],
                        'lldp',
                        $remote_port_id,
                        $lldp['lldpRemSysName'],
                        $remote_port_name,
                        null,
                        $lldp['lldpRemSysDesc'],
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

if (Config::get('autodiscovery.ospf') === true) {
    echo ' OSPF Discovery: ';
    $sql = 'SELECT DISTINCT(`ospfNbrIpAddr`),`device_id` FROM `ospf_nbrs` WHERE `device_id`=?';
    foreach (dbFetchRows($sql, [$device['device_id']]) as $nbr) {
        try {
            $ip = IP::parse($nbr['ospfNbrIpAddr']);

            if ($ip->inNetworks(Config::get('autodiscovery.nets-exclude'))) {
                echo 'x';
                continue;
            }

            if (! $ip->inNetworks(Config::get('nets'))) {
                echo 'i';
                continue;
            }

            $name = gethostbyaddr($ip);
            $remote_device_id = discover_new_device($name, $device, 'OSPF');
        } catch (\LibreNMS\Exceptions\InvalidIpException $e) {
            //
        }
    }
    echo PHP_EOL;
}

d_echo($link_exists);

$sql = 'SELECT * FROM `links` AS L, `ports` AS I WHERE L.local_port_id = I.port_id AND I.device_id = ?';
foreach (dbFetchRows($sql, [$device['device_id']]) as $test) {
    $local_port_id = $test['local_port_id'];
    $remote_hostname = $test['remote_hostname'];
    $remote_port = $test['remote_port'];
    d_echo("$local_port_id -> $remote_hostname -> $remote_port \n");

    if (! $link_exists[$local_port_id][$remote_hostname][$remote_port]) {
        echo '-';
        $rows = dbDelete('links', '`id` = ?', [$test['id']]);
        d_echo("$rows deleted ");
    }
}

// remove orphaned links
$deleted = (int) dbDeleteOrphans('links', ['devices.device_id.local_device_id']);
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
