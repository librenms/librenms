<?php

$multiplier = 1;
$divisor    = 1000;
foreach ($pre_cache['aos7_oids'] as $index => $entry) {
    if (is_numeric($entry['ddmPortTxBiasCurrent']) && $entry['ddmPortTxBiasCurrent'] != 0) {
        $oid = '.1.3.6.1.4.1.6486.801.1.2.1.5.1.1.2.6.1.12.' . $index ;
        $limit_low = ($entry['ddmPortTxBiasCurrentLowAlarm']/$divisor);
        $warn_limit_low = ($entry['ddmPortTxBiasCurrentLowWarning']/$divisor);
        $limit = ($entry['ddmPortTxBiasCurrentHiAlarm']/$divisor);
        $warn_limit = ($entry['ddmPortTxBiasCurrentHiWarning']/$divisor);
        $value = $entry['ddmPortTxBiasCurrent']/$divisor;
        $entPhysicalIndex = $index;
        $entPhysicalIndex_measured = 'ports';
        $port_descr = get_port_by_index_cache($device['device_id'], str_replace(array('.1','.2','.3','.4'), '', $index));
        $descr = $port_descr['ifName'] . ' TX Bias';
        discover_sensor($valid['sensor'], 'current', $device, $oid, $index, 'aos7', $descr, $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $value, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured);
    }
}
