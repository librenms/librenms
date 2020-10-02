<?php

$multiplier = 1;
$divisor = 1000;
foreach ($pre_cache['aos7_oids'] as $index => $entry) {
    if (is_numeric($entry['ddmPortTemperature']) && $entry['ddmPortTemperature'] != 0) {
        $oid = '.1.3.6.1.4.1.6486.801.1.2.1.5.1.1.2.6.1.2.' . $index;
        $limit_low = $entry['ddmPortTempLowAlarm'] / $divisor;
        $warn_limit_low = $entry['ddmPortTempLowWarning'] / $divisor;
        $limit = $entry['ddmPortTempHiAlarm'] / $divisor;
        $warn_limit = $entry['ddmPortTempHiWarning'] / $divisor;
        $value = $entry['ddmPortTemperature'] / $divisor;
        $curent = $entry['ddmPortTemperature'];
        $entPhysicalIndex = $index;
        $entPhysicalIndex_measured = 'ports';
        $port_descr = get_port_by_index_cache($device['device_id'], str_replace(['.1', '.2', '.3', '.4'], '', $index));
        $descr = $port_descr['ifName'] . ' DDM Temperature';
        discover_sensor($valid['sensor'], 'temperature', $device, $oid, 'rx-' . $index, 'aos7', $descr, $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $value, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured);
    }
}
