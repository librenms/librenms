<?php

echo 'IronWare ';
$oids = snmp_walk($device, 'snAgentTempSensorDescr', '-Osqn', 'FOUNDRY-SN-AGENT-MIB:FOUNDRY-SN-ROOT-MIB');
$oids = trim($oids);
$oids = str_replace('.1.3.6.1.4.1.1991.1.1.2.13.1.1.3.', '', $oids);
foreach (explode("\n", $oids) as $data) {
    $data = trim($data);
    if ($data != '') {
        list($oid)       = explode(' ', $data);
        $temperature_oid = ".1.3.6.1.4.1.1991.1.1.2.13.1.1.4.$oid";
        $descr_oid       = ".1.3.6.1.4.1.1991.1.1.2.13.1.1.3.$oid";
        $descr           = snmp_get($device, $descr_oid, '-Oqv', '');
        $temperature     = snmp_get($device, $temperature_oid, '-Oqv', '');
        if (!strstr($descr, 'No') && !strstr($temperature, 'No') && $descr != '' && $temperature != '0') {
            $descr = str_replace('"', '', $descr);
            $descr = str_replace('temperature', '', $descr);
            $descr = str_replace('temperature', '', $descr);
            $descr = str_replace('sensor', 'Sensor', $descr);
            $descr = str_replace('Line module', 'Slot', $descr);
            $descr = str_replace('Switch Fabric module', 'Fabric', $descr);
            $descr = str_replace('Active management module', 'Mgmt Module', $descr);
            $descr = str_replace('  ', ' ', $descr);
            $descr = trim($descr);

            $current = ($temperature / 2);

            discover_sensor($valid['sensor'], 'temperature', $device, $temperature_oid, $oid, 'ironware', $descr, '2', '1', null, null, null, null, $current);
        }
    }
}
