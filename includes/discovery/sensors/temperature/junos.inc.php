<?php

echo 'JunOS ';
$oids = snmp_walk($device, '.1.3.6.1.4.1.2636.3.1.13.1.7', '-Osqn', 'JUNIPER-MIB', 'junos');
$oids = trim($oids);
foreach (explode("\n", $oids) as $data) {
    $data     = trim($data);
    $data = substr($data, 29);
    if ($data) {
        list($oid)       = explode(' ', $data);
        $temperature_oid = ".1.3.6.1.4.1.2636.3.1.13.1.7.$oid";
        $descr_oid       = ".1.3.6.1.4.1.2636.3.1.13.1.5.$oid";
        $descr           = snmp_get($device, $descr_oid, '-Oqv', 'JUNIPER-MIB', 'junos');
        $temperature     = snmp_get($device, $temperature_oid, '-Oqv', 'JUNIPER-MIB', 'junos');
        if (!strstr($descr, 'No') && !strstr($temperature, 'No') && $descr != '' && $temperature != '0') {
            $descr = str_replace('"', '', $descr);
            $descr = str_replace('temperature', '', $descr);
            $descr = str_replace('temperature', '', $descr);
            $descr = str_replace('sensor', '', $descr);
            $descr = trim($descr);

            discover_sensor($valid['sensor'], 'temperature', $device, $temperature_oid, $oid, 'junos', $descr, '1', '1', null, null, null, null, $temperature);
        }
    }
}

$multiplier = 1;
$divisor    = 1;
foreach ($pre_cache['junos_oids'] as $index => $entry) {
    if (is_numeric($entry['jnxDomCurrentModuleTemperature']) && $entry['jnxDomCurrentModuleTemperature'] != 0 && $entry['jnxDomCurrentModuleTemperatureLowAlarmThreshold']) {
        $oid = '.1.3.6.1.4.1.2636.3.60.1.1.1.1.8.'.$index;
        $descr = dbFetchCell('SELECT `ifDescr` FROM `ports` WHERE `ifIndex`= ? AND `device_id` = ?', array($index, $device['device_id'])) . ' Temperature';
        $limit_low = $entry['jnxDomCurrentModuleTemperatureLowAlarmThreshold']/$divisor;
        $warn_limit_low = $entry['jnxDomCurrentModuleTemperatureLowWarningThreshold']/$divisor;
        $limit = $entry['jnxDomCurrentModuleTemperatureHighAlarmThreshold']/$divisor;
        $warn_limit = $entry['jnxDomCurrentModuleTemperatureHighWarningThreshold']/$divisor;
        $current = $entry['jnxDomCurrentModuleTemperature'];
        $entPhysicalIndex = $index;
        $entPhysicalIndex_measured = 'ports';

        discover_sensor($valid['sensor'], 'temperature', $device, $oid, 'rx-'.$index, 'junos', $descr, $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $current, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured);
    }
}
foreach ($pre_cache['junos_ifoptics_oids'] as $index => $entry) {
    if (is_numeric($entry['jnxPMCurTemperature'])) {
        $oid = '.1.3.6.1.4.1.2636.3.71.1.2.1.1.39.'.$index;
        $interface = dbFetchCell('SELECT `ifDescr` FROM `ports` WHERE `ifIndex`= ? AND `device_id` = ?', array($index, $device['device_id']));
        $descr = $interface . ' Temperature';
        # create ifoptioc_aternative index et-0/0/0 eq 1.1.1.1
        $t = explode('/', $interface, 3);
        $t0 = explode('-', $t[0], 2);
        $t1 = $t0[1] + 1;
        $t2 = $t[1] + 1;
        $t3 = $t[2] + 1;
        $alt_index = '1.' . $t1 . '.' . $t2 . '.' . $t3;

        $limit_low = $pre_cache['junos_ifoptics2_oids'][$alt_index]['jnxModuleTempLowThresh']*$multiplier;
        $warn_limit_low = null;
        $limit = $pre_cache['junos_ifoptics2_oids'][$alt_index]['jnxModuleTempHighThresh']*$multiplier;
        $warn_limit = null;
        $current = $entry['jnxPMCurTemperature']*$multiplier;
        $entPhysicalIndex = $index;
        $entPhysicalIndex_measured = 'ports';
        discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, 'junos', $descr, $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $current, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured);
    }
}
