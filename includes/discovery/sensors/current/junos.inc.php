<?php

if ($device['os'] == 'junos' || $device['os_group'] == 'junos') {
    echo 'JunOS ';

    $multiplier = 1;
    $divisor    = 1000000;
    foreach ($junos_oids as $index => $entry) {
        if (is_numeric($entry['jnxDomCurrentTxLaserBiasCurrent'])) {
            $oid = '.1.3.6.1.4.1.2636.3.60.1.1.1.1.6.'.$index;
            $descr = dbFetchCell('SELECT `ifDescr` FROM `ports` WHERE `ifIndex`= ? AND `device_id` = ?', array($index, $device['device_id'])) . ' Rx Current';
            $limit_low = $entry['jnxDomCurrentTxLaserBiasCurrentLowAlarmThreshold']/$divisor;
            $warn_limit_low = $entry['jnxDomCurrentTxLaserBiasCurrentLowWarningThreshold']/$divisor;
            $limit = $entry['jnxDomCurrentTxLaserBiasCurrentHighAlarmThreshold']/$divisor;
            $warn_limit = $entry['jnxDomCurrentTxLaserBiasCurrentHighWarningThreshold']/$divisor;
            $current = $entry['jnxDomCurrentTxLaserBiasCurrent'];
            $entPhysicalIndex = $index;
            $entPhysicalIndex_measured = 'ports';
            discover_sensor($valid['sensor'], 'current', $device, $oid, 'rx-'.$index, 'junos', $descr, $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $current, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured);
        }

    }

}
