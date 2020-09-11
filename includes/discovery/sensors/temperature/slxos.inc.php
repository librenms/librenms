<?php
$oids = snmp_walk($device, '.1.3.6.1.4.1.1588.2.1.1.1.1.22.1.2', '-Osqn');
$oids = trim($oids);
foreach (explode("\n", $oids) as $data) {
    $data = trim($data);
    list($dataoid,$dataval) = explode(" ", $data);
    $oidparts = explode(".", $dataoid);
    $oididx = $oidparts[count($oidparts)-1];
    if ($data and $dataval == "1") {
        $value_oid = ".1.3.6.1.4.1.1588.2.1.1.1.1.22.1.4.".$oididx;
        $descr_oid = ".1.3.6.1.4.1.1588.2.1.1.1.1.22.1.5.".$oididx;
        $value = snmp_get($device, $value_oid, '-Oqv');
        $descr = snmp_get($device, $descr_oid, '-Oqv');
        if (!strstr($descr, 'No') and !strstr($value, 'No')) {
            $descr = str_replace('"', '', $descr);
            $descr = trim($descr);
            discover_sensor($valid['sensor'], 'temperature', $device, $value_oid, $oididx, 'slxos', $descr, '1', '1', null, null, '80', '100', $value);
        }
    }
}
