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
        $port_auth_domain = $PortAuthSessionEntryParameters['cafSessionDomain'];
        $IPHextoDec = IP::fromHexString($PortAuthSessionEntryParameters['cafSessionClientAddress']);
        $CheckExistIndex = dbFetchRow('SELECT port_index FROM `ports_nac` WHERE `port_index` = ?', $port_index_nac);
        $CheckExistAuth = dbFetchRow('SELECT auth_id FROM `ports_nac` WHERE `auth_id` = ?', $port_auth_id_nac);
        $CheckExistDomain = dbFetchRow('SELECT PortAuthSessionDomain FROM `ports_nac` WHERE `PortAuthSessionDomain` = ? AND `port_index` = ?', array($port_auth_domain, $port_index_nac));
        if ($CheckExistAuth['auth_id'] == $port_auth_id_nac) {
            echo "Auth ID Found.\n";
        } 
        elseif ($CheckExistAuth['auth_id'] == null and $CheckExistIndex['port_index'] == null) {
            echo "Auth ID and Interface Index not found. Creating...\n";
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
        elseif (($CheckExistIndex['port_index'] == $port_index_nac ) && ($PortAuthSessionEntryParameters['cafSessionDomain'] == $CheckExistDomain['PortAuthSessionDomain'])) {
            echo "Port Index and Domaion Were Found. Updating...\n";
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
            ),'ports_nac', '`port_index` = ? AND `PortAuthSessionDomain` = ?' ,  array($port_index_nac, $PortAuthSessionEntryParameters['cafSessionDomain']));
            foreach ($cafSessionMethodsInfoEntry as $cafSessionMethodsInfoEntryNAC => $cafSessionMethodsInfoEntryParameters) {
                $port_index_nac = substr($cafSessionMethodsInfoEntryNAC, 0, strpos($cafSessionMethodsInfoEntryNAC, "."));
                $port_method_nac = substr(strstr(substr(strstr($cafSessionMethodsInfoEntryNAC, "."), 1), "."), 1);
                $port_auth_id_nac = substr($cafSessionMethodsInfoEntryNAC, 0, strpos($cafSessionMethodsInfoEntryNAC, '.', strpos($cafSessionMethodsInfoEntryNAC, '.')+1));
                $port_auth_id_nac = strstr($port_auth_id_nac, '.');
                $port_auth_id_nac = substr($port_auth_id_nac, 1);
                $port_auth_id_nac = substr($cafSessionMethodsInfoEntryNAC, 0, strpos($cafSessionMethodsInfoEntryNAC, '.', strpos($cafSessionMethodsInfoEntryNAC, '.')+1));
                dbQuery("UPDATE `ports_nac` SET `PortSessionMethod` = '".$port_method_nac."' WHERE `ports_nac`.`auth_id` = '".$port_auth_id_nac."' AND `ports_nac`.`port_index` = '".$port_index_nac."';");
                dbQuery("UPDATE `ports_nac` SET `PortAuthSessionAuthcStatus` = '".$cafSessionMethodsInfoEntryParameters['cafSessionMethodState']."' WHERE `ports_nac`.`auth_id` = '".$port_auth_id_nac."';");
            }
        } elseif (($CheckExistAuth['auth_id'] == null) && ($CheckExistIndex['port_index'] == $port_index_nac) && ($CheckExistDomain['PortAuthSessionDomain'] == null)) {
            echo "Auth ID not found. Creating...\n";
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
            echo 'Nothing';
        }
        unset($CheckExistIndex, $CheckExistAuth, $CheckExistDomain);
    }
    foreach ($ports_mapped['maps']['ifIndex'] as $ports_mapped_index => $ports_mapped_id) {
        dbQuery("UPDATE `ports_nac` SET `port_id` = '".$ports_mapped_id."' WHERE `ports_nac`.`port_index` = '".$ports_mapped_index."';");
    }
    foreach ($ports_mapped['maps']['ifName'] as $ports_mapped_desc => $ports_mapped_id) {
        dbQuery("UPDATE `ports_nac` SET `port_descr` = '".$ports_mapped_desc."' WHERE `ports_nac`.`port_id` = '".$ports_mapped_id."';");
    }
}
