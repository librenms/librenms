<?php

echo 'Datacom';

$multiplier = 1;
$divisor = 100;
foreach ($pre_cache['datacom_oids'] as $index => $entry) {
    if (is_numeric(str_replace('dBm', '', $entry['ddTransceiversRxPower']))) {
        $oid = '.1.3.6.1.4.1.3709.3.5.201.1.28.1.1.4.' . $index;
        $descr = dbFetchCell('SELECT `ifDescr` FROM `ports` WHERE `ifIndex`= ? AND `device_id` = ?', [$index, $device['device_id']]) . ' Rx Power';
        /* Basic default values */
        $limit_low = -30;
        $warn_limit_low = -12.3;
        $limit = 2.5;
        $warn_limit = -3;
        $current = $entry['ddTransceiversRxPower'];
        $entPhysicalIndex = $index;
        $entPhysicalIndex_measured = 'ports';
        discover_sensor($valid['sensor'], 'dbm', $device, $oid, 'rx-' . $index, 'datacom', $descr, $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $current, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured);
    }

    if (is_numeric(str_replace('dBm', '', $entry['ddTransceiversTxPower']))) {
        $oid = '.1.3.6.1.4.1.3709.3.5.201.1.28.1.1.5.' . $index;
        $descr = dbFetchCell('SELECT `ifDescr` FROM `ports` WHERE `ifIndex`= ? AND `device_id` = ?', [$index, $device['device_id']]) . ' Tx Power';
        /* Basic default values */
        $limit_low = -30;
        $warn_limit_low = -12.3;
        $limit = 2.5;
        $warn_limit = -3;
        $current = $entry['ddTransceiversTxPower'];
        $entPhysicalIndex = $index;
        $entPhysicalIndex_measured = 'ports';
        discover_sensor($valid['sensor'], 'dbm', $device, $oid, 'tx-' . $index, 'datacom', $descr, $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $current, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured);
    }
}
