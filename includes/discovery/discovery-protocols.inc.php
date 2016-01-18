<?php

echo 'Discovery protocols:';

global $link_exists;

$community = $device['community'];

if ($device['os'] == 'ironware' && $config['autodiscovery']['xdp'] === true) {
    echo ' Brocade FDP: ';
    $fdp_array = snmpwalk_cache_twopart_oid($device, 'snFdpCacheEntry', array(), 'FOUNDRY-SN-SWITCH-GROUP-MIB');
    d_echo($fdp_array);
    if ($fdp_array) {
        unset($fdp_links);
        foreach (array_keys($fdp_array) as $key) {
            $interface    = dbFetchRow('SELECT * FROM `ports` WHERE `device_id` = ? AND `ifIndex` = ?', array($device['device_id'], $key));
            $fdp_if_array = $fdp_array[$key];
            d_echo($fdp_if_array);
            foreach (array_keys($fdp_if_array) as $entry_key) {
                $fdp              = $fdp_if_array[$entry_key];
                $remote_device_id = dbFetchCell('SELECT `device_id` FROM `devices` WHERE `sysName` = ? OR `hostname` = ?', array($fdp['snFdpCacheDeviceId'], $fdp['snFdpCacheDeviceId']));

                if (!$remote_device_id) {
                    $remote_device_id = discover_new_device($fdp['snFdpCacheDeviceId'], $device, 'FDP', $interface);
                }

                if ($remote_device_id) {
                    $if             = $fdp['snFdpCacheDevicePort'];
                    $remote_port_id = dbFetchCell('SELECT port_id FROM `ports` WHERE (`ifDescr` = ? OR `ifName` = ?) AND `device_id` = ?', array($if, $if, $remote_device_id));
                }
                else {
                    $remote_port_id = '0';
                }

                discover_link($interface['port_id'], $fdp['snFdpCacheVendorId'], $remote_port_id, $fdp['snFdpCacheDeviceId'], $fdp['snFdpCacheDevicePort'], $fdp['snFdpCachePlatform'], $fdp['snFdpCacheVersion'], $device['device_id'], $remote_device_id);
            }
        }//end foreach
    }//end if
}//end if

echo ' CISCO-CDP-MIB: ';
unset($cdp_array);
if ($config['autodiscovery']['xdp'] === true) {
    $cdp_array = snmpwalk_cache_twopart_oid($device, 'cdpCache', array(), 'CISCO-CDP-MIB');
    d_echo($cdp_array);
    if ($cdp_array) {
        unset($cdp_links);
        foreach (array_keys($cdp_array) as $key) {
            $interface        = dbFetchRow('SELECT * FROM `ports` WHERE device_id = ? AND `ifIndex` = ?', array($device['device_id'], $key));
            $cdp_if_array = $cdp_array[$key];
            d_echo($cdp_if_array);
            foreach (array_keys($cdp_if_array) as $entry_key) {
                $cdp = $cdp_if_array[$entry_key];
                if (is_valid_hostname($cdp['cdpCacheDeviceId'])) {
                    $remote_device_id = dbFetchCell('SELECT `device_id` FROM `devices` WHERE `sysName` = ? OR `hostname` = ?', array($cdp['cdpCacheDeviceId'], $cdp['cdpCacheDeviceId']));

                    if (!$remote_device_id) {
                        $remote_device_id = discover_new_device($cdp['cdpCacheDeviceId'], $device, 'CDP', $interface);
                    }

                    if ($remote_device_id) {
                        $if             = $cdp['cdpCacheDevicePort'];
                        $remote_port_id = dbFetchCell('SELECT port_id FROM `ports` WHERE (`ifDescr` = ? OR `ifName` = ?) AND `device_id` = ?', array($if, $if, $remote_device_id));
                    }
                    else {
                        $remote_port_id = '0';
                    }

                    if ($interface['port_id'] && $cdp['cdpCacheDeviceId'] && $cdp['cdpCacheDevicePort']) {
                        discover_link($interface['port_id'], 'cdp', $remote_port_id, $cdp['cdpCacheDeviceId'], $cdp['cdpCacheDevicePort'], $cdp['cdpCachePlatform'], $cdp['cdpCacheVersion'], $device['device_id'], $remote_device_id);
                    }
                }
                else {
                    echo 'X';
                }//end if
            }//end foreach
        }//end foreach
    }//end if
}//end if


unset($lldp_array);

if ($device['os'] == 'pbn' && $config['autodiscovery']['xdp'] === true) {

    echo ' NMS-LLDP-MIB: '; 
    $lldp_array  = snmpwalk_cache_oid($device, 'lldpRemoteSystemsData', array(), 'NMS-LLDP-MIB');
    d_echo($lldp_array);
    if ($lldp_array) {
        unset($lldp_links);
        foreach (array_keys($lldp_array) as $key) {
            $lldp = $lldp_array[$key];
            d_echo($lldp);
            $interface     = dbFetchRow('SELECT * FROM `ports` WHERE `device_id` = ? AND `ifIndex` = ?', array($device['device_id'], $lldp['lldpRemLocalPortNum']));
            $remote_device_id = dbFetchCell('SELECT `device_id` FROM `devices` WHERE `sysName` = ? OR `hostname` = ?', array($lldp['lldpRemSysName'], $lldp['lldpRemSysName']));

            if (!$remote_device_id && is_valid_hostname($lldp['lldpRemSysName'])) {
                $remote_device_id = discover_new_device($lldp['lldpRemSysName'], $device, 'LLDP', $interface);
            }

            if ($remote_device_id) {
                $if             = $lldp['lldpRemPortDesc'];
                $id             = $lldp['lldpRemPortId'];
                $remote_port_id = dbFetchCell('SELECT `port_id` FROM `ports` WHERE (`ifDescr` = ? OR `ifName` = ? OR `ifDescr` = ? OR `ifName` = ?) AND `device_id` = ?', array($if, $if, $id, $id, $remote_device_id));
            }
            else {
                $remote_port_id = '0';
            }

            if (is_numeric($interface['port_id']) && isset($lldp['lldpRemSysName']) && isset($lldp['lldpRemPortId'])) {
                discover_link($interface['port_id'], 'lldp', $remote_port_id, $lldp['lldpRemSysName'], $lldp['lldpRemPortId'], null, $lldp['lldpRemSysDesc'], $device['device_id'], $remote_device_id);
            }
        }//end foreach
    }//end if

} elseif ($config['autodiscovery']['xdp'] === true) {
    
    echo ' LLDP-MIB: ';
    $lldp_array  = snmpwalk_cache_threepart_oid($device, 'lldpRemoteSystemsData', array(), 'LLDP-MIB');
    d_echo($lldp_array);
    $dot1d_array = snmpwalk_cache_oid($device, 'dot1dBasePortIfIndex', array(), 'BRIDGE-MIB');
    d_echo($dot1d_array);
    if ($lldp_array) {
        $lldp_links = '';
        foreach (array_keys($lldp_array) as $key) {
            $lldp_if_array = $lldp_array[$key];
            d_echo($lldp_if_array);
            foreach (array_keys($lldp_if_array) as $entry_key) {
                if (is_numeric($dot1d_array[$entry_key]['dot1dBasePortIfIndex'])) {
                    $ifIndex = $dot1d_array[$entry_key]['dot1dBasePortIfIndex'];
                }
                else {
                    $ifIndex = $entry_key;
                }

                $interface     = dbFetchRow('SELECT * FROM `ports` WHERE `device_id` = ? AND `ifIndex` = ?', array($device['device_id'], $ifIndex));
                $lldp_instance = $lldp_if_array[$entry_key];
                d_echo($lldp_instance);
                foreach (array_keys($lldp_instance) as $entry_instance) {
                    $lldp             = $lldp_instance[$entry_instance];
                    $remote_device_id = dbFetchCell('SELECT `device_id` FROM `devices` WHERE `sysName` = ? OR `hostname` = ?', array($lldp['lldpRemSysName'], $lldp['lldpRemSysName']));

                    if (!$remote_device_id && is_valid_hostname($lldp['lldpRemSysName'])) {
                        $remote_device_id = discover_new_device($lldp['lldpRemSysName'], $device, 'LLDP', $interface);
                    }
                    // normalize MAC address if present
                    if ($lldp['lldpRemChassisIdSubtype'] == 'macAddress') {
                        $remote_mac_address = str_replace(array(' ', ':', '-'), '', strtolower($lldp['lldpRemChassisId']));
                    }
                    // get remote device hostname from db by MAC address and replace lldpRemSysName if absent
                    if (!$remote_device_id && $remote_mac_address) {
                        $remote_device_id = dbFetchCell('SELECT `device_id` FROM `ports` WHERE ifPhysAddress = ? AND `deleted` = ?', array($remote_mac_address, '0'));
                        if ($remote_device_id) {
                            $remote_device_hostname = dbFetchRow('SELECT `hostname` FROM `devices` WHERE `device_id` = ?', array($remote_device_id));
                        }    
                        if ($remote_device_hostname['hostname']) {
                            $lldp['lldpRemSysName'] = $remote_device_hostname['hostname'];
                        }
                    }
                    if ($remote_device_id) {
                        $if             = $lldp['lldpRemPortDesc'];
                        $id             = $lldp['lldpRemPortId'];
                        $remote_port_id = dbFetchCell('SELECT `port_id` FROM `ports` WHERE (`ifDescr` = ? OR `ifName` = ? OR `ifDescr` = ? OR `ifName` = ? OR `ifPhysAddress` = ?) AND `device_id` = ?', array($if, $if, $id, $id, $remote_mac_address, $remote_device_id));
                    }
                    else {
                        $remote_port_id = '0';
                    }

                    if (is_numeric($interface['port_id']) && isset($lldp['lldpRemSysName']) && isset($lldp['lldpRemPortId'])) {
                        discover_link($interface['port_id'], 'lldp', $remote_port_id, $lldp['lldpRemSysName'], $lldp['lldpRemPortId'], null, $lldp['lldpRemSysDesc'], $device['device_id'], $remote_device_id);
                    }
                }//end foreach
            }//end foreach
        }//end foreach
    }//end if
}//end elseif

echo ' OSPF Discovery: ';

if ($config['autodiscovery']['ospf'] === true) {
    echo "enabled\n";
    foreach (dbFetchRows('SELECT DISTINCT(`ospfNbrIpAddr`),`device_id` FROM `ospf_nbrs` WHERE `device_id`=?', array($device['device_id'])) as $nbr) {
        $ip = $nbr['ospfNbrIpAddr'];
        if (match_network($config['autodiscovery']['nets-exclude'], $ip)) {
            echo 'x';
            continue;
        }

        if (!match_network($config['nets'], $ip)) {
            echo 'i';
            continue;
        }

        $name             = gethostbyaddr($ip);
        $remote_device_id = discover_new_device($name, $device, 'OSPF');
    }
}
else {
    echo "disabled\n";
}

d_echo($link_exists);

$sql = "SELECT * FROM `links` AS L, `ports` AS I WHERE L.local_port_id = I.port_id AND I.device_id = '".$device['device_id']."'";
foreach (dbFetchRows($sql) as $test) {
    $local_port_id   = $test['local_port_id'];
    $remote_hostname = $test['remote_hostname'];
    $remote_port     = $test['remote_port'];
    d_echo("$local_port_id -> $remote_hostname -> $remote_port \n");

    if (!$link_exists[$local_port_id][$remote_hostname][$remote_port]) {
        echo '-';
        $rows = dbDelete('links', '`id` = ?', array($test['id']));
        d_echo("$rows deleted ");
    }
}

unset($link_exists);
echo "\n";
