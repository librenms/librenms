<?php

/*
 * Try to discover any MEF Links
 */

/*
 * Variable to hold the discovered MEF Links.
 */

$mef_list = [];

/*
 * Fetch information about MEF Links.
 */

$oids = snmpwalk_cache_multi_oid($device, 'MefServiceEvcCfgEntry', $oids, 'MEF-UNI-EVC-MIB');

echo 'MEF : ';
foreach ($oids as $index => $entry) {
    $mefIdent = $entry['mefServiceEvcCfgIdentifier'];
    $mefType = $entry['mefServiceEvcCfgServiceType'];
    $mefMtu = $entry['mefServiceEvcCfgMtuSize'];
    $mefAdmState = $entry['mefServiceEvcCfgAdminState'];
    $mefRowState = $entry['mefServiceEvcCfgRowStatus'];

    /*
     * Coriant MEF-EVC is quite strange, MTU is sometime set to 0 setting it to "strange" default value
     * According to Coriant this should be fixed in Nov 2017.
     */
    if (($mefMtu == 0) && ($device['os'] == 'coriant')) {
        $mefMtu = 1600;
    }

    /*
     * Check if the MEF is already known for this host
     */
    if (dbFetchCell('SELECT COUNT(id) FROM `mefinfo` WHERE `device_id` = ? AND `mefID` = ?', [$device['device_id'], $index]) == 0) {
        $mefid = dbInsert(['device_id' => $device['device_id'], 'mefID' => $index, 'mefType' => $mefType, 'mefIdent' => $mefIdent, 'mefMTU' => $mefMtu, 'mefAdmState' => $mefAdmState, 'mefRowState' => $mefRowState], 'mefinfo');
        log_event('MEF link: ' . $mefIdent . ' (' . $index . ') Discovered', $device, 'system', 2);
        echo '+';
    } else {
        echo '.';
    }
    /*
     * Save the discovered MEF Link
     */
    $mef_list[] = $index;
}

/*
 * Get a list of all the known MEF Links for this host
 */

$sql = "SELECT id, mefID, mefIdent FROM mefinfo WHERE device_id = '" . $device['device_id'] . "'";
foreach (dbFetchRows($sql) as $db_mef) {
    /*
     * Delete the MEF Link that are removed from the host.
     */
    if (! in_array($db_mef['mefID'], $mef_list)) {
        dbDelete('mefinfo', '`id` = ?', [$db_mef['id']]);
        log_event('MEF link: ' . $db_mef['mefIdent'] . ' Removed', $device, 'system', 3);
        echo '-';
    }
}
/*
 * Finished MEF information
 */
unset($mef_list, $oids, $db_mef);
echo PHP_EOL;
