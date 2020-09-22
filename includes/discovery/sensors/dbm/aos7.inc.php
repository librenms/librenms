<?php

$multiplier = 1;
$divisor = 1000;
foreach ($pre_cache['aos7_oids'] as $index => $entry) {
    if (is_numeric($entry['ddmPortRxOpticalPower']) && $entry['ddmPortRxOpticalPower'] != 0 && $entry['ddmPortTxOutputPower'] != 0) {
        $oid = '.1.3.6.1.4.1.6486.801.1.2.1.5.1.1.2.6.1.22.' . $index;
//      $oidRx = snmp_get($device, $oid, '-Oqv', 'ddmRxOpticalPower', '/opt/librenms/mibs/nokia/aos7/ALCATEL-IND1-PORT-MIB');
        $limit_low = ($entry['ddmPortRxOpticalPowerLowAlarm'] / $divisor);
        $warn_limit_low = ($entry['ddmPortRxOpticalPowerLowWarning'] / $divisor);
        $limit = ($entry['ddmPortRxOpticalPowerHiAlarm'] / $divisor);
        $warn_limit = ($entry['ddmPortRxOpticalPowerHiWarningh'] / $divisor);
        $value = $entry['ddmPortRxOpticalPower'] / $divisor;
        $entPhysicalIndex = $index;
        $entPhysicalIndex_measured = 'ports';
        $port_descr = get_port_by_index_cache($device['device_id'], str_replace(['.1', '.2', '.3', '.4'], '', $index));
        $descr = $port_descr['ifName'] . ' RX Power';
        discover_sensor($valid['sensor'], 'dbm', $device, $oid, 'rx-' . $index, 'aos7', $descr, $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $value, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured);
    }
    if (is_numeric($entry['ddmPortTxOutputPower'])) {
        $oid = '.1.3.6.1.4.1.6486.801.1.2.1.5.1.1.2.6.1.17.' . $index;
//      $oidTx = snmp_get($device, $oid, '-Oqv', 'ddmTxOutputPower', '/opt/librenms/mibs/nokia/aos7/ALCATEL-IND1-PORT-MIB');
        $limit_low = ($entry['ddmPortTxOutputPowerLowAlarm'] / $divisor);
        $warn_limit_low = ($entry['ddmPortTxOutputPowerLowWarning'] / $divisor);
        $limit = ($entry['ddmPortTxOutputPowerHiAlarm'] / $divisor);
        $warn_limit = ($entry['ddmPortTxOutputPowerHiWarningh'] / $divisor);
        $value = $entry['ddmPortTxOutputPower'] / $divisor;
        $entPhysicalIndex = $index;
        $entPhysicalIndex_measured = 'ports';
        $port_descr = get_port_by_index_cache($device['device_id'], str_replace(['.1', '.2', '.3', '.4'], '', $index));
        $descr = $port_descr['ifName'] . ' TX Power';
        discover_sensor($valid['sensor'], 'dbm', $device, $oid, 'tx-' . $index, 'aos7', $descr, $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $value, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured);
    }
}
