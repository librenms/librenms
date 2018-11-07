<?php

use LibreNMS\Util\IP;
if (!get_dev_attrib($device, 'poll_cisco-nac', 'true')) {
    echo "\nCisco-NAC\n";
    $ports_mapped = get_ports_mapped($device['device_id'], true);
    $ports = $ports_mapped['ports'];
    $PortAuthSessionEntry = snmpwalk_cache_oid($device, 'cafSessionEntry', array(), 'CISCO-AUTH-FRAMEWORK-MIB');
    $cafSessionMethodsInfoEntry = snmpwalk_cache_oid($device, 'cafSessionMethodsInfoEntry', array(), 'CISCO-AUTH-FRAMEWORK-MIB');
    foreach ($PortAuthSessionEntry as $PortAuthSessionEntryNAC => $PortAuthSessionEntryParameters) {
        $port_index_nac = substr($PortAuthSessionEntryNAC, 0, strpos($PortAuthSessionEntryNAC, "."));
        $port_auth_id_nac = strstr($PortAuthSessionEntryNAC, "'");
        $port_auth_id_nac = substr($port_auth_id_nac, 0, -1);
        $port_auth_id_nac = substr($port_auth_id_nac, 1);
        $CheckExistIndex = dbFetchRow('SELECT * FROM `ports_nac` WHERE `port_index` = ?', $port_index_nac);
        $CheckExistAuth = dbFetchRow('SELECT * FROM `ports_nac` WHERE `auth_id` = ?', $port_auth_id_nac);        
        $IPHextoDec = IP::fromHexString($PortAuthSessionEntryParameters['cafSessionClientAddress']);
        if ($CheckExistIndex['port_index'] != null) {
            echo "Port Index Was Found. Updating...\n";
            $IPHextoDec = IP::fromHexString($PortAuthSessionEntryParameters['cafSessionClientAddress']);
            dbUpdate(array(
                'auth_id' => $port_auth_id_nac,
                'port_index' => $port_index_nac,
                'PortAuthSessionMacAddress' => $PortAuthSessionEntryParameters['cafSessionClientMacAddress'],
                'PortAuthSessionIPAddress' => $IPHextoDec,
                'PortAuthSessionAuthzStatus' => $PortAuthSessionEntryParameters['cafSessionStatus'],
                'PortAuthSessionDomain' => $PortAuthSessionEntryParameters['cafSessionDomain'],
                'PortAuthSessionHostMode' => $PortAuthSessionEntryParameters['cafSessionAuthHostMode'],
                'PortAuthSessionUserName' => $PortAuthSessionEntryParameters['cafSessionAuthUserName'],
                'PortAuthSessionAuthzBy' => $PortAuthSessionEntryParameters['cafSessionAuthorizedBy'],
                'PortAuthSessionTimeOut' => $PortAuthSessionEntryParameters['cafSessionTimeout'],
                'PortAuthSessionTimeLeft' => $PortAuthSessionEntryParameters['cafSessionTimeLeft'],
                'device_id' => $device['device_id']
            ), 'ports_nac', '`port_index` = ?', array($CheckExistIndex['port_index']));
            foreach ($cafSessionMethodsInfoEntry as $cafSessionMethodsInfoEntryNAC => $cafSessionMethodsInfoEntryParameters) {
                $port_index_nac = substr($cafSessionMethodsInfoEntryNAC, 0, strpos($cafSessionMethodsInfoEntryNAC, "."));
                $port_method_nac = substr(strstr(substr(strstr($cafSessionMethodsInfoEntryNAC, "."), 1), "."), 1);
                $port_auth_id_nac = substr($cafSessionMethodsInfoEntryNAC, 0, strpos($cafSessionMethodsInfoEntryNAC, '.', strpos($cafSessionMethodsInfoEntryNAC, '.')+1));
                $port_auth_id_nac = strstr($port_auth_id_nac, '.');
                $port_auth_id_nac = substr($port_auth_id_nac, 1);
                dbQuery("UPDATE `ports_nac` SET `PortSessionMethod` = '".$port_method_nac."' WHERE `ports_nac`.`auth_id` = '".$port_auth_id_nac."' AND `ports_nac`.`port_index` = '".$port_index_nac."';");
                dbQuery("UPDATE `ports_nac` SET `PortAuthSessionAuthcStatus` = '".$cafSessionMethodsInfoEntryParameters['cafSessionMethodState']."' WHERE `ports_nac`.`auth_id` = '".$port_auth_id_nac."';");
            }
        }
        else {
            if ($CheckExistAuth == null) { 
                echo "Auth ID Not Found. Creating...\n";
                dbInsert(array('auth_id' => $port_auth_id_nac), 'ports_nac');
                $IPHextoDec = IP::fromHexString($PortAuthSessionEntryParameters['cafSessionClientAddress']);
                dbUpdate(array(
                    'port_index' => $port_index_nac,
                    'PortAuthSessionMacAddress' => $PortAuthSessionEntryParameters['cafSessionClientMacAddress'],
                    'PortAuthSessionIPAddress' => $IPHextoDec,
                    'PortAuthSessionAuthzStatus' => $PortAuthSessionEntryParameters['cafSessionStatus'],
                    'PortAuthSessionDomain' => $PortAuthSessionEntryParameters['cafSessionDomain'],
                    'PortAuthSessionHostMode' => $PortAuthSessionEntryParameters['cafSessionAuthHostMode'],
                    'PortAuthSessionUserName' => $PortAuthSessionEntryParameters['cafSessionAuthUserName'],
                    'PortAuthSessionAuthzBy' => $PortAuthSessionEntryParameters['cafSessionAuthorizedBy'],
                    'PortAuthSessionTimeOut' => $PortAuthSessionEntryParameters['cafSessionTimeout'],
                    'PortAuthSessionTimeLeft' => $PortAuthSessionEntryParameters['cafSessionTimeLeft'],
                    'device_id' => $device['device_id']
                ), 'ports_nac', '`auth_id` = ?', array($port_auth_id_nac));
                foreach ($cafSessionMethodsInfoEntry as $cafSessionMethodsInfoEntryNAC => $cafSessionMethodsInfoEntryParameters) {
                    $port_index_nac = substr($cafSessionMethodsInfoEntryNAC, 0, strpos($cafSessionMethodsInfoEntryNAC, "."));
                    $port_method_nac = substr(strstr(substr(strstr($cafSessionMethodsInfoEntryNAC, "."), 1), "."), 1);
                    $port_auth_id_nac = substr($cafSessionMethodsInfoEntryNAC, 0, strpos($cafSessionMethodsInfoEntryNAC, '.', strpos($cafSessionMethodsInfoEntryNAC, '.')+1));
                    $port_auth_id_nac = strstr($port_auth_id_nac, '.');
                    $port_auth_id_nac = substr($port_auth_id_nac, 1);
                    dbQuery("UPDATE `ports_nac` SET `PortSessionMethod` = '".$port_method_nac."' WHERE `ports_nac`.`auth_id` = '".$port_auth_id_nac."' AND `ports_nac`.`port_index` = '".$port_index_nac."';");
                    dbQuery("UPDATE `ports_nac` SET `PortAuthSessionAuthcStatus` = '".$cafSessionMethodsInfoEntryParameters['cafSessionMethodState']."' WHERE `ports_nac`.`auth_id` = '".$port_auth_id_nac."';");
                }
            }
            else {
                echo "Auth ID Found.";
            }    
        }
    }
    foreach ($ports_mapped['maps']['ifIndex'] as $ports_mapped_index => $ports_mapped_id) {
        dbQuery("UPDATE `ports_nac` SET `port_id` = '".$ports_mapped_id."' WHERE `ports_nac`.`port_index` = '".$ports_mapped_index."';");
    }
    foreach ($ports_mapped['maps']['ifName'] as $ports_mapped_desc => $ports_mapped_id) {
        dbQuery("UPDATE `ports_nac` SET `port_descr` = '".$ports_mapped_desc."' WHERE `ports_nac`.`port_id` = '".$ports_mapped_id."';");
    }
}
