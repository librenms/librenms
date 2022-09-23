<?php

echo 'MEF Links: ';

/*
 * Get a list of all the known MEF Links for this host.
 */

$db_info_list = dbFetchRows('SELECT id, mefID, mefType, mefIdent, mefMTU, mefAdmState, mefRowState FROM mefinfo WHERE device_id = ?', [$device['device_id']]);

if (! empty($db_info_list)) {
    $current_mefinfo = snmpwalk_cache_multi_oid($device, 'MefServiceEvcCfgEntry', [], 'MEF-UNI-EVC-MIB');

    foreach ($db_info_list as $db_info) {
        $mef_info = [];

        $mef_info['mefType'] = $current_mefinfo[$db_info['mefID']]['mefServiceEvcCfgServiceType'];
        $mef_info['mefIdent'] = $current_mefinfo[$db_info['mefID']]['mefServiceEvcCfgIdentifier'];
        $mef_info['mefMTU'] = $current_mefinfo[$db_info['mefID']]['mefServiceEvcCfgMtuSize'];
        $mef_info['mefAdmState'] = $current_mefinfo[$db_info['mefID']]['mefServiceEvcCfgAdminState'];
        $mef_info['mefRowState'] = $current_mefinfo[$db_info['mefID']]['mefServiceEvcCfgRowStatus'];

        /*
         * Coriant MEF-EVC is quite strange, MTU is sometime set to 0 so we can set it into 1600 instead
         * According to Coriant this should be fixed in Nov 2017
         */
        if (($mef_info['mefMTU'] == 0) && ($device['os'] == 'coriant')) {
            $mef_info['mefMTU'] = 1600;
        }

        /*
         * Process all the MEF properties.
         */
        foreach ($mef_info as $property => $value) {
            /*
             * Check the property for any modifications.
             */
            if ($mef_info[$property] != $db_info[$property]) {
                // FIXME - this should loop building a query and then run the query after the loop (bad geert!)
                dbUpdate([$property => $mef_info[$property]], 'mefinfo', '`id` = ?', [$db_info['id']]);
                if ($db_info['mefIdent'] != null) {
                    log_event('MEF Link : ' . $db_info['mefIdent'] . ' (' . preg_replace('/^mef/', '', $db_info[$property]) . ') -> ' . $mef_info[$property], $device);
                }
            }
        }
    }//end foreach
}
/*
 * Finished discovering MEF Links information.
 */

unset($db_info_list, $current_mefinfo);
echo PHP_EOL;
