<?php

$multiplier = 1;
$divisor = 1000;
foreach ($pre_cache['aos7_oids'] as $index => $entry) {
    if (is_numeric($entry['ddmPortSupplyVoltage']) && $entry['ddmPortSupplyVoltage'] != 0) {
        $oid = '.1.3.6.1.4.1.6486.801.1.2.1.5.1.1.2.6.1.7.' . $index;
        $limit_low = ($entry['ddmPortSupplyVoltageLowAlarm'] / $divisor);
        $warn_limit_low = ($entry['ddmPortSupplyVoltageLowWarning'] / $divisor);
        $limit = ($entry['ddmPortSupplyVoltageHiAlarm'] / $divisor);
        $warn_limit = ($entry['ddmPortSupplyVoltageHiWarning'] / $divisor);
        $value = $entry['ddmPortSupplyVoltage'] / $divisor;
        $entPhysicalIndex = $index;
        $entPhysicalIndex_measured = 'ports';
        $port_descr = get_port_by_index_cache($device['device_id'], str_replace(['.1', '.2', '.3', '.4'], '', $index));
        $descr = $port_descr['ifName'] . ' TX Voltage';
        discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, 'aos7', $descr, $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $value, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured);
    }
}
